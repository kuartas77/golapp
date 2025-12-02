<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRequest;
use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

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
            return datatables()->collection(
                $this->repository->getDatatable(request('year_', now()->year))
            )->toJson();
        }
        return view('competition.match.index');
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        view()->share('information', $this->repository->getInformationToMatch());
        return view('competition.match.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(CompetitionRequest $request): RedirectResponse
    {
        $match = $this->repository->createMatchSkill(
            $request->only([
                'tournament_id', 'competition_group_id', 'date', 'hour', 'num_match',
                'place', 'rival_name', 'final_score', 'general_concept', 'school_id'
            ]),
            $request->only([
                'inscriptions_id', 'assistance', 'titular', 'played_approx',
                'position', 'goals', 'yellow_cards', 'red_cards','goal_assists','goal_saves',
                'qualification', 'observation'
            ])
        );
        if ($match->wasRecentlyCreated) {
            alert()->success(env('APP_NAME'), __("messages.match_stored"));
            return redirect()->to(route('matches.index'));
        } else {
            alert()->error(env('APP_NAME'), __("messages.match_fail"));
            return back()->withInput($request->input());
        }
    }

    /**
     * @param Game $match
     * @return Application|Factory|View
     */
    public function edit(Game $match)
    {
        view()->share('information', $this->repository->getInformationToMatch($match));
        return view('competition.match.edit');
    }

    /**
     * @param Request $request
     * @param Game $match
     * @return Application|RedirectResponse|Redirector
     */
    public function update(CompetitionRequest $request, Game $match)
    {
        $matchData = $request->only([
            'tournament_id', 'competition_group_id', 'date', 'hour', 'num_match',
            'place', 'rival_name', 'final_score', 'general_concept', 'school_id'
        ]);
        $skillsData = $request->only([
            'inscriptions_id', 'assistance', 'titular', 'played_approx',
            'position', 'goals', 'yellow_cards', 'red_cards','goal_assists','goal_saves',
            'qualification', 'observation', 'ids'
        ]);

        if ($this->repository->updateMatchSkill($matchData, $skillsData, $match)) {
            alert()->success(env('APP_NAME'), __("messages.match_updated"));
            return redirect(route('matches.index'));
        }
        alert()->error(env('APP_NAME'), __("messages.match_fail"));
        return redirect(route('matches.index'));
    }

    /**
     * @param Game $match
     * @return Application|RedirectResponse|Redirector
     */
    public function destroy(Game $match)
    {
        if ($match->forceDelete()) {
            alert()->success(env('APP_NAME'), __("messages.match_deleted"));
            return redirect(route('matches.index'));
        }
        alert()->error(env('APP_NAME'), __("messages.match_fail"));
        return redirect(route('matches.index'));
    }
}
