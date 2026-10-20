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
 * Matches incoming unseen messages against "waiting" recipients by sender address, marks a
 * match as replied, and marks the IMAP message itself \Seen so it isn't reprocessed on the
 * next run - this script never needs its own "already processed" tracking table as a result.
 * Only ever marks messages \Seen, never deletes/moves them - the admin can still read every
 * reply normally in the Gmail inbox.
 */
class ImapReplyCheckerService
{
    /**
     * @return int number of replies matched and recorded
     */
    public function checkForReplies(EmailOutreachAccount $account): int
    {
        $mailbox = '{'.$account->imap_host.':'.$account->imap_port.'/imap/ssl}INBOX';
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

                // Only ever a "waiting" recipient on one of THIS account's own campaigns can be
                // the thing a reply advances - anything else (a genuinely new, unrelated email
                // landing in this inbox) is left completely alone, untouched and still unseen,
                // for the admin to read normally.
                $recipient = EmailOutreachRecipient::where('email', $fromEmail)
                    ->where('status', 'waiting')
                    ->whereHas('campaign', function ($q) use ($account) {
                        $q->where('account_id', $account->id);
                    })
                    ->orderBy('last_sent_at', 'desc')
                    ->first();

                if (!$recipient) {
                    continue;
                }

                $recipient->status = 'replied';
                $recipient->last_engaged_at = now();
                $recipient->save();

                EmailOutreachLog::create([
                    'recipient_id' => $recipient->id,
                    'event' => 'replied',
                    'step' => $recipient->current_step,
                ]);

                imap_setflag_full($connection, (string) $messageId, '\\Seen');
                $matched++;
            }
        } finally {
            imap_close($connection);
        }

        return $matched;
    }
}
