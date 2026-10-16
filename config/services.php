<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'bold' => [
        'key' => env('BOLD_API_KEY'),
        // env('BOLD_API_BASE', default) only falls back when the key is entirely absent — an
        // empty BOLD_API_BASE= line in .env still yields '', so the default lives here instead
        // and BoldService reads this value as-is without its own fallback chain.
        'base_url' => env('BOLD_API_BASE') ?: 'https://integrations.api.bold.co/online/link/v1',
        'webhook_secret' => env('BOLD_WEBHOOK_SECRET'),
        // true only with Bold's TEST keys: test-mode webhooks are signed with an empty key,
        // which anyone could forge, so that signature is accepted only in sandbox mode.
        'sandbox' => (bool) env('BOLD_SANDBOX', false),
    ],

    // Claude reviews the diploma and ID document dentists upload before buying a course.
    // validation_model can be raised to claude-sonnet-5-5 if the cheaper model misses cases.
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'validation_model' => env('ANTHROPIC_VALIDATION_MODEL') ?: 'claude-haiku-5-5',
    ],

    // Colombia's public registry of health professionals (ReTHUS), linked from the admin review
    'rethus_lookup_url' => env('RETHUS_LOOKUP_URL') ?: 'https://web.sispro.gov.co/THS/Cliente/ConsultasPublicas/ConsultaPublicaDeTHxIdentificacion.aspx',

    // Read via config(), not env(), by public_html/deploy-hook.php — once `config:cache`
    // has run, env() calls outside config/*.php files return null, since Laravel stops
    // reading .env entirely at that point. config() values survive because they're
    // baked into the cached array.
    'deploy_hook_token' => env('DEPLOY_HOOK_TOKEN'),

    // Bunny Stream — video hosting with CDN-level download prevention.
    // library_id  : numeric ID of your Stream Library (dash.bunny.net > Stream)
    // hostname    : CDN pull zone hostname, e.g. vz-abc123.b-cdn.net
    // token_key   : optional signing secret for URL token auth (recommended in prod)
    'bunny_stream' => [
        'library_id' => env('BUNNY_STREAM_LIBRARY_ID'),
        'hostname' => env('BUNNY_STREAM_HOSTNAME'),
        'token_key' => env('BUNNY_STREAM_TOKEN_KEY'),
        // Public intro video of the doctor, opened from "Conoce a tu Doctora" on the home page
        'intro_video_id' => env('BUNNY_STREAM_INTRO_VIDEO_ID'),
    ],

];
