<?php

namespace App\Http\Controllers\Groups;

use Illuminate\View\View;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Exception;
use Closure;
use App\Repositories\TrainingGroupRepository;
use App\Models\TrainingGroup;
use App\Models\Inscription;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Http\Requests\Groups\TrainingGroupRequest;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

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
        $training_group = $this->repository->createTrainingGroup($request);
        if ($training_group->wasRecentlyCreated) {
            Alert::success(env('APP_NAME'), __('messages.training_group_create_success'));
        } else {
            Alert::error(env('APP_NAME'), __('messages.ins_create_failure'));
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
        abort_if($trainingGroup->id === 1, 401, 'El Grupo Provicional No Se Puede Eliminar o Modificar');

        $trainingGroup = $this->repository->updateTrainingGroup($request, $trainingGroup);
        if ($trainingGroup->exists) {
            Alert::success(env('APP_NAME'), __('messages.training_group_edit_success'));
        } else {
            Alert::error(env('APP_NAME'), __('messages.ins_create_failure'));
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
        $firtsTrainigGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', getSchool(auth()->user())->id)->id;
        abort_if($firtsTrainigGroup == $trainingGroup->id, 401, 'El Grupo Provicional No Se Puede Eliminar o Modificar');

        if ($trainingGroup->delete()) {
            Alert::success(env('APP_NAME'), __('messages.ins_delete_success'));
        } else {
            Alert::error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
        return redirect(route('training_groups.index'));
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

    public function groupList(Request $request): JsonResponse
    {
        $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
        if (isSchool() || isAdmin()) {
            $training_groups = $this->repository->getListGroupsSchedule(deleted: false, filter: $filter);
        } elseif (isInstructor()) {
            $training_groups = $this->repository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter);
        }

        $groups = $training_groups->map(fn ($group) => ['id' => $group->id, 'text' => $group->full_schedule_group]);
        $queryCategories = Inscription::where('year', now()->year)->distinct()->schoolId()->get();
        $categories = $queryCategories->map(fn ($category) => ['id' => $category->category, 'text' => $category->category]);

        return response()->json([
            'data' => [
                'groups' => $groups,
                'categories' => $categories
            ]
        ]);
    }

    public function getClassDays(Request $request)
    {
        $group = TrainingGroup::findOrFail($request->training_group_id);

        $classDays = TrainingGroupRepository::getClassDays($group, $request->month);

        return response()->json($classDays);
    }
}
