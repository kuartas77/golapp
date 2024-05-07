<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\Instructor\AssistsController;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load(['profile', 'school']);
});

Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->name('v1.')->group(function (){

    Route::post('register', [RegisterController::class, 'register']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('inscriptions', InscriptionController::class);
    Route::apiResource('schools', SchoolController::class);
});

Route::prefix('instructor')->name('instructor.')->middleware(['auth:sanctum'])->group(function (){

    Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']])->middleware('ability:group-index');
    Route::apiResource('assists', AssistsController::class, ['only' => ['index', 'update']])->middleware('abilities:assists-index,assists-update');
});
