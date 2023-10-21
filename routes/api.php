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

Route::middleware(['auth:sanctum'])->group(function($route){

    $route->get('/user', function (Request $request) {
        return $request->user();
    });

    $route->prefix('admin')->name('admin.')->group(function ($route){

        $route->post('register', [RegisterController::class, 'register']);
        $route->apiResource('users', UserController::class);
        $route->apiResource('inscriptions', InscriptionController::class);
        $route->apiResource('schools', SchoolController::class);
    });

    $route->prefix('instructor')->name('instructor.')->group(function ($route){

        $route->get('training_groups', [GroupsController::class, 'getTrainingGroups']);
        $route->get('training_group/{id}', [GroupsController::class, 'getTrainingGroup']);
        $route->apiResource('assists', AssistsController::class);
    });
});
