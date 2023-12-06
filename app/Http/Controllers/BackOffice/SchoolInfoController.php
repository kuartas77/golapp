<?php

namespace App\Http\Controllers\BackOffice;

use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolInfoController
{
    public function index(Request $request)
    {
        return view('backoffice.schools-info.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        abort_unless($request->ajax(), 404);
        return response()->noContent();
    }

    /**
     * Display the specified resource.
     *
     * @param School $school
     * @return JsonResponse
     */
    public function show(School $school, Request $request)
    {
        abort_unless($request->ajax(), 404);
        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param School $school
     * @return Response
     */
    public function update(Request $request, School $school)
    {
        abort_unless($request->ajax(), 404);
        return response()->noContent();
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
