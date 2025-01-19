<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\SchoolsController;
use App\Http\Controllers\Portal\LoginController;
use App\Http\Controllers\Portal\InscriptionsController;
use App\Http\Controllers\Portal\HomePlayerController;
use App\Http\Controllers\Players\PlayerExportController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ExportController;

Route::name('portal.')->group(function(){

    Route::get('ingreso', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('ingreso', [LoginController::class, 'login'])->name('player.login');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware(['guest'])->group(function () {
        Route::get('escuelas', [SchoolsController::class, 'index'])->name('school.index');
        Route::get('escuelas/{school}', [SchoolsController::class, 'show'])->name('school.show');

        Route::post('{school}/inscripcion', [InscriptionsController::class, 'store'])->name('school.inscription.store');

        Route::prefix('autocomplete')->group(function () {
            Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
            Route::get('search_doc', [MasterController::class, 'searchDoc'])->name('autocomplete.search_doc');
        });
    });

    Route::get('dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('player.images');

    Route::middleware(['auth:players'])->group(function () {

        Route::get('jugador', [HomePlayerController::class, 'index'])->name('player.home');
        Route::get('jugador/{unique_code}', [HomePlayerController::class, 'show'])->name('player.show');
        Route::put('player/{unique_code}', [HomePlayerController::class, 'update'])->name('player.update');


        Route::prefix('export')->name('export.')->group(function () {
            Route::get('player/{player}/pdf', [ExportController::class, 'exportPlayerPDF'])->name('player');
            Route::get('inscription/{player_id}/{inscription_id}/{year?}/{quarter?}', [PlayerExportController::class, 'exportInscription'])->name('inscription');
        });
    });
});
