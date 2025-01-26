<?php

use App\Http\Controllers\BackOffice\ContractsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\BackOffice\SettingValueController;
use App\Http\Controllers\BackOffice\SchoolInfoController;
use App\Http\Controllers\BackOffice\SchoolController;
use App\Http\Controllers\BackOffice\ManualEmailController;

Route::middleware(['auth', 'role:super-admin|school'])->group(function () {

    Route::post('school/choose', [SchoolController::class, 'choose'])->name('school.choose');
});

Route::middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('emails_registration_school', ManualEmailController::class);

    Route::prefix('config')->name('config.')->group(function (){

        Route::resource("schools", SchoolController::class);
        Route::resource("schools_info", SchoolInfoController::class);
        Route::resource("settings", SettingValueController::class);
        Route::resource("users", UserController::class);

        Route::resource("contracts", ContractsController::class)->except(['destroy']);

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
            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => $e->getMessage()
                ]
            ]);
        }
    })->name('upload');



});