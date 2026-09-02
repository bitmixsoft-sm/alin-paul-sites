<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lists the account's available ElevenLabs voices for admin voice-picker dropdowns.
 * Used both by the AI Profiles catalog (AdminAIProfileController) and by real female
 * profiles' "Simli Voice ID" field (WebUsersController) - Simli's default TTS provider is
 * ElevenLabs (see SimliService::resolveVoiceProvider()), so a voice ID that's valid for one
 * is valid for the other.
 */
final class ElevenLabsVoiceCatalog
{
    /**
     * @return array<string, string> voice_id => voice name
     */
    public function list(): array
    {
        $apiKey = (string) config('services.elevenlabs.api_key', env('ELEVENLABS_API_KEY', ''));

        if ($apiKey === '' || str_contains($apiKey, 'your_') || str_contains($apiKey, 'placeholder')) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['xi-api-key' => $apiKey])
                ->get('https://api.elevenlabs.io/v1/voices');

            if (! $response->successful()) {
                return [];
            }

            $voices = $response->json('voices', []);
            $formatted = [];

            foreach ($voices as $voice) {
                $formatted[(string) ($voice['voice_id'] ?? '')] = (string) ($voice['name'] ?? 'Unknown');
            }

            return $formatted;
        } catch (\Exception $e) {
            Log::error('ElevenLabs voices fetch failed', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
