<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Portal\InscriptionsController;
use App\Http\Controllers\Portal\HomePlayerController;
use App\Http\Controllers\Portal\LoginController;
use App\Http\Controllers\Portal\SchoolsController;
use App\Models\Player;

Route::name('portal.')->group(function(){

    Route::get('ingreso', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::get('acudientes/login', [LoginController::class, 'showLoginForm']);
    Route::post('ingreso', [LoginController::class, 'login'])->name('guardian.login');
    Route::post('acudientes/login', [LoginController::class, 'login']);
    Route::get('acudientes/recuperar', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('acudientes/recuperar', [LoginController::class, 'sendResetLink'])->name('password.email');
    Route::get('acudientes/restablecer', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('acudientes/restablecer', [LoginController::class, 'resetPassword'])->name('password.update');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('autocomplete')->group(function () {
        Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
        Route::get('search_doc', [MasterController::class, 'searchDoc'])->name('autocomplete.search_doc');
    });

    Route::middleware(['guest:guardians'])->group(function () {
        Route::get('escuelas', [SchoolsController::class, 'index'])->name('school.index');
        Route::get('escuelas/{school}', [SchoolsController::class, 'show'])->name('school.show');

        Route::post('{school}/inscripcion', [InscriptionsController::class, 'store'])->name('school.inscription.store');
    });

    Route::get('dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('player.images');

    Route::middleware(['ensure.guardian'])->group(function () {
        Route::get('acudientes', [HomePlayerController::class, 'index'])->name('guardians.home');
        Route::get('acudientes/jugadores/{player:unique_code}', [HomePlayerController::class, 'show'])->name('guardians.players.show');
        Route::put('acudientes/jugadores/{player:unique_code}', [HomePlayerController::class, 'update'])->name('player.update');
        Route::get('acudientes/jugadores/{player:unique_code}/inscripciones/{inscription}', [HomePlayerController::class, 'inscriptionReport'])->name('export.inscription');

        Route::get('jugador', fn () => redirect()->route('portal.guardians.home'))->name('player.home');
        Route::get('jugador/{player:unique_code}', fn (Player $player) => redirect()->route('portal.guardians.players.show', [$player]))->name('player.show');
    });
});
