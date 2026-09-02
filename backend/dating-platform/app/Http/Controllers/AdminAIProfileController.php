<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\AIProfile;
use App\AISetting;
use App\Conversation;
use App\ConversationMessage;
use App\Services\AI\ElevenLabsVoiceCatalog;
use App\Services\AI\StyleDistillationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

final class AdminAIProfileController extends Controller
{
    public function index(Request $request, ElevenLabsVoiceCatalog $elevenLabsVoiceCatalog): JsonResponse|View
    {
        $this->authorize('viewAny', AIProfile::class);

        $profiles = AIProfile::query()
            ->with('createdByAdmin:id,firstname,lastname,email')
            ->latest('id')
            ->paginate((int) $request->input('per_page', 20));

        if (! $request->expectsJson()) {
            $on_page = 'AI Profiles';
            $elevenLabsVoices = $elevenLabsVoiceCatalog->list();
            $conversationOptions = $this->getConversationOptions();
            $aiSetting = AISetting::current();

            return view('admin.ai_profiles', compact('profiles', 'on_page', 'elevenLabsVoices', 'conversationOptions', 'aiSetting'));
        }

        return response()->json($profiles);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', AIProfile::class);

        $validated = $request->validate([
            'name'                     => ['required', 'string', 'max:120'],
            'system_prompt'            => ['required', 'string'],
            'voice_id'                 => ['required', 'string', 'max:120'],
            'heygen_talking_photo_id'  => ['nullable', 'string', 'max:120'],
            'tavus_face_id'            => ['nullable', 'string', 'max:120'],
            'tavus_replica_id'         => ['nullable', 'string', 'max:120'],
            'simli_face_id'            => ['nullable', 'string', 'max:120'],
            'static_image'             => ['nullable', 'image', 'max:5120'],
            'static_image_path'        => ['nullable', 'string', 'max:2048'],
            'is_active'                => ['sometimes', 'boolean'],
        ]);

        if (! $request->hasFile('static_image') && trim((string) ($validated['static_image_path'] ?? '')) === '') {
            $message = 'Please upload a static image or provide a static image URL/path.';

            if (! $request->expectsJson()) {
                return redirect('/admin/ai-profiles')->withInput()->with('status', $message);
            }

            return response()->json(['error' => 'validation_failed', 'message' => $message], 422);
        }

        $staticImagePath = $this->resolveStaticImagePath($request, $validated['static_image_path'] ?? null);

        $learningSnapshot = [];

        $talkingPhotoId = trim((string) ($validated['heygen_talking_photo_id'] ?? ''));
        if ($talkingPhotoId !== '') {
            $learningSnapshot['heygen_talking_photo_id'] = $talkingPhotoId;
        }

        $tavusFaceId = trim((string) ($validated['tavus_face_id'] ?? ''));
        if ($tavusFaceId !== '') {
            $learningSnapshot['tavus_face_id'] = $tavusFaceId;
        }

        $tavusReplicaId = trim((string) ($validated['tavus_replica_id'] ?? ''));
        if ($tavusReplicaId !== '') {
            $learningSnapshot['tavus_replica_id'] = $tavusReplicaId;
        }

        $simliFaceId = trim((string) ($validated['simli_face_id'] ?? ''));
        if ($simliFaceId !== '') {
            $learningSnapshot['simli_face_id'] = $simliFaceId;
        }

        $profile = AIProfile::query()->create([
            'created_by_admin_id' => (int) Auth::id(),
            'name'                => $validated['name'],
            'static_image_path'   => $staticImagePath,
            'system_prompt'       => $validated['system_prompt'],
            'voice_id'            => $validated['voice_id'],
            'learning_snapshot'   => $learningSnapshot,
            'is_active'           => (bool) ($validated['is_active'] ?? true),
        ]);

        if (! $request->expectsJson()) {
            return redirect('/admin/ai-profiles')->with('status', 'AI profile created successfully.');
        }

        return response()->json($profile->fresh(), 201);
    }

    public function show(AIProfile $aiProfile): JsonResponse
    {
        $this->authorize('view', $aiProfile);

        return response()->json($aiProfile->load('createdByAdmin:id,firstname,lastname,email'));
    }

