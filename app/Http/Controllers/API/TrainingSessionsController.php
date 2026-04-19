<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TrainingSessionUpsertRequest;
use App\Models\TrainingSession;
use App\Repositories\TrainingSessionRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TrainingSessionsController extends Controller
{
    public function __construct(private TrainingSessionRepository $repository)
    {
    }

    public function store(TrainingSessionUpsertRequest $request): JsonResponse
    {
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
            'data' => $this->serialize($this->repository->findAccessibleOrFail($trainingSession->id)),
        ], Response::HTTP_CREATED);
    }

    public function show(int $trainingSession): JsonResponse
    {
        return response()->json([
            'data' => $this->serialize($this->repository->findAccessibleOrFail($trainingSession)),
        ]);
    }

    public function update(TrainingSessionUpsertRequest $request, int $trainingSession): JsonResponse
    {
        $model = $this->repository->findAccessibleForMutationOrFail($trainingSession);

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
            'data' => $this->serialize($this->repository->findAccessibleOrFail($model->id)),
        ]);
    }

    private function ensureGroupAccess(int $trainingGroupId, int $year): void
    {
        abort_unless(
            $this->repository->trainingGroupIsAccessible($trainingGroupId, $year),
            Response::HTTP_NOT_FOUND
        );
    }

    private function serialize(TrainingSession $trainingSession): array
    {
        $trainingSession->loadMissing([
            'user:id,name',
            'training_group:id,name,category,days,schedules',
            'tasks' => fn ($query) => $query->orderBy('task_number'),
        ]);

        $tasks = collect(range(1, 3))
            ->map(function (int $taskNumber) use ($trainingSession): array {
                $task = $trainingSession->tasks->firstWhere('task_number', $taskNumber);

                return [
                    'task_number' => $taskNumber,
                    'task_name' => $task?->task_name,
                    'general_objective' => $task?->general_objective,
                    'specific_goal' => $task?->specific_goal,
                    'content_one' => $task?->content_one,
                    'content_two' => $task?->content_two,
                    'content_three' => $task?->content_three,
                    'ts' => $task?->ts,
                    'sr' => $task?->sr,
                    'tt' => $task?->tt,
                    'observations' => $task?->observations,
                ];
            })
            ->values()
            ->all();

        return [
            'id' => $trainingSession->id,
            'creator_name' => $trainingSession->user?->name,
            'training_group_id' => $trainingSession->training_group_id,
            'training_group_name' => $trainingSession->training_group?->full_group,
            'year' => $trainingSession->year,
            'period' => $trainingSession->period,
            'session' => $trainingSession->session,
            'date' => $trainingSession->date,
            'hour' => $trainingSession->hour,
            'training_ground' => $trainingSession->training_ground,
            'material' => $trainingSession->material,
            'warm_up' => $trainingSession->warm_up,
            'back_to_calm' => $trainingSession->back_to_calm,
            'players' => $trainingSession->players,
            'absences' => $trainingSession->absences,
            'incidents' => $trainingSession->incidents,
            'feedback' => $trainingSession->feedback,
            'created_at' => $trainingSession->created_at?->format('Y-m-d'),
            'updated_at' => $trainingSession->updated_at?->format('Y-m-d'),
            'export_pdf_url' => route('export.training_sessions.pdf', [$trainingSession->id]),
            'tasks' => $tasks,
        ];
    }
}
