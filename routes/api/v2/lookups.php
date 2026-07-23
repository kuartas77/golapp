<?php

use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Route;

Route::prefix('autocomplete')->group(function () {
    // Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
    // Route::get('identification_document_exists', [MasterController::class, 'existDocument'])->name('autocomplete.document_exists');
    // Route::get('code_unique_verify', [MasterController::class, 'codeUniqueVerify'])->name('autocomplete.verify_code');
    Route::get('list_code_unique', [MasterController::class, 'listUniqueCode']);
    Route::get('search_unique_code', [MasterController::class, 'searchUniqueCode']);
    // Route::get('list_code_unique_inscription', [MasterController::class, 'listUniqueCodeWithInscription'])->name('autocomplete.list_code_unique_inscription');
    Route::get('competition_groups', [MasterController::class, 'competitionGroupsByTournament']);
    Route::get('tournaments', [MasterController::class, 'tournamentsBySchool']);
});
