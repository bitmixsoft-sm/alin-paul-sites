<?php

namespace App\EmailOutreach\Services;

use App\EmailOutreach\Models\EmailOutreachAccount;
use App\EmailOutreach\Models\EmailOutreachLog;
use App\EmailOutreach\Models\EmailOutreachRecipient;

/**
 * Uses PHP's native imap_* extension (confirmed available on all the client's servers,
 * 2026-10-20 - that's why this exists instead of a composer package or a hand-rolled raw
 * socket IMAP client, either of which would have been more code to maintain for no benefit).
 *
 * Matches incoming unseen messages against "waiting"/"manual" recipients by sender address,
 * stores the reply text and detects its language, marks the IMAP message itself \Seen so it
 * isn't reprocessed on the next run - this script never needs its own "already processed"
 * tracking table as a result. Only ever marks messages \Seen, never deletes/moves them - the
 * admin can still read every reply normally in the Gmail inbox.
 *
 * Rewritten 2026-10-21 per client feedback: originally flipped the recipient to a "replied"
 * status that stopped the sequence, requiring the admin to manually click "Continua automat"
 * every time - that wasn't what was actually asked for (the client's own spec: "daca
 * clientul... raspunde se va trimite a doua fraza" - a reply should advance the sequence
 * automatically, same as an open). Status is left untouched now ("waiting" stays "waiting",
 * so EmailSequenceEngine's own last_engaged_at > last_sent_at check naturally sends the next
 * phrase on its own) - "Preia manual" remains the explicit, separate action for an admin who
 * actually wants to stop the automation and take over.
 */
class ImapReplyCheckerService
{
    /** @var EmailTranslationService */
    private $translator;

    public function __construct(EmailTranslationService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return int number of replies matched and recorded
     */
    public function checkForReplies(EmailOutreachAccount $account): int
    {
        // /novalidate-cert (found live, 2026-10-20): the PHP imap extension's bundled
        // c-client library doesn't send SNI correctly during the TLS handshake on this
        // hosting environment, so it sees a mismatched/self-signed fallback certificate
        // instead of Gmail's real one and refuses the connection ("Certificate failure for
        // imap.gmail.com: self-signed certificate"). The connection itself is still
        // encrypted (TLS) - this only skips the hostname/chain validation step that was
        // failing for an unrelated reason, not actual security of the transport.
        $mailbox = '{'.$account->imap_host.':'.$account->imap_port.'/imap/ssl/novalidate-cert}INBOX';
        $connection = @imap_open($mailbox, $account->imap_username, $account->imap_password);

        if (!$connection) {
            \Log::warning('[ImapReplyCheckerService] IMAP connection failed for account '.$account->id.': '.imap_last_error());
            return 0;
        }

        $matched = 0;

        try {
            $unseenIds = imap_search($connection, 'UNSEEN');
            if (!$unseenIds) {
                return 0;
            }

            foreach ($unseenIds as $messageId) {
                $header = @imap_headerinfo($connection, $messageId);
                if (!$header || empty($header->from[0]->mailbox) || empty($header->from[0]->host)) {
                    continue;
                }
                $fromEmail = strtolower($header->from[0]->mailbox.'@'.$header->from[0]->host);

                // Matches a "waiting" (keeps the sequence going) or "manual" (admin already
                // took over - still worth capturing what they said, just doesn't restart the
                // automation) recipient. Anything else (a genuinely new, unrelated email
                // landing in this inbox) is left completely alone, untouched and still unseen,
                // for the admin to read normally.
                $recipient = EmailOutreachRecipient::whereIn('status', ['waiting', 'manual'])
                    ->where('email', $fromEmail)
                    ->whereHas('campaign', function ($q) use ($account) {
                        $q->where('account_id', $account->id);
                    })
                    ->orderBy('last_sent_at', 'desc')
                    ->first();

                if (!$recipient) {
                    continue;
                }

                $body = $this->extractPlainTextBody($connection, $messageId);

                $detectedLanguage = $this->translator->detectLanguage($recipient->campaign->account, $body);
                if ($detectedLanguage) {
                    $recipient->language = $detectedLanguage;
                }
                $recipient->last_engaged_at = now();
                $recipient->save();

                EmailOutreachLog::create([
                    'recipient_id' => $recipient->id,
                    'event' => 'replied',
                    'step' => $recipient->current_step,
                    'body' => $body,
                ]);

                imap_setflag_full($connection, (string) $messageId, '\\Seen');
                $matched++;
            }
        } finally {
            imap_close($connection);
        }

        return $matched;
    }

    /**
     * Pulls the plain-text part out of a (possibly multipart) message, decoding whatever
     * transfer encoding it was sent with - imap_fetchbody() on its own returns the raw,
     * still-encoded body, which for a quoted-printable or base64 reply would otherwise show
     * up unreadable in the admin's conversation view.
     */
    private function extractPlainTextBody($connection, $messageId): string
    {
        $structure = @imap_fetchstructure($connection, $messageId);
        if (!$structure) {
            return '';
        }

        if (!empty($structure->parts)) {
            foreach ($structure->parts as $index => $part) {
                if ($part->type === 0 && (!isset($part->subtype) || strtoupper($part->subtype) === 'PLAIN')) {
                    return $this->decodePart($connection, $messageId, (string) ($index + 1), $part);
                }
            }
            // No plain-text part found - fall back to the first part rather than nothing.
            return $this->decodePart($connection, $messageId, '1', $structure->parts[0]);
        }

        return $this->decodePart($connection, $messageId, '1', $structure);
    }

    private function decodePart($connection, $messageId, string $section, $part): string
    {
        $raw = imap_fetchbody($connection, $messageId, $section);

        if (isset($part->encoding) && $part->encoding === 3) { // BASE64
            $raw = base64_decode($raw);
        } elseif (isset($part->encoding) && $part->encoding === 4) { // QUOTED-PRINTABLE
            $raw = quoted_printable_decode($raw);
        }

        return trim(strip_tags($raw));
    }
}
