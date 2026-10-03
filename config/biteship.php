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
        'contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE'),
        'address' => env('BITESHIP_ORIGIN_ADDRESS'),
        'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE'),
        'latitude' => env('BITESHIP_ORIGIN_LATITUDE'),
        'longitude' => env('BITESHIP_ORIGIN_LONGITUDE'),
    ],

    'webhook' => [
        'signature_key' => env('BITESHIP_WEBHOOK_SIGNATURE_KEY'),
        'signature_secret' => env('BITESHIP_WEBHOOK_SIGNATURE_SECRET'),
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
