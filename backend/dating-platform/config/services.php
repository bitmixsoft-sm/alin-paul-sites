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

];
