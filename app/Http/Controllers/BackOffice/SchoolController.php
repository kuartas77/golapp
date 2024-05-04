<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\SchoolCreateRequest;
use App\Http\Requests\BackOffice\SchoolUpdateRequest;
use App\Models\School;
use App\Repositories\SchoolRepository;
use App\Service\API\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SchoolController extends Controller
{

    private SchoolRepository $repository;

    public function __construct(SchoolRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('backoffice.school.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(SchoolCreateRequest $request, RegisterService $registerService): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        return response()->json($registerService->createUserSchoolUsesCase($request));
    }

    /**
     * Display the specified resource.
     *
     * @param School $school
     * @return JsonResponse
     */
    public function show(School $school, Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        return response()->json($school);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param School $school
     * @return Response
     */
    public function update(SchoolUpdateRequest $request, School $school)
    {
        abort_unless($request->ajax(), 404);
        $school = $this->repository->update($request, $school);
        return response()->json($school->isDirty());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param School $school
     * @return Response
     */
    public function destroy(School $school)
    {
        //
    }

    public function choose(Request $request)
    {
        $prefixKey = isAdmin() ? 'admin.' : (isSchool() ? 'school.': '');
        Session::put("{$prefixKey}selected_school", $request->school_id);

        Cache::remember(School::KEY_SCHOOL_CACHE . "_{$prefixKey}_{$request->school_id}",
            now()->addMinutes(env('SESSION_LIFETIME', 120)),
            fn() => School::with(['settingsValues'])->find($request->school_id));
        return response()->json(true, 200);
    }
}
