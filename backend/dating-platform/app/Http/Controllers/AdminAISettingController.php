<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\AIProfile;
use App\AISetting;
use App\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class AdminAISettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', AIProfile::class);

        $on_page = 'AI Settings';
        $aiSetting = AISetting::current();

        $aiUsageToday = Message::where('ai_generated', true)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $aiUsageThisWeek = Message::where('ai_generated', true)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return view('admin.ai_settings', compact('on_page', 'aiSetting', 'aiUsageToday', 'aiUsageThisWeek'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage', AIProfile::class);

        $validated = $request->validate([
            'live_avatar_provider' => ['required', 'string', 'in:tavus_cvi,simli'],
            'live_video_layout' => ['required', 'string', 'in:floating,docked'],
            'live_video_blur_enabled' => ['required', 'in:0,1'],
            'live_video_blur_amount' => ['required', 'integer', 'between:' . AISetting::MIN_LIVE_VIDEO_BLUR_AMOUNT . ',' . AISetting::MAX_LIVE_VIDEO_BLUR_AMOUNT],
            'live_video_mute_enabled' => ['required', 'in:0,1'],
            'live_video_mute_default' => ['required', 'in:0,1'],
            // The "Async Video Reply" section of the form is currently commented out
            // (feature not ready), so these two fields are never submitted — nullable,
            // not required, or every save of this form would fail validation.
            'avatar_video_enabled' => ['nullable', 'in:0,1'],
            'avatar_video_provider' => ['nullable', 'string', 'in:did,heygen,tavus'],
            'tavus_api_key' => ['nullable', 'string', 'max:500'],
            'simli_api_key' => ['nullable', 'string', 'max:500'],
            'heygen_api_key' => ['nullable', 'string', 'max:500'],
            'did_api_key' => ['nullable', 'string', 'max:500'],
            'openai_api_key' => ['nullable', 'string', 'max:500'],
            'openai_model' => ['nullable', 'string', 'max:100'],
            'text_ai_enabled' => ['required', 'in:0,1'],
            'live_ai_video_enabled' => ['required', 'in:0,1'],
            'chat_instructions_enabled' => ['nullable', 'in:0,1'],
            'chat_general_instructions' => ['nullable', 'string', 'max:8000'],
            'chat_product_knowledge' => ['nullable', 'string', 'max:8000'],
        ]);

        $aiSetting = AISetting::current();

        $aiSetting->live_avatar_provider = $validated['live_avatar_provider'];
        $aiSetting->live_video_layout = $validated['live_video_layout'];
        $aiSetting->live_video_blur_enabled = (bool) ((int) $validated['live_video_blur_enabled']);
        $aiSetting->live_video_blur_amount = (int) $validated['live_video_blur_amount'];
        $aiSetting->live_video_mute_enabled = (bool) ((int) $validated['live_video_mute_enabled']);
        $aiSetting->live_video_mute_default = (bool) ((int) $validated['live_video_mute_default']);

        if (array_key_exists('avatar_video_enabled', $validated)) {
            $aiSetting->avatar_video_enabled = (bool) ((int) $validated['avatar_video_enabled']);
        }

        if (array_key_exists('avatar_video_provider', $validated)) {
            $aiSetting->avatar_video_provider = $validated['avatar_video_provider'];
        }

        foreach (['tavus_api_key', 'simli_api_key', 'heygen_api_key', 'did_api_key', 'openai_api_key'] as $key) {
            $value = trim((string) ($validated[$key] ?? ''));

            if ($value !== '') {
                $aiSetting->{$key} = $value;
            }
        }

        // Unlike the API keys (left blank to mean "don't touch the saved secret"), the model
        // field is safe to overwrite outright — an empty value here just means "fall back to
        // .env", which is a normal, intentional state, not an accidental blank submit.
        $aiSetting->openai_model = trim((string) ($validated['openai_model'] ?? ''));
        $aiSetting->text_ai_enabled = (bool) ((int) $validated['text_ai_enabled']);
        $aiSetting->live_ai_video_enabled = (bool) ((int) $validated['live_ai_video_enabled']);

        if (array_key_exists('chat_instructions_enabled', $validated) && $validated['chat_instructions_enabled'] !== null) {
            $aiSetting->chat_instructions_enabled = (bool) ((int) $validated['chat_instructions_enabled']);
        }

        // Saved as null (= "use the built-in default", see ChatSalesPrompts) when left empty or
        // identical to the default, so future improvements to the default still reach admins who
        // never actually customized it, instead of being frozen at whatever it said on the day
        // they first pressed Save.
        foreach ([
            'chat_general_instructions' => \App\Services\AI\ChatSalesPrompts::DEFAULT_GENERAL_INSTRUCTIONS,
            'chat_product_knowledge' => \App\Services\AI\ChatSalesPrompts::DEFAULT_PRODUCT_KNOWLEDGE,
        ] as $field => $default) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $normalize = static fn (string $text): string => trim(str_replace("
", "
", $text));
            $submitted = $normalize((string) $validated[$field]);

            $aiSetting->{$field} = ($submitted === '' || $submitted === $normalize($default)) ? null : $submitted;
        }

        $aiSetting->save();

        return redirect('/admin/ai-settings')->with('status', 'AI settings updated successfully.');
    }
}
