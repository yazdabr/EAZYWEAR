<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DOKU QRIS Configuration
    |--------------------------------------------------------------------------
    */

    'env' => env('DOKU_QRIS_ENV', 'sandbox'),

    'base_url' => env(
        'DOKU_QRIS_BASE_URL',
        'https://api-sandbox.doku.com'
    ),

    /*
    |--------------------------------------------------------------------------
    | QRIS Credentials
    |--------------------------------------------------------------------------
    |
    | These are intentionally separate from the VA credentials in doku.php.
    |
    */

    'client_id' => env('DOKU_QRIS_CLIENT_ID'),

    'client_secret' => env('DOKU_QRIS_CLIENT_SECRET'),

    'shared_key' => env('DOKU_QRIS_SHARED_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Identification
    |--------------------------------------------------------------------------
    |
    | MPAN / NMID mapping must follow DOKU's confirmed QRIS contract.
    | Do not assume either value is merchantId or terminalId.
    |
    */

    'mpan' => env('DOKU_QRIS_MPAN'),

    'nmid' => env('DOKU_QRIS_NMID'),

    /*
    |--------------------------------------------------------------------------
    | SNAP
    |--------------------------------------------------------------------------
    */

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