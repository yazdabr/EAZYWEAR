<?php

return [
    'environment' => env('DOKU_MINIMARKET_ENV', env('DOKU_ENV', 'sandbox')),

    'base_url' => env(
        'DOKU_MINIMARKET_BASE_URL',
        env('DOKU_BASE_URL', 'https://api-sandbox.doku.com')
    ),

    'client_id' => env(
        'DOKU_MINIMARKET_CLIENT_ID',
        env('DOKU_CLIENT_ID')
    ),

    'secret_key' => env(
        'DOKU_MINIMARKET_SECRET_KEY',
        env('DOKU_SECRET_KEY')
    ),

    'alfamart' => [
        'endpoint' => env(
            'DOKU_ALFAMART_ENDPOINT',
            '/alfa-online-to-offline/v2/payment-code'
        ),
        'expired_time' => 1440,
        'reusable_status' => false,
        'notification_url' => env(
            'DOKU_ALFAMART_NOTIFICATION_URL',
            rtrim((string) env('APP_URL', ''), '/')
                . '/api/doku/alfamart/notification'
        ),
    ],
];
