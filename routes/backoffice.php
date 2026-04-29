<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\BackOffice\SettingValueController;
use App\Http\Controllers\BackOffice\SchoolInfoController;
use App\Http\Controllers\BackOffice\SchoolController;
use App\Http\Controllers\BackOffice\ManualEmailController;
use Illuminate\Http\RedirectResponse;

Route::middleware(['auth', 'role:super-admin|school'])->group(function () {

    Route::post('school/choose', [SchoolController::class, 'choose'])->name('school.choose');
});

Route::middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('emails_registration_school', ManualEmailController::class);

    Route::prefix('config')->name('config.')->group(function (){
        // El CRUD SPA de super-admin vive en resources/js/pages/admin/school y
        // consume sus datos desde /api/v2/admin/schools y /api/v2/admin/schools/options.
        Route::resource("schools", SchoolController::class);
        Route::resource("schools_info", SchoolInfoController::class);
        Route::resource("settings", SettingValueController::class);
        Route::resource("users", UserController::class);

        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::get('', fn (): RedirectResponse => redirect('/administracion/contratos'))->name('index');
            Route::get('create', fn (): RedirectResponse => redirect('/administracion/contratos'))->name('create');
            Route::get('{contract}/edit', fn (): RedirectResponse => redirect('/administracion/contratos'))->name('edit');
        });

        Route::prefix('datatables')->name('datatables.')->group(function () {
            // Route::get('enabled', [DataTableController::class, 'enabledInscriptions'])->name('inscriptions.enabled');
            // Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups'])->name('training_groups.enabled');
            // Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups'])->name('training_groups.retired');
            // Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups'])->name('competition_groups.enabled');
            // Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups'])->name('competition_groups.retired');
            // Route::get('days_enabled', [DataTableController::class, 'enabledDays'])->name('days.enabled');
            // Route::get('players_enabled', [DataTableController::class, 'enabledPlayers'])->name('players.enabled');
            Route::get('schools', [DataTableController::class, 'schools'])->name('schools');
            Route::get('schools_info', [DataTableController::class, 'schoolsInfo'])->name('schools_info');

        });
    });

    Route::post('upload', function(Request $request){
        try {

            return response()->json([
                        'uploaded' => true,
                        'url' => asset('img/user.png')
                    ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => $e->getMessage()
                ]
            ]);
        }
    })->name('upload');



});
