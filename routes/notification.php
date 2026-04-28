<?php

use App\Http\Controllers\API\Notifications\InvoiceController;
use App\Http\Controllers\API\Notifications\LoginPlayerController;
use App\Http\Controllers\API\Notifications\TopicNotificationsController;
use App\Http\Controllers\API\Notifications\UniformRequestController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginPlayerController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::prefix('notifications')->middleware('abilities:notification-index')->group(function() {
        Route::get('', [TopicNotificationsController::class, 'index']);
        Route::get('/{id}', [TopicNotificationsController::class, 'show']);
        Route::put('/read-all', [TopicNotificationsController::class, 'readAll']);
        Route::put('/read', [TopicNotificationsController::class, 'read']);
    });

    Route::prefix('invoices')->group(function() {
        Route::middleware('abilities:payment-index')->group(function () {
            Route::get('', [InvoiceController::class, 'index']);
            Route::get('statistics', [InvoiceController::class, 'statistics']);
            Route::get('/{invoice}', [InvoiceController::class, 'show']);
        });

        Route::post('payment', [InvoiceController::class, 'payment'])->middleware('abilities:payment-update');

    });

    Route::prefix('requests')->group(function() {
        Route::middleware('abilities:request-index')->group(function () {
            Route::get('', [UniformRequestController::class, 'index']);
            Route::get('statistics', [UniformRequestController::class, 'statistics']);
            Route::get('/{uniformRequest}', [UniformRequestController::class, 'show']);
        });

        Route::middleware('abilities:request-store')->group(function () {
            Route::post('store', [UniformRequestController::class, 'store']);
            Route::get('/{uniformRequest}/cancel', [UniformRequestController::class, 'cancel']);
        });
    });

});
