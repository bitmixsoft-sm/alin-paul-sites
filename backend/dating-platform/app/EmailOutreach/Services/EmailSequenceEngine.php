<?php

namespace App\EmailOutreach\Services;

use App\EmailOutreach\Models\EmailOutreachCampaign;
use App\EmailOutreach\Models\EmailOutreachLog;
use App\EmailOutreach\Models\EmailOutreachRecipient;
use Carbon\Carbon;

/**
 * The automation core (client's request, 2026-10-20) - run periodically by
 * App\EmailOutreach\Console\RunEmailOutreachCommand (see artisan schedule). One pass:
 *
 *  1. "pending" recipients (never contacted) -> send phrase #1.
 *  2. "waiting" recipients who opened the last phrase (no reply - IMAP replies are handled
 *     separately by ImapReplyCheckerService, which already moves them straight to "replied")
 *     since it was sent -> send the next phrase, if one exists.
 *  3. "waiting" recipients with zero engagement for campaign.abandon_after_days since the last
 *     phrase was sent -> "abandoned", abandoned_at = now.
 *  4. "abandoned" recipients whose abandon_after_days has elapsed AND the campaign allows a
 *     restart -> back to "pending" (step reset to 0), picked up by step 1 on THIS SAME pass.
 *
 * "replied" and "manual" recipients are never touched here - see
 * AdminEmailOutreachController@continueAutomation for the admin's explicit way to put a
 * "replied" recipient back into the sequence after reading what they wrote.
 *
 * Respects account.daily_send_limit across ALL of that account's campaigns combined (it's one
 * mailbox, Google doesn't care which campaign a send belongs to).
 */
class EmailSequenceEngine
{
    /** @var SmtpMailerService */
    private $mailer;

    /** @var EmailTranslationService */
    private $translator;

    // Plain PHP 5.6-style constructor on purpose - PHP 8's constructor property promotion
    // would break this on the client's PHP 7.1.3 sites.
    public function __construct(SmtpMailerService $mailer, EmailTranslationService $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @return int how many emails were actually sent during this pass (used by the admin's
     * manual "Trimite acum" button to report back what happened).
     */
    public function processCampaign(EmailOutreachCampaign $campaign): int
    {
        $sent = 0;

        $account = $campaign->account;
        if (!$account || !$campaign->active) {
            return $sent;
        }

        $sentToday = EmailOutreachLog::where('event', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->whereHas('recipient.campaign', function ($q) use ($account) {
                $q->where('account_id', $account->id);
            })
            ->count();
        $remaining = max(0, $account->daily_send_limit - $sentToday);

        $this->restartAbandoned($campaign);
        $this->abandonStale($campaign);

        if ($remaining <= 0) {
            return $sent;
        }

        $maxStep = $campaign->maxStep();
        if ($maxStep < 1) {
            return $sent; // no phrases authored yet
        }

        // Pending first (first impressions matter more than a 2nd/3rd phrase to someone
        // already engaged), then engaged waiters.
        $pending = $campaign->recipients()->where('status', 'pending')->limit($remaining)->get();
        foreach ($pending as $recipient) {
            if ($remaining-- <= 0) {
                break;
            }
            if ($this->sendStep($campaign, $recipient, 1)) {
                $sent++;
            }
        }

        if ($remaining <= 0) {
            return $sent;
        }

        $engagedWaiting = $campaign->recipients()
            ->where('status', 'waiting')
            ->where('current_step', '<', $maxStep)
            ->whereNotNull('last_engaged_at')
            ->whereColumn('last_engaged_at', '>', 'last_sent_at')
            ->limit($remaining)
            ->get();
        foreach ($engagedWaiting as $recipient) {
            if ($remaining-- <= 0) {
                break;
            }
            if ($this->sendStep($campaign, $recipient, $recipient->current_step + 1)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function sendStep(EmailOutreachCampaign $campaign, EmailOutreachRecipient $recipient, int $step): bool
    {
        $message = $campaign->messages()->where('step_order', $step)->first();
        if (!$message) {
            return false;
        }

        $body = $message->body;
        if ($recipient->user_id && $recipient->user && $recipient->user->firstname) {
            $body = str_replace('{nume}', $recipient->user->firstname, $body);
        }

        $targetLanguage = $recipient->language;
        if ($targetLanguage && $targetLanguage !== $campaign->base_language) {
            $body = $this->translator->translate($campaign->account, $body, $targetLanguage);
        }

        // 'public' disk now (see AdminEmailOutreachController::storeMessage) so the admin can
        // preview the image as a thumbnail - the actual file sits under storage/app/public/.
        $imagePath = $message->image_path ? storage_path('app/public/'.$message->image_path) : null;
        if ($imagePath) {
            $body .= '<br><img src="cid:outreach-image" style="max-width:100%;">';
        }
        // Tracking pixel - see routes for the matching GET /email-outreach/track/{token}.gif.
        $body .= '<img src="'.url('/email-outreach/track/'.$recipient->open_token.'.gif').'" width="1" height="1" style="display:none" alt="">';

        $subject = $recipient->current_step === 0 ? 'TrovaMequi' : 'TrovaMequi';

        $result = $this->mailer->send($campaign->account, $recipient->email, $subject, $body, $imagePath);

        if ($result['success']) {
            $recipient->status = 'waiting';
            $recipient->current_step = $step;
            $recipient->last_sent_at = now();
            $recipient->save();

            EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'sent', 'step' => $step]);
            return true;
        }

        \Log::error('[EmailSequenceEngine] send failed for recipient '.$recipient->id.': '.$result['error']);
        EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'bounced', 'step' => $step]);
        return false;
    }

    private function abandonStale(EmailOutreachCampaign $campaign): void
    {
        $cutoff = now()->subDays($campaign->abandon_after_days);

        $campaign->recipients()
            ->where('status', 'waiting')
            ->where('last_sent_at', '<', $cutoff)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_engaged_at')->orWhere('last_engaged_at', '<', $cutoff);
            })
            ->update(['status' => 'abandoned', 'abandoned_at' => now()]);
    }

    private function restartAbandoned(EmailOutreachCampaign $campaign): void
    {
        if (!$campaign->restart_after_abandon) {
            return;
        }

        $cutoff = now()->subDays($campaign->abandon_after_days);

        $toRestart = $campaign->recipients()
            ->where('status', 'abandoned')
            ->where('abandoned_at', '<', $cutoff)
            ->get();

        foreach ($toRestart as $recipient) {
            $recipient->status = 'pending';
            $recipient->current_step = 0;
            $recipient->last_sent_at = null;
            $recipient->last_engaged_at = null;
            $recipient->abandoned_at = null;
            $recipient->save();

            EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'restarted']);
        }
    }
}
