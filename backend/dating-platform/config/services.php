<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'model_id' => env('ELEVENLABS_MODEL_ID', 'eleven_multilingual_v2'),
    ],

    'did' => [
        'api_key' => env('DID_API_KEY'),
        'base_url' => env('DID_BASE_URL', 'https://api.d-id.com'),
    ],

    'heygen' => [
        'api_key' => env('HEYGEN_API_KEY'),
        'base_url' => env('HEYGEN_BASE_URL', 'https://api.heygen.com'),
        'talking_photo_id' => env('HEYGEN_TALKING_PHOTO_ID'),
        'voice_id' => env('HEYGEN_VOICE_ID'),
    ],

    'avatar_video' => [
        'provider' => env('AVATAR_VIDEO_PROVIDER', 'did'),
    ],

    'tavus' => [
        'api_key'    => env('TAVUS_API_KEY'),
        'base_url'   => env('TAVUS_BASE_URL', 'https://tavusapi.com'),
        'replica_id' => env('TAVUS_REPLICA_ID'),
    ],

    'simli' => [
        'api_key'            => env('SIMLI_API_KEY'),
        'base_url'           => env('SIMLI_BASE_URL', 'https://api.simli.ai'),
        'face_id'            => env('SIMLI_FACE_ID'),
        'tts_provider'       => env('SIMLI_TTS_PROVIDER', 'ElevenLabs'),
        'tts_api_key'        => env('SIMLI_TTS_API_KEY'),
        'voice_id'           => env('SIMLI_VOICE_ID'),
        'llm_provider'       => env('SIMLI_LLM_PROVIDER'),
        'llm_model'          => env('SIMLI_LLM_MODEL'),
        'llm_api_key'        => env('SIMLI_LLM_API_KEY'),
        'llm_base_url'       => env('SIMLI_LLM_BASE_URL'),
        'language'           => env('SIMLI_LANGUAGE', 'en'),
        'max_session_length' => env('SIMLI_MAX_SESSION_LENGTH', 3600),
        'max_idle_time'      => env('SIMLI_MAX_IDLE_TIME', 300),
    ],

    // Which provider powers the real-time "Start Live Video Session" call.
    // Options: 'tavus_cvi', 'simli'. Can be overridden per-request (see AIChatController).
    'live_avatar' => [
        'provider' => env('LIVE_AVATAR_PROVIDER', 'tavus_cvi'),
    ],

    'alerts' => [
        'admin_email' => env('ADMIN_ALERT_EMAIL'),
    ],

    // "Login/Register with Google" (see App\Http\Controllers\Auth\GoogleController). Redirect
    // URI must be registered exactly as-is in the Google Cloud Console OAuth client (Authorized
    // redirect URIs) - it's <APP_URL>/auth/google/callback unless overridden.
    // 'enabled' is the on/off switch: components/google-auth-button.blade.php only renders the
    // button when this is true, and the redirect/callback routes 404 when it's false - so
    // turning it off fully removes it, not just hides it while leaving the routes reachable.
    'google' => [
        'enabled' => env('GOOGLE_LOGIN_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    // Single "enter your email, we'll figure out if that's a login or a registration" auth
    // form (components/unified-auth-form.blade.php) instead of separate Register/Login
    // tabs/modals the visitor has to pick between first. Old tabs/modals stay in the codebase
    // untouched and render instead whenever this is false - flip it back any time with no
    // other change needed, see the @if(config('services.unified_login.enabled')) branches in
    // layouts/layout.blade.php and index.blade.php.
    'unified_login' => [
        'enabled' => env('UNIFIED_LOGIN_ENABLED', false),
    ],

    // When true, the header's "Chat / Messages" dropdown (components/header/chat-header.blade.php)
    // also shows AI Companion conversations interleaved with real chats, sorted by recency -
    // clicking one navigates to Find Friends and opens it there via the existing AI popup.
    // When false (default), that dropdown only ever shows real chats, and the AI Inbox stays
    // its own separate panel on the Find Friends page (find_friends.blade.php's .ai-inbox-panel).
    'merge_ai_inbox' => env('MERGE_AI_INBOX', false),

];
