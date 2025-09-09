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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'qps' => [
        'base_url' => env('QPS_BASE_URL', 'https://demo.qpse.pe'),
        'token_url' => env('QPS_TOKEN_URL', 'https://demo.qpse.pe/api/token'),
        'api_url' => env('QPS_API_URL', 'https://demo.qpse.pe/api/v1'),
        'username' => env('QPS_USERNAME'),
        'password' => env('QPS_PASSWORD'),
        'enabled' => env('QPS_ENABLED', false),
        // Configuración dinámica desde base de datos
        'use_dynamic_config' => env('QPS_USE_DYNAMIC_CONFIG', true),
    ],

];
