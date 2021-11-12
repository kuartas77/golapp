<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\SchoolCreateRequest;
use App\Http\Requests\BackOffice\SchoolUpdateRequest;
use App\Models\School;
use App\Repositories\SchoolRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $this->repository->getAll();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(SchoolCreateRequest $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        $school = $this->repository->create($request);
        return response()->json($school->wasRecentlyCreated);
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
        //
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
}
