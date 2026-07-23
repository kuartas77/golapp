<?php

use App\Http\Controllers\API\Portal\GuardianAuthController;
use App\Http\Controllers\API\Portal\GuardianEvaluationController;
use App\Http\Controllers\API\Portal\GuardianPlayerController;
use App\Http\Controllers\API\Portal\GuardianProfileController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Portal\ContractController as PortalContract;
use App\Http\Controllers\Portal\InscriptionsController as PortalInscription;
use App\Http\Controllers\Portal\SchoolsController as PortalSchool;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('escuelas/data', [PortalSchool::class, 'indexData'])->name('school.index.data');
    Route::get('escuelas/{school}/data', [PortalSchool::class, 'showData'])->name('school.show.data');
    Route::get('escuelas/{school}/contracts/{contractTypeCode}', [PortalContract::class, 'show'])->name('school.contract.show');

    Route::post('inscription-client-errors', [PortalInscription::class, 'clientError'])
        ->name('inscription.client-error');
    Route::post('{school}/inscripcion/verificar-correo/solicitar', [PortalInscription::class, 'requestGuardianEmailCode'])
        ->name('school.inscription.guardian-email.request');
    Route::post('{school}/inscripcion/verificar-correo/confirmar', [PortalInscription::class, 'confirmGuardianEmailCode'])
        ->name('school.inscription.guardian-email.confirm');
    Route::post('{school}/inscripcion', [PortalInscription::class, 'store'])->name('school.inscription.store');

    Route::prefix('autocomplete')->group(function () {
        Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
        Route::get('search_doc', [MasterController::class, 'searchDoc'])->name('autocomplete.search_doc');
    });

    Route::get('dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('player.images');

    Route::prefix('acudientes')->name('guardians.')->group(function () {
        Route::post('login', [GuardianAuthController::class, 'login'])->name('login');
        Route::post('forgot-password', [GuardianAuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('reset-password', [GuardianAuthController::class, 'resetPassword'])->name('reset-password');

        Route::middleware(['auth:sanctum', 'ensure.guardian'])->group(function () {
            Route::get('me', [GuardianAuthController::class, 'me'])->name('me');
            Route::post('logout', [GuardianAuthController::class, 'logout'])->name('logout');
            Route::put('profile', [GuardianProfileController::class, 'update'])->name('profile.update');

            Route::get('players', [GuardianPlayerController::class, 'index'])->name('players.index');
            Route::get('players/{player}', [GuardianPlayerController::class, 'show'])->name('players.show');
            Route::put('players/{player}', [GuardianPlayerController::class, 'update'])->name('players.update');
            Route::get('players/{player}/inscription-report/{inscription?}', [GuardianPlayerController::class, 'inscriptionReport'])->name('players.inscription-report');
            Route::get('evaluations/{evaluation}/pdf', [GuardianEvaluationController::class, 'pdf'])->name('evaluations.pdf');
            Route::get('inscriptions/{inscription}/comparison', [GuardianPlayerController::class, 'comparison'])->name('inscriptions.comparison');
        });
    });
});
