<?php

return [
    'paths' => [
        'api/*',
        'api/login',
        'api/register',
        'api/logout',
        'api/user',
        'api/subscription/*',
        'api/stripe/*',
        'sanctum/csrf-cookie',
        'login',
        'register',
        'logout',
        'storage/*',
        'broadcasting/auth',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:8000',
        'http://localhost:3000',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
