<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRequest;
use App\Http\Requests\CompetitionStoreRequest;
use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private GameRepository $repository;

    public function __construct(GameRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return Application|Factory|JsonResponse|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->of(
                $this->repository->getDatatable(request('year_', now()->year))
            )
            ->filterColumn('tournament_id', fn ($query, $keyword) => $query->where('tournament_id', $keyword))
            ->filterColumn('competition_group_id', fn ($query, $keyword) => $query->where('competition_group_id', $keyword))
            ->toJson();
        }
        return view('competition.match.index');
    }

    public function store(CompetitionStoreRequest $request): JsonResponse
    {
        abort_if(!instructorCanAccessCompetitionGroup($request->input('competition_group_id')), 404);

        $response = [];
        $response['success'] = $this->repository->createMatchSkill($request);
        return response()->json($response);
    }

    public function show($id): JsonResponse
    {
        $match = $id && (int) $id > 0 ? $this->accessibleMatch((int) $id) : null;
        $information = $this->repository->getInformationToMatch($match);
        return response()->json($information);
    }

    public function update(CompetitionRequest $request, Game $match): JsonResponse
    {
        abort_if(!instructorCanAccessCompetitionGroup($request->input('competition_group_id')), 404);
        $match = $this->accessibleMatch($match->id);

        $response = [];
        $response['success'] = $this->repository->updateMatchSkill($request, $match);
        return response()->json($response);
    }

    public function destroy(Game $match): JsonResponse
    {
        $match = $this->accessibleMatch($match->id);

        $response = [];
        $response['success'] = $match->forceDelete() ?? false;
        return response()->json($response);
    }

    private function accessibleMatch(int $id): Game
    {
        return Game::query()
            ->schoolId()
            ->when(isInstructor(), fn ($query) => $query->whereHas('competitionGroup', fn ($groupQuery) => $groupQuery->byInstructor()))
            ->findOrFail($id);
    }
}
