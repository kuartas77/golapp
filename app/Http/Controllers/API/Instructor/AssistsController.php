<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AssistsRequest;
use App\Http\Requests\API\AssistsUpdateRequest;
use App\Http\Resources\API\Assists\AssistsCollection;
use App\Repositories\AssistRepository;
use App\Service\API\Instructor\AssistsService;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\JsonResponse;

class AssistsController extends Controller
{
    public function __construct(
        private AssistsService $assistsService,
        private AssistRepository $repository,
        private InstructorPeriodEditPolicy $periodEditPolicy,
    ) {
        $this->middleware('ability:assists-index')->only('index');
        $this->middleware('ability:assists-update')->only('upsert');
    }

    public function index(AssistsRequest $request): AssistsCollection
    {
        return new AssistsCollection($this->assistsService->getAssists($request->validated()));
    }

    public function upsert(AssistsUpdateRequest $request): JsonResponse
    {
        $assistDto = $request->toDto();

        $this->periodEditPolicy->assertCanMutateYearMonth($assistDto->year, $assistDto->month, 'assist');

        if ($this->repository->inscriptionBelongsToDeletedRecord($assistDto->inscription_id)) {
            return response()->json([
                'message' => AssistRepository::RETIRED_INSCRIPTION_MESSAGE,
                'errors' => [
                    'assist' => [AssistRepository::RETIRED_INSCRIPTION_MESSAGE],
                ],
            ], 422);
        }

        $updated = $this->repository->upsert($assistDto);

        return response()->json(['data' => $updated]);
    }
}
