<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
	protected $table = 'packages';
    protected $guarded = [];

    /**
     * How much to blur the AI live video for users on this package, 0 (fully clear) to
     * 100 (fully blurred). Null means unset (e.g. a legacy custom package) — the caller
     * should fall back to AISetting::liveVideoBlurAmount(), the site-wide default.
     */
    public function videoBlurPercent(): ?int
    {
        if ($this->video_blur_percent === null) {
            return null;
        }

        return min(max((int) $this->video_blur_percent, 0), 100);
    }

    /**
     * Whether users on this package hear the AI's voice during a live video session (video
     * keeps playing either way — this only silences audio playback). Null means unset (e.g.
     * a legacy custom package) — the caller should fall back to AISetting::liveVideoMuteDefault().
     */
    public function audioMuted(): ?bool
    {
        if ($this->audio_muted === null) {
            return null;
        }

        return (bool) $this->audio_muted;
    }

}
