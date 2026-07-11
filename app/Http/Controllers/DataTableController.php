<?php

namespace App\Http\Controllers;

use App\Models\MethodologyRecord;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\UserRepository;
use App\Service\DataTables\InventoryDataTableService;
use App\Service\DataTables\CompetitionDataTableService;
use App\Service\DataTables\MethodologyDataTableService;
use App\Service\DataTables\PlayerEvaluationDataTableService;
use App\Service\DataTables\TrainingSessionDataTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableController extends Controller
{
    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private TrainingGroupRepository $trainingGroupRepository,
        private CompetitionGroupRepository $competitionGroupRepository,
        private PlayerRepository $playerRepository,
        private ScheduleRepository $scheduleRepository,
        private SchoolRepository $schoolRepository,
        private UserRepository $userRepository,
        private InventoryDataTableService $inventoryDataTables,
        private CompetitionDataTableService $competitionDataTables,
        private TrainingSessionDataTableService $trainingSessionDataTables,
        private MethodologyDataTableService $methodologyDataTables,
        private PlayerEvaluationDataTableService $playerEvaluationDataTables,
    ) {}

    public function enabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsEnabled())
            ->filterColumn('training_group_id', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('inscriptions.training_group_id', $keyword)
                        ->orWhere('inscriptions.complementary_group_id', $keyword);
                });
            })
            ->filterColumn('start_date', fn ($query, $keyword) => $query->whereDate('start_date', $keyword))
            ->filterColumn('category', fn ($query, $keyword) => $query->where('category', $keyword))
            ->filterColumn('inscriptions.pre_inscription', fn ($query, $keyword) => $query->where('inscriptions.pre_inscription', (bool) $keyword))
            ->filterColumn('player.last_names', function ($query, $keyword) {
                $sql = "CONCAT(players.names, ' ', players.last_names) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->toJson();
    }

    public function disabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsDisabled())->toJson();
    }

    /**
     * @return JsonResponse|void
     */
    public function enabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()
            ->of($this->trainingGroupRepository->listGroupEnabled())
            ->editColumn('members_count', fn ($group) => $group->is_complementary
                ? $group->complementary_inscriptions_count
                : $group->members_count)
            ->toJson();
    }

    /**
     * @return JsonResponse|void
     */
    public function disabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupDisabled())->toJson();
    }

    public function enabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->competitionGroupRepository->listGroupEnabled())->toJson();
    }

    public function disabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @return JsonResponse|void
     */
    public function enabledSchedules(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->scheduleRepository->all())->toJson();
    }

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
        abort_unless($request->ajax() && (isAdmin() || isSchool() || isInstructor()), 403);

        return $this->competitionDataTables->matches();

    }

    public function trainingSessions(Request $request)
    {
        return $this->trainingSessionDataTables->sessions();
    }

    public function sessionPlannings(Request $request)
    {
        return $this->trainingSessionDataTables->plannings();
    }

    public function methodologyRecords(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $type = $request->query('type');

        abort_if($type && ! in_array($type, MethodologyRecord::TYPES, true), 422);

        return $this->methodologyDataTables->records($type);
    }

    public function playerEvaluations(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return $this->playerEvaluationDataTables->evaluations(
            (int) getSchool(auth()->user())->id,
            $request->only(['player_id', 'training_group_id', 'evaluation_period_id', 'status', 'evaluation_type', 'search']),
        );
    }

    public function inventoryProducts(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);
        return $this->inventoryDataTables->products((int) getSchool(auth()->user())->id);
    }

    public function inventoryMovements(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return $this->inventoryDataTables->movements((int) getSchool(auth()->user())->id);
    }

}
