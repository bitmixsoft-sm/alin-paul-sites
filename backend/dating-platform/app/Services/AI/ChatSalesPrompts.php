<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Message;
use App\Pack;
use App\Settings;
use App\User;

/**
 * The instructions that turn the real-chat AI (ChatBotController::sendAiReply()) from a
 * storyteller into something that actually leads the conversation toward a subscription -
 * client's request, 2026-09-21 (in the tests, the AI "starts with stories", ignores "is it paid?"
 * and has no inclination to sell the camera feature).
 *
 * Three pieces, all sent as extra system messages by AIOrchestratorService:
 * 1. General instructions (admin-editable, AI Settings page) - length, language, the goal.
 * 2. Product knowledge (admin-editable) - what the subscription is/costs. {packages} and
 *    {chat_rule} are filled in live from the real Packages table / Settings, so this can't drift
 *    from what the site actually sells.
 * 3. A per-conversation status note (never editable, built here) - whether THIS man already pays,
 *    so paying members aren't pitched a subscription they already have.
 *
 * The texts below are DEFAULTS, used whenever the admin's saved text is empty - a first draft
 * meant to be replaced/refined by the client.
 */
final class ChatSalesPrompts
{
    public const DEFAULT_GENERAL_INSTRUCTIONS = <<<'TXT'
Conversation rules for every reply:
- Keep every message SHORT: one to three brief sentences, in a casual chat style. Never write long paragraphs and never tell long stories.
- Always reply in the same language the other person writes in.
- Your goal in every conversation is to convince the other person to get a subscription, and to keep him talking with you for as long as possible.
- Ask him something in almost every message so the conversation keeps going, and steer it toward more time together: talking longer, seeing each other on camera, getting to know each other better.
- When he asks about payment, prices, whether something costs money, or about using the camera: answer directly and honestly using the product information below, and lead him toward subscribing. Never dodge these questions and never change the subject away from them.
- Stay warm, flirty and persuasive - never rude, never aggressive. Do not repeat the same pitch twice in a row; if he hesitates, respond to his hesitation and gently try again.
- Never invent prices, packages or features that are not in the product information below.
TXT;

    public const DEFAULT_PRODUCT_KNOWLEDGE = <<<'TXT'
About this website (use it when he asks about payment, prices, subscription, credits or the camera - never invent anything beyond this):
- {chat_rule}
- Video (camera) calls with the women here use credits, and credits come with the subscription packages - so to video chat with you he needs an active package. This is the main reason to subscribe.
- Available packages: {packages}
- He can subscribe from the "Packages" page of the website.
TXT;

    /**
     * Fills {packages} and {chat_rule} live - see the class docblock on why not hardcoded.
     */
    public static function renderProductKnowledge(string $template): string
    {
        return strtr($template, [
            '{packages}' => self::packagesList(),
            '{chat_rule}' => self::chatRule(),
        ]);
    }

    private static function packagesList(): string
    {
        // Same filter the public /packages page uses (custom = 1 are hidden one-off packs, e.g.
        // Boost's) - minus the free Trial (User::paidPackage() also excludes it by name), since
        // it isn't something to "sell".
        $packs = Pack::where('custom', '!=', 1)
            ->where('price', '>', 0)
            ->where('name', '!=', 'Trial')
            ->orderBy('price')
            ->get();

        if ($packs->isEmpty()) {
            return '(see the Packages page)';
        }

        return $packs->map(function (Pack $pack): string {
            $line = $pack->name . ': ' . $pack->price . ' ' . $pack->currency;

            if ((int) $pack->credits > 0) {
                $line .= ', ' . $pack->credits . ' credits';
            }

            if ((int) $pack->duration > 0) {
                $line .= ', valid ' . $pack->duration . ' days';
            }

            return $line;
        })->implode('; ');
    }

    private static function chatRule(): string
    {
        // Mirrors ChatController's actual gating (Settings id 24 "Chat pe credite", id 10 "Mesaje
        // gratis pe zi").
        if (Settings::where('id', 24)->value('value') === 'yes') {
            return 'Every message he sends costs 1 credit; credits come with the subscription packages.';
        }

        $freePerDay = (int) Settings::where('id', 10)->value('value');

        return "Members without a paid subscription can send only {$freePerDay} messages per day; a paid subscription removes that limit.";
    }

    /**
     * Whether THIS man already pays, so the AI doesn't pitch a subscription he already has.
     * $man may be null (the admin Preview tool has no real conversation) - treated as a free
     * member, since that's the case the sales behavior needs to be tested against.
     */
    public static function userStatusNote(?User $man): string
    {
        if ($man !== null && $man->paidPackage()) {
            $pack = $man->paidPackage();

            return 'Status of the man you are talking to: he is ALREADY a paying member (package: ' . $pack->name . ', credits left: ' . (int) $man->credits . '). '
                . 'Do not pitch a subscription to him. Thank him warmly if it fits, and keep him engaged and enjoying the chat - encourage longer conversations and video calls.';
        }

        $note = 'Status of the man you are talking to: he does NOT have a paid subscription (free member)';

        if ($man !== null) {
            $note .= ', credits left: ' . (int) $man->credits;

            if (Settings::where('id', 24)->value('value') !== 'yes') {
                $used = Message::where('from_user', $man->id)->whereDate('created_at', now()->toDateString())->count();
                $limit = (int) Settings::where('id', 10)->value('value');
                $note .= ", messages sent today: {$used} of {$limit} free";
            }
        }

        return $note . '. Lead the conversation toward getting a subscription.';
    }
}
