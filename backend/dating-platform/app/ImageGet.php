<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImageGet extends Model
{
	protected $table = 'images';
    protected $guarded = [];

    /**
     * The price that actually applies right now - `price` (EUR) in money mode, `price_credits`
     * (a plain credits count) in credits mode. Two independent numbers, not one converted from
     * the other - same convention as Boost's BOOST_COST_CREDITS vs BOOST_PRICE_AMOUNT. Found the
     * hard way (2026-09-22): `users.credits` is a plain int column, so charging a EUR price like
     * 1.50 directly against it silently truncated to 1 credit.
     */
    public function effectivePrice(): float
    {
        return \App\Settings::where('name', 'CONTENT_UNLOCK_PRICE_MODE')->value('value') === 'money'
            ? (float) $this->price
            : (float) $this->price_credits;
    }

    /**
     * Whether this photo has an owner-set price in whichever mode is currently active - 0/null
     * (the default for every photo that existed before this feature, and any photo an
     * admin/editor never priced) means free for everyone, including an album it belongs to that
     * itself has a price (client's answer #4: photos left at 0 stay visible to everyone
     * regardless of the album's own price).
     */
    public function isPriced(): bool
    {
        return $this->effectivePrice() > 0;
    }

    /**
     * Free photos: always true. Priced photos: true for the uploader, any admin/editor
     * (client's answer #7), or anyone who has actually unlocked this specific photo (see
     * ContentUnlock) - checked by content_unlocks first (cheap, no extra query for the common
     * free-photo case), not by re-deriving it from an album unlock, since a photo can be priced
     * independently even inside an album (see AlbumUnlockService for how buying the album
     * grants this row too).
     */
    public function isViewableBy(?User $viewer): bool
    {
        if (! $this->isPriced()) {
            return true;
        }

        if ($viewer === null) {
            return false;
        }

        if ((int) $viewer->id === (int) $this->user_id || $viewer->isAdmin()) {
            return true;
        }

        return ContentUnlock::where('user_id', $viewer->id)
            ->where('unlockable_type', ContentUnlock::TYPE_IMAGE)
            ->where('unlockable_id', $this->id)
            ->exists();
    }

    /**
     * Same as isViewableBy(), but also grants access when $album itself is unlocked - buying a
     * whole album unlocks everything inside it, even a photo that independently carries its own
     * price (client's answer #4 expects admins to leave per-photo prices at 0 inside a priced
     * album, but nothing stops one from also being individually priced, and an album buyer
     * should not then hit a second paywall on a photo they already paid to see).
     */
    public function isViewableInAlbum(?User $viewer, Album $album): bool
    {
        return $this->isViewableBy($viewer) || $album->isUnlockedBy($viewer);
    }

    /**
     * displayName() variant for a photo rendered inside a specific album - see
     * isViewableInAlbum() for why the album's own unlock state also matters here.
     */
    public function displayNameInAlbum(Album $album): string
    {
        $viewer = Auth::check() ? Auth::user() : null;

        if ($this->isViewableInAlbum($viewer, $album)) {
            return $this->name;
        }

        if ($this->blurred_name === null || $this->blurred_name === '') {
            app(\App\Services\ImageBlurService::class)->ensureBlurredCopy($this);
        }

        return $this->blurred_name ?: '';
    }

    /**
     * The filename every view should render (`/storage/images/{{ $image->displayName() }}`) -
     * the blurred copy for a priced photo the current visitor (Auth::user(), or null when
     * signed out) hasn't unlocked, the real file otherwise. See the migration/ImageBlurService
     * docblocks for why this is a filename swap rather than a protected streaming route: the
     * real name is simply never sent to a browser that hasn't paid.
     */
    public function displayName(): string
    {
        if ($this->isViewableBy(Auth::check() ? Auth::user() : null)) {
            return $this->name;
        }

        if ($this->blurred_name === null || $this->blurred_name === '') {
            // Lazily generated here rather than only at the moment a price is first set, so a
            // photo priced through any path (admin edit, a future bulk-price tool, ...) is
            // still safe to display the very first time it's rendered afterward - returning
            // the real, un-blurred name to an unpaid viewer even once would defeat the whole
            // point, so this can't be skipped just because generation hasn't run yet.
            app(\App\Services\ImageBlurService::class)->ensureBlurredCopy($this);
        }

        // If blurring failed (unreadable/corrupt source file) there is still no safe image to
        // show a name for - the view layer is expected to render the price/lock overlay instead
        // of an <img> at all in that case, not fall back to the real photo.
        return $this->blurred_name ?: '';
    }
}
