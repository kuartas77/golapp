<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Resources\API\Groups\GroupCollection;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Http\Resources\API\Groups\TrainingGroupResource;

class GroupsController
{
    public function getTrainingGroups(TrainingGroupsService $trainingGroupsService)
    {
        return new GroupCollection($trainingGroupsService->getGroups());
    }

    public function getTrainingGroup($id, TrainingGroupsService $trainingGroupsService)
    {
        $group = $trainingGroupsService->getGroup((int)$id);
        return new TrainingGroupResource($group);
    }
}
