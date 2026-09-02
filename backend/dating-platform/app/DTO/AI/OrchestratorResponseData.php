<?php

declare(strict_types=1);

namespace App\DTO\AI;

use App\Enums\Emotion;

final readonly class OrchestratorResponseData
{
    public function __construct(
        public string $assistantText,
        public Emotion $emotion,
        public string $audioUrl,
        public ?string $videoUrl,
        public string $videoStatus,
        public array $raw = [],
    ) {
    }
}
