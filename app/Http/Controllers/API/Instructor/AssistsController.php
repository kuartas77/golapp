<?php

namespace App\Http\Controllers\API\Instructor;

use App\Models\Assist;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\AssistRepository;
use App\Http\Requests\API\AssistsRequest;
use App\Service\API\Instructor\AssistsService;
use App\Http\Requests\API\AssistsUpdateRequest;
use App\Http\Resources\API\Assists\AssistsCollection;

class AssistsController extends Controller
{

    public function __construct(private AssistsService $assistsService, private AssistRepository $repository)
    {
        $this->middleware('ability:assists-index')->only('index');
        $this->middleware('ability:assists-update')->only('upsert');
    }

    public function index(AssistsRequest $request, ): AssistsCollection
    {
        return new AssistsCollection($this->assistsService->getAssists($request->validated()));
    }

    public function upsert(AssistsUpdateRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->repository->upsert($request->validated())]);
    }
}
