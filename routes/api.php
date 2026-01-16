<?php

use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\API\Instructor\AssistsController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Notifications\LoginPlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::post('refresh-token', [LoginController::class, 'refresh'])->name('api.refresh');

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

Route::prefix('notify')->group(function() {

    Route::post('login', [LoginPlayerController::class, 'login']);


    Route::middleware(['auth:sanctum'])->group(function(){

        Route::prefix('notifications')->group(function() {
            Route::get('', function(){
                return response()->json([]);
            });
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
        Route::prefix('payments')->group(function() {
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
        Route::prefix('requests')->group(function() {
            Route::get('', function(){

                $response = [
                    'id' => '',
                    'user_id' => '',
                    'user_name' => null,
                    'type' => 'UNIFORM',
                    'quantity' => 0,
                    'size' => null,
                    'brand' => null,
                    'model' => null,
                    'color' => null,
                    'additional_notes' => null,
                    'status' => 'PENDING',
                    'created_at' => null,
                    'updated_at' => null,
                    'approved_at' => null,
                    'delivered_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ];

                return response()->json([$response], 200);

            });

            Route::post('', function(Request $request){
                $response = [
                    'id' => Str::uuid(),
                    'user_id' => '',
                    'user_name' => null,
                    'type' => 'UNIFORM',
                    'quantity' => 0,
                    'size' => null,
                    'brand' => null,
                    'model' => null,
                    'color' => null,
                    'additional_notes' => null,
                    'status' => 'PENDING',
                    'created_at' => null,
                    'updated_at' => null,
                    'approved_at' => null,
                    'delivered_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ];
                return response()->json($response, 200);
            });




            Route::get('statistics', function(Request $request){

                $response = [
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'delivered' => 0,
                    'rejected' => 0,
                    'cancelled' => 0,
                ];

                return response()->json($response, 200);
            });
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
            // TODO:rutas

    });
});
