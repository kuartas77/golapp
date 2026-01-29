<?php

use App\Http\Controllers\API\Notifications\InvoiceController;
use App\Http\Controllers\API\Notifications\LoginPlayerController;
use App\Http\Controllers\API\Notifications\NotificationController;
use App\Http\Controllers\API\Notifications\UniformRequestController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginPlayerController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::prefix('notifications')->group(function() {
        Route::get('', [NotificationController::class, 'index']);
        Route::get('/{topic_notification}', [NotificationController::class, 'show']);
        Route::put('/read-all', [NotificationController::class, 'readAll']);
        Route::put('/{topic_notification}/read', [NotificationController::class, 'read']);
    });

    Route::prefix('invoices')->group(function() {

        Route::get('', [InvoiceController::class, 'index']);

        Route::get('statistics', [InvoiceController::class, 'statistics']);

        Route::post('payment', [InvoiceController::class, 'payment']);

        Route::get('/{invoice}', [InvoiceController::class, 'show']);

    });

    Route::prefix('requests')->group(function() {
        Route::get('', [UniformRequestController::class, 'index']);

        Route::get('statistics', [UniformRequestController::class, 'statistics']);

        Route::post('store', [UniformRequestController::class, 'store']);

        Route::get('/{uniformRequest}', [UniformRequestController::class, 'show']);

        Route::get('/{uniformRequest}/cancel', [UniformRequestController::class, 'cancel']);
    });

});
