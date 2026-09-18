<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Message;
use App\Settings;
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
 *
 * $connection (client's follow-up request, 2026-09-18) optionally points this at one of the
 * external sites configured in config/database.php/ExternalSiteRegistry instead of this app's
 * own database - same users/messages schema, since it's the same codebase on a different
 * domain, so no other change is needed here beyond which connection the two queries run on.
 *
 * The message-count cap, character cap, and whether both sides of the conversation are included
 * (client's follow-up, 2026-09-18: "esetleg nagy munka lenne hogy ezt betenni egy beallitasba")
 * are admin-editable via /admin/settings (category "AI Style Learning") instead of hardcoded -
 * see the migration that added those 3 rows. $maxChars stays overridable by a caller too (falls
 * back to the setting only when not passed explicitly), for any future caller that needs its own
 * value without touching the site-wide default.
 */
final class ProfileTranscriptBuilder
{
    public function build(int $femaleUserId, ?int $maxChars = null, ?string $connection = null): string
    {
        $maxChars ??= (int) (Settings::where('name', 'AI_STYLE_LEARNING_MAX_CHARS')->value('value') ?? 40000);
        $maxMessages = (int) (Settings::where('name', 'AI_STYLE_LEARNING_MAX_MESSAGES')->value('value') ?? 4000);
        // Default 'yes' (the original, only behavior before this setting existed) - including
        // the other party's lines gives the AI context for WHY a line was said, which matters
        // more for "Stil (ton)" than the trade-off (a very slim chance the AI misattributes a
        // client's line as hers when extracting "Fraze exacte") costs.
        $includeBothParties = (Settings::where('name', 'AI_STYLE_LEARNING_INCLUDE_BOTH_PARTIES')->value('value') ?? 'yes') !== 'no';

        $femaleName = User::on($connection)->where('id', $femaleUserId)->value('firstname') ?: 'Her';

        $messagesQuery = Message::on($connection);

        if ($includeBothParties) {
            $messagesQuery->where('from_user', $femaleUserId)->orWhere('to_user', $femaleUserId);
        } else {
            $messagesQuery->where('from_user', $femaleUserId);
        }

        $messages = $messagesQuery
            ->orderByDesc('id')
            ->limit($maxMessages)
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
