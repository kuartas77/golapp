<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingSessionRepository
{
    public function __construct(protected TrainingSession $trainingSession)
    {
    }

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

    public function trainingGroupIsAccessible(int $trainingGroupId, int $year): bool
    {
        return TrainingGroup::query()
            ->schoolId()
            ->whereKey($trainingGroupId)
            ->when(isInstructor(), fn (Builder $query) => $query->byInstructor($year))
            ->exists();
    }

    public function store(array $payload): ?TrainingSession
    {
        $trainingSession = null;
        try {

            $trainingSession = $this->makeTraininSession(new TrainingSession(), $payload);

            DB::beginTransaction();

            $trainingSession->save();

            $tasks = $this->makeTask($payload);

            if ($tasks !== []) {
                $trainingSession->tasks()->createMany($tasks);
            }

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $trainingSession = null;
        }

        return $trainingSession;
    }

    public function update(TrainingSession $trainingSession, array $payload): bool
    {
        $success = false;
        try {

            $trainingSession = $this->makeTraininSession($trainingSession, $payload);

            DB::beginTransaction();

            $trainingSession->save();

            $tasks = $this->makeTask($payload);

            if ($tasks !== []) {
                $trainingSession->tasks()->forceDelete();
                $trainingSession->tasks()->createMany($tasks);
            }

            DB::commit();
            $success = true;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $success = false;
        }

        return $success;
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

    private function makeTask(array $payload): array
    {
        if (isset($payload['tasks']) && is_array($payload['tasks'])) {
            return collect($payload['tasks'])
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
