<?php

use App\Http\Controllers\API\Admin\ContractController as AdminContractController;
use App\Http\Controllers\API\Admin\GroupAssignmentController;
use App\Http\Controllers\API\Admin\InscriptionCustomChargeController;
use App\Http\Controllers\API\Admin\InvoiceCustomItemController as AdminInvoiceCustomItemController;
use App\Http\Controllers\API\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\SchoolDataExportController;
use App\Http\Controllers\API\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\Evaluations\EvaluationTemplateController;
use App\Http\Controllers\Groups\CompetitionGroupController;
use App\Http\Controllers\Groups\TrainingGroupController;
use App\Http\Controllers\SchoolPages\SchoolsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function () {
    Route::middleware('school.permission:school.module.school_profile')->group(function () {
        Route::get('school', [SchoolsController::class, 'index']);
        Route::put('school/{school}', [SchoolsController::class, 'update']);
    });

    Route::middleware('school.permission:school.module.contracts')->prefix('contracts')->group(function () {
        Route::get('', [AdminContractController::class, 'index']);
        Route::put('{contractTypeCode}', [AdminContractController::class, 'update']);
    });

    Route::middleware('school.permission:school.module.billing')->group(function () {
        Route::apiResource('invoice-items-custom', AdminInvoiceCustomItemController::class)
            ->names('billing.invoice-items-custom');
        Route::get('inscription-custom-charges', [InscriptionCustomChargeController::class, 'index']);
        Route::put('inscription-custom-charges/{charge}', [InscriptionCustomChargeController::class, 'update']);
        Route::delete('inscription-custom-charges/{charge}', [InscriptionCustomChargeController::class, 'destroy']);
    });

    Route::middleware('school.permission:school.module.user_management')->group(function () {
        Route::get('users/{user}/profile', [UsersController::class, 'profile']);
        Route::apiResource('users', UsersController::class);
    });

    Route::middleware('school.permission:school.module.training_groups')->group(function () {
        Route::apiResource('training_groups', TrainingGroupController::class, ['only' => ['show', 'store', 'update']])
            ->names('admin.training_groups');
        Route::apiResource('schedules', AdminScheduleController::class, ['except' => ['create', 'edit']])
            ->names('admin.schedules');
        Route::get('training-groups/board', [GroupAssignmentController::class, 'trainingBoard']);
        Route::post('training-groups/move', [GroupAssignmentController::class, 'moveTraining']);
    });

    Route::middleware('school.permission:school.module.competition_groups')->group(function () {
        Route::apiResource('competition_groups', CompetitionGroupController::class, ['only' => ['show', 'store', 'update']]);
        Route::apiResource('tournaments', AdminTournamentController::class, ['except' => ['create', 'edit']])
            ->names('admin.tournaments');
        Route::get('competition-groups/board', [GroupAssignmentController::class, 'competitionBoard']);
        Route::post('competition-groups/move', [GroupAssignmentController::class, 'moveCompetition']);
    });

    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('schools/options', [SchoolController::class, 'options']);
        Route::get('schools/{school}/permissions', [SchoolController::class, 'permissions']);
        Route::put('schools/{school}/permissions', [SchoolController::class, 'updatePermissions']);
        Route::get('schools/{school}/data-exports', [SchoolDataExportController::class, 'index']);
        Route::post('schools/{school}/data-exports', [SchoolDataExportController::class, 'store']);
        Route::get('schools/{school}/data-exports/{dataExport}', [SchoolDataExportController::class, 'show']);
        Route::get('schools/{school}/data-exports/{dataExport}/download', [SchoolDataExportController::class, 'download']);
        Route::get('schools/{school}', [SchoolController::class, 'show']);
        Route::post('schools', [SchoolController::class, 'store']);
        Route::put('schools/{school}', [SchoolController::class, 'update']);
        Route::delete('schools/{school}', [SchoolController::class, 'destroy']);
    });

    Route::middleware(['role:super-admin'])->prefix('evaluation-templates')->group(function () {
        Route::get('options', [EvaluationTemplateController::class, 'options']);
        Route::get('', [EvaluationTemplateController::class, 'index']);
        Route::post('', [EvaluationTemplateController::class, 'store']);
        Route::get('{evaluationTemplate}', [EvaluationTemplateController::class, 'show']);
        Route::put('{evaluationTemplate}', [EvaluationTemplateController::class, 'update']);
        Route::patch('{evaluationTemplate}', [EvaluationTemplateController::class, 'update']);
        Route::patch('{evaluationTemplate}/status', [EvaluationTemplateController::class, 'updateStatus']);
        Route::post('{evaluationTemplate}/duplicate', [EvaluationTemplateController::class, 'duplicate']);
        Route::delete('{evaluationTemplate}', [EvaluationTemplateController::class, 'destroy']);
    });
});
