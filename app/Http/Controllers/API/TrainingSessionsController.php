<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TrainingSessionUpsertRequest;
use App\Models\TrainingSession;
use App\Repositories\TrainingSessionRepository;
use App\Service\InstructorPeriodEditPolicy;
use App\Service\TrainigSession\TrainingSessionAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TrainingSessionsController extends Controller
{
    public function __construct(
        private TrainingSessionRepository $repository,
        private TrainingSessionAttendanceService $attendanceService,
        private InstructorPeriodEditPolicy $periodEditPolicy,
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
            'data' => $this->serialize($this->repository->findAccessibleOrFail($trainingSession->id)),
        ], Response::HTTP_CREATED);
    }

    public function show(int $trainingSession): JsonResponse
    {
        return response()->json([
            'data' => $this->serialize($this->repository->findAccessibleOrFail($trainingSession)),
        ]);
    }

    public function attendanceContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'training_group_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $year = (int) substr($validated['date'], 0, 4);

        $this->periodEditPolicy->assertCanMutateDate($validated['date'], 'date');

        if (! $this->periodEditPolicy->enabled() && $year !== (int) now()->year) {
            throw ValidationException::withMessages([
                'date' => 'La sincronización de asistencias solo está disponible para el año actual.',
            ]);
        }

        $group = $this->repository->findAccessibleTrainingGroupOrFail(
            (int) $validated['training_group_id'],
            $year
        );

        return response()->json([
            'data' => $this->attendanceService->context($group, $validated['date']),
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
            'data' => $this->serialize($this->repository->findAccessibleOrFail($model->id)),
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
            'absence_inscription_ids' => $trainingSession->absence_inscription_ids ?? [],
            'absence_names' => $this->attendanceService->absenceNames($trainingSession),
            'attendance_synced_at' => $trainingSession->attendance_synced_at?->toISOString(),
            'attendance_synced' => $trainingSession->attendance_synced_at !== null,
            'period_locked' => ! $this->periodEditPolicy->canMutateDate($trainingSession->date),
            'incidents' => $trainingSession->incidents,
            'feedback' => $trainingSession->feedback,
            'created_at' => $trainingSession->created_at?->format('Y-m-d'),
            'updated_at' => $trainingSession->updated_at?->format('Y-m-d'),
            'export_pdf_url' => route('export.training_sessions.pdf', [$trainingSession->id]),
            'tasks' => $tasks,
        ];
    }
}
