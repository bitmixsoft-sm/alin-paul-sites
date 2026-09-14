<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AI\AIOrchestratorService;
use App\Services\AI\PersonaPromptBuilder;
use App\Services\AI\ProfileConversionRankingService;
use App\Services\AI\ProfileTranscriptBuilder;
use App\Services\AI\StyleDistillationService;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

/**
 * Client's request (see conversation with the client, 2026-09-14): let an admin take the
 * conversation history of whichever female profile has actually converted clients into paying
 * subscribers the best, distill it into a reusable "conversational style" (tone/approach, not
 * literal sentences - see PersonaPromptBuilder/AIOrchestratorService's existing styleGuide
 * plumbing, built for the separate AI Companions catalog but unused for real profiles until
 * now), and apply that style to other real female profiles' AI auto-replies
 * (ChatBotController::sendAiReply()).
 */
final class AdminStyleLearningController extends Controller
{
    public function index(Request $request, ProfileConversionRankingService $ranking): View
    {
        $this->authorizeAdmin();

        $on_page = 'AI Style Learning';
        $ranked = $ranking->rank();

        $profiles = User::where('gender', 'female')
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname', 'learning_snapshot']);

        return view('admin.ai_style_learning', compact('on_page', 'ranked', 'profiles'));
    }

    /**
     * Builds a transcript from $user's OWN message history and distills a style guide from it -
     * "let this profile learn from her own conversations", the first half of the client's ask.
     */
    public function distill(Request $request, User $user, ProfileTranscriptBuilder $transcripts, StyleDistillationService $distillation): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->authorizeFemale($user);

        $transcript = $transcripts->build($user->id);

        if (trim($transcript) === '') {
            return back()->with('status', $user->name() . ' has no message history to learn from yet.');
        }

        try {
            $styleGuide = $distillation->distill($transcript);
        } catch (Throwable $throwable) {
            return back()->with('status', 'Failed to distill style: ' . $throwable->getMessage());
        }

        $this->saveStyleGuide($user, $styleGuide, sourceUserId: $user->id);

        return back()->with('status', 'Style guide learned from ' . $user->name() . '\'s own conversations and saved.');
    }

    /**
     * Copies an already-distilled style guide from one profile onto one or more others - "use
     * the best-converting profile's approach on other profiles too", the second half of the
     * client's ask.
     */
    public function apply(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'source_user_id' => ['required', 'integer', 'exists:users,id'],
            'target_user_ids' => ['required', 'array', 'min:1'],
            'target_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $source = User::where('id', $validated['source_user_id'])->firstOrFail();
        $this->authorizeFemale($source);

        $styleGuide = trim((string) (($source->learning_snapshot ?? [])['style_guide'] ?? ''));

        if ($styleGuide === '') {
            return back()->with('status', $source->name() . ' does not have a distilled style guide yet - distill one first.');
        }

        $targets = User::where('gender', 'female')
            ->whereIn('id', $validated['target_user_ids'])
            ->get();

        foreach ($targets as $target) {
            $this->saveStyleGuide($target, $styleGuide, sourceUserId: $source->id);
        }

        return back()->with('status', 'Applied ' . $source->name() . '\'s style to ' . $targets->count() . ' profile(s).');
    }

    public function clear(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->authorizeFemale($user);

        $user->learning_snapshot = null;
        $user->save();

        return back()->with('status', 'Style guide removed from ' . $user->name() . '.');
    }

    /**
     * Testing level 1 from the conversation with the user (2026-09-14): generates the SAME
     * test message's reply with and without the profile's saved style guide, side by side, so
     * the admin/client can immediately see whether it's wired up and whether it actually
     * changes anything - without needing to run a real chat conversation to find out.
     */
    public function preview(Request $request, AIOrchestratorService $orchestrator, PersonaPromptBuilder $persona): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = User::where('id', $validated['user_id'])->firstOrFail();
        $this->authorizeFemale($user);

        $systemPrompt = $persona->build($user);
        $styleGuide = trim((string) (($user->learning_snapshot ?? [])['style_guide'] ?? ''));

        try {
            $without = $orchestrator->generateTextReply(message: $validated['message'], systemPrompt: $systemPrompt);
            $with = $styleGuide !== ''
                ? $orchestrator->generateTextReply(message: $validated['message'], systemPrompt: $systemPrompt, styleGuide: $styleGuide)
                : null;
        } catch (Throwable $throwable) {
            return response()->json(['error' => $throwable->getMessage()], 502);
        }

        return response()->json([
            'without_style' => $without,
            'with_style' => $with,
            'has_style_guide' => $styleGuide !== '',
        ]);
    }

    private function saveStyleGuide(User $user, string $styleGuide, int $sourceUserId): void
    {
        $user->learning_snapshot = [
            'style_guide' => $styleGuide,
            'style_guide_updated_at' => now()->toIso8601String(),
            'style_guide_source_user_id' => $sourceUserId,
        ];
        $user->save();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    private function authorizeFemale(User $user): void
    {
        abort_unless($user->gender === 'female', 422, 'This feature only applies to female profiles.');
    }
}
