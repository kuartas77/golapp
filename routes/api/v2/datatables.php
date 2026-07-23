<?php

use App\Http\Controllers\DataTableController;
use Illuminate\Support\Facades\Route;

Route::prefix('datatables')->group(function () {
    Route::middleware([
        'role:super-admin|school',
        'school.permission:school.module.inscriptions',
    ])->group(function () {
        Route::get('inscriptions_enabled', [DataTableController::class, 'enabledInscriptions']);
        Route::get('inscriptions_disabled', [DataTableController::class, 'disabledInscriptions']);
    });

    Route::middleware('school.permission:school.module.training_groups')->group(function () {
        Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups']);
        Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups']);
    });

    Route::middleware('school.permission:school.module.competition_groups')->group(function () {
        Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups']);
        Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups']);
    });

    Route::middleware('school.permission:school.module.training_groups')->group(function () {
        Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules']);
    });
    Route::middleware([
        'role:super-admin|school',
        'school.permission:school.module.players',
    ])->group(function () {
        Route::get('players_enabled', [DataTableController::class, 'enabledPlayers']);
    });

    Route::middleware('school.permission:school.module.training_sessions')->group(function () {
        Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions']);
    });
    Route::middleware('school.permission:school.module.session_planning')->group(function () {
        Route::get('session_plannings', [DataTableController::class, 'sessionPlannings']);
    });
    Route::middleware('school.permission:school.module.methodology')->group(function () {
        Route::get('methodology_records', [DataTableController::class, 'methodologyRecords']);
    });
    Route::middleware('school.permission:school.module.evaluations')->group(function () {
        Route::get('player_evaluations', [DataTableController::class, 'playerEvaluations']);
    });
    Route::middleware([
        'role:super-admin|school',
        'school.permission:school.module.inventory',
    ])->group(function () {
        Route::get('inventory_products', [DataTableController::class, 'inventoryProducts']);
        Route::get('inventory_movements', [DataTableController::class, 'inventoryMovements']);
    });
    Route::middleware('school.permission:school.module.user_management')->group(function () {
        Route::get('users_enabled', [DataTableController::class, 'enabledUsers']);
    });

    Route::middleware('school.permission:school.module.matches')->group(function () {
        Route::get('matches', [DataTableController::class, 'matches']);
    });

    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('schools', [DataTableController::class, 'schools']);
        Route::get('schools_info', [DataTableController::class, 'schoolsInfo']);
    });
});
