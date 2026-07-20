<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TrainingSessionUpsertRequest;
use App\Repositories\TrainingSessionRepository;
use App\Service\InstructorPeriodEditPolicy;
use App\Service\TrainigSession\TrainingSessionReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrainingSessionsController extends Controller
{
    public function __construct(
        private TrainingSessionRepository $repository,
        private InstructorPeriodEditPolicy $periodEditPolicy,
        private TrainingSessionReadService $reads,
    ) {}

    public function store(TrainingSessionUpsertRequest $request): JsonResponse
    {
        $this->periodEditPolicy->assertCanMutateDate($request->input('date'), 'date');

        $this->ensureGroupAccess(
            $request->integer('training_group_id'),
            $request->integer('year')
        );

        $trainingSession = $this->repository->store($request->validated());

        if (!$trainingSession) {
            return response()->json([
                'message' => __('messages.error_general'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => __('messages.training_session_created'),
            'data' => $this->reads->serialize($this->repository->findAccessibleOrFail($trainingSession->id)),
        ], Response::HTTP_CREATED);
    }

    public function show(int $trainingSession): JsonResponse
    {
        return response()->json([
            'data' => $this->reads->serialize($this->repository->findAccessibleOrFail($trainingSession)),
        ]);
    }

    public function attendanceContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'training_group_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        return response()->json([
            'data' => $this->reads->attendanceContext($validated),
        ]);
    }

    public function update(TrainingSessionUpsertRequest $request, int $trainingSession): JsonResponse
    {
        $model = $this->repository->findAccessibleForMutationOrFail($trainingSession);

        $this->periodEditPolicy->assertCanMutateDate($model->date, 'date');
        $this->periodEditPolicy->assertCanMutateDate($request->input('date'), 'date');

        $this->ensureGroupAccess(
            $request->integer('training_group_id'),
            $request->integer('year')
        );

        $payload = $request->validated();
        unset($payload['user_id']);

        if (!$this->repository->update($model, $payload)) {
            return response()->json([
                'message' => __('messages.error_general'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => __('Actualizado'),
            'data' => $this->reads->serialize($this->repository->findAccessibleOrFail($model->id)),
        ]);
    }

    public function destroy(int $trainingSession): JsonResponse
    {
        $model = $this->repository->findAccessibleForMutationOrFail($trainingSession);

        if (!$this->repository->destroy($model)) {
            return response()->json([
                'message' => __('messages.error_general'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => __('Eliminado'),
        ]);
    }

    private function ensureGroupAccess(int $trainingGroupId, int $year): void
    {
        $this->repository->findAccessibleTrainingGroupOrFail($trainingGroupId, $year);
    }
}
