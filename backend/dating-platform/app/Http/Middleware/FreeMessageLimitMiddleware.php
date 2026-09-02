<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Conversation;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FreeMessageLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user === null || $user->isAdmin() || $user->paidPackage()) {
            return $next($request);
        }

        if ((bool) config('ai.skip_message_limit', false)) {
            return $next($request);
        }

        $unlimitedIds = array_filter(array_map('intval', explode(',', (string) config('ai.unlimited_user_ids', ''))));
        if (in_array((int) $user->id, $unlimitedIds, true)) {
            return $next($request);
        }

        $limit = (int) config('ai.free_message_limit', 5);
        $usedMessages = $this->resolveUsedMessages($request, (int) $user->id);

        if ($usedMessages < $limit) {
            return $next($request);
        }

        return $this->limitReachedResponse($limit, $usedMessages);
    }

    private function resolveUsedMessages(Request $request, int $userId): int
    {
        $conversationId = (int) $request->input('conversation_id', 0);

        if ($conversationId > 0) {
            $conversation = Conversation::query()
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();

            if ($conversation !== null) {
                return (int) $conversation->message_count;
            }
        }

        return (int) Conversation::query()
            ->where('user_id', $userId)
            ->sum('message_count');
    }

    private function limitReachedResponse(int $limit, int $usedMessages): JsonResponse
    {
        return response()->json([
            'error' => 'free_limit_reached',
            'message' => 'You reached the free message limit. Upgrade to continue chatting.',
            'show_payment_modal' => true,
            'used_messages' => $usedMessages,
            'free_message_limit' => $limit,
        ], 402);
    }
}
