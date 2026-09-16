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
        switch ($featureKey) {
            case 'boost':
                $minutes = $durationMinutes ?? 45;
                $user->boosted_until = now()->addMinutes($minutes);
                $user->save();

                return true;

            default:
                Log::error('[FeatureActivationRegistry] Unknown feature_key on a paid order: ' . $featureKey, [
                    'user_id' => $user->id,
                ]);

                return false;
        }
    }
}
