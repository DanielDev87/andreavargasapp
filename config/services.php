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
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

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
    ],

];
