<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConversationMessage extends Model
{
    protected $table = 'conversation_messages';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'channel',
        'content',
        'emotion',
        'conversion_signal',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
