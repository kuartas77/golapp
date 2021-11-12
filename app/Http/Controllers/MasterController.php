<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\PlayerRepository;
use App\Repositories\InscriptionRepository;

class MasterController extends Controller
{

    /**
     * @var PlayerRepository
     */
    private PlayerRepository $playerRepository;
    /**
     * @var InscriptionRepository
     */
    private InscriptionRepository $inscriptionRepository;

    public function __construct(PlayerRepository $playerRepository, InscriptionRepository $inscriptionRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->inscriptionRepository = $inscriptionRepository;
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
        return $this->responseJson($this->playerRepository->checkDocumentExists($request));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function codeUniqueVerify(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        return $this->responseJson($this->playerRepository->checkUniqueCode($request));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listUniqueCode(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);

        $enabled = Player::query()->whereHas('inscriptions', function ($q) {
            $q->where('year', now());
        });

        if ($request->filled('trashed')) {
            $players = Player::query()->whereHas('inscriptions', function ($q) use ($enabled) {
                $q->withTrashed()->whereNotIn('player_id', $enabled->pluck('id'));
            })->pluck('unique_code');
        } else {
            $players = $enabled->pluck('unique_code');
        }
        return $this->responseJson($players);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUniqueCode(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 401);

        if ($request->filled('unique')){
            $response = $this->playerRepository->searchUniqueCode($request->only(['unique_code']));
        }else{
            $response = $this->inscriptionRepository->searchInscriptionCompetition($request->only(['unique_code','competition_group_id']));
        }
        return $this->responseJson($response);
    }
}
