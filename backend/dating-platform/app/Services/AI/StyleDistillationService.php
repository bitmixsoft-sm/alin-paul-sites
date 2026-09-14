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

    /**
     * The alternative to distill()'s free-form style guide: pulls actual verbatim lines out of
     * the transcript instead of summarizing the approach. Used for real profiles' "literal
     * phrase" learning mode (AdminStyleLearningController) - the client's stricter ask ("use
     * only phrases from that conversation"), as opposed to the "inspired by the tone" mode
     * distill() already powers for both the AI Companions catalog and real profiles.
     *
     * @return array<int, string>
     */
    public function extractPhrases(string $transcript, int $max = 15): array
    {
        $aiSetting = AISetting::current();
        $model = $aiSetting->openaiModel();
        $apiKey = $aiSetting->apiKey('openai');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $instructions = 'You analyze a chat transcript between a person/persona and users on a dating platform. '
            . "Select up to {$max} of the persona's own lines that were the most effective, persuasive, or emotionally "
            . 'engaging - especially ones that led the conversation toward the user subscribing or buying credits, or '
            . 'that built strong emotional connection. Copy each one EXACTLY as written in the transcript - do not '
            . 'paraphrase, correct, translate, or clean them up in any way, even if they contain typos or informal '
            . 'language. Respond with ONLY a JSON array of strings, nothing else, e.g. ["line one", "line two"]. If '
            . "fewer than {$max} good lines exist, return fewer - never invent lines that aren't in the transcript.";

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withToken($apiKey)
            ->post(env('OPENAI_API_BASE_URL', 'https://api.openai.com/v1') . '/chat/completions', [
                'model' => $model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $instructions . ' Wrap the array in an object: {"lines": [...]}, since the response format requires a JSON object.',
                    ],
                    ['role' => 'user', 'content' => $transcript],
                ],
            ]);

        $content = $this->extractFirstChoiceContent($response);
        $decoded = json_decode($content, true);
        $lines = is_array($decoded) ? ($decoded['lines'] ?? $decoded) : [];

        if (! is_array($lines)) {
            throw new RuntimeException('OpenAI response was not a JSON array of lines.');
        }

        return array_values(array_filter(array_map(
            static fn ($line): string => trim((string) $line),
            $lines
        ), static fn (string $line): bool => $line !== ''));
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
