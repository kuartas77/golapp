<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\TrainingGroup;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

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
        view()->share('groups', $this->repository->getListGroupsSchedule());
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
    public function assignGroup($inscription_id, Request $request): JsonResponse
    {
        if ($request->ajax()) {
            if ($this->repository->assignTrainingGroup($inscription_id, $request))
                return $this->responseJson(true, 200);
            else
                return $this->responseJson(false, 404);
        }
        return $this->responseJson(false, 404);
    }
}
