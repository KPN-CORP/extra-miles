<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    // Darwinbox -> auth-service handshake yang menerbitkan JWT untuk SPA mobile.
    // Dibaca lewat config(), BUKAN env() langsung di controller: deploy menjalankan
    // 'php artisan optimize' (config:cache), dan saat config ter-cache Laravel tidak
    // memuat .env sama sekali -- env() akan mengembalikan null di runtime.
    'auth_service' => [
        'url' => env('AUTH_SERVICE_URL'),
    ],

    // Darwinbox SSO handshake (SsoController). Secrets live in .env only.
    'darwinbox' => [
        'check_token_url' => env('DARWINBOX_CHECK_TOKEN_URL', 'https://kpncorporation.darwinbox.com/checkToken'),
        'api_key' => env('DARWINBOX_API_KEY'),
        // base64("user:password"), exactly as it goes after "Basic ".
        'basic_auth' => env('DARWINBOX_BASIC_AUTH'),
        // XOR key Darwinbox uses to wrap the redirect payload.
        'payload_key' => env('DARWINBOX_PAYLOAD_KEY'),
        // Refuse an SSO login when checkToken does not say whose token it is.
        // Only switch off temporarily while finding the right response field.
        'enforce_identity' => (bool) env('DARWINBOX_SSO_ENFORCE_IDENTITY', true),
    ],

    // Systems that SSO through this app: after Darwinbox verifies the employee,
    // we sign a short HS256 JWT and redirect them to `url?token=...`.
    'sso_vendors' => [
        'lms' => [
            'url' => 'https://kpn-lms.bluebridgecorp.com/api/sso/darwinbox/receive',
            'audience' => 'VENDOR_LMS',
            'secret' => env('SSO_LMS_SECRET'),
        ],
        'cmpr' => [
            'url' => 'https://kpn-cmpr.bluebridgecorp.com/api/sso/darwinbox/receive',
            'audience' => 'VENDOR_LMS',
            'secret' => env('SSO_CMPR_SECRET'),
        ],
        'expl' => [
            'url' => 'https://nastar-academy.com/api/sso/darwinbox',
            'audience' => 'VENDOR_PS',
            'secret' => env('SSO_EXPL_SECRET'),
        ],
    ],

    // Lifetime of those vendor JWTs, in seconds (default 30 days, as before).
    'sso_vendor_ttl' => (int) env('SSO_VENDOR_TTL', 2592000),

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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

];
