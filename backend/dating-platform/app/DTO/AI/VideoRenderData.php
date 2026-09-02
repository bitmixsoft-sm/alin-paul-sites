<?php

declare(strict_types=1);

namespace App\DTO\AI;

final readonly class VideoRenderData
{
    public function __construct(
        public ?string $videoUrl,
        public string $status,
        public array $providerPayload,
    ) {
    }
}
