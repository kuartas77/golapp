<?php

use App\Custom\PDFContractTest;
use App\Models\Inscription;
use App\Models\School;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Service\SharedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('test')->group(function () {

    Route::get('tester', function (Request $request) {



        // $school = School::query()->with('inscriptions')->firstWhere('id', 9);

        // $service = new SharedService();
        // foreach ($$school->inscriptions as $inscription) {
        //     $service->paymentAssist($inscription);
        // }

        // $validated = $request->validate([
        //     'unique_code' => ['required', 'numeric']
        // ]);

        // if (!$validated) {
        //     return response()->json($validated, 422);
        // }

        // $inscription = Inscription::with(['school'])->firstWhere('unique_code', '20250001');
        // $school = $inscription->school;
        // $destinations = [];
        // $destinations[$school->email] = $school->name;
        // Notification::route('mail', $destinations)->notify(
        //     (new InscriptionToSchoolNotification($inscription, $school))->onQueue('emails')
        // );

        response()->json(['success'], 200);
    });

    Route::get('contract', function (Request $request) {

        $validated = $request->validate([
            'school_id' => ['required', 'numeric'],
            'document' => ['required', 'numeric'],
        ]);

        if (!$validated) {
            return response()->json($validated, 422);
        }

        $params = $request->only(['school_id']);
        $params['empty'] = true;

        return PDFContractTest::makeContract(
            documentOption: $request->input('document', 0),
            filename: 'CONTRATO.pdf',
            params: $params
        );
    });
});
