<?php

use Notification\Controllers\AuthController;
use Notification\Controllers\NotificationController;
use Notification\Core\Route;
use Notification\Middleware\JwtMiddleware;
use Notification\Middleware\RateLimitMiddleware;

Route::group(['middleware' => [RateLimitMiddleware::class]], function () {
    Route::post('/api/auth/register', [AuthController::class, 'register']);
    Route::post('/api/auth/login', [AuthController::class, 'login']);

    Route::group(['middleware' => [JwtMiddleware::class]], function () {
        Route::post('/api/notifications', [NotificationController::class, 'createNotification']);
        Route::get('/api/notifications/{id}', [NotificationController::class, 'getNotification']);
    });
});