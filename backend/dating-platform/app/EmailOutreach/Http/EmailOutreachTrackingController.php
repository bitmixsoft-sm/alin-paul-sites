<?php

namespace App\EmailOutreach\Http;

use App\EmailOutreach\Models\EmailOutreachLog;
use App\EmailOutreach\Models\EmailOutreachRecipient;
use App\Http\Controllers\Controller;

/**
 * Public, unauthenticated route (see routes file) - this is the tracking-pixel endpoint
 * embedded in outgoing emails, loaded by the RECIPIENT's email client, never by a logged-in
 * admin. Kept in its own controller specifically so it never accidentally ends up behind
 * AdminEmailOutreachController's admin-only middleware.
 */
class EmailOutreachTrackingController extends Controller
{
    public function trackOpen($token)
    {
        $recipient = EmailOutreachRecipient::where('open_token', $token)->first();

        if ($recipient && $recipient->status === 'waiting') {
            // Guard against the same client re-fetching the pixel multiple times counting as
            // repeated engagement - only the first open after the last send matters.
            if (!$recipient->last_engaged_at || $recipient->last_engaged_at < $recipient->last_sent_at) {
                $recipient->last_engaged_at = now();
                $recipient->save();
                EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'opened', 'step' => $recipient->current_step]);
            }
        }

        // 1x1 transparent GIF, served regardless of whether a match was found - never reveal
        // tracking-validity via a different response (so the pixel can't be used to probe
        // which tokens are real).
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7');
        return response($pixel, 200)->header('Content-Type', 'image/gif');
    }
}
