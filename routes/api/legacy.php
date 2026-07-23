<?php

use App\Http\Controllers\API\Instructor\AssistsController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh-token', [LoginController::class, 'refresh'])->name('api.refresh');

    Route::get('check', [UserController::class, 'check']);
    Route::get('user', [UserController::class, 'user']);

    Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*']);

    Route::prefix('instructor')->middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']])
            ->names('instructor.training_groups');

        Route::get('statistics/groups', [GroupsController::class, 'statistics']);
        Route::get('attendances', [AssistsController::class, 'index']);
        Route::post('attendances/upsert', [AssistsController::class, 'upsert']);
    });

    // Legacy API admin surface disabled during security review.
    // Keep the block here for traceability until we confirm why these routes still exist.
    // Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->name('v1.')->group(function (){
    //
    //     Route::post('register', [RegisterController::class, 'register']);
    //     Route::apiResource('users', UsersController::class);
    //     Route::apiResource('inscriptions', InscriptionController::class);
    //     Route::apiResource('schools', SchoolController::class);
    // });
});
