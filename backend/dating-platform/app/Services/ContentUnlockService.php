<?php

declare(strict_types=1);

namespace App\Services;

use App\Album;
use App\ContentUnlock;
use App\ImageGet;
use App\Pack;
use App\Settings;
use App\User;
use Illuminate\Support\Collection;

/**
 * Client's request (2026-09-21/22): admin/editor prices a photo or album; a client pays once
 * (credits or real money, per the CONTENT_UNLOCK_PRICE_MODE setting) to see it, permanently.
 *
 * Mirrors BoostController's split exactly, generalized to any priced item instead of one fixed
 * feature: grant() is the instant, credits-mode effect (and also what the real-money path calls
 * once payment is confirmed - see FeatureActivationRegistry); checkout() is the money-mode
 * counterpart that reuses the existing multi-provider /payments flow via a hidden Pack, same as
 * BoostController::checkout().
 */
final class ContentUnlockService
{
    /** Same provider list/reasoning as BoostController::SUPPORTED_PROVIDERS. */
    private const SUPPORTED_PROVIDERS = ['CENTRALPAY_ACTIVE', 'PAYPAL_ACTIVE', 'CCBILL_ACTIVE', 'WIRE-TRANSFER_ACTIVE'];

    public function enabled(): bool
    {
        return Settings::where('name', 'CONTENT_UNLOCK_ENABLED')->value('value') !== 'no';
    }

    public function priceMode(): string
    {
        return Settings::where('name', 'CONTENT_UNLOCK_PRICE_MODE')->value('value') === 'money' ? 'money' : 'credits';
    }

    public function findImage(int $id): ImageGet
    {
        return ImageGet::where('id', $id)->firstOrFail();
    }

    public function findAlbum(int $id): Album
    {
        return Album::where('id', $id)->firstOrFail();
    }

    /**
     * Whichever of the item's two independent prices matches the currently active mode - see
     * ImageGet::effectivePrice()/Album::effectivePrice() (money = the EUR `price` column,
     * credits = the plain-integer `price_credits` column; never one derived from the other).
     */
    public function priceOf(string $type, int $id): float
    {
        $item = $type === ContentUnlock::TYPE_IMAGE ? $this->findImage($id) : $this->findAlbum($id);

        return $item->effectivePrice();
    }

    /**
     * Credits-mode purchase - instant, mirrors BoostController::activate()'s bare credits
     * decrement. Returns an error code string on failure, or null on success, rather than
     * throwing - the controller turns either into the JSON response the unlock button expects.
     */
    public function purchaseWithCredits(User $user, string $type, int $id): ?string
    {
        $price = $this->priceOf($type, $id);

        if ($price <= 0) {
            return 'not_priced';
        }

        if ((float) $user->credits < $price) {
            return 'not_enough_credits';
        }

        $user->credits = $user->credits - $price;
        $user->save();

        $this->grant($user, $type, $id, 'credits', $price);

        return null;
    }

    /**
     * The real-money counterpart to purchaseWithCredits() - creates a hidden Pack (same pattern
     * as BoostController::createHiddenPack()) tagged with a feature_key encoding exactly which
     * photo/album this is for, then sends the browser through the normal /payments flow. The
     * actual unlock only happens once that payment is confirmed - see FeatureActivationRegistry,
     * which parses the feature_key back apart and calls grant() below.
     *
     * @return array{pack: Pack, activeProviders: Collection<int, string>}|null null when no
     *               supported provider is currently active (caller shows an error instead).
     */
    public function startMoneyCheckout(string $type, int $id): ?array
    {
        $activeProviders = Settings::whereIn('name', self::SUPPORTED_PROVIDERS)
            ->where('value', 'yes')
            ->pluck('name');

        if ($activeProviders->isEmpty()) {
            return null;
        }

        $price = $this->priceOf($type, $id);
        $label = $type === ContentUnlock::TYPE_IMAGE ? 'Poza' : 'Album';

        $pack = new Pack();
        $pack->custom = 1;
        $pack->name = $label . ' #' . $id;
        $pack->price = $price;
        $pack->credits = 0;
        $pack->currency = 'EUR';
        $pack->featured = 0;
        $pack->type = 'credits';
        $pack->duration = 0;
        $pack->feature_key = 'unlock_' . $type . ':' . $id;
        $pack->feature_duration_minutes = null;
        $pack->save();

        return ['pack' => $pack, 'activeProviders' => $activeProviders];
    }

    /**
     * The actual effect of a purchase, however it was paid for - a single ContentUnlock row.
     * `if_version`-style idempotency isn't needed here the way it matters for the artifact
     * database (there's no concurrent-edit race on "did this user already buy this"): the
     * unique(user_id, unlockable_type, unlockable_id) index on content_unlocks just makes a
     * second grant() call (e.g. a webhook retry) a harmless no-op via updateOrInsert.
     */
    public function grant(User $user, string $type, int $id, string $method, ?float $pricePaid = null): bool
    {
        ContentUnlock::query()->updateOrInsert(
            ['user_id' => $user->id, 'unlockable_type' => $type, 'unlockable_id' => $id],
            ['method' => $method, 'price_paid' => $pricePaid ?? $this->priceOf($type, $id), 'created_at' => now()]
        );

        return true;
    }
}
