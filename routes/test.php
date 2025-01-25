<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Models\Inscription;
use App\Custom\PDFContractTest;

Route::middleware(['auth'])->prefix('test')->group(function () {

    Route::get('tester', function (Request $request) {

        $validated = $request->validate([
            'unique_code' => ['required', 'numeric']
        ]);

        if (!$validated) {
            return response()->json($validated, 422);
        }

        $inscription = Inscription::with(['school'])->firstWhere('unique_code', '20250001');
        $school = $inscription->school;
        $destinations = [];
        $destinations[$school->email] = $school->name;
        Notification::route('mail', $destinations)->notify(
            (new InscriptionToSchoolNotification($inscription, $school))->onQueue('emails')
        );

        response()->json(['success'], 200);
    });

    Route::get('contract', function (Request $request) {
        $validated = $request->validate([
            'school_id' => ['required', 'numeric']
        ]);

        if (!$validated) {
            return response()->json($validated, 422);
        }

        return PDFContractTest::makeContract(
            documentOption: 1,
            filename: 'CONTRATO.pdf',
            params: $request->only(['school_id'])
        );
    });
});
