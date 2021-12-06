<?php

namespace App\Http\Controllers;

use App\Repositories\CompetitionGroupRepository;
use App\Repositories\DayRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DataTableController extends Controller
{
    public function __construct(private InscriptionRepository $inscriptionRepository,
                                private TrainingGroupRepository $trainingGroupRepository,
                                private CompetitionGroupRepository $competitionGroupRepository,
                                private PlayerRepository $playerRepository, 
                                private DayRepository $dayRepository)
    {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledInscriptions(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->inscriptionRepository->getInscriptionsEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledTrainingGroups(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function disabledTrainingGroups(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function disabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledDays(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->dayRepository->all())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledPlayers(Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 403);

        return datatables()->collection($this->playerRepository->getPlayersPeople())->toJson();
    }
}
