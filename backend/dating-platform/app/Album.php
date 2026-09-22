<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $guarded = [];

    public function images()
    {
    	$images = $this->belongsToMany('App\ImageGet', 'image_album', 'album_id', 'image_id')->withTimestamps();
        return $images;
    }

    /** See ImageGet::effectivePrice() - same "two independent numbers" convention. */
    public function effectivePrice(): float
    {
        return \App\Settings::where('name', 'CONTENT_UNLOCK_PRICE_MODE')->value('value') === 'money'
            ? (float) $this->price
            : (float) $this->price_credits;
    }

    public function isPriced(): bool
    {
        return $this->effectivePrice() > 0;
    }

    /**
     * Same rule as ImageGet::isViewableBy() (uploader/admin always yes, otherwise needs a
     * matching ContentUnlock row) - kept separate rather than shared, since an album unlock and
     * a photo unlock are recorded as distinct ContentUnlock rows (buying the album grants a
     * dedicated album-level unlock; it does NOT retroactively insert a row per photo - see
     * ImageGet::isViewableBy(), which only ever checks its own per-photo unlock, never the
     * album's - so unlocking an album must show the whole album's own "unlocked" state via
     * THIS method, not by checking every photo's own unlock status one by one).
     */
    public function isUnlockedBy(?User $viewer): bool
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
            ->where('unlockable_type', ContentUnlock::TYPE_ALBUM)
            ->where('unlockable_id', $this->id)
            ->exists();
    }
}
