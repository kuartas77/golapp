<?php

namespace App\Http\Controllers\API\Instructor;

use App\Models\Assist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * @return Response
     */
    public function index(AssistsRequest $request, AssistsService $assistsService)
    {
        return new AssitsCollection($assistsService->getAssists($request->validated()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Assist $assist
     * @return Response
     */
    public function update(AssistsUpdateRequest $request, Assist $assist, AssistRepository $repository)
    {
        return response()->json(['data' => $repository->update($assist, $request->validated())]);
    }
}
