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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'imagemagick' => [
        'binary' => env('IMAGEMAGICK_BINARY', 'magick'),
        // Ghostscript's bin dir is injected into the subprocess's own PATH explicitly,
        // rather than relying on the system PATH — a system-wide PATH change doesn't
        // reach an already-running PHP/queue-worker process until it's restarted.
        'ghostscript_bin' => env('GHOSTSCRIPT_BIN_DIR', 'C:\\Program Files\\gs\\gs10.07.1\\bin'),
    ],

];
