<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://flatnest.xyz',
        'https://www.flatnest.xyz',
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        env('FRONTEND_URL', ''),
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
