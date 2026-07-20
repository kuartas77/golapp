<?php

namespace App\Service\TrainigSession;

use App\Models\TrainingSession;
use App\Repositories\TrainingSessionRepository;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Validation\ValidationException;

class TrainingSessionReadService
{
    public function __construct(
        private TrainingSessionRepository $repository,
        private TrainingSessionAttendanceService $attendanceService,
        private InstructorPeriodEditPolicy $periodEditPolicy,
    ) {
    }

    public function attendanceContext(array $validated): array
    {
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

        return $this->attendanceService->context($group, $validated['date']);
    }

    public function serialize(TrainingSession $trainingSession): array
    {
        $trainingSession->loadMissing([
            'user:id,name',
            'training_group:id,name,category,days,schedules',
            'tasks' => fn ($query) => $query->orderBy('task_number'),
        ]);

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
            'tasks' => $this->taskPayloads($trainingSession),
        ];
    }

    private function taskPayloads(TrainingSession $trainingSession): array
    {
        return collect(range(1, 3))
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
    }
}
