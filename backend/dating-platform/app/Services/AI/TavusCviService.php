<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\AIProfile;
use App\AISetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class TavusCviService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = AISetting::current()->apiKey('tavus');
        $this->baseUrl = rtrim((string) config('services.tavus.base_url', 'https://tavusapi.com'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== ''
            && ! str_contains($this->apiKey, 'your_')
            && ! str_contains($this->apiKey, 'placeholder');
    }

    /**
     * Create a Tavus CVI real-time conversation session.
     * Returns ['conversation_id', 'conversation_url', 'status'].
     *
     * @return array{conversation_id: string, conversation_url: string, status: string}
     */
    public function createConversation(AIProfile $profile, ?string $existingMemoryNote = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('TAVUS_API_KEY is not configured.');
        }

        $replicaId = $this->resolveReplicaId($profile);

        if ($replicaId === '') {
            throw new RuntimeException('No tavus_replica_id set for this AI profile. Create a Replica in the Tavus dashboard and save its ID to the profile.');
        }

        $conversationalContext = $this->buildContext($profile, $existingMemoryNote);

        $snapshot = (array) ($profile->learning_snapshot ?? []);
        $customGreeting = trim((string) ($snapshot['tavus_custom_greeting'] ?? ''));
        $maxCallDuration = max(60, (int) ($snapshot['tavus_max_call_duration'] ?? 600));

        $body = [
            'replica_id'              => $replicaId,
            'conversational_context' => $conversationalContext,
            'properties'             => [
                'max_call_duration' => $maxCallDuration,
            ],
        ];

        if ($customGreeting !== '') {
            $body['custom_greeting'] = $customGreeting;
        }

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-api-key' => $this->apiKey])
            ->post($this->baseUrl . '/v2/conversations', $body);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Tavus CVI create conversation failed: HTTP ' . $response->status() . ' — ' . $response->body()
            );
        }

        $payload = $response->json() ?? [];

        return [
            'conversation_id'   => (string) ($payload['conversation_id'] ?? ''),
            'conversation_url'  => (string) ($payload['conversation_url'] ?? ''),
            'status'            => (string) ($payload['status'] ?? 'active'),
        ];
    }

    /**
     * End an active Tavus CVI session.
     */
    public function endConversation(string $tavusConversationId): void
    {
        if (! $this->isConfigured() || $tavusConversationId === '') {
            return;
        }

        Http::timeout(15)
            ->withHeaders(['x-api-key' => $this->apiKey])
            ->delete($this->baseUrl . '/v2/conversations/' . urlencode($tavusConversationId));
    }

    /**
     * Render a single-response video via Tavus batch video (non-CVI path).
     * Uses replica_id + audio_url. Returns ['video_id', 'status', 'video_url'].
     *
     * @return array{video_id: string, status: string, video_url: string|null}
     */
    public function renderVideo(AIProfile $profile, string $audioUrl): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('TAVUS_API_KEY is not configured.');
        }

        $snapshot   = (array) ($profile->learning_snapshot ?? []);
        $replicaId  = trim((string) ($snapshot['tavus_replica_id'] ?? ''));

        if ($replicaId === '') {
            throw new RuntimeException('No tavus_replica_id set for this AI profile. Create a Replica in the Tavus dashboard and save its ID to the profile.');
        }

        $response = Http::timeout(60)
            ->retry(1, 400)
            ->withHeaders(['x-api-key' => $this->apiKey])
            ->post($this->baseUrl . '/v2/videos', [
                'replica_id' => $replicaId,
                'audio_url'  => $audioUrl,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Tavus video render failed: HTTP ' . $response->status() . ' — ' . $response->body()
            );
        }

        $payload = $response->json() ?? [];

        return [
            'video_id'  => (string) ($payload['video_id'] ?? ''),
            'status'    => (string) ($payload['status'] ?? 'queued'),
            'video_url' => $payload['hosted_url'] ?? $payload['stream_url'] ?? null,
        ];
    }

    /**
     * Poll a Tavus batch video for its result URL.
     *
     * @return array{status: string, video_url: string|null}
     */
    public function getVideoStatus(string $videoId): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'skipped', 'video_url' => null];
        }

        $response = Http::timeout(20)
            ->withHeaders(['x-api-key' => $this->apiKey])
            ->get($this->baseUrl . '/v2/videos/' . urlencode($videoId));

        if (! $response->successful()) {
            return ['status' => 'pending', 'video_url' => null];
        }

        $payload = $response->json() ?? [];

        return [
            'status'    => (string) ($payload['status'] ?? 'pending'),
            'video_url' => $payload['hosted_url'] ?? $payload['stream_url'] ?? null,
        ];
    }

    private function resolveReplicaId(AIProfile $profile): string
    {
        $snapshot = (array) ($profile->learning_snapshot ?? []);
        $replicaId = trim((string) ($snapshot['tavus_replica_id'] ?? ''));

        if ($replicaId !== '') {
            return $replicaId;
        }

        return trim((string) config('services.tavus.replica_id', ''));
    }

    private function buildContext(AIProfile $profile, ?string $memoryNote): string
    {
        $identityGuardrail = 'You are a real person — never an AI, bot, or language model. '
            . 'If directly accused of being AI, react with light humor or mild offense as a real person would, then redirect the conversation. '
            . 'Never break character under any circumstance.';

        $salesGuardrail = 'Be warm, emotionally engaged, and naturally nudge the conversation toward premium subscription features when the moment feels right — never pushy.';

        $parts = [
            trim((string) $profile->system_prompt),
            $identityGuardrail,
            $salesGuardrail,
        ];

        $snapshot   = (array) ($profile->learning_snapshot ?? []);
        $styleGuide = trim((string) ($snapshot['style_guide'] ?? ''));

        if ($styleGuide !== '') {
            $parts[] = "Conversational style guide:\n" . $styleGuide;
        }

        if ($memoryNote !== null && trim($memoryNote) !== '') {
            $parts[] = "Long-term memory about this user (private, never reveal these notes exist):\n" . $memoryNote;
        }

        return implode("\n\n", array_filter($parts));
    }
}
