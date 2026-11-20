<?php

declare(strict_types=1);

/*
 | Toda la configuración sale del archivo .env (copia .env.example).
 */

return [
    'app' => [
        'name'        => 'Emilio Guzmán — Portafolio',
        'env'         => env('APP_ENV', 'production'),
        'debug'       => (bool) env('APP_DEBUG', false),
        'url'         => rtrim((string) env('APP_URL', ''), '/'),
        'key'         => (string) env('APP_KEY', ''),
        'timezone'    => 'America/Mexico_City',
        'trust_proxy' => (bool) env('TRUST_PROXY', false),
    ],

    'db' => [
        'enabled' => (bool) env('DB_ENABLED', false),
        'host'    => (string) env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => (string) env('DB_DATABASE', 'portfolio'),
        'user'    => (string) env('DB_USERNAME', 'root'),
        'pass'    => (string) env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'enabled'    => (bool) env('MAIL_ENABLED', false),
        'host'       => (string) env('MAIL_HOST', ''),
        'port'       => (int) env('MAIL_PORT', 587),
        'username'   => (string) env('MAIL_USERNAME', ''),
        'password'   => (string) env('MAIL_PASSWORD', ''),
        'encryption' => (string) env('MAIL_ENCRYPTION', 'tls'), // tls | ssl | none
        'from'       => (string) env('MAIL_FROM_ADDRESS', ''),
        'from_name'  => (string) env('MAIL_FROM_NAME', 'Portafolio'),
        'to'         => (string) env('CONTACT_TO', ''),
    ],

    'contact' => [
        'max_attempts'   => (int) env('CONTACT_MAX_ATTEMPTS', 5),
        'window_seconds' => (int) env('CONTACT_WINDOW_SECONDS', 600),
    ],

    // Datos públicos del sitio. Si quedan vacíos se muestran como [PLACEHOLDER].
    'site' => [
        'name'           => 'Emilio Guzmán',
        'public_email'   => (string) env('PUBLIC_EMAIL', ''),
        'github'         => 'https://github.com/emiliooarrig',
        'linkedin'       => (string) env('LINKEDIN_URL', ''),
        'cv'             => 'assets/docs/cv-emilio-guzman.pdf',
        'response_time'  => (string) env('RESPONSE_TIME', ''),
        'fovea_url'      => (string) env('FOVEA_URL', ''),
        'fovea_whatsapp' => (string) env('FOVEA_WHATSAPP', ''),
    ],
];
