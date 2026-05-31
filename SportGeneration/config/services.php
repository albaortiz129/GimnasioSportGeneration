<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Credenciales para servicios externos.
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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    // Ajustes de OpenRouter para respuestas de IA.
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('OPENROUTER_MODEL', 'openrouter/free'),
    ],

    // Configuración del chat IA de Sport Generation.
    'ai_chat' => [
        // Activa/desactiva el widget de chat.
        'enabled' => env('AI_CHAT_ENABLED', true),

        // Correo mostrado cuando no hay respuesta fiable.
        'support_email' => env('AI_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'soporte@example.com')),
    ],

    'sport_generation' => [
        'admin_email' => env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'admin@example.com')),
        'admin_password' => env('ADMIN_PASSWORD'),
        'trainer_request_email' => env('TRAINER_REQUEST_EMAIL', env('MAIL_FROM_ADDRESS', 'soporte@example.com')),
        'unpaid_notification_email' => env('UNPAID_NOTIFICATION_EMAIL'),
    ],

];
