<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\AISetting;
use App\Conversation;
use App\ConversationMessage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ConversationMemoryService
{
    /**
     * Folds older messages into a running summary once enough of them pile up
     * beyond the verbatim history window, and returns the memory note to
     * inject into the system prompt (existing summary if nothing new to fold).
     */
    public function refreshSummary(Conversation $conversation): ?string
    {
        $keepVerbatim = max(0, (int) config('ai.history_message_limit', 20));
        $batchSize = max(1, (int) config('ai.memory_summary_batch_size', 15));

        $messages = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get(['id', 'role', 'content']);

        $total = $messages->count();

        if ($total <= $keepVerbatim) {
            return $conversation->summary;
        }

        $cutoffMessage = $messages->get($total - $keepVerbatim - 1);
        if ($cutoffMessage === null) {
            return $conversation->summary;
        }

        $summarizedThroughId = (int) ($conversation->summarized_through_message_id ?? 0);

        $pending = $messages->filter(
            fn (ConversationMessage $message): bool => $message->id > $summarizedThroughId && $message->id <= $cutoffMessage->id
        )->values();

        if ($pending->count() < $batchSize) {
            return $conversation->summary;
        }

        $summary = $this->summarize((string) ($conversation->summary ?? ''), $pending);

        $conversation->summary = $summary;
        $conversation->summarized_through_message_id = $cutoffMessage->id;
        $conversation->save();

        return $summary;
    }

    /**
     * @param \Illuminate\Support\Collection<int, ConversationMessage> $newMessages
     */
    private function summarize(string $existingSummary, $newMessages): string
    {
        $model = 'gpt-4o-mini';
        $apiKey = AISetting::current()->apiKey('openai');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $transcript = $newMessages
            ->map(fn (ConversationMessage $message): string => ucfirst($message->role) . ': ' . $message->content)
            ->implode("\n");

        $instructions = 'You maintain a running long-term memory note for an ongoing relationship-style chat persona. '
            . "Update the memory note below with facts from the new messages: the user's name, preferences, important life "
            . 'details, promises made, emotional milestones, and anything the persona should keep remembering. Keep it factual, '
            . 'concise (max 250 words), written in third person as private notes (never shown to the user). Merge overlapping '
            . 'facts instead of duplicating them, and drop anything that is no longer relevant.';

        $userContent = "Existing memory note:\n" . ($existingSummary !== '' ? $existingSummary : '(none yet)')
            . "\n\nNew messages to fold in:\n" . $transcript;

        $response = Http::timeout(30)
            ->retry(1, 300)
            ->withToken($apiKey)
            ->post(env('OPENAI_API_BASE_URL', 'https://api.openai.com/v1') . '/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => $instructions],
                    ['role' => 'user', 'content' => $userContent],
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
