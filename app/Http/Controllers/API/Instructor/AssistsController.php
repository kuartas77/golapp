<?php

namespace App\Http\Controllers\API\Instructor;

use App\Models\Assist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\AssistRepository;
use App\Http\Requests\API\AssistsRequest;
use App\Service\API\Instructor\AssistsService;
use App\Http\Requests\API\AssistsUpdateRequest;
use App\Http\Resources\API\Assists\AssitsCollection;

class AssistsController extends Controller
{
/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AssistsRequest $request, AssistsService $assistsService)
    {
        return new AssitsCollection($assistsService->getAssists($request->validated()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  Assist $assist
     * @return \Illuminate\Http\Response
     */
    public function show(Assist $assist)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Assist $assist
     * @return \Illuminate\Http\Response
     */
    public function update(AssistsUpdateRequest $request, Assist $assist, AssistRepository $repository)
    {
        return response()->json(['data' => $repository->update($assist, $request->validated())]);
    }

    /**s
     * Remove the specified resource from storage.
     *
     * @param  Assist $assist
     * @return \Illuminate\Http\Response
     */
    public function destroy(Assist $assist)
    {
        abort(404);
    }
}
