<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Special Batch Fulfillment
    |--------------------------------------------------------------------------
    |
    | This batch is available from 30 October 2026 through 3 November 2026.
    | The daily capacity is shared between courier and pickup orders.
    |
    */

    'special_batch' => [
        'start_date' => '2026-10-30',
        'end_date' => '2026-11-03',
        'daily_capacity' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pickup Hours
    |--------------------------------------------------------------------------
    */

    'pickup' => [
        'start_time' => '09:00',
        'end_time' => '17:00',
        'same_day_cutoff' => '14:00',
    ],

];