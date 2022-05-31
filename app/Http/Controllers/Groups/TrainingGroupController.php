<?php

namespace App\Http\Controllers\Groups;

use Exception;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\Foundation\Application;
use App\Http\Requests\Groups\TrainingGroupRequest;

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
    public function index(): Factory|View|Application
    {
        return view('groups.training.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TrainingGroupRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(TrainingGroupRequest $request): Redirector|RedirectResponse|Application
    {
        $training_group = $this->repository->setTrainingGroup($request, true);
        if ($training_group->wasRecentlyCreated) {
            alert()->success(env('APP_NAME'), __('messages.training_group_create_success'));            
        } else{
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }

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
    public function update(TrainingGroupRequest $request, TrainingGroup $trainingGroup)
    {
        abort_if($trainingGroup->id === 1, 401 , 'El Grupo Provicional No Se Puede Eliminar o Modificar');

        $trainingGroup = $this->repository->setTrainingGroup($request, false, $trainingGroup);
        if ($trainingGroup->exists){
            alert()->success(env('APP_NAME'), __('messages.training_group_edit_success'));
        } else{
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }

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
        abort_if($trainingGroup->id === 1, 401 , 'El Grupo Provicional No Se Puede Eliminar o Modificar');

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
