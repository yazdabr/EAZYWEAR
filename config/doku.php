<?php

return [
    'environment' => env('DOKU_ENV', 'sandbox'),

    'base_url' => env(
        'DOKU_BASE_URL',
        'https://api-sandbox.doku.com'
    ),

    'client_id' => env('DOKU_CLIENT_ID'),

    'api_key' => env('DOKU_API_KEY'),

    'secret_key' => env('DOKU_SECRET_KEY'),

    'channel_id' => env('DOKU_CHANNEL_ID', 'H2H'),

    'merchant_private_key_path' => env(
        'DOKU_MERCHANT_PRIVATE_KEY_PATH',
        'storage/app/private/doku/merchant-private.pem'
    ),

    'merchant_public_key_path' => env(
        'DOKU_MERCHANT_PUBLIC_KEY_PATH',
        'storage/app/private/doku/merchant-public.pem'
    ),

    'va' => [
        'endpoint' => env(
            'DOKU_VA_ENDPOINT',
            '/virtual-accounts/bi-snap-va/v1.1/transfer-va/create-va'
        ),

        'channel_id' => env('DOKU_VA_CHANNEL_ID', 'H2H'),

        'virtual_account_trx_type' => env(
            'DOKU_VA_TRX_TYPE',
            'C'
        ),

        'partner_service_id' => env(
            'DOKU_VA_PARTNER_SERVICE_ID',
            '19008'
        ),

        'merchant_bin' => env(
            'DOKU_VA_MERCHANT_BIN',
            '190089'
        ),

        'customer_prefix' => env(
            'DOKU_VA_CUSTOMER_PREFIX',
            '9'
        ),

        'banks' => [
            'BCA' => [
                'name' => 'BCA',
                'partner_service_id' => '19008',
                'customer_no' => '9',
                'merchant_bin' => '190089',
                'channel' => 'VIRTUAL_ACCOUNT_BCA',
            ],

            'MANDIRI' => [
                'name' => 'Mandiri',
                'partner_service_id' => '86188',
                'customer_no' => '0',
                'merchant_bin' => '861880',
                'channel' => 'VIRTUAL_ACCOUNT_BANK_MANDIRI',
            ],

            'BNI' => [
                'name' => 'BNI',
                'partner_service_id' => '98829172',
                'customer_no' => '3',
                'merchant_bin' => '988291723',
                'channel' => 'VIRTUAL_ACCOUNT_BNI',
            ],

            'BRI' => [
                'name' => 'BRI',
                'partner_service_id' => '13925',
                'customer_no' => '0',
                'merchant_bin' => '139250',
                'channel' => 'VIRTUAL_ACCOUNT_BRI',
            ],

            'BSI' => [
                'name' => 'BSI',
                'partner_service_id' => '6059',
                'customer_no' => '9',
                'merchant_bin' => '60599',
                'channel' => 'VIRTUAL_ACCOUNT_BSI',
            ],
        ],
    ],
];