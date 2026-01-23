<?php

use App\Http\Controllers\API\Notifications\InvoiceController;
use App\Http\Controllers\API\Notifications\LoginPlayerController;
use App\Http\Controllers\API\Notifications\RequestController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginPlayerController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::prefix('notifications')->group(function() {
        Route::get('', function(){
            return response()->json([]);
        });
    });

    Route::prefix('invoices')->group(function() {

        Route::get('', [InvoiceController::class, 'index']);

        Route::get('statistics', [InvoiceController::class, 'statistics']);

        Route::post('store', [InvoiceController::class, 'store']);

    });


    Route::prefix('requests')->group(function() {
        Route::get('', [RequestController::class, 'index']);

        Route::get('statistics', [RequestController::class, 'statistics']);

        Route::post('store', [RequestController::class, 'store']);
    });
        // TODO:rutas

});
