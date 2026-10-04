<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DokuNotificationController;

Route::post(
    '/doku/notification',
    [DokuNotificationController::class, 'paymentNotification']
);