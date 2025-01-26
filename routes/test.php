<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Models\Inscription;
use App\Custom\PDFContractTest;
use App\Models\Player;

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

        $player = Player::with('people')->find(560);

        $people = $player->people;

        $tutor = $people->firstWhere('tutor', true);
        $mother = $people->whereIn('relationship', ['15', '16'])->first();
        $dad = $people->whereIn('relationship', ['20', '21'])->first();

        dd($mother, $dad);

        // $validated = $request->validate([
        //     'school_id' => ['required', 'numeric'],
        //     'document' => ['required', 'numeric'],
        // ]);

        // if (!$validated) {
        //     return response()->json($validated, 422);
        // }

        // return PDFContractTest::makeContract(
        //     documentOption: $request->input('document', 0),
        //     filename: 'CONTRATO.pdf',
        //     params: $request->only(['school_id'])
        // );
    });
});
