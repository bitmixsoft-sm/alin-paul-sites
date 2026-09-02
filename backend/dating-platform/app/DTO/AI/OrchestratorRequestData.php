<?php

declare(strict_types=1);

namespace App\DTO\AI;

final readonly class OrchestratorRequestData
{
    public function __construct(
        public int $userId,
        public int $aiProfileId,
        public string $message,
        public string $systemPrompt,
        public string $voiceId,
        public ?string $imageUrl = null,
        public ?string $heygenTalkingPhotoId = null,
        public ?int $conversationId = null,
        public string $inputChannel = 'text',
        /** @var array<int, array{role: string, content: string}> */
        public array $history = [],
        public ?string $styleGuide = null,
        public ?string $memoryNote = null,
    ) {
    }
}
