<?php

namespace App\Http\Controllers\API\Instructor;

use App\Models\Assist;
use Illuminate\Http\JsonResponse;
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
    public function index(AssistsRequest $request, AssistsService $assistsService): AssitsCollection
    {
        return new AssitsCollection($assistsService->getAssists($request->validated()));
    }

    public function update(AssistsUpdateRequest $request, Assist $assist, AssistRepository $repository): JsonResponse
    {
        return response()->json(['data' => $repository->update($assist, $request->validated())]);
    }
}
