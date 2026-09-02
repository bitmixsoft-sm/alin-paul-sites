<?php

return [
    'free_message_limit' => (int) env('AI_FREE_MESSAGE_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | Conversation History Window
    |--------------------------------------------------------------------------
    | How many prior messages (user + assistant) are replayed into the chat
    | completion request so the AI remembers the ongoing conversation.
    | Set to 0 to disable (legacy single-turn behaviour).
    |
    */
    'history_message_limit' => (int) env('AI_HISTORY_MESSAGE_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Long-term Memory Summarization
    |--------------------------------------------------------------------------
    | Once a conversation has more messages than history_message_limit, the
    | older ones are periodically folded into a running summary instead of
    | being dropped. memory_summary_batch_size controls how many new messages
    | must pile up before triggering another summarization pass (avoids
    | re-summarizing on every single turn).
    |
    */
    'memory_summary_batch_size' => (int) env('AI_MEMORY_SUMMARY_BATCH_SIZE', 15),

    /*
    |--------------------------------------------------------------------------
    | Demo / Development Mode
    |--------------------------------------------------------------------------
    | When enabled, all AI provider calls are skipped and a canned response is
    | returned instantly. Set AI_DEMO_MODE=true in your .env to activate.
    |
    */
    'demo_mode' => (bool) env('AI_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Message Limit Bypass
    |--------------------------------------------------------------------------
    | skip_message_limit: Set AI_SKIP_MESSAGE_LIMIT=true to bypass the free
    |   message cap for ALL users (useful during development/testing).
    |
    | unlimited_user_ids: Comma-separated user IDs that are never throttled,
    |   e.g. AI_UNLIMITED_USER_IDS=1,2,5  (no DB migration needed).
    |
    */
    'skip_message_limit' => (bool) env('AI_SKIP_MESSAGE_LIMIT', false),
    'unlimited_user_ids' => env('AI_UNLIMITED_USER_IDS', ''),

    /*
    |--------------------------------------------------------------------------
    | Audio File Retention
    |--------------------------------------------------------------------------
    | audio_retention_days: How many days to keep generated audio files before
    |   automatic cleanup. Default: 30 days. Set AI_AUDIO_RETENTION_DAYS in .env
    |   Set to 0 to disable automatic cleanup (keep indefinitely).
    |
    */
    'audio_retention_days' => (int) env('AI_AUDIO_RETENTION_DAYS', 10),

    /*
    |--------------------------------------------------------------------------
    | Public Media Base URL
    |--------------------------------------------------------------------------
    | External video providers (D-ID/HeyGen) must reach media over public HTTPS.
    | Set AI_PUBLIC_MEDIA_BASE_URL to your public domain/tunnel, e.g.
    | https://abc123.ngrok-free.app
    |
    */
    'public_media_base_url' => rtrim((string) env('AI_PUBLIC_MEDIA_BASE_URL', ''), '/'),
];
