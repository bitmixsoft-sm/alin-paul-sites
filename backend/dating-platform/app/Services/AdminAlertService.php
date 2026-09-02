<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Fire-and-forget email alert for operational failures an admin can't see any other
 * way (no SSH/log access day-to-day). Throttled per $key so a burst of user requests
 * hitting the same upstream outage sends one email instead of one per request.
 */
final class AdminAlertService
{
    public function notify(string $key, string $subject, string $body, int $throttleMinutes = 30): void
    {
        $recipient = trim((string) config('services.alerts.admin_email'));

        if ($recipient === '') {
            Log::warning('AdminAlertService: ADMIN_ALERT_EMAIL is not configured, skipping email alert.', [
                'subject' => $subject,
            ]);

            return;
        }

        $throttleCacheKey = 'admin_alert:' . $key;

        if (Cache::has($throttleCacheKey)) {
            return;
        }

        Cache::put($throttleCacheKey, true, now()->addMinutes($throttleMinutes));

        try {
            Mail::raw($body, function ($message) use ($recipient, $subject): void {
                $message->to($recipient)->subject('[trovamequi.me] ' . $subject);
            });
        } catch (Throwable $throwable) {
            Log::error('AdminAlertService: failed to send admin alert email.', [
                'subject' => $subject,
                'error' => $throwable->getMessage(),
            ]);
        }
    }
}
