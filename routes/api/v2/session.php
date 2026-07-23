<?php

use App\Http\Controllers\API\AuthControllerSPA;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ProfileController as ApiProfileController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\BackOffice\SchoolController as BackOfficeShoolController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('logout', [AuthControllerSPA::class, 'logout']);

Route::prefix('settings')->group(function () {
    Route::get('general', [SettingsController::class, 'index']);
    Route::get('groups', [SettingsController::class, 'configGroups']);
});

Route::get('dashboard', [DashboardController::class, 'index']);
Route::get('kpis', [DashboardController::class, 'kpis'])->middleware('role:super-admin|school|instructor');

Route::get('user', [UserController::class, 'user']);
Route::get('profile', [ApiProfileController::class, 'show']);
Route::put('profile', [ApiProfileController::class, 'update']);

Route::prefix('admin')->group(function () {
    Route::get('info_campus', [BackOfficeShoolController::class, 'infoCampus']);
    Route::post('change_school', [BackOfficeShoolController::class, 'choose']);
});
