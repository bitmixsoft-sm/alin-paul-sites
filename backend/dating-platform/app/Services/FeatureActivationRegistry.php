<?php

declare(strict_types=1);

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Log;

/**
 * The generic side of "pay a fixed amount of real money -> activate a feature for a duration"
 * (client's request, 2026-09-16, first used for Boost's real-money option). A hidden Pack
 * with a non-null feature_key (see the migration that added it, and
 * BoostController::createHiddenPack()) represents one of these purchases - it still flows
 * through the exact same payment machinery every real Pack purchase already uses (CentralPay,
 * PayPal, CCBill, Wire-transfer - see PaymentsController), just with credits: 0 and
 * type: 'credits' so that normal success handling is a harmless no-op instead of actually
 * assigning it to the user as their active package. Each payment success path checks
 * $pack->feature_key right after resolving the Pack, and calls activate() here instead of (or
 * alongside) the usual credit/User_Pack grant.
 *
 * Adding a new paid feature later (e.g. "Highlighted profile") means: pick a feature_key, add
 * a case here that does whatever that feature needs (set a column, insert a row, etc.), and
 * create its own hidden Pack the same way Boost does - no changes to the payment plumbing
 * itself.
 */
final class FeatureActivationRegistry
{
    /**
     * @return bool true if $featureKey was recognized and activated, false otherwise (logged,
     *               not thrown - the caller is a payment webhook, where the payment itself
     *               already succeeded regardless of whether the app-side effect works, so this
     *               deliberately doesn't blow up the webhook response over it)
     */
    public static function activate(string $featureKey, User $user, ?int $durationMinutes): bool
    {
        if ($featureKey === 'boost') {
            $minutes = $durationMinutes ?? 45;
            $user->boosted_until = now()->addMinutes($minutes);
            $user->save();

            return true;
        }

        // "unlock_image:123" / "unlock_album:45" - the priced photo/album feature (client's
        // request, 2026-09-21/22). Encodes the target id in the key itself (unlike 'boost',
        // which has exactly one meaning) since a purchase here is always for one specific photo
        // or album, not a flat feature toggle - see ContentUnlockService::checkout(), which
        // builds the hidden Pack's feature_key this way.
        if (str_starts_with($featureKey, 'unlock_image:') || str_starts_with($featureKey, 'unlock_album:')) {
            [$prefix, $id] = explode(':', $featureKey, 2);
            $type = $prefix === 'unlock_image' ? \App\ContentUnlock::TYPE_IMAGE : \App\ContentUnlock::TYPE_ALBUM;

            return app(\App\Services\ContentUnlockService::class)->grant($user, $type, (int) $id, 'money');
        }

        Log::error('[FeatureActivationRegistry] Unknown feature_key on a paid order: ' . $featureKey, [
            'user_id' => $user->id,
        ]);

        return false;
    }
}
