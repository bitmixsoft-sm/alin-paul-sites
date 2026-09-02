<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings table for the video-avatar providers used by the AI chat
 * (which provider is active + that provider's API key), managed from the admin
 * AI Settings page instead of the .env file.
 */
final class AISetting extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'live_avatar_provider',
        'live_video_layout',
        'live_video_blur_enabled',
        'live_video_blur_amount',
        'live_video_mute_enabled',
        'live_video_mute_default',
        'avatar_video_provider',
        'avatar_video_enabled',
        'tavus_api_key',
        'simli_api_key',
        'heygen_api_key',
        'did_api_key',
        'openai_api_key',
        'openai_model',
        'text_ai_enabled',
        'live_ai_video_enabled',
    ];

    protected $casts = [
        'avatar_video_enabled' => 'boolean',
        'live_video_blur_enabled' => 'boolean',
        'live_video_blur_amount' => 'integer',
        'live_video_mute_enabled' => 'boolean',
        'live_video_mute_default' => 'boolean',
        'tavus_api_key' => 'encrypted',
        'simli_api_key' => 'encrypted',
        'heygen_api_key' => 'encrypted',
        'did_api_key' => 'encrypted',
        'openai_api_key' => 'encrypted',
        'text_ai_enabled' => 'boolean',
        'live_ai_video_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        /** @var self $settings */
        $settings = static::query()->firstOrCreate([]);

        return $settings;
    }

    public function liveAvatarProvider(): string
    {
        return in_array($this->live_avatar_provider, ['tavus_cvi', 'simli'], true)
            ? $this->live_avatar_provider
            : 'tavus_cvi';
    }

    public function liveVideoLayout(): string
    {
        return in_array($this->live_video_layout, ['floating', 'docked'], true)
            ? $this->live_video_layout
            : 'floating';
    }

    public const MIN_LIVE_VIDEO_BLUR_AMOUNT = 0;
    public const MAX_LIVE_VIDEO_BLUR_AMOUNT = 100;

    // The CSS blur() strength (px) applied at 100% — an internal implementation detail,
    // not admin-configurable, so the admin-facing unit stays percent everywhere (both
    // here and on the per-package blur setting) instead of mixing px and percent.
    // Exposed read-only via maxBlurPx() so the admin preview image can render the same
    // conversion the real chat view uses.
    private const MAX_BLUR_PX = 40;

    public static function maxBlurPx(): int
    {
        return self::MAX_BLUR_PX;
    }

    public function liveVideoBlurEnabled(): bool
    {
        return (bool) $this->live_video_blur_enabled;
    }

    /**
     * Default blur percentage (0-100) used as a fallback for users whose package has no
     * blur percentage of its own set (e.g. a legacy custom package).
     */
    public function liveVideoBlurAmount(): int
    {
        return min(
            max((int) $this->live_video_blur_amount, self::MIN_LIVE_VIDEO_BLUR_AMOUNT),
            self::MAX_LIVE_VIDEO_BLUR_AMOUNT
        );
    }

    /**
     * Converts a package-derived blur percentage (0-100) to the CSS blur() px amount.
     */
    public function liveVideoBlurAmountForPercent(int $percent): int
    {
        $percent = min(max($percent, 0), 100);

        return (int) round(self::MAX_BLUR_PX * $percent / 100);
    }

    /**
     * Master switch for the "mute AI audio" feature. When disabled, nobody is ever muted,
     * regardless of what their package says.
     */
    public function liveVideoMuteEnabled(): bool
    {
        return (bool) $this->live_video_mute_enabled;
    }

    /**
     * Fallback mute setting used for users whose package has no mute setting of its own
     * (e.g. a legacy custom package).
     */
    public function liveVideoMuteDefault(): bool
    {
        return (bool) $this->live_video_mute_default;
    }

    public function avatarVideoProvider(): string
    {
        return in_array($this->avatar_video_provider, ['did', 'heygen', 'tavus', 'tavus_cvi'], true)
            ? $this->avatar_video_provider
            : 'did';
    }

    public function avatarVideoEnabled(): bool
    {
        return (bool) $this->avatar_video_enabled;
    }

    public function apiKey(string $provider): string
    {
        $value = trim((string) match ($provider) {
            'tavus' => $this->tavus_api_key,
            'simli' => $this->simli_api_key,
            'heygen' => $this->heygen_api_key,
            'did' => $this->did_api_key,
            'openai' => $this->openai_api_key,
            default => '',
        });

        if ($value !== '') {
            return $value;
        }

        return trim((string) match ($provider) {
            'tavus' => config('services.tavus.api_key', env('TAVUS_API_KEY', '')),
            'simli' => config('services.simli.api_key', env('SIMLI_API_KEY', '')),
            'heygen' => config('services.heygen.api_key', env('HEYGEN_API_KEY', '')),
            'did' => config('services.did.api_key', env('DID_API_KEY', '')),
            'openai' => config('services.openai.api_key', env('OPENAI_API_KEY', '')),
            default => '',
        });
    }

    /**
     * The OpenAI model used for the real-chat AI auto-reply text generation, admin-set with
     * a fallback to .env (same pattern as apiKey()).
     */
    public function openaiModel(): string
    {
        $value = trim((string) $this->openai_model);

        if ($value !== '') {
            return $value;
        }

        return trim((string) config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o')));
    }

    /**
     * Whether the OpenAI text-reply integration has a usable API key configured (admin
     * field or .env fallback) — used to show a setup status indicator on the AI Settings page.
     */
    public function openaiConfigured(): bool
    {
        $key = $this->apiKey('openai');

        return $key !== '' && ! str_contains($key, 'your_') && ! str_contains($key, 'placeholder');
    }

    /**
     * Master switch for the real-chat AI auto-reply (ChatBotController) — independent of
     * the legacy "Activare Chat Bot" Settings row (id=22), which only gates the old
     * keyword-match fallback bot.
     */
    public function textAiEnabled(): bool
    {
        return (bool) $this->text_ai_enabled;
    }

    /**
     * Master switch for live AI video calls to real (non-catalog) female profiles
     * (ChatController::startAiVideoSession / SimliService::createSessionForUser). Off by
     * default — unlike text_ai_enabled, this is a materially more expensive feature that
     * also needs a Simli Face ID configured per profile before it can work at all.
     */
    public function liveAiVideoEnabled(): bool
    {
        return (bool) $this->live_ai_video_enabled;
    }

    /**
     * Resolves the live-video blur px amount and audio-mute flag for a given viewer, based
     * on their package tier — shared by FindFriendsController (AI Companions live video)
     * and ChatController::startAiVideoSession() (real-profile live video call), so both
     * honor the same package-tier privacy settings identically.
     *
     * @return array{amountPx: int, audioMuted: bool}
     */
    public function resolveVideoPrivacyForUser(?User $user): array
    {
        try {
            $pack = $user ? $user->package() : false;
        } catch (\Throwable $e) {
            $pack = false;
        }

        $videoBlurPercent = ($pack ? $pack->videoBlurPercent() : null) ?? $this->liveVideoBlurAmount();
        $amountPx = $this->liveVideoBlurAmountForPercent($videoBlurPercent);

        $audioMuted = $this->liveVideoMuteEnabled()
            && (($pack ? $pack->audioMuted() : null) ?? $this->liveVideoMuteDefault());

        return ['amountPx' => $amountPx, 'audioMuted' => $audioMuted];
    }
}
