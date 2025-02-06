<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Groups\GroupCollection;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Http\Resources\API\Groups\TrainingGroupResource;

class GroupsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ability:group-index')->only('index');
        $this->middleware('ability:group-show')->only('show');
    }

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
