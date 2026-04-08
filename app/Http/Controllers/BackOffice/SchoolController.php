<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\SchoolCreateRequest;
use App\Http\Requests\BackOffice\SchoolUpdateRequest;
use App\Models\School;
use App\Repositories\SchoolRepository;
use App\Service\API\RegisterService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class SchoolController extends Controller
{

    private SchoolRepository $repository;

    public function __construct(SchoolRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): Factory|View|Application
    {
        return view('backoffice.school.index');
    }

    /**
     * @throws ValidationException
     */
    public function store(SchoolCreateRequest $request, RegisterService $registerService): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        return response()->json($registerService->createUserSchoolUsesCase($request));
    }

    public function show(School $school, Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        return response()->json($school);
    }

    public function update(SchoolUpdateRequest $request, School $school): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $school = $this->repository->update($request, $school);
        return response()->json($school->isDirty());
    }

    public function destroy(School $school)
    {
        //
    }

    public function infoCampus(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $response = $this->repository->checkSchoolCampus();
        return response()->json($response, 200);
    }

    public function choose(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        $success = $this->repository->chooseSchool();
        return response()->json($success, $success ? 200 : 500);
    }
}
