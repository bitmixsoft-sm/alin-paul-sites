<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\User;

/**
 * Builds the AI persona text for a real female profile: the admin's custom prompt if set
 * (resources/views/admin/user_profile.blade.php), else a template from the profile's own
 * fields. Shared by the real-chat text auto-reply (ChatBotController) and the live Simli
 * video call (SimliService::createSessionForUser()) so both speak as the same "person."
 */
final class PersonaPromptBuilder
{
    public function build(User $user): string
    {
        $customPrompt = trim((string) $user->ai_system_prompt);

        if ($customPrompt !== '') {
            return $customPrompt;
        }

        $details = [];
        $details[] = 'Your name is ' . $user->firstname . '.';

        if (! empty($user->birthday)) {
            $details[] = 'You are ' . $user->age() . ' years old.';
        }

        if (! empty($user->city) || ! empty($user->country)) {
            $details[] = 'You live in ' . trim($user->city . ' ' . $user->country) . '.';
        }

        if (! empty($user->job)) {
            $details[] = 'Your job: ' . $user->job . '.';
        }

        if (! empty($user->description)) {
            $details[] = 'About you: ' . $user->description;
        }

        if (! empty($user->moto)) {
            $details[] = 'Your personal motto/tagline: "' . $user->moto . '".';
        }

        return "You are chatting on a dating website. " . implode(' ', $details)
            . ' Be warm, flirty, and genuinely curious about the person you are talking to.';
    }
}
