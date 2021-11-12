<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\TrainingGroup;
use App\Repositories\TrainingGroupRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class TrainingGroupController extends Controller
{

    /**
     * @var TrainingGroupRepository
     */
    private $repository;

    public function __construct(TrainingGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('groups.training.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response|void
     */
    public function create(): Response
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(Request $request)
    {
        $training_group = $this->repository->setTrainingGroup($request, true);
        if (is_null($training_group))
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        else
            alert()->success(env('APP_NAME'), __('messages.training_group_create_success'));

        return redirect(route('training_groups.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function show(TrainingGroup $trainingGroup): JsonResponse
    {
        $trainingGroup = $this->repository->getTrainingGroup($trainingGroup);
        return $this->responseJson($trainingGroup);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function edit(TrainingGroup $trainingGroup): JsonResponse
    {
        return $this->responseJson($this->repository->getTrainingGroup($trainingGroup));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param TrainingGroup $trainingGroup
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, TrainingGroup $trainingGroup)
    {
        $trainingGroup = $this->repository->setTrainingGroup($request, false, $trainingGroup);
        if (is_null($trainingGroup))
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        else
            alert()->success(env('APP_NAME'), __('messages.training_group_edit_success'));

        return redirect(route('training_groups.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TrainingGroup $trainingGroup
     * @return void
     * @throws Exception
     */
    public function destroy(TrainingGroup $trainingGroup)
    {
        abort_unless(isAdmin(), 401);
        abort_if($trainingGroup->id === 1, 401);
        if ($trainingGroup->delete()) {
            alert()->success(env('APP_NAME'), __('messages.ins_delete_success'));
        } else {
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }

    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function availabilityGroup(TrainingGroup $trainingGroup): JsonResponse
    {
        $trainingGroup->loadCount('inscriptions');
        return response()->json(['data' => $trainingGroup->inscriptions_count]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterGroupYear(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        return response()->json($this->repository->getGroupsYear($request->input('year')));
    }


}
