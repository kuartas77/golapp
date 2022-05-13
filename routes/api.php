<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Admin\SchoolController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [LoginController::class, 'login']);



Route::middleware(['auth:sanctum'])->group(function (){

    Route::post('register', [RegisterController::class, 'register']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('inscriptions', InscriptionController::class);
    Route::apiResource('schools', SchoolController::class);
});

