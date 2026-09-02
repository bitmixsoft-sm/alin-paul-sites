<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'user_id',
        'ai_profile_id',
        'channel',
        'last_user_emotion',
        'message_count',
        'conversion_context',
        'summary',
        'summarized_through_message_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'conversion_context' => 'array',
        'summarized_through_message_id' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiProfile(): BelongsTo
    {
        return $this->belongsTo(AIProfile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }
}
