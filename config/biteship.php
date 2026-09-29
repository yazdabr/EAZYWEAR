<?php

return [
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
    'api_key' => env('BITESHIP_API_KEY'),
    'timeout' => (int) env('BITESHIP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Shipping Origin
    |--------------------------------------------------------------------------
    |
    | Keep the origin in environment variables so the location can be changed
    | without changing application code.
    |
    */
    'origin' => [
        'latitude' => env('BITESHIP_ORIGIN_LATITUDE'),
        'longitude' => env('BITESHIP_ORIGIN_LONGITUDE'),
        'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Courier Allowlist
    |--------------------------------------------------------------------------
    |
    | Server-controlled courier codes. Do not accept this value from the
    | browser when calculating the final shipping amount.
    |
    */
    'couriers' => env('BITESHIP_COURIERS'),
];
