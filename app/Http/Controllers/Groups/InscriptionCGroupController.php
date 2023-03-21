<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Repositories\CompetitionGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionCGroupController extends Controller
{
    /**
     * @var CompetitionGroupRepository
     */
    private $groupRepository;

    public function __construct(CompetitionGroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $group = CompetitionGroup::query()->schoolId()->select('id', 'year')
                ->find($request->input('competition_group_id'));

            $groupsCompetition = $this->groupRepository->getGroupsYear($group->year);

            $inscriptions = Inscription::query()->schoolId()->with('player')->whereCategory($group->year)
                ->whereCompetition()->where('year', now()->year)->get();

            return response()->json([
                'rows' => CompetitionGroup::query()->schoolId()->rows($inscriptions),
                'groups' => $groupsCompetition
            ]);
        }

        $insWithOutGroup = Inscription::query()->schoolId()->with('player')->whereCompetition()->where('year', now()->year)->get();

        view()->share('groupsCompetition', $this->groupRepository->getGroupsYear());
        view()->share('insWithGroup', collect([]));
        view()->share('insWithOutGroup', $insWithOutGroup);

        return view('groups.competition.admin_group');
    }

    /**
     * @param CompetitionGroup $competitionGroup
     * @return JsonResponse
     */
    public function makeRows(CompetitionGroup $competitionGroup): JsonResponse
    {
        list($rows, $count) = $this->groupRepository->makeRows($competitionGroup);
        return response()->json(['rows' => $rows, 'count' => $count]);
    }

    /**
     * @param $inscription
     * @param Request $request
     * @return JsonResponse
     */
    public function assignGroup($inscription, Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $destination = $request->input('destination_group');
            $inscription = Inscription::findOrFail($inscription);
            $updated = $inscription->update(['competition_group_id' => $destination]);

            if ($updated === true && isset($destination)) {
                $response = 1;
            } else if ($updated === true && !isset($destination)) {
                $response = 2;
            } else {
                $response = 3;
            }
            return response()->json(['response' => $response]);
        }
        return response()->json([], 404);
    }
}
