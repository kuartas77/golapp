<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Groups\CompetitionGroupRequest;
use App\Models\CompetitionGroup;
use App\Repositories\CompetitionGroupRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionGroupController extends Controller
{

    /**
     * @var CompetitionGroupRepository
     */
    private $repository;

    public function __construct(CompetitionGroupRepository $repository)
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
        return view('groups.competition.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompetitionGroupRequest $request)
    {
        $competitionGroup = $this->repository->createOrUpdateTeam(
            $request->only([
                'name', 'year', 'tournament_id',
                'user_id', 'category', 'school_id'
            ])
        );
        if ($competitionGroup->wasRecentlyCreated) {
            alert()->success(env('APP_NAME'), __('messages.training_group_create_success'));
        } else {
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param CompetitionGroup $competitionGroup
     * @return JsonResponse
     */
    public function show(CompetitionGroup $competitionGroup): JsonResponse
    {
        return $this->responseJson($competitionGroup);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetitionGroup $competitionGroup
     * @return JsonResponse
     */
    public function edit(CompetitionGroup $competitionGroup): JsonResponse
    {
        return $this->responseJson($competitionGroup);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(CompetitionGroupRequest $request, CompetitionGroup $competitionGroup)
    {
        $competitionGroup = $this->repository->createOrUpdateTeam(
            $request->only([
                'name', 'year', 'tournament_id',
                'user_id', 'category', 'school_id'
            ]),
            false,
            $competitionGroup
        );

        if (!$competitionGroup->exists)
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        else {
            alert()->success(env('APP_NAME'), __('messages.training_group_create_success'));
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CompetitionGroup $competitionGroup
     */
    public function destroy(CompetitionGroup $competitionGroup)
    {
        abort(404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterGroupYear(Request $request): JsonResponse
    {
        return response()->json($this->repository->getGroupsYear($request->input('year')));
    }

    public function availabilityGroup(CompetitionGroup $competitionGroup): JsonResponse
    {
        $competitionGroup->loadCount('inscriptions');
        return response()->json(['data' => $competitionGroup->inscriptions_count]);
    }
}
