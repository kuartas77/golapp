<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Groups\TrainingGroupRequest;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Repositories\TrainingGroupRepository;
use App\Service\Groups\TrainingGroupYearFilter;
use Closure;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
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
     * @return JsonResponse
     */
    public function store(TrainingGroupRequest $request): JsonResponse
    {
        $response = [];
        $trainingGroup = $this->repository->createTrainingGroup($request);
        if ($trainingGroup->wasRecentlyCreated) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
        }

        return response()->json($response);
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
     * @return JsonResponse
     */
    public function update(TrainingGroupRequest $request, TrainingGroup $trainingGroup): JsonResponse
    {
        $response = [];
        $trainingGroup = $this->repository->updateTrainingGroup($request, $trainingGroup);
        if ($trainingGroup->exists) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
        }

        return response()->json($response);
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $school = getSchool($user);
        $firtsTrainigGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', $school->id)->id;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $filter = Closure::fromCallable([TrainingGroupYearFilter::class, 'activeForCurrentYear']);
        $training_groups = collect();
        if (isSchool() || isAdmin()) {
            $training_groups = $this->repository->getListGroupsSchedule(deleted: false, filter: $filter);
        } elseif (isInstructor()) {
            $training_groups = $this->repository->getListGroupsSchedule(deleted: false, user_id: $user->id, filter: $filter);
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
        $group = TrainingGroup::query()
            ->schoolId()
            ->when(isInstructor(), fn($query) => $query->byInstructor())
            ->findOrFail($request->training_group_id);

        $classDays = TrainingGroupRepository::getClassDays($group, $request->month);

        return response()->json($classDays);
    }
}
