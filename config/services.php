<?php
// Fitur: Panel chat dan pengaturan model
// connect with ../app/Http/Controllers/Api/AgentIde/ChatController.php, ../app/Services/AgentIde/LlmProviderService.php, ../app/Services/ChatAiService.php
// connect with ../app/Services/Harness/SenopatiAgent.php, ../tests/Feature/LiveHarnessTest.php

return [
    'ide_runner_image' => env('IDE_RUNNER_IMAGE'),

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

    'senopati' => [
        'endpoint' => env('SENOPATI_ENDPOINT'),
        'base_url' => env('SENOPATI_BASE_URL', 'https://senopati.its.ac.id'),
        'api_key' => env('SENOPATI_API_KEY'),
    ],

];
