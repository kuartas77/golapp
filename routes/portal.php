<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\SchoolsController;
use App\Http\Controllers\Portal\LoginController;
use App\Http\Controllers\Portal\InscriptionsController;
use App\Http\Controllers\MasterController;

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


});


Route::middleware([])->group(function () {
    // Route::get('/', [PublicController::class, 'index'])->name('public');
    // ;
    // Route::get('img/public/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('public.images');
});

// portal.login.form
// portal.school.show
// portal.school.inscription.form
// portal.school.player.home