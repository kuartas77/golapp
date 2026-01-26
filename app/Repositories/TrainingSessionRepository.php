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
        DB::beginTransaction();
        try {

            $trainingSession = new TrainingSession([
                "school_id" => $payload["school_id"],
                "user_id" => $payload["user_id"],
                "training_group_id" => $payload["training_group_id"],
                "year" => $payload["year"],
                "period" => $payload["period"],
                "session" => $payload["session"],
                "date" => $payload["date"],
                "hour" => $payload["hour"],
                "training_ground" => $payload["training_ground"],
                "material" => $payload["material"],
                "back_to_calm" => $payload["back_to_calm"],
                "players" => $payload["players"],
                "absences" => $payload["absences"],
                "incidents" => $payload["incidents"],
                "feedback" => $payload["feedback"],
                "warm_up" => $payload["warm_up"],
            ]);

            $trainingSession->save();

            $keys = array_keys($payload['task_number']);
            $tasks = [];

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
}
