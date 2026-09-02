<?php

declare(strict_types=1);

namespace App;

use App\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AIProfile extends Model
{
    use SoftDeletes;

    protected $table = 'ai_profiles';

    protected $fillable = [
        'created_by_admin_id',
        'name',
        'static_image_path',
        'system_prompt',
        'voice_id',
        'learning_snapshot',
        'is_active',
    ];

    protected $casts = [
        'learning_snapshot' => 'array',
        'is_active' => 'boolean',
    ];

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function imageUrl(): ?string
    {
        $staticImagePath = (string) $this->static_image_path;

        if ($staticImagePath === '') {
            return null;
        }

        if (str_starts_with($staticImagePath, 'http://') || str_starts_with($staticImagePath, 'https://')) {
            return $this->normalizeMediaUrl($staticImagePath);
        }

        $publicBaseUrl = rtrim((string) config('ai.public_media_base_url', ''), '/');
        if ($publicBaseUrl !== '') {
            return $publicBaseUrl . '/storage/' . ltrim($staticImagePath, '/');
        }

        $diskBaseUrl = rtrim((string) config('filesystems.disks.ai_public.url', ''), '/');
        if ($diskBaseUrl !== '') {
            return $diskBaseUrl . '/' . ltrim($staticImagePath, '/');
        }

        return url('/storage/' . ltrim($staticImagePath, '/'));
    }

    private function normalizeMediaUrl(string $absoluteUrl): string
    {
        $publicBaseUrl = rtrim((string) config('ai.public_media_base_url', ''), '/');
        if ($publicBaseUrl === '') {
            return $absoluteUrl;
        }

        $path = (string) (parse_url($absoluteUrl, PHP_URL_PATH) ?? '');
        if ($path !== '' && str_starts_with($path, '/storage/')) {
            return $publicBaseUrl . $path;
        }

        return $absoluteUrl;
    }
}
