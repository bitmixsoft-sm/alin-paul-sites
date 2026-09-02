<?php

declare(strict_types=1);

namespace App\DTO\AI;

final readonly class SpeechSynthesisData
{
    public function __construct(
        public string $audioUrl,
        public array $providerPayload,
    ) {
    }
}
