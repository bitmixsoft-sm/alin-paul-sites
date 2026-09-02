<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\AISetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StyleDistillationService
{
    public function distill(string $transcript): string
    {
        $aiSetting = AISetting::current();
        $model = $aiSetting->openaiModel();
        $apiKey = $aiSetting->apiKey('openai');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $instructions = 'You analyze chat transcripts between a person/persona and users on a dating platform. '
            . "Produce a concise style guide (max 300 words) that another AI persona can follow to imitate the same "
            . "conversational approach. Cover: tone and personality traits, sentence length and rhythm, flirting style, "
            . "emojis/punctuation habits, how the persona builds emotional connection, and the specific phrasing patterns "
            . "it uses to nudge users toward subscribing or continuing the conversation (without ever sounding like a sales pitch). "
            . 'Do not mention AI, prompts, or that this is an analysis — write it as direct persona instructions ("You speak in short, '
            . 'playful sentences...").';

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withToken($apiKey)
            ->post(env('OPENAI_API_BASE_URL', 'https://api.openai.com/v1') . '/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => $instructions],
                    ['role' => 'user', 'content' => $transcript],
                ],
            ]);

        return $this->extractFirstChoiceContent($response);
    }

    private function extractFirstChoiceContent(Response $response): string
    {
        if ($response->status() === 429) {
            throw new RuntimeException('OPENAI_QUOTA_EXCEEDED: ' . ($response->json('error.message') ?? 'Quota exceeded.'));
        }

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI request failed with status ' . $response->status());
        }

        $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

        if ($content === '') {
            throw new RuntimeException('OpenAI response did not include message content.');
        }

        return $content;
    }
}
