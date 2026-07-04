<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SessionPlanningUpsertRequest;
use App\Models\TrainingSession;
use App\Repositories\TrainingSessionRepository;
use App\Service\InstructorPeriodEditPolicy;
use App\Service\TrainigSession\TrainingSessionAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SessionPlanningController extends Controller
{
    public function __construct(
        private TrainingSessionRepository $repository,
        private TrainingSessionAttendanceService $attendanceService,
        private InstructorPeriodEditPolicy $periodEditPolicy,
    ) {}

    public function store(SessionPlanningUpsertRequest $request): JsonResponse
    {
        $this->periodEditPolicy->assertCanMutateDate($request->input('date'), 'date');
        $this->repository->findAccessibleTrainingGroupOrFail($request->integer('training_group_id'), $request->integer('year'));
        $session = $this->repository->storePlanned($request->validated());

        return $session
            ? response()->json(['message' => 'Planificación creada.', 'data' => $this->serialize($this->repository->findAccessiblePlannedOrFail($session->id))], Response::HTTP_CREATED)
            : response()->json(['message' => __('messages.error_general')], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function show(int $sessionPlanning): JsonResponse
    {
        return response()->json(['data' => $this->serialize($this->repository->findAccessiblePlannedOrFail($sessionPlanning))]);
    }

    public function update(SessionPlanningUpsertRequest $request, int $sessionPlanning): JsonResponse
    {
        $model = $this->repository->findAccessiblePlannedForMutationOrFail($sessionPlanning);
        $this->periodEditPolicy->assertCanMutateDate($model->date, 'date');
        $this->periodEditPolicy->assertCanMutateDate($request->input('date'), 'date');
        $this->repository->findAccessibleTrainingGroupOrFail($request->integer('training_group_id'), $request->integer('year'));
        $payload = $request->validated();
        unset($payload['user_id']);

        return $this->repository->updatePlanned($model, $payload)
            ? response()->json(['message' => 'Planificación actualizada.', 'data' => $this->serialize($this->repository->findAccessiblePlannedOrFail($model->id))])
            : response()->json(['message' => __('messages.error_general')], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function destroy(int $sessionPlanning): JsonResponse
    {
        $model = $this->repository->findAccessiblePlannedForMutationOrFail($sessionPlanning);
        $this->periodEditPolicy->assertCanMutateDate($model->date, 'date');
        abort_unless(isSchool() || isAdmin(), 403);

        return $this->repository->destroy($model)
            ? response()->json(['message' => 'Planificación eliminada.'])
            : response()->json(['message' => __('messages.error_general')], 500);
    }

    public function attendanceContext(Request $request): JsonResponse
    {
        $validated = $request->validate(['training_group_id' => ['required', 'integer'], 'date' => ['required', 'date_format:Y-m-d']]);
        $this->periodEditPolicy->assertCanMutateDate($validated['date'], 'date');
        $year = (int) substr($validated['date'], 0, 4);
        if (! $this->periodEditPolicy->enabled() && $year !== (int) now()->year) {
            throw ValidationException::withMessages(['date' => 'La sincronización de asistencias solo está disponible para el año actual.']);
        }
        $group = $this->repository->findAccessibleTrainingGroupOrFail((int) $validated['training_group_id'], $year);
        return response()->json(['data' => $this->attendanceService->context($group, $validated['date'])]);
    }

    private function serialize(TrainingSession $session): array
    {
        $session->loadMissing(['user:id,name', 'training_group:id,name,category,days,schedules', 'phases']);
        return [
            'id' => $session->id, 'creator_name' => $session->user?->name,
            'training_group_id' => $session->training_group_id, 'training_group_name' => $session->training_group?->full_group,
            'year' => $session->year, 'period' => $session->period, 'session' => $session->session,
            'date' => $session->date, 'hour' => $session->hour, 'training_ground' => $session->training_ground,
            'material' => $session->material, 'warm_up' => $session->warm_up, 'back_to_calm' => $session->back_to_calm,
            'players' => $session->players, 'absences' => $session->absences,
            'absence_inscription_ids' => $session->absence_inscription_ids ?? [],
            'absence_names' => $this->attendanceService->absenceNames($session),
            'attendance_synced' => $session->attendance_synced_at !== null,
            'period_locked' => ! $this->periodEditPolicy->canMutateDate($session->date),
            'incidents' => $session->incidents, 'feedback' => $session->feedback,
            'created_at' => $session->created_at?->format('Y-m-d'),
            'export_pdf_url' => route('session-plannings.pdf', $session->id),
            'phases' => $session->phases->map->only(['position', 'name', 'time', 'dosage', 'description', 'diagram'])->values(),
        ];
    }
}
