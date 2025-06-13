<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StatisticsRequest;
use App\Http\Resources\API\Groups\GroupCollection;
use App\Http\Resources\API\Groups\GroupStatisticsCollection;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Http\Resources\API\Groups\TrainingGroupResource;
use Illuminate\Support\Facades\Cache;

class GroupsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ability:group-index')->only(['index', 'statistics']);
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

    public function statistics(StatisticsRequest $request, TrainingGroupsService $trainingGroupsService): GroupStatisticsCollection
    {
        return Cache::remember(
            "statistics.groups.user.".auth()->user()->id,
            now()->addMinutes(5),
            fn() => new GroupStatisticsCollection($trainingGroupsService->getGroups())
        );
    }
}
