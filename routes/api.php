<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\Instructor\AssistsController;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::get('check', [UserController::class, 'check']);
    Route::get('user', [UserController::class, 'user']);

    Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*']);

    Route::prefix('instructor')->name('instructor.')->middleware(['auth:sanctum'])->group(function (){

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);

        Route::get('statistics/groups', [GroupsController::class, 'statistics']);
        Route::get('attendances', [AssistsController::class, 'index']);
        Route::post('attendances/upsert', [AssistsController::class, 'upsert']);
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->name('v1.')->group(function (){

        Route::post('register', [RegisterController::class, 'register']);
        Route::apiResource('users', UsersController::class);
        Route::apiResource('inscriptions', InscriptionController::class);
        Route::apiResource('schools', SchoolController::class);
    });

});
