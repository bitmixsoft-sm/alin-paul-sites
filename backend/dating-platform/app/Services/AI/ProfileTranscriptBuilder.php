<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Message;
use App\User;

/**
 * Builds a plain-text transcript of a real female profile's own message history, formatted for
 * StyleDistillationService::distill() (the same service the AI Companions catalog already uses
 * for its "learn a style from a transcript" admin action - see AdminAIProfileController).
 *
 * The client asked for "the whole conversation from the last years or as much as is available",
 * but an LLM call has a finite context window - $maxChars caps the transcript to the most
 * recent messages that fit, rather than failing or silently truncating mid-request. For a
 * profile with years of history this means "as much of the recent history as fits", not
 * literally every message ever sent - worth surfacing to the client as a real limitation, not a
 * corner cut for convenience.
 */
final class ProfileTranscriptBuilder
{
    public function build(int $femaleUserId, int $maxChars = 40000): string
    {
        $femaleName = User::where('id', $femaleUserId)->value('firstname') ?: 'Her';

        $messages = Message::query()
            ->where('from_user', $femaleUserId)
            ->orWhere('to_user', $femaleUserId)
            ->orderByDesc('id')
            ->limit(4000)
            ->get(['from_user', 'to_user', 'message']);

        $lines = [];
        $length = 0;

        foreach ($messages as $message) {
            $speaker = (int) $message->from_user === $femaleUserId ? $femaleName : 'Client';
            $line = $speaker . ': ' . trim((string) $message->message);

            // Messages were pulled newest-first so the cap keeps the most RECENT history when
            // there's more than fits - prepending here restores chronological order in the
            // final transcript.
            $length += strlen($line) + 1;

            if ($length > $maxChars) {
                break;
            }

            array_unshift($lines, $line);
        }

        return implode("\n", $lines);
    }
}