    public function update(Request $request, AIProfile $aiProfile): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $aiProfile);

        $validated = $request->validate([
            'name'                     => ['sometimes', 'required', 'string', 'max:120'],
            'system_prompt'            => ['sometimes', 'required', 'string'],
            'voice_id'                 => ['sometimes', 'required', 'string', 'max:120'],
            'heygen_talking_photo_id'  => ['sometimes', 'nullable', 'string', 'max:120'],
            'tavus_face_id'            => ['sometimes', 'nullable', 'string', 'max:120'],
            'tavus_replica_id'         => ['sometimes', 'nullable', 'string', 'max:120'],
            'simli_face_id'            => ['sometimes', 'nullable', 'string', 'max:120'],
            'static_image'             => ['nullable', 'image', 'max:5120'],
            'static_image_path'        => ['nullable', 'string', 'max:2048'],
            'is_active'                => ['sometimes', 'boolean'],
            'learning_snapshot'        => ['sometimes', 'array'],
        ]);

        if ($request->hasFile('static_image') || $request->filled('static_image_path')) {
            $validated['static_image_path'] = $this->resolveStaticImagePath(
                $request,
                $validated['static_image_path'] ?? null,
                $aiProfile->static_image_path
            );
        }

        $snapshotKeys = ['heygen_talking_photo_id', 'tavus_face_id', 'tavus_replica_id', 'simli_face_id'];
        $snapshot = (array) ($aiProfile->learning_snapshot ?? []);
        $snapshotChanged = false;

        foreach ($snapshotKeys as $key) {
            if ($request->has($key)) {
                $value = trim((string) $request->input($key, ''));

                if ($value === '') {
                    unset($snapshot[$key]);
                } else {
                    $snapshot[$key] = $value;
                }

                $snapshotChanged = true;
                unset($validated[$key]);
            }
        }

        if ($snapshotChanged) {
            $validated['learning_snapshot'] = $snapshot;
        }

        $aiProfile->fill($validated);
        $aiProfile->save();

        if (! $request->expectsJson()) {
            return redirect('/admin/ai-profiles')->with('status', 'AI profile updated successfully.');
        }

        return response()->json($aiProfile->fresh());
    }

    public function destroy(Request $request, AIProfile $aiProfile): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $aiProfile);

        $aiProfile->delete();

        if (! $request->expectsJson()) {
            return redirect('/admin/ai-profiles')->with('status', 'AI profile deleted successfully.');
        }

        return response()->json(['status' => 'ok']);
    }

    public function distillStyle(Request $request, AIProfile $aiProfile, StyleDistillationService $distillationService): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $aiProfile);

        $validated = $request->validate([
            'transcript' => ['nullable', 'string', 'max:20000'],
            'conversation_ids' => ['nullable', 'array'],
            'conversation_ids.*' => ['integer', 'exists:conversations,id'],
        ]);

        $combinedTranscript = $this->buildCombinedTranscript(
            (array) ($validated['conversation_ids'] ?? []),
            (string) ($validated['transcript'] ?? '')
        );

        if (trim($combinedTranscript) === '') {
            $message = 'Select at least one conversation or paste a transcript to learn from.';

            if (! $request->expectsJson()) {
                return redirect('/admin/ai-profiles')->with('status', $message);
            }

            return response()->json(['error' => 'empty_transcript', 'message' => $message], 422);
        }

        try {
            $styleGuide = $distillationService->distill($combinedTranscript);
        } catch (Throwable $throwable) {
            report($throwable);

            if (! $request->expectsJson()) {
                return redirect('/admin/ai-profiles')->with('status', 'Failed to distill style from transcript: ' . $throwable->getMessage());
            }

            return response()->json(['error' => 'distillation_failed', 'message' => $throwable->getMessage()], 502);
        }

        $snapshot = (array) ($aiProfile->learning_snapshot ?? []);
        $snapshot['style_guide'] = $styleGuide;
        $snapshot['style_guide_updated_at'] = now()->toIso8601String();

        $aiProfile->learning_snapshot = $snapshot;
        $aiProfile->save();

        if (! $request->expectsJson()) {
            return redirect('/admin/ai-profiles')->with('status', 'Style guide distilled and saved for "' . $aiProfile->name . '".');
        }

        return response()->json(['style_guide' => $styleGuide]);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function getConversationOptions(): array
    {
        return Conversation::query()
            ->with('aiProfile:id,name')
            ->where('message_count', '>', 0)
            ->latest('id')
            ->limit(100)
            ->get(['id', 'ai_profile_id', 'message_count', 'started_at'])
            ->map(fn (Conversation $conversation): array => [
                'id' => (int) $conversation->id,
                'label' => sprintf(
                    '#%d — %s (%d messages, %s)',
                    $conversation->id,
                    $conversation->aiProfile->name ?? 'Unknown profile',
                    (int) $conversation->message_count,
                    optional($conversation->started_at)->format('Y-m-d') ?? 'n/a'
                ),
            ])
            ->all();
    }

    /**
     * @param array<int, int> $conversationIds
     */
    private function buildCombinedTranscript(array $conversationIds, string $manualTranscript): string
    {
        $parts = [];

        if (! empty($conversationIds)) {
            $conversations = Conversation::query()
                ->whereIn('id', $conversationIds)
                ->with(['messages' => fn ($query) => $query->orderBy('id')])
                ->get();

            foreach ($conversations as $conversation) {
                $lines = $conversation->messages
                    ->whereIn('role', ['user', 'assistant'])
                    ->map(fn (ConversationMessage $message): string => ucfirst($message->role) . ': ' . $message->content)
                    ->values()
                    ->all();

                if (! empty($lines)) {
                    $parts[] = "--- Conversation #{$conversation->id} ---\n" . implode("\n", $lines);
                }
            }
        }

        $manualTranscript = trim($manualTranscript);
        if ($manualTranscript !== '') {
            $parts[] = "--- Additional pasted transcript ---\n" . $manualTranscript;
        }

        $combined = implode("\n\n", $parts);
        $maxChars = 20000;

        if (mb_strlen($combined) > $maxChars) {
            $combined = mb_substr($combined, -$maxChars);
        }

        return $combined;
    }

    private function resolveStaticImagePath(Request $request, ?string $staticImagePath, ?string $oldPath = null): string
    {
        if ($request->hasFile('static_image')) {
            $newPath = $request->file('static_image')->store('ai-profiles', 'ai_public');

            if ($oldPath !== null && Storage::disk('ai_public')->exists($oldPath)) {
                Storage::disk('ai_public')->delete($oldPath);
            }

            return $newPath;
        }

        if ($staticImagePath !== null && $staticImagePath !== '') {
            return $staticImagePath;
        }

        if ($oldPath !== null && $oldPath !== '') {
            return $oldPath;
        }

        abort(422, 'Either static_image upload or static_image_path is required.');
    }
}
