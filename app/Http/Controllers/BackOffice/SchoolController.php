<?php

namespace App\Http\Controllers\BackOffice;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\API\RegisterService;
use App\Repositories\SchoolRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\BackOffice\SchoolCreateRequest;
use App\Http\Requests\BackOffice\SchoolUpdateRequest;

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
        $school = School::find($request->school_id);
        Session::put('admin.school', $school);
        return response()->json(true, 200);
    }
}
