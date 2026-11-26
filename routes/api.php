<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DokuNotificationController;
use App\Http\Controllers\DokuQrisNotificationController;
use App\Http\Controllers\DokuAlfamartNotificationController;

Route::post(
    '/doku/notification',
    [DokuNotificationController::class, 'paymentNotification']
);

Route::post(
    '/doku/qris/notification',
    [DokuQrisNotificationController::class, 'paymentNotification']
);

Route::post(
    '/doku/alfamart/notification',
    [DokuAlfamartNotificationController::class, 'paymentNotification']
);