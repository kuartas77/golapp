<?php

use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\API\AuthControllerSPA;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Instructor\AssistsController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\PlayersController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Assists\AssistController;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Groups\CompetitionGroupController;
use App\Http\Controllers\Groups\TrainingGroupController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Players\PlayerController;
use App\Http\Controllers\SchoolPages\SchoolsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::post('logout', [LoginController::class, 'logout']);
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


Route::prefix('v2')->group(function(){

    Route::post('login', [AuthControllerSPA::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function(){

        Route::post('logout', [AuthControllerSPA::class, 'logout']);

        Route::prefix('settings')->group(function(){
            Route::get('general', [SettingsController::class, 'index']);
            Route::get('groups', [SettingsController::class, 'configGroups']);
        });

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('kpis', [DashboardController::class, 'kpis']);

        Route::get('user', [UserController::class, 'user']);

        Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function (){
            Route::get('school', [SchoolsController::class, 'index']);
            Route::put('school/{school}', [SchoolsController::class, 'update']);
            Route::apiResource('users', UsersController::class);
            Route::apiResource('training_groups', TrainingGroupController::class, ['only' => ['show', 'store', 'update']]);
            Route::apiResource('competition_groups', CompetitionGroupController::class, ['only' => ['show', 'store', 'update']]);

        });

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);
        Route::get("training_group/classdays", [TrainingGroupController::class, 'getClassDays']);

        Route::apiResource("players", PlayerController::class, ['only' => ['edit','show', 'update']]);



        Route::apiResource("payments", PaymentController::class)->only(['index','update', 'show']);
        Route::apiResource("assists", AssistController::class)->except(['create','edit', 'destroy']);

        Route::prefix('datatables')->group(function () {
            Route::get('inscriptions_enabled', [DataTableController::class, 'enabledInscriptions']);
            Route::get('inscriptions_disabled', [DataTableController::class, 'disabledInscriptions']);
            Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups']);
            Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups']);
            Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups']);
            Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups']);
            Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules']);
            Route::get('players_enabled', [DataTableController::class, 'enabledPlayers']);
            Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions']);
            Route::get('users_enabled', [DataTableController::class, 'enabledUsers']);

            Route::middleware(['role:super-admin'])->group(function (){
                Route::get('schools', [DataTableController::class, 'schools']);
                Route::get('schools_info', [DataTableController::class, 'schoolsInfo']);
            });
        });
    });

});