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

    // Ajustes de OpenRouter para respuestas IA cuando no responde local.
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('OPENROUTER_MODEL', 'openrouter/free'),
    ],

    // Configuracion del chat IA de SeaFit.
    'ai_chat' => [
        // Activa/desactiva el widget de chat.
        'enabled' => env('AI_CHAT_ENABLED', true),

        // Correo mostrado cuando no hay respuesta fiable.
        'support_email' => env('AI_SUPPORT_EMAIL', 'soporte.seafit@gmail.com'),

        // Umbral minimo para aceptar respuestas por reglas PHP.
        'min_local_score' => (int) env('AI_CHAT_MIN_LOCAL_SCORE', 5),

        // Activa/desactiva la capa Python (scikit-learn).
        'python_enabled' => env('AI_CHAT_PYTHON_ENABLED', true),

        // Binario de Python: "python", "python3" o ruta completa.
        'python_bin' => env('AI_CHAT_PYTHON_BIN', 'python'),

        // Ruta del script Python dentro del proyecto.
        'python_script' => env('AI_CHAT_PYTHON_SCRIPT', 'ai_python/chat_infer.py'),

        // Maximo de segundos que esperamos al script Python.
        'python_timeout' => (int) env('AI_CHAT_PYTHON_TIMEOUT', 8),

        // Confianza minima para aceptar respuesta del modelo Python.
        'python_min_confidence' => (float) env('AI_CHAT_PYTHON_MIN_CONFIDENCE', 0.58),
    ],

];
