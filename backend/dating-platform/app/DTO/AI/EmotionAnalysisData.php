<?php

declare(strict_types=1);

namespace App\DTO\AI;

use App\Enums\Emotion;

final readonly class EmotionAnalysisData
{
    public function __construct(
        public Emotion $emotion,
        public string $rawModelOutput,
    ) {
    }
}
