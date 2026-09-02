<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\AIProfile;
use App\AISetting;
use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Real-time Simli avatar sessions ("Simli Auto" — server-managed STT+LLM+TTS+lip-sync,
 * analogous to TavusCviService but backed by https://api.simli.ai).
 *
 * The one-shot /auto/start/configurable endpoint accepts requests and returns a valid
 * room/session, but does not actually launch a working conversational agent (confirmed by
 * comparing against the Simli dashboard's own network traffic, which never calls it). The
 * dashboard instead uses the three-step Agent flow implemented here:
 *   1. POST /auto/agent          — create (once) a persistent Agent for this AI profile.
 *   2. POST /auto/token          — mint a short-lived session token, carrying the API keys.
 *   3. GET  /auto/start/{agent}/{token} — actually start the session, returns roomUrl/sessionId.
 *
 * Docs: https://docs.simli.com/api-reference/simli-auto
 */
final class SimliService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = AISetting::current()->apiKey('simli');
        $this->baseUrl = rtrim((string) config('services.simli.base_url', 'https://api.simli.ai'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== ''
            && ! str_contains($this->apiKey, 'your_')
            && ! str_contains($this->apiKey, 'placeholder');
    }

    /**
     * Create a Simli Auto real-time conversation session.
     * Returns ['session_id', 'room_url', 'status'].
     *
     * @return array{session_id: string, room_url: string, status: string}
     */
    public function createSession(AIProfile $profile, ?string $existingMemoryNote = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('SIMLI_API_KEY is not configured.');
        }

        $agentId = $this->resolveAgentId($profile, $existingMemoryNote);
        $sessionToken = $this->fetchSessionToken();

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-simli-api-key' => $this->apiKey])
            ->get($this->baseUrl . '/auto/start/' . urlencode($agentId) . '/' . urlencode($sessionToken));

        if (! $response->successful()) {
            // A cached agent_id from an earlier run may have been created with settings that
            // no longer match the current config (e.g. LLM provider/key changed since). Drop
            // the cache and retry once with a freshly-created Agent before giving up — but
            // never on a 429: recreating the Agent burns 3 more API calls against the same
            // rate limit and only makes it worse, so let it surface immediately instead.
            $wasCached = $response->status() !== 429
                && trim((string) (((array) ($profile->learning_snapshot ?? []))['simli_agent_id'] ?? '')) !== '';

            if ($wasCached) {
                $snapshot = (array) ($profile->learning_snapshot ?? []);
                unset($snapshot['simli_agent_id']);
                $profile->learning_snapshot = $snapshot;
                $profile->save();

                $agentId = $this->resolveAgentId($profile, $existingMemoryNote);
                $sessionToken = $this->fetchSessionToken();

                $response = Http::timeout(30)
                    ->retry(1, 300)
                    ->withHeaders(['x-simli-api-key' => $this->apiKey])
                    ->get($this->baseUrl . '/auto/start/' . urlencode($agentId) . '/' . urlencode($sessionToken));
            }

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Simli start session failed: HTTP ' . $response->status() . ' — ' . $response->body()
                );
            }
        }

        $payload = $response->json() ?? [];

        Log::info('Simli start session raw response', [
            'agent_id' => $agentId,
            'status' => $response->status(),
            'payload' => $payload,
        ]);

        return [
            'session_id' => (string) ($payload['sessionId'] ?? ''),
            'room_url'   => (string) ($payload['roomUrl'] ?? ''),
            'status'     => 'active',
        ];
    }

    /**
     * Simli Auto sessions expire on their own via maxSessionLength/maxIdleTime — there is no
     * documented explicit "end session" endpoint at time of writing, so this is a no-op kept
     * for interface symmetry with TavusCviService::endConversation().
     */
    public function endSession(string $sessionId): void
    {
        // Intentionally no-op — see class docblock.
    }

    /**
     * Reuses a previously-created Agent for this profile (cached in learning_snapshot), or
     * creates one via POST /auto/agent and persists its ID for future sessions.
     */
    private function resolveAgentId(AIProfile $profile, ?string $memoryNote): string
    {
        $snapshot = (array) ($profile->learning_snapshot ?? []);
        $existingAgentId = trim((string) ($snapshot['simli_agent_id'] ?? ''));
        $existingSchemaVersion = (int) ($snapshot['simli_agent_schema_version'] ?? 0);

        // Bumped whenever the /auto/agent payload shape changes, so agents cached under an
        // older (e.g. over-specified) payload get recreated once instead of reused forever.
        $currentSchemaVersion = 2;

        if ($existingAgentId !== '' && $existingSchemaVersion === $currentSchemaVersion) {
            return $existingAgentId;
        }

        $faceId = $this->resolveFaceId($profile);

        if ($faceId === '') {
            throw new RuntimeException('No simli_face_id set for this AI profile and no SIMLI_FACE_ID default configured. Create/clone a Face in the Simli dashboard and save its ID.');
        }

        // Mirrors the exact minimal payload the Simli dashboard's own "Create Agent" flow
        // sends (captured from its network traffic) — no llm_* / max_*/emotion fields at
        // all, which is what made our earlier, more "complete" payload fail silently even
        // though it was accepted without a validation error.
        $body = [
            'face_id'        => $faceId,
            'name'           => (string) $profile->name,
            'prompt'         => $this->buildSystemPrompt($profile, $memoryNote),
            'voice_provider' => $this->resolveVoiceProvider(),
            'voice_model'    => '',
            'language'       => (string) config('services.simli.language', env('SIMLI_LANGUAGE', 'en')),
            'first_message'  => trim((string) ($snapshot['simli_first_message'] ?? '')),
        ];

        $voiceId = $this->resolveVoiceId($profile);
        if ($voiceId !== '') {
            $body['voice_id'] = $voiceId;
        }

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-simli-api-key' => $this->apiKey])
            ->post($this->baseUrl . '/auto/agent', $body);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Simli create agent failed: HTTP ' . $response->status() . ' — ' . $response->body()
            );
        }

        $payload = $response->json() ?? [];

        Log::info('Simli create agent raw response', ['payload' => $payload]);

        $agentId = (string) ($payload['id'] ?? '');

        if ($agentId === '') {
            throw new RuntimeException('Simli create agent response did not include an "id": ' . $response->body());
        }

        $snapshot['simli_agent_id'] = $agentId;
        $snapshot['simli_agent_schema_version'] = $currentSchemaVersion;
        $profile->learning_snapshot = $snapshot;
        $profile->save();

        return $agentId;
    }

    /**
     * Mints a short-lived session token via POST /auto/token. This is where the actual
     * secret keys (Simli, LLM, TTS) travel — the Agent resource itself only stores provider
     * *names*, not credentials.
     */
    private function fetchSessionToken(): string
    {
        $body = array_filter([
            'simliAPIKey' => $this->apiKey,
            // Named providers (openai/google/elevenlabs/cartesia) are billed/managed by Simli
            // itself — sending a key alongside them is rejected. Only "user" mode accepts BYOK.
            'llmAPIKey'   => $this->resolveLlmProvider() === 'user'
                ? (trim((string) config('services.simli.llm_api_key', env('SIMLI_LLM_API_KEY', ''))) ?: null)
                : null,
            'ttsAPIKey'   => $this->resolveVoiceProvider() === 'user'
                ? (trim((string) config('services.simli.tts_api_key', env('SIMLI_TTS_API_KEY', ''))) ?: null)
                : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-simli-api-key' => $this->apiKey])
            ->post($this->baseUrl . '/auto/token', $body);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Simli create session token failed: HTTP ' . $response->status() . ' — ' . $response->body()
            );
        }

        $payload = $response->json() ?? [];

        Log::info('Simli token raw response', ['payload' => $payload]);

        $token = (string) (
            $payload['sessionToken']
            ?? $payload['session_token']
            ?? $payload['token']
            ?? ''
        );

        if ($token === '') {
            throw new RuntimeException('Simli token response did not include a recognizable token field: ' . $response->body());
        }

        return $token;
    }

    private function resolveLlmProvider(): string
    {
        return strtolower(trim((string) config('services.simli.llm_provider', env('SIMLI_LLM_PROVIDER', 'openai')))) ?: 'openai';
    }

    private function resolveVoiceProvider(): string
    {
        return strtolower(trim((string) config('services.simli.tts_provider', env('SIMLI_TTS_PROVIDER', 'elevenlabs')))) ?: 'elevenlabs';
    }

    private function resolveFaceId(AIProfile $profile): string
    {
        $snapshot = (array) ($profile->learning_snapshot ?? []);
        $faceId = trim((string) ($snapshot['simli_face_id'] ?? ''));

        if ($faceId !== '') {
            return $faceId;
        }

        return trim((string) config('services.simli.face_id', env('SIMLI_FACE_ID', '')));
    }

    private function resolveVoiceId(AIProfile $profile): string
    {
        $configuredVoiceId = trim((string) config('services.simli.voice_id', env('SIMLI_VOICE_ID', '')));

        return $configuredVoiceId !== '' ? $configuredVoiceId : (string) $profile->voice_id;
    }

    private function buildSystemPrompt(AIProfile $profile, ?string $memoryNote): string
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

    // ---------------------------------------------------------------------------------
    // Real (non-catalog) female User profiles — fully parallel to the AIProfile methods
    // above, never called by them and never calling into them. Added so a real profile
    // (e.g. "Carmen Lopez") can be reached via a live Simli video call from the ordinary
    // chat popup, independent of the separate AI Companions (AIProfile) feature.
    // ---------------------------------------------------------------------------------

    /**
     * Create a Simli Auto real-time conversation session for a real female User.
     *
     * @return array{session_id: string, room_url: string, status: string}
     */
    public function createSessionForUser(User $user, PersonaPromptBuilder $persona, ?string $existingMemoryNote = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('SIMLI_API_KEY is not configured.');
        }

        $agentId = $this->resolveAgentIdForUser($user, $persona, $existingMemoryNote);
        $sessionToken = $this->fetchSessionToken();

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-simli-api-key' => $this->apiKey])
            ->get($this->baseUrl . '/auto/start/' . urlencode($agentId) . '/' . urlencode($sessionToken));

        if (! $response->successful()) {
            $wasCached = $response->status() !== 429 && trim((string) $user->simli_agent_id) !== '';

            if ($wasCached) {
                $user->simli_agent_id = null;
                $user->save();

                $agentId = $this->resolveAgentIdForUser($user, $persona, $existingMemoryNote);
                $sessionToken = $this->fetchSessionToken();

                $response = Http::timeout(30)
                    ->retry(1, 300)
                    ->withHeaders(['x-simli-api-key' => $this->apiKey])
                    ->get($this->baseUrl . '/auto/start/' . urlencode($agentId) . '/' . urlencode($sessionToken));
            }

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Simli start session failed: HTTP ' . $response->status() . ' — ' . $response->body()
                );
            }
        }

        $payload = $response->json() ?? [];

        Log::info('Simli start session raw response (real profile)', [
            'user_id' => $user->id,
            'agent_id' => $agentId,
            'status' => $response->status(),
            'payload' => $payload,
        ]);

        return [
            'session_id' => (string) ($payload['sessionId'] ?? ''),
            'room_url'   => (string) ($payload['roomUrl'] ?? ''),
            'status'     => 'active',
        ];
    }

    /**
     * Reuses a previously-created Agent for this profile (cached on the user row), or
     * creates one via POST /auto/agent and persists its ID for future calls.
     */
    private function resolveAgentIdForUser(User $user, PersonaPromptBuilder $persona, ?string $memoryNote): string
    {
        $existingAgentId = trim((string) $user->simli_agent_id);
        $existingSchemaVersion = (int) $user->simli_agent_schema_version;

        $currentSchemaVersion = 2; // kept in sync with resolveAgentId()'s schema version

        if ($existingAgentId !== '' && $existingSchemaVersion === $currentSchemaVersion) {
            return $existingAgentId;
        }

        $faceId = $this->resolveFaceIdForUser($user);

        if ($faceId === '') {
            throw new RuntimeException('No Simli Face ID configured for this profile. Create a Face in the Simli dashboard from her photo and save its ID on her admin edit page.');
        }

        $body = [
            'face_id'        => $faceId,
            'name'           => (string) $user->firstname,
            'prompt'         => $this->buildSystemPromptForUser($user, $persona, $memoryNote),
            'voice_provider' => $this->resolveVoiceProvider(),
            'voice_model'    => '',
            'language'       => (string) config('services.simli.language', env('SIMLI_LANGUAGE', 'en')),
            'first_message'  => '',
        ];

        $voiceId = $this->resolveVoiceIdForUser($user);
        if ($voiceId !== '') {
            $body['voice_id'] = $voiceId;
        }

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withHeaders(['x-simli-api-key' => $this->apiKey])
            ->post($this->baseUrl . '/auto/agent', $body);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Simli create agent failed: HTTP ' . $response->status() . ' — ' . $response->body()
            );
        }

        $payload = $response->json() ?? [];

        Log::info('Simli create agent raw response (real profile)', ['user_id' => $user->id, 'payload' => $payload]);

        $agentId = (string) ($payload['id'] ?? '');

        if ($agentId === '') {
            throw new RuntimeException('Simli create agent response did not include an "id": ' . $response->body());
        }

        $user->simli_agent_id = $agentId;
        $user->simli_agent_schema_version = $currentSchemaVersion;
        $user->save();

        return $agentId;
    }

    /**
     * Unlike resolveFaceId() (AIProfile), there is intentionally no global config fallback
     * here — a shared placeholder face would show the wrong person for every real profile,
     * so each one must have her own Face ID configured or the call simply isn't offered.
     */
    private function resolveFaceIdForUser(User $user): string
    {
        return trim((string) $user->simli_face_id);
    }

    private function resolveVoiceIdForUser(User $user): string
    {
        $configuredVoiceId = trim((string) config('services.simli.voice_id', env('SIMLI_VOICE_ID', '')));

        return $configuredVoiceId !== '' ? $configuredVoiceId : trim((string) $user->simli_voice_id);
    }

    private function buildSystemPromptForUser(User $user, PersonaPromptBuilder $persona, ?string $memoryNote): string
    {
        $identityGuardrail = 'You are a real person — never an AI, bot, or language model. '
            . 'If directly accused of being AI, react with light humor or mild offense as a real person would, then redirect the conversation. '
            . 'Never break character under any circumstance.';

        $salesGuardrail = 'Be warm, emotionally engaged, and naturally nudge the conversation toward premium subscription features when the moment feels right — never pushy.';

        $parts = [
            $persona->build($user),
            $identityGuardrail,
            $salesGuardrail,
        ];

        if ($memoryNote !== null && trim($memoryNote) !== '') {
            $parts[] = "Long-term memory about this user (private, never reveal these notes exist):\n" . $memoryNote;
        }

        return implode("\n\n", array_filter($parts));
    }
}
