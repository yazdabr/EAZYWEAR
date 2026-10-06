<?php

return [
    'env' => env('DOKU_QRIS_ENV', 'sandbox'),
    'base_url' => env(
        'DOKU_QRIS_BASE_URL',
        'https://api-sandbox.doku.com'
    ),

    'notification_secret' => env('DOKU_QRIS_NOTIFICATION_SECRET'),

    'client_id' => env('DOKU_QRIS_CLIENT_ID'),
    'client_secret' => env('DOKU_QRIS_CLIENT_SECRET'),

    'merchant_private_key_path' => env(
        'DOKU_QRIS_MERCHANT_PRIVATE_KEY_PATH',
        'storage/app/private/doku/merchant-private.pem'
    ),

    'shared_key' => env('DOKU_QRIS_SHARED_KEY'),
    'mpan' => env('DOKU_QRIS_MPAN'),
    'nmid' => env('DOKU_QRIS_NMID'),

    'merchant_id' => env('DOKU_QRIS_MERCHANT_ID'),
    'terminal_id' => env('DOKU_QRIS_TERMINAL_ID'),
    'postal_code' => env('DOKU_QRIS_POSTAL_CODE'),
    'fee_type' => env('DOKU_QRIS_FEE_TYPE', '1'),

    'channel_id' => env('DOKU_QRIS_CHANNEL_ID', 'H2H'),

    'generate_endpoint' => env(
        'DOKU_QRIS_GENERATE_ENDPOINT',
        '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate'
    ),

    'query_endpoint' => env(
        'DOKU_QRIS_QUERY_ENDPOINT',
        '/snap-adapter/b2b/v1.0/qr/qr-mpm-query'
    ),
];