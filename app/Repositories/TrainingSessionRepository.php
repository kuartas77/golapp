<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Service\TrainigSession\TrainingSessionAttendanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingSessionRepository
{
    public function __construct(
        protected TrainingSession $trainingSession,
        private TrainingSessionAttendanceService $attendanceService,
    ) {}

    private function accessibleBaseQuery(): Builder
    {
        return $this->trainingSession->query()
            ->select('training_sessions.*')
            ->whereHas('training_group')
            ->schoolId()
            ->when(isInstructor(), function (Builder $query): void {
                $query->whereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('training_group_user')
                        ->whereColumn('training_group_user.training_group_id', 'training_sessions.training_group_id')
                        ->where('training_group_user.user_id', auth()->id())
                        ->whereColumn('training_group_user.assigned_year', 'training_sessions.year');
                });
            });
    }

    public function accessibleQuery(): Builder
    {
        return $this->accessibleBaseQuery()
            ->with([
                'school',
                'user:id,name',
                'training_group:id,name,category,days,schedules',
            ])
            ->withCount(['tasks']);
    }

    public function datatableQuery(): Builder
    {
        return $this->accessibleQuery()
            ->leftJoin('users', 'users.id', '=', 'training_sessions.user_id')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'training_sessions.training_group_id')
            ->addSelect([
                'users.name as creator_name_sort',
                'training_groups.name as training_group_name_sort',
            ]);
    }

    public function list(): Collection
    {
        return $this->accessibleQuery()->get();
    }

    public function findAccessibleOrFail(int $id): TrainingSession
    {
        return $this->accessibleQuery()
            ->with(['school','tasks' => fn ($query) => $query->orderBy('task_number')])
            ->findOrFail($id);
    }

    public function findAccessibleForMutationOrFail(int $id): TrainingSession
    {
        return $this->accessibleBaseQuery()->findOrFail($id);
    }

    public function findAccessibleTrainingGroupOrFail(int $trainingGroupId, int $year): TrainingGroup
    {
        return TrainingGroup::query()
            ->schoolId()
            ->whereKey($trainingGroupId)
            ->when(isInstructor(), fn (Builder $query) => $query->byInstructor($year))
            ->whereRaw('LOWER(name) <> ?', ['provisional'])
            ->firstOrFail();
    }

    public function store(array $payload): ?TrainingSession
    {
        try {
            return DB::transaction(function () use ($payload): TrainingSession {
                $this->ensureUniqueGroupDate($payload);
                $trainingSession = $this->makeTraininSession(new TrainingSession(), $payload);
                $trainingSession->save();

                $tasks = $this->makeTask($payload);
                if ($tasks !== []) {
                    $trainingSession->tasks()->createMany($tasks);
                }

                if ($payload['sync_attendance'] ?? false) {
                    $group = $this->findAccessibleTrainingGroupOrFail(
                        (int) $payload['training_group_id'],
                        (int) $payload['year']
                    );
                    $this->attendanceService->sync(
                        $trainingSession,
                        $group,
                        $payload['absence_inscription_ids'] ?? []
                    );
                }

                return $trainingSession;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $throwable) {
            report($throwable);
            return null;
        }
    }

    public function update(TrainingSession $trainingSession, array $payload): bool
    {
        try {
            return DB::transaction(function () use ($trainingSession, $payload): bool {
                $this->ensureSyncedIdentityIsUnchanged($trainingSession, $payload);
                $this->ensureUniqueGroupDate($payload, $trainingSession->id);
                $trainingSession = $this->makeTraininSession($trainingSession, $payload);
                $trainingSession->save();

                $tasks = $this->makeTask($payload);
                if ($tasks !== []) {
                    $trainingSession->tasks()->forceDelete();
                    $trainingSession->tasks()->createMany($tasks);
                }

                if ($payload['sync_attendance'] ?? false) {
                    $group = $this->findAccessibleTrainingGroupOrFail(
                        (int) $payload['training_group_id'],
                        (int) $payload['year']
                    );
                    $this->attendanceService->sync(
                        $trainingSession,
                        $group,
                        $payload['absence_inscription_ids'] ?? []
                    );
                }

                return true;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $throwable) {
            report($throwable);
            return false;
        }
    }

    public function destroy(TrainingSession $trainingSession): bool
    {
        try {
            DB::transaction(function () use ($trainingSession): void {
                $trainingSession->tasks()->delete();
                $trainingSession->delete();
            });

            return true;
        } catch (\Throwable $throwable) {
            report($throwable);

            return false;
        }
    }

    private function makeTraininSession(TrainingSession $trainingSession, array $payload): TrainingSession
    {
        foreach ([
            'school_id',
            'user_id',
            'training_group_id',
            'year',
            'period',
            'session',
            'date',
            'hour',
            'training_ground',
            'material',
            'back_to_calm',
            'players',
            'absences',
            'incidents',
            'feedback',
            'warm_up',
            'coaches',
        ] as $attribute) {
            if (array_key_exists($attribute, $payload)) {
                $trainingSession->{$attribute} = $payload[$attribute];
            }
        }

        return $trainingSession;
    }

    private function ensureSyncedIdentityIsUnchanged(TrainingSession $trainingSession, array $payload): void
    {
        if (! $trainingSession->attendance_synced_at) {
            return;
        }

        if (
            (int) $trainingSession->training_group_id !== (int) $payload['training_group_id']
            || (string) $trainingSession->date !== (string) $payload['date']
        ) {
            throw ValidationException::withMessages([
                'date' => 'El grupo y la fecha no se pueden cambiar después de sincronizar la asistencia.',
            ]);
        }
    }

    private function ensureUniqueGroupDate(array $payload, ?int $exceptId = null): void
    {
        $exists = $this->trainingSession->query()
            ->schoolId()
            ->where('training_group_id', $payload['training_group_id'])
            ->whereDate('date', $payload['date'])
            ->when($exceptId, fn (Builder $query) => $query->where('training_sessions.id', '<>', $exceptId))
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => 'Ya existe una sesión activa para este grupo y fecha.',
            ]);
        }
    }

    private function makeTask(array $payload): array
    {
        if (isset($payload['tasks']) && is_array($payload['tasks'])) {
            return collect($payload['tasks'])
                ->filter(fn (array $task): bool => ! empty($task['task_name']))
                ->map(function (array $task, int $index): array {
                    return [
                        'task_number' => $task['task_number'] ?? ($index + 1),
                        'task_name' => $task['task_name'] ?? null,
                        'general_objective' => $task['general_objective'] ?? null,
                        'specific_goal' => $task['specific_goal'] ?? null,
                        'content_one' => $task['content_one'] ?? null,
                        'content_two' => $task['content_two'] ?? null,
                        'content_three' => $task['content_three'] ?? null,
                        'ts' => $task['ts'] ?? null,
                        'sr' => $task['sr'] ?? null,
                        'tt' => $task['tt'] ?? null,
                        'observations' => $task['observations'] ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        $tasks = [];
        $keys = array_keys($payload['task_number']);

        foreach ($keys as $key) {
            if (is_null($payload["task_name"][$key])) {
                continue;
            }

            $tasks[] = [
                'task_number' => $payload["task_number"][$key],
                'task_name' => $payload["task_name"][$key],
                'general_objective' => $payload["general_objective"][$key],
                'specific_goal' => $payload["specific_goal"][$key],
                'content_one' => $payload["content_one"][$key],
                'content_two' => $payload["content_two"][$key],
                'content_three' => $payload["content_three"][$key],
                'ts' => $payload["ts"][$key],
                'sr' => $payload["sr"][$key],
                'tt' => $payload["tt"][$key],
                'observations' => $payload["observations"][$key],
            ];
        }
        return $tasks;
    }
}
