<?php

namespace App\Http\Controllers\SchoolPages;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolUpdateRequest;
use App\Models\School;
use App\Service\API\RegisterService;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    use ErrorTrait;

    public function index(Request $request, School $school)
    {
        $school = getSchool(auth()->user());
        $school->load(['settingsValues']);

        return response()->json($school);
    }

    public function update(SchoolUpdateRequest $request, School $school, RegisterService $registerService)
    {
        $success = $registerService->updateSchoolUsesCase($request, $school);

        return response()->json(['success' => $success]);
    }
}
