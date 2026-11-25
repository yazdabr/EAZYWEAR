<?php

return [
    'expired_time' => (int) env('DOKU_O2O_EXPIRED_TIME', 1440),
    'info' => env('DOKU_O2O_INFO', 'Eazywear Indonesia'),
    'indomaret_receipt_description' => env(
        'DOKU_O2O_INDOMARET_RECEIPT_DESCRIPTION',
        'Pembelian Produk Eazywear'
    ),
];
