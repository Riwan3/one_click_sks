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

    'spdk' => [
        'url' => env('SPDK_API_URL'),
        'key' => env('SPDK_API_KEY'),
        'timeout' => env('SPDK_TIMEOUT', 30),
    ],

    // Notifikasi push (Firebase Cloud Messaging HTTP v1). Isi dengan path file JSON service account;
    // kosong = notifikasi push dimatikan.
    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS'),
    ],

    'ho_api' => [
        'url' => env('HO_API_URL'),
        'token' => env('HO_API_TOKEN'),
        'timeout' => env('HO_API_TIMEOUT', 60),
    ],

    // Koneksi ke OneClick Control Center (pull): CC memanggil /api/cc/status dengan bearer token ini.
    'control_center' => [
        'token' => env('CONTROL_CENTER_TOKEN'),
        'version' => env('APP_RELEASE', 'unknown'),
        'deployed_at' => env('APP_DEPLOYED_AT'),
    ],

    'browsershot' => [
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
        'node_binary' => env('BROWSERSHOT_NODE_BINARY'),
        'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),
    ],

];
