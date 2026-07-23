<?php

use App\Http\Controllers\API\AuthControllerSPA;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthControllerSPA::class, 'login']);
Route::post('forgot-password', [AuthControllerSPA::class, 'forgotPassword']);
Route::post('reset-password', [AuthControllerSPA::class, 'resetPassword']);
