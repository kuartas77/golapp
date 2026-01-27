<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Traits\ErrorTrait;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\DB;

class TrainingSessionRepository
{
    use ErrorTrait;

    public function __construct(protected TrainingSession $trainingSession)
    {
    }

    public function list()
    {
        return $this->trainingSession->query()->whereHas('training_group')->with(['school', 'user', 'training_group'])->withCount(['tasks'])->schoolId()->get();
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
            $this->logError('TrainingSessionRepository@store', $throwable);
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
            $this->logError('TrainingSessionRepository@update', $throwable);
            $success = false;
        }

        return $success;
    }

    private function makeTraininSession(TrainingSession $trainingSession, array $payload): TrainingSession
    {
        $trainingSession->school_id = $payload["school_id"];
        $trainingSession->user_id = $payload["user_id"];
        $trainingSession->training_group_id = $payload["training_group_id"];
        $trainingSession->year = $payload["year"];
        $trainingSession->period = $payload["period"];
        $trainingSession->session = $payload["session"];
        $trainingSession->date = $payload["date"];
        $trainingSession->hour = $payload["hour"];
        $trainingSession->training_ground = $payload["training_ground"];
        $trainingSession->material = $payload["material"];
        $trainingSession->back_to_calm = $payload["back_to_calm"];
        $trainingSession->players = $payload["players"];
        $trainingSession->absences = $payload["absences"];
        $trainingSession->incidents = $payload["incidents"];
        $trainingSession->feedback = $payload["feedback"];
        $trainingSession->warm_up = $payload["warm_up"];

        return $trainingSession;
    }

    private function makeTask(array $payload): array
    {
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
