<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recovery email pesanan yang gagal masuk antrean.
Schedule::command('emails:recover-orders')
    ->everyMinute()
    ->withoutOverlapping();

// Recovery email penyelesaian pesanan.
Schedule::command('emails:recover-completed')
    ->everyMinute()
    ->withoutOverlapping();