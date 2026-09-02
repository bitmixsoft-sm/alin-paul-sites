<?php

declare(strict_types=1);

namespace App\Enums;

enum Emotion: string
{
    case Neutral = 'neutral';
    case Happy = 'happy';
    case Excited = 'excited';
    case Curious = 'curious';
    case Flirty = 'flirty';
    case Sad = 'sad';
    case Frustrated = 'frustrated';
    case Angry = 'angry';
    case Anxious = 'anxious';

    public static function fallback(): self
    {
        return self::Neutral;
    }
}
