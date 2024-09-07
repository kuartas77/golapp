<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Resources\API\Groups\GroupCollection;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Http\Resources\API\Groups\TrainingGroupResource;

class GroupsController
{
    public function index(TrainingGroupsService $trainingGroupsService): GroupCollection
    {
        return new GroupCollection($trainingGroupsService->getGroups());
    }

    public function show($id, TrainingGroupsService $trainingGroupsService): TrainingGroupResource
    {
        $group = $trainingGroupsService->getGroup((int)$id);
        return new TrainingGroupResource($group);
    }
}
