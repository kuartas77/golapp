<?php

use App\Http\Controllers\API\AuthControllerSPA;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthControllerSPA::class, 'login'])->middleware('throttle:auth-login');
Route::post('forgot-password', [AuthControllerSPA::class, 'forgotPassword'])->middleware('throttle:password-recovery');
Route::post('reset-password', [AuthControllerSPA::class, 'resetPassword'])->middleware('throttle:password-reset');
