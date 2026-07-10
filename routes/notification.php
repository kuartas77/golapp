<?php

use App\Http\Controllers\API\Notifications\InvoiceController;
use App\Http\Controllers\API\Notifications\Guardians\GuardianInvoiceController;
use App\Http\Controllers\API\Notifications\Guardians\GuardianDeviceTokenController;
use App\Http\Controllers\API\Notifications\Guardians\GuardianPlayerController;
use App\Http\Controllers\API\Notifications\Guardians\GuardianTopicNotificationsController;
use App\Http\Controllers\API\Notifications\Guardians\GuardianUniformRequestController;
use App\Http\Controllers\API\Notifications\Guardians\LoginGuardianController;
use App\Http\Controllers\API\Notifications\LoginPlayerController;
use App\Http\Controllers\API\Notifications\TopicNotificationsController;
use App\Http\Controllers\API\Notifications\UniformRequestController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginPlayerController::class, 'login']);

Route::prefix('v2/guardians')->group(function () {
    Route::post('login', [LoginGuardianController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('refresh', [LoginGuardianController::class, 'refresh'])->middleware('abilities:refresh');
        Route::post('logout', [LoginGuardianController::class, 'logout']);
        Route::post('notifications/device-token', [GuardianDeviceTokenController::class, 'store']);

        Route::get('players', [GuardianPlayerController::class, 'index']);

        Route::prefix('notifications')->middleware('abilities:notification-index')->group(function () {
            Route::get('', [GuardianTopicNotificationsController::class, 'index']);
            Route::get('/{notification}', [GuardianTopicNotificationsController::class, 'show']);
            Route::put('/read-all', [GuardianTopicNotificationsController::class, 'readAll']);
            Route::put('/read', [GuardianTopicNotificationsController::class, 'read']);
            Route::put('/read/{notification}', [GuardianTopicNotificationsController::class, 'read']);
        });

        Route::prefix('invoices')->group(function () {
            Route::middleware('abilities:payment-index')->group(function () {
                Route::get('', [GuardianInvoiceController::class, 'index']);
                Route::get('statistics', [GuardianInvoiceController::class, 'statistics']);
                Route::get('/{invoice}', [GuardianInvoiceController::class, 'show']);
            });

            Route::post('payment', [GuardianInvoiceController::class, 'payment'])->middleware('abilities:payment-update');
        });

        Route::prefix('requests')->group(function () {
            Route::middleware('abilities:request-index')->group(function () {
                Route::get('', [GuardianUniformRequestController::class, 'index']);
                Route::get('statistics', [GuardianUniformRequestController::class, 'statistics']);
                Route::get('/{uniformRequest}', [GuardianUniformRequestController::class, 'show']);
            });

            Route::middleware('abilities:request-store')->group(function () {
                Route::post('', [GuardianUniformRequestController::class, 'store']);
                Route::post('/{uniformRequest}/cancel', [GuardianUniformRequestController::class, 'cancel']);
            });
        });
    });
});

Route::middleware(['auth:sanctum'])->group(function(){

    Route::prefix('notifications')->middleware('abilities:notification-index')->group(function() {
        Route::get('', [TopicNotificationsController::class, 'index']);
        Route::get('/{id}', [TopicNotificationsController::class, 'show']);
        Route::put('/read-all', [TopicNotificationsController::class, 'readAll']);
        Route::put('/read/{id}', [TopicNotificationsController::class, 'read']);
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
