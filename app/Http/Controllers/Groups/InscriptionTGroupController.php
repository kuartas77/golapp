<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\TrainingGroup;
use App\Repositories\TrainingGroupRepository;
use App\Service\SharedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionTGroupController extends Controller
{
    /**
     * @var TrainingGroupRepository
     */
    private $repository;

    public function __construct(TrainingGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $firstGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', getSchool(auth()->user())->id);
        $groups = $this->repository->getListGroupsSchedule()->pluck('full_schedule_group', 'id');
        $groups->prepend($firstGroup->full_schedule_group, $firstGroup->id);

        view()->share('groups', $groups);
        return view('groups.training.admin_group');
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function makeRows(TrainingGroup $trainingGroup): JsonResponse
    {
        return response()->json(['rows' => $this->repository->makeRows($trainingGroup)]);
    }

    /**
     * @param $inscription_id
     * @param Request $request
     * @return JsonResponse
     */
    public function assignGroup($inscription_id, Request $request, SharedService $sharedService): JsonResponse
    {
        if ($request->ajax()) {
            if ($sharedService->assignTrainingGroup($inscription_id, $request))
                return $this->responseJson(true, 200);
            else
                return $this->responseJson(false, 404);
        }
        return $this->responseJson(false, 404);
    }
}
