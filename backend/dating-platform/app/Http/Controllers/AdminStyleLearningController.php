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
 * subscribers the best, learn from it, and apply that to other real female profiles' AI
 * auto-replies (ChatBotController::sendAiReply()).
 *
 * Two learning modes, picked per profile (2026-09-14 follow-up - the client's "inspired by tone"
 * vs. "use only phrases from that conversation" distinction):
 * - 'style': a paraphrased tone/approach guide (StyleDistillationService::distill()) - the same
 *   styleGuide plumbing the AI Companions catalog already used, just extended to real profiles.
 * - 'phrases': a bank of the persona's own real lines, extracted verbatim
 *   (StyleDistillationService::extractPhrases()), reused near-verbatim by the AI rather than
 *   paraphrased.
 * Only one mode is active per profile at a time - learning in one mode doesn't clear data saved
 * from the other, but ChatBotController::resolveLearning() only ever reads whichever 'mode' says
 * is current.
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
     * Builds a transcript from $user's OWN message history and learns from it in whichever
     * mode was requested - "let this profile learn from her own conversations".
     */
    public function distill(Request $request, User $user, ProfileTranscriptBuilder $transcripts, StyleDistillationService $distillation): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->authorizeFemale($user);

        $mode = $request->input('mode') === 'phrases' ? 'phrases' : 'style';

        $transcript = $transcripts->build($user->id);

        if (trim($transcript) === '') {
            return back()->with('status', $user->name() . ' nu are inca istoric de mesaje din care sa invete.');
        }

        try {
            if ($mode === 'phrases') {
                $phrases = $distillation->extractPhrases($transcript);

                if (empty($phrases)) {
                    return back()->with('status', 'Nu s-au gasit fraze potrivite in conversatiile lui ' . $user->name() . '.');
                }

                $this->saveLearning($user, mode: 'phrases', phrases: $phrases, sourceUserId: $user->id);
            } else {
                $styleGuide = $distillation->distill($transcript);
                $this->saveLearning($user, mode: 'style', styleGuide: $styleGuide, sourceUserId: $user->id);
            }
        } catch (Throwable $throwable) {
            return back()->with('status', 'Invatarea a esuat: ' . $throwable->getMessage());
        }

        $modeLabel = $mode === 'phrases' ? 'fraze exacte' : 'stil (ton)';

        return back()->with('status', 'A invatat ' . $modeLabel . ' din propriile conversatii ale lui ' . $user->name() . ' si a salvat.');
    }

    /**
     * Copies an already-learned style/phrases from one profile onto one or more others - "use
     * the best-converting profile's approach on other profiles too", the second half of the
     * client's ask. Copies whichever mode is currently active on the source.
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

        $snapshot = $source->learning_snapshot ?? [];
        $mode = $snapshot['mode'] ?? null;
        $styleGuide = trim((string) ($snapshot['style_guide'] ?? ''));
        $phrases = $snapshot['phrase_examples'] ?? [];

        if ($mode === null || ($mode === 'style' && $styleGuide === '') || ($mode === 'phrases' && empty($phrases))) {
            return back()->with('status', $source->name() . ' nu are inca nimic invatat - invata mai intai un stil sau fraze.');
        }

        $targets = User::where('gender', 'female')
            ->whereIn('id', $validated['target_user_ids'])
            ->get();

        foreach ($targets as $target) {
            $this->saveLearning($target, mode: $mode, styleGuide: $styleGuide !== '' ? $styleGuide : null, phrases: ! empty($phrases) ? $phrases : null, sourceUserId: $source->id);
        }

        return back()->with('status', 'Ce a invatat ' . $source->name() . ' a fost aplicat la ' . $targets->count() . ' profil(uri).');
    }

    public function clear(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->authorizeFemale($user);

        $user->learning_snapshot = null;
        $user->save();

        return back()->with('status', 'Ce a invatat ' . $user->name() . ' a fost eliminat.');
    }

    /**
     * Testing level 1 from the conversation with the user (2026-09-14): generates the SAME
     * test message's reply with and without the profile's saved learning, side by side, so
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
        [$styleGuide, $phraseExamples] = $this->resolveLearning($user);
        $hasLearning = $styleGuide !== null || $phraseExamples !== null;

        try {
            $without = $orchestrator->generateTextReply(message: $validated['message'], systemPrompt: $systemPrompt);
            $with = $hasLearning
                ? $orchestrator->generateTextReply(message: $validated['message'], systemPrompt: $systemPrompt, styleGuide: $styleGuide, phraseExamples: $phraseExamples)
                : null;
        } catch (Throwable $throwable) {
            return response()->json(['error' => $throwable->getMessage()], 502);
        }

        return response()->json([
            'without_style' => $without,
            'with_style' => $with,
            'has_style_guide' => $hasLearning,
        ]);
    }

    /**
     * Same resolution logic as ChatBotController::resolveLearning() - duplicated rather than
     * shared, since these two controllers otherwise have no common base worth introducing just
     * for this.
     *
     * @return array{0: ?string, 1: ?array<int, string>} [styleGuide, phraseExamples]
     */
    private function resolveLearning(User $user): array
    {
        $snapshot = $user->learning_snapshot ?? [];
        $mode = $snapshot['mode'] ?? 'style';

        if ($mode === 'phrases') {
            $phrases = $snapshot['phrase_examples'] ?? [];

            return [null, ! empty($phrases) ? $phrases : null];
        }

        $styleGuide = trim((string) ($snapshot['style_guide'] ?? ''));

        return [$styleGuide !== '' ? $styleGuide : null, null];
    }

    /**
     * @param array<int, string>|null $phrases
     */
    private function saveLearning(User $user, string $mode, ?string $styleGuide = null, ?array $phrases = null, int $sourceUserId = 0): void
    {
        $user->learning_snapshot = [
            'mode' => $mode,
            'style_guide' => $styleGuide,
            'phrase_examples' => $phrases,
            'updated_at' => now()->toIso8601String(),
            'source_user_id' => $sourceUserId,
        ];
        $user->save();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    private function authorizeFemale(User $user): void
    {
        abort_unless($user->gender === 'female', 422, 'Aceasta functie se aplica doar profilurilor feminine.');
    }
}
