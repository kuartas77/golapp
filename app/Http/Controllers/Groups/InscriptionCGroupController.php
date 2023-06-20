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

            $inscriptions = Inscription::query()->schoolId()->with(['player'])->where('year', now()->year)->get();

            list($rows, $count) = $this->groupRepository->makeInscriptionRows($inscriptions);
            return response()->json([
                'rows' => $rows,
                'count' => $count,
                'groups' => $groupsCompetition
            ]);
        }

        $insWithOutGroup = Inscription::query()->schoolId()->with(['player'])->where('year', now()->year)->get();

        view()->share('groupsCompetition', $this->groupRepository->getGroupsYear());
        view()->share('insWithGroup', collect([]));
        view()->share('insWithOutGroup', $insWithOutGroup);
        view()->share('insWithOutGroupCount', $insWithOutGroup->count());

        return view('groups.competition.admin_group');
    }

    /**
     * @param CompetitionGroup $competitionGroup
     * @return JsonResponse
     */
    public function makeRows(CompetitionGroup $competitionGroup): JsonResponse
    {
        $inscriptions = Inscription::query()->schoolId()->with(['player'])->where('year', now()->year)->get();

        list($rows, $count) = $this->groupRepository->makeRows($competitionGroup);
        list($inscriptionRows, $inscriptioncount) = $this->groupRepository->makeInscriptionRows($inscriptions);

        return response()->json(['rows' => $rows, 'count' => $count, 'inscriptionRows' => $inscriptionRows, 'inscriptioncount' => $inscriptioncount]);
    }

    /**
     * @param $inscription
     * @param Request $request
     * @return JsonResponse
     */
    public function assignGroup($inscription, Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $response = $this->groupRepository->assignInscriptionGroup($inscription, $request->destination_group, filter_var($request->assign, FILTER_VALIDATE_BOOL));
        return response()->json(['response' => $response]);
    }
}
