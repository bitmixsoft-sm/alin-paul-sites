<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\AIProfile;
use App\AISetting;
use App\Conversation;
use App\ConversationMessage;
use App\DTO\AI\OrchestratorRequestData;
use App\Services\AI\AIOrchestratorService;
use App\Services\AI\ConversationMemoryService;
use App\Services\AI\SimliService;
use App\Services\AI\TavusCviService;
use App\Services\AdminAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Throwable;

final class AIChatController extends Controller
{
    private const HISTORY_DISPLAY_LIMIT = 200;

    public function room(): View
    {
        $profiles = AIProfile::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'static_image_path'])
            ->orderBy('name')
            ->get()
            ->toArray();

        return view('ai.avatar-chat', [
            'profiles' => $profiles,
            'aiSetting' => AISetting::current(),
        ]);
    }

    public function send(Request $request, AIOrchestratorService $orchestrator, ConversationMemoryService $memoryService): JsonResponse
    {
        $validated = $request->validate([
            'ai_profile_id' => ['required', 'integer', 'exists:ai_profiles,id'],
            'message' => ['required', 'string', 'max:5000'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'input_channel' => ['nullable', 'in:text,voice'],
        ]);

        $userId = $this->resolveActingUserId($request);
        $profile = AIProfile::query()->where('id', $validated['ai_profile_id'])->where('is_active', true)->firstOrFail();
        $conversation = $this->resolveConversation(
            userId: $userId,
            profileId: (int) $profile->id,
            conversationId: isset($validated['conversation_id']) ? (int) $validated['conversation_id'] : null,
            inputChannel: (string) ($validated['input_channel'] ?? 'text'),
        );

        $inputChannel = (string) ($validated['input_channel'] ?? 'text');

        $userMessage = ConversationMessage::query()->create([
            'conversation_id' => (int) $conversation->id,
            'user_id' => $userId,
            'role' => 'user',
            'channel' => $inputChannel,
            'content' => (string) $validated['message'],
            'metadata' => [
                'source' => 'chat-room',
            ],
        ]);

        $memoryNote = $conversation->summary;

        try {
            $memoryNote = $memoryService->refreshSummary($conversation);
        } catch (Throwable $throwable) {
            report($throwable);
        }

        try {
            $result = $orchestrator->orchestrate(
                new OrchestratorRequestData(
                    userId: $userId,
                    aiProfileId: (int) $profile->id,
                    message: (string) $validated['message'],
                    systemPrompt: (string) $profile->system_prompt,
                    voiceId: (string) $profile->voice_id,
                    imageUrl: $profile->imageUrl(),
                    heygenTalkingPhotoId: (string) data_get((array) ($profile->learning_snapshot ?? []), 'heygen_talking_photo_id', ''),
                    conversationId: (int) $conversation->id,
                    inputChannel: $inputChannel,
                    history: $this->resolveHistory((int) $conversation->id, excludeMessageId: (int) $userMessage->id),
                    styleGuide: $this->resolveStyleGuide((array) ($profile->learning_snapshot ?? [])),
                    memoryNote: $memoryNote,
                )
            );
        } catch (Throwable $throwable) {
            report($throwable);

            $userMessage->conversion_signal = 'orchestration_failed';
            $userMessage->save();

            if (str_contains($throwable->getMessage(), 'OPENAI_QUOTA_EXCEEDED')) {
                return response()->json([
                    'error' => 'openai_quota_exceeded',
                    'message' => 'The AI service has reached its usage limit. Please contact the administrator to top up the OpenAI account.',
                ], 503);
            }

            return response()->json([
                'error' => 'ai_orchestration_failed',
                'message' => 'AI response generation failed. Please try again in a moment.',
            ], 502);
        }

        $assistantSignal = $this->resolveConversionSignal($result->assistantText);

        ConversationMessage::query()->create([
            'conversation_id' => (int) $conversation->id,
            'role' => 'assistant',
            'channel' => 'text',
            'content' => $result->assistantText,
            'emotion' => $result->emotion->value,
            'conversion_signal' => $assistantSignal,
            'metadata' => [
                'audio_url' => $result->audioUrl,
                'video_url' => $result->videoUrl,
                'video_status' => $result->videoStatus,
            ],
        ]);

        if ($assistantSignal !== null) {
            $userMessage->conversion_signal = 'received_' . $assistantSignal;
            $userMessage->save();
        }

        $context = (array) ($conversation->conversion_context ?? []);
        $context['last_assistant_preview'] = mb_substr($result->assistantText, 0, 250);
        $context['last_interaction_at'] = Carbon::now()->toIso8601String();
        $context['last_conversion_signal'] = $assistantSignal;
        $context['last_user_message_id'] = $userMessage->id;
        $context['last_assistant_emotion'] = $result->emotion->value;

        $conversation->message_count = (int) $conversation->message_count + 1;
        $conversation->last_user_emotion = $result->emotion->value;
        $conversation->conversion_context = $context;
        $conversation->save();

        return response()->json([
            'conversation_id' => $conversation->id,
            'assistant_text' => $result->assistantText,
            'emotion' => $result->emotion->value,
            'audio_url' => $result->audioUrl,
            'video_url' => $result->videoUrl,
            'video_status' => $result->videoStatus,
            'meta' => $result->raw,
        ]);
    }

    public function videoStatus(Request $request, AIOrchestratorService $orchestrator): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => ['required', 'string', 'max:120'],
        ]);

        $status = $orchestrator->fetchVideoStatus((string) $validated['video_id']);

        return response()->json($status);
    }

    /**
     * Returns the user's most recent conversation with the given AI profile, so the
     * chat popup can re-populate its message list when it's reopened (it otherwise
     * starts out empty since only in-memory JS state tracks the open conversation).
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ai_profile_id' => ['required', 'integer', 'exists:ai_profiles,id'],
        ]);

        $conversation = Conversation::query()
            ->where('user_id', $this->resolveActingUserId($request))
            ->where('ai_profile_id', (int) $validated['ai_profile_id'])
            ->orderByDesc('id')
            ->first();

        if ($conversation === null) {
            return response()->json([
                'conversation_id' => null,
                'messages' => [],
            ]);
        }

        $messages = ConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->limit(self::HISTORY_DISPLAY_LIMIT)
            ->get(['role', 'content', 'created_at']);

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages->map(fn (ConversationMessage $message): array => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
                'created_at' => $message->created_at?->toIso8601String(),
            ])->values()->all(),
        ]);
    }

    public function startVideoSession(Request $request, TavusCviService $tavus, SimliService $simli, ConversationMemoryService $memoryService, AdminAlertService $adminAlert): JsonResponse
    {
        $validated = $request->validate([
            'ai_profile_id'   => ['required', 'integer', 'exists:ai_profiles,id'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'provider'        => ['nullable', 'string', 'in:tavus_cvi,simli'],
        ]);

        $userId  = $this->resolveActingUserId($request);
        $profile = AIProfile::query()->where('id', $validated['ai_profile_id'])->where('is_active', true)->firstOrFail();
        $provider = $this->resolveLiveAvatarProvider($validated['provider'] ?? null);

        $memoryNote = null;

        if (isset($validated['conversation_id'])) {
            $conversation = Conversation::query()
                ->where('id', $validated['conversation_id'])
                ->where('user_id', $userId)
                ->first();

            if ($conversation !== null) {
                try {
                    $memoryNote = $memoryService->refreshSummary($conversation);
                } catch (Throwable $throwable) {
                    report($throwable);
                }
            }
        }

        try {
            if ($provider === 'simli') {
                $session = $simli->createSession($profile, $memoryNote);
                $roomUrl = $session['room_url'];
                $sessionId = $session['session_id'];
                $status = $session['status'];
            } else {
                $session = $tavus->createConversation($profile, $memoryNote);
                $roomUrl = $session['conversation_url'];
                $sessionId = $session['conversation_id'];
                $status = $session['status'];
            }
        } catch (Throwable $throwable) {
            report($throwable);

            if (! str_contains($throwable->getMessage(), 'HTTP 429') && ! str_contains($throwable->getMessage(), 'Rate limit')) {
                $adminAlert->notify(
                    key: 'live_session_failed:' . $provider,
                    subject: 'Live video session failing: ' . $provider,
                    body: "The \"{$provider}\" live avatar provider is failing to start sessions.\n\n"
                        . "User ID: {$userId}\nAI Profile ID: {$profile->id}\n\n"
                        . "Error:\n" . $throwable->getMessage(),
                );
            }

            return response()->json([
                'error'   => 'live_session_failed',
                'message' => $this->friendlyLiveSessionError($throwable),
            ], 502);
        }

        return response()->json([
            'provider'              => $provider,
            'room_url'              => $roomUrl,
            'session_id'            => $sessionId,
            'status'                => $status,
            // legacy keys kept for backward compatibility with the existing frontend
            'conversation_url'      => $roomUrl,
            'tavus_conversation_id' => $sessionId,
        ]);
    }

    /**
     * Translates provider exceptions (which carry raw HTTP/JSON details meant for
     * logs) into a message that's safe and sensible to show to end users.
     */
    private function friendlyLiveSessionError(Throwable $throwable): string
    {
        $message = $throwable->getMessage();

        if (str_contains($message, 'HTTP 429') || str_contains($message, 'Rate limit')) {
            return 'The live video service is busy right now. Please wait a moment and try again.';
        }

        if (str_contains($message, 'create agent failed') || str_contains($message, 'HTTP 5') || str_contains($message, 'HTTP 400')) {
            return 'The live video service is temporarily unavailable. Our team has been notified and is looking into it — please try again shortly.';
        }

        return 'We couldn\'t start the live video session. Please try again in a little while.';
    }

    public function endVideoSession(Request $request, TavusCviService $tavus, SimliService $simli): JsonResponse
    {
        $validated = $request->validate([
            'tavus_conversation_id' => ['nullable', 'string', 'max:120'],
            'session_id'            => ['nullable', 'string', 'max:120'],
            'provider'              => ['nullable', 'string', 'in:tavus_cvi,simli'],
        ]);

        $sessionId = (string) ($validated['session_id'] ?? $validated['tavus_conversation_id'] ?? '');
        $provider  = $this->resolveLiveAvatarProvider($validated['provider'] ?? null);

        try {
            if ($provider === 'simli') {
                $simli->endSession($sessionId);
            } else {
                $tavus->endConversation($sessionId);
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }

        return response()->json(['status' => 'ended']);
    }

    /**
     * The live avatar provider defaults to the admin-configured AISetting, but can be
     * overridden per-request (e.g. a UI toggle) so both providers can be A/B tested.
     */
    private function resolveLiveAvatarProvider(?string $requested): string
    {
        if ($requested !== null && in_array($requested, ['tavus_cvi', 'simli'], true)) {
            return $requested;
        }

        return AISetting::current()->liveAvatarProvider();
    }

    /**
     * Admin/editor accounts can act as one of their managed sub-accounts (WebAccount) when
     * chatting with an AI profile, mirroring ChatController@send's existing "from" convention
     * for the real user-to-user chat's admin account-switcher (resources/views/components/chat.blade.php).
     * As with that existing convention, the "from" id is trusted outright once the isAdmin()
     * gate passes — there's no extra check that the id is actually one of the admin's own
     * WebAccounts.
     */
    private function resolveActingUserId(Request $request): int
    {
        $from = $request->input('from');

        if ($request->user()->isAdmin() && $from !== null && $from !== 'no') {
            return (int) $from;
        }

        return (int) $request->user()->id;
    }

    private function resolveConversation(int $userId, int $profileId, ?int $conversationId, string $inputChannel): Conversation
    {
        if ($conversationId !== null) {
            $existingConversation = Conversation::query()
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->where('ai_profile_id', $profileId)
                ->first();

            if ($existingConversation !== null) {
                return $existingConversation;
            }
        }

        return Conversation::query()->create([
            'user_id' => $userId,
            'ai_profile_id' => $profileId,
            'channel' => $inputChannel,
            'started_at' => Carbon::now(),
            'message_count' => 0,
            'conversion_context' => [],
        ]);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function resolveHistory(int $conversationId, int $excludeMessageId): array
    {
        $limit = (int) config('ai.history_message_limit', 20);

        if ($limit <= 0) {
            return [];
        }

        return ConversationMessage::query()
            ->where('conversation_id', $conversationId)
            ->where('id', '!=', $excludeMessageId)
            ->whereIn('role', ['user', 'assistant'])
            ->latest('id')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn (ConversationMessage $message): array => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
            ])
            ->values()
            ->all();
    }

    private function resolveStyleGuide(array $learningSnapshot): ?string
    {
        $styleGuide = trim((string) ($learningSnapshot['style_guide'] ?? ''));

        return $styleGuide !== '' ? $styleGuide : null;
    }

    private function resolveConversionSignal(string $assistantText): ?string
    {
        $normalizedText = mb_strtolower($assistantText);

        if (
            str_contains($normalizedText, 'upgrade') ||
            str_contains($normalizedText, 'premium') ||
            str_contains($normalizedText, 'subscribe') ||
            str_contains($normalizedText, 'subscription')
        ) {
            return 'subscription_prompt';
        }

        if (
            str_contains($normalizedText, 'continue this') ||
            str_contains($normalizedText, 'unlock') ||
            str_contains($normalizedText, 'exclusive')
        ) {
            return 'upsell_soft';
        }

        return null;
    }
}
