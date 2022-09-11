<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\BackOffice\SchoolController;
use App\Http\Controllers\BackOffice\SchoolInfoController;
use App\Http\Controllers\BackOffice\SettingValueController;



Route::middleware(['auth', 'role:super-admin'])->group(function ($route) {

    $route->prefix('config')->name('config.')->group(function ($route){
        
        $route->resource("schools", SchoolController::class);
        $route->resource("schools", SchoolInfoController::class);
        $route->resource("settings", SettingValueController::class);
        $route->resource("users", UserController::class);

        $route->prefix('datatables')->name('datatables.')->group(function ($route) {
            // $route->get('enabled', [DataTableController::class, 'enabledInscriptions'])->name('inscriptions.enabled');
            // $route->get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups'])->name('training_groups.enabled');
            // $route->get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups'])->name('training_groups.retired');
            // $route->get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups'])->name('competition_groups.enabled');
            // $route->get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups'])->name('competition_groups.retired');
            // $route->get('days_enabled', [DataTableController::class, 'enabledDays'])->name('days.enabled');
            // $route->get('players_enabled', [DataTableController::class, 'enabledPlayers'])->name('players.enabled');
            $route->get('schools', [DataTableController::class, 'schools'])->name('schools');
            $route->get('schools_info', [DataTableController::class, 'schoolsInfo'])->name('schools_info');
    
        });
    });

    

});