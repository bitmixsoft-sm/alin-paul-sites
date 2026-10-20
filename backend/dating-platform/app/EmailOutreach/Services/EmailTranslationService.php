<?php

namespace App\EmailOutreach\Services;

use App\EmailOutreach\Models\EmailOutreachAccount;
use Illuminate\Support\Facades\Http;

/**
 * Optional (account->openai_api_key may be blank, see EmailOutreachAccount) - translates an
 * outgoing phrase into a recipient's language, and detects the language of an incoming reply.
 * Deliberately calls OpenAI directly with the account's own key rather than reusing this app's
 * App\Services\AI\* classes, so the whole EmailOutreach feature stays a self-contained, 1:1
 * copy-pasteable module for the client's other sites (some on Laravel 5.7, which don't have
 * those classes at all).
 */
class EmailTranslationService
{
    public function translate(EmailOutreachAccount $account, string $text, string $targetLanguage): string
    {
        if (!$account->openai_api_key || !$targetLanguage) {
            return $text;
        }

        $response = Http::timeout(20)
            ->withToken($account->openai_api_key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => "Translate the user's message into the language with ISO code \"{$targetLanguage}\". "
                        . 'Keep the tone and any HTML formatting exactly as-is. Reply with ONLY the translated text, nothing else.'],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        if (!$response->ok()) {
            \Log::warning('[EmailTranslationService] translate() failed: '.$response->body());
            return $text;
        }

        $translated = trim((string) ($response->json('choices.0.message.content') ?? ''));
        return $translated !== '' ? $translated : $text;
    }

    /**
     * @return string|null a 2-letter ISO language code, or null if detection failed
     */
    public function detectLanguage(EmailOutreachAccount $account, string $text): ?string
    {
        if (!$account->openai_api_key || trim($text) === '') {
            return null;
        }

        $response = Http::timeout(20)
            ->withToken($account->openai_api_key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'temperature' => 0,
                'messages' => [
                    ['role' => 'system', 'content' => 'Identify the language of the user\'s message. Reply with ONLY its 2-letter ISO 639-1 code (e.g. "en", "ro", "hu"), nothing else.'],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        if (!$response->ok()) {
            return null;
        }

        $code = strtolower(trim((string) ($response->json('choices.0.message.content') ?? '')));
        return preg_match('/^[a-z]{2}$/', $code) ? $code : null;
    }
}
