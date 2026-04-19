<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\PlayerRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\GameRepository;

class DataTableController extends Controller
{
    public function __construct(
        private InscriptionRepository      $inscriptionRepository,
        private TrainingGroupRepository    $trainingGroupRepository,
        private CompetitionGroupRepository $competitionGroupRepository,
        private PlayerRepository           $playerRepository,
        private ScheduleRepository         $scheduleRepository,
        private SchoolRepository           $schoolRepository,
        private TrainingSessionRepository  $trainingSessionRepository,
        private UserRepository             $userRepository,
        private GameRepository             $gameRepository,
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsEnabled())
        ->filterColumn('training_group_id', fn ($query, $keyword) => $query->where('training_group_id', $keyword))
        ->filterColumn('start_date', fn ($query, $keyword) => $query->whereDate('start_date', $keyword))
        ->filterColumn('category', fn ($query, $keyword) => $query->where('category', $keyword))
        ->filterColumn('player.last_names', function($query, $keyword) {
            $sql = "CONCAT(players.names, ' ', players.last_names) like ?";
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function disabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->trainingGroupRepository->listGroupEnabled())->toJson();
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

        return datatables()->of($this->competitionGroupRepository->listGroupEnabled())->toJson();
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
    public function enabledSchedules(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->scheduleRepository->all())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledPlayers(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->playerRepository->getPlayersPeople())
            ->filterColumn('full_names', function ($query, $keyword) {
                $sql = "CONCAT(players.names, ' ', players.last_names) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->orderColumn('full_names', function ($query, $order) {
                $query->orderBy('players.last_names', $order)
                    ->orderBy('players.names', $order);
            })
            ->toJson();
    }

    public function enabledUsers(Request $request)
    {
        abort_unless($request->ajax(), 403);
        return datatables()->of($this->userRepository->getAll())->toJson();
    }

    public function schools(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->of($this->schoolRepository->getAll())->toJson();
    }

    public function schoolsInfo(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->of($this->schoolRepository->schoolsInfo())->toJson();
    }

    public function matches(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->of($this->gameRepository->getDatatable())->toJson();

    }

    public function trainingSessions(Request $request)
    {
        return datatables()->eloquent($this->trainingSessionRepository->datatableQuery())
            ->filterColumn('creator_name', function ($query, $keyword) {
                $query->where('users.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('training_groups.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('creator_name', 'users.name $1')
            ->orderColumn('training_group_name', 'training_groups.name $1')
            ->addColumn('creator_name', fn ($model) => $model->user?->name ?? '')
            ->addColumn('training_group_name', fn ($model) => $model->training_group?->full_group ?? '')
            ->editColumn('date', fn ($model) => Carbon::parse($model->date)->format('Y-m-d'))
            ->editColumn('created_at', fn ($model) => $model->created_at?->format('Y-m-d'))
            ->addColumn('export_pdf_url', fn ($model) => route('export.training_sessions.pdf', [$model->id]))
            ->toJson();
    }
}
