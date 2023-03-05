<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\DayRepository;
use Illuminate\Http\RedirectResponse;
use App\Repositories\PlayerRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\CompetitionGroupRepository;

class DataTableController extends Controller
{
    public function __construct(private InscriptionRepository $inscriptionRepository,
                                private TrainingGroupRepository $trainingGroupRepository,
                                private CompetitionGroupRepository $competitionGroupRepository,
                                private PlayerRepository $playerRepository, 
                                private DayRepository $dayRepository,
                                private SchoolRepository $schoolRepository)
    {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->inscriptionRepository->getInscriptionsEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function disabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function disabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledDays(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->dayRepository->all())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledPlayers(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->playerRepository->getPlayersPeople())->toJson();
    }

    public function schools(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->collection($this->schoolRepository->getAll())
            ->addColumn('logo', '{{$logo}}!')
            ->addColumn('name', '{{$name}}!')
            ->addColumn('agent', '{{$agent}}!')
            ->addColumn('address', '{{$address}}')
            ->addColumn('phone', '{{$phone}}')
            ->addColumn('email', '{{$email}}')
            ->addColumn('is_enable', fn($model) => $model->is_enable ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>')
            ->addColumn('created_at', fn($model) => $model->created_at->format('Y-m-d'))
            ->escapeColumns([])
            ->toJson();
    }

    public function schoolsInfo(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->collection($this->schoolRepository->schoolsInfo())->toJson();
    }
}
