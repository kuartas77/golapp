<?php

namespace App\Http\Controllers;

use App\Models\CompetitionGroup;
use App\Models\Master;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\PlayerRepository;
use App\Repositories\InscriptionRepository;

class MasterController extends Controller
{

    public function __construct(private PlayerRepository $playerRepository, private InscriptionRepository $inscriptionRepository)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function autoComplete(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        return response()->json(Master::getAutocomplete($request));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function existDocument(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        return $this->responseJson($this->playerRepository->checkDocumentExists($request->input('doc')));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function codeUniqueVerify(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        return $this->responseJson($this->playerRepository->checkUniqueCode($request->input('unique_code')));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listUniqueCode(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $players = $this->playerRepository->getListPlayersNotInscription($request->filled('trashed'));
        return $this->responseJson($players);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUniqueCode(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);

        if ($request->filled('unique')) {
            $response = $this->playerRepository->searchUniqueCode($request->only(['unique_code']));
        } else {
            $response = $this->inscriptionRepository->searchInscriptionCompetition($request->only(['unique_code', 'competition_group_id']));
        }
        return $this->responseJson($response);
    }

    public function tournamentsBySchool(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $response = Tournament::query()->schoolId()->orderBy('id')->get()->map(function($tournament){
            return ['id' => $tournament->id, 'text' => $tournament->name];
        });
        return $this->responseJson($response);
    }

    public function competitionGroupsByTournament(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $response = CompetitionGroup::query()->schoolId()->where('tournament_id', $request->tournament_id)->orderBy('name')->get()->map(function ($group) {
            return ['id' => $group->id, 'text' => $group->full_name_group];
        });
        return $this->responseJson($response);
    }
}
