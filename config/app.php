<?php

return [
    'name' => getenv('APP_NAME') ?: 'EthioMarket',
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '1', FILTER_VALIDATE_BOOL),
    'url' => getenv('APP_URL') ?: 'http://localhost',

    'locale' => getenv('APP_LOCALE') ?: 'en',
    'fallback_locale' => 'en',
    'supported_locales' => ['en', 'am'],

    'currency' => getenv('APP_CURRENCY') ?: 'ETB',
    'supported_currencies' => ['ETB', 'USD', 'EUR', 'KES', 'NGN'],

    'assets' => [
        'css' => '/assets/css/style.css',
        'js' => '/assets/js/app.js',
    ],

    'security' => [
        'csrf_token_key' => '_csrf',
        'password_algo' => PASSWORD_DEFAULT,
    ],

    'database' => require __DIR__ . '/database.php',
];
