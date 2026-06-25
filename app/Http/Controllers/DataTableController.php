<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\MethodologyRecord;
use App\Repositories\PlayerRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\MethodologyRecordRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\GameRepository;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\InventoryMovement;
use App\Models\InventoryProduct;

class DataTableController extends Controller
{
    private const EVALUATION_STATUS_LABELS = [
        'draft' => 'Borrador',
        'completed' => 'Completada',
        'closed' => 'Cerrada',
    ];

    private const EVALUATION_TYPE_LABELS = [
        'initial' => 'Inicial',
        'periodic' => 'Periódica',
        'final' => 'Final',
        'special' => 'Especial',
    ];

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
        private MethodologyRecordRepository $methodologyRecordRepository,
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
        abort_unless($request->ajax() && (isAdmin() || isSchool() || isInstructor()), 403);

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

    public function methodologyRecords(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $type = $request->query('type');

        abort_if($type && ! in_array($type, MethodologyRecord::TYPES, true), 422);

        return datatables()->eloquent($this->methodologyRecordRepository->datatableQuery($type))
            ->filterColumn('title', fn ($query, $keyword) => $query->where('methodology_records.title', 'like', "%{$keyword}%"))
            ->filterColumn('creator_name', fn ($query, $keyword) => $query->where('users.name', 'like', "%{$keyword}%"))
            ->filterColumn('training_group_name', fn ($query, $keyword) => $query->where('training_groups.name', 'like', "%{$keyword}%"))
            ->orderColumn('title', 'methodology_records.title $1')
            ->orderColumn('creator_name', 'users.name $1')
            ->orderColumn('training_group_name', 'training_groups.name $1')
            ->orderColumn('created_at', 'methodology_records.created_at $1')
            ->addColumn('creator_name', fn (MethodologyRecord $record) => $record->user?->name ?? '')
            ->addColumn('training_group_name', fn (MethodologyRecord $record) => $record->trainingGroup?->name ?? '')
            ->editColumn('created_at', fn (MethodologyRecord $record) => $record->created_at?->format('Y-m-d'))
            ->addColumn('export_pdf_url', fn (MethodologyRecord $record) => route('methodology.records.pdf', ['id' => $record->id]))
            ->toJson();
    }

    public function playerEvaluations(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $schoolId = (int) getSchool(auth()->user())->id;

        $query = PlayerEvaluation::query()
            ->select('player_evaluations.*')
            ->join('inscriptions', 'inscriptions.id', '=', 'player_evaluations.inscription_id')
            ->leftJoin('players', 'players.id', '=', 'inscriptions.player_id')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'inscriptions.training_group_id')
            ->leftJoin('evaluation_periods', 'evaluation_periods.id', '=', 'player_evaluations.evaluation_period_id')
            ->leftJoin('evaluation_templates', 'evaluation_templates.id', '=', 'player_evaluations.evaluation_template_id')
            ->where('player_evaluations.school_id', $schoolId)
            ->when(isInstructor(), function ($query) {
                $query->whereHas('inscription.trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor());
            })
            ->with([
                'inscription.player',
                'inscription.trainingGroup',
                'period',
                'template.trainingGroup',
            ]);

        return datatables()->eloquent($query)
            ->filter(function ($query) use ($request) {
                $playerId = $request->input('player_id');
                $trainingGroupId = $request->input('training_group_id');
                $periodId = $request->input('evaluation_period_id');
                $status = $request->input('status');
                $type = $request->input('evaluation_type');
                $keyword = trim((string) data_get($request->input('search'), 'value', ''));

                $query
                    ->when($playerId, fn ($subQuery) => $subQuery->where('inscriptions.player_id', $playerId))
                    ->when($trainingGroupId, fn ($subQuery) => $subQuery->where('inscriptions.training_group_id', $trainingGroupId))
                    ->when($periodId, fn ($subQuery) => $subQuery->where('player_evaluations.evaluation_period_id', $periodId))
                    ->when($status, fn ($subQuery) => $subQuery->where('player_evaluations.status', $status))
                    ->when($type, fn ($subQuery) => $subQuery->where('player_evaluations.evaluation_type', $type));

                if ($keyword === '') {
                    return;
                }

                $like = "%{$keyword}%";
                $statusMatches = $this->matchingEvaluationKeys(self::EVALUATION_STATUS_LABELS, $keyword);
                $typeMatches = $this->matchingEvaluationKeys(self::EVALUATION_TYPE_LABELS, $keyword);

                $query->where(function ($searchQuery) use ($like, $statusMatches, $typeMatches) {
                    $searchQuery
                        ->where('player_evaluations.id', 'like', $like)
                        ->orWhere('player_evaluations.status', 'like', $like)
                        ->orWhere('player_evaluations.evaluation_type', 'like', $like)
                        ->orWhere('player_evaluations.overall_score', 'like', $like)
                        ->orWhere('players.unique_code', 'like', $like)
                        ->orWhere('players.names', 'like', $like)
                        ->orWhere('players.last_names', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(players.names, ''), ' ', COALESCE(players.last_names, '')) LIKE ?", [$like])
                        ->orWhere('training_groups.name', 'like', $like)
                        ->orWhere('evaluation_periods.name', 'like', $like)
                        ->orWhere('evaluation_templates.name', 'like', $like);

                    if (! empty($statusMatches)) {
                        $searchQuery->orWhereIn('player_evaluations.status', $statusMatches);
                    }

                    if (! empty($typeMatches)) {
                        $searchQuery->orWhereIn('player_evaluations.evaluation_type', $typeMatches);
                    }
                });
            })
            ->orderColumn('player_name', function ($query, $order) {
                $query->orderBy('players.last_names', $order)
                    ->orderBy('players.names', $order);
            })
            ->orderColumn('player_code', 'players.unique_code $1')
            ->orderColumn('training_group_name', 'training_groups.name $1')
            ->orderColumn('period_name', 'evaluation_periods.name $1')
            ->orderColumn('template_name', 'evaluation_templates.name $1')
            ->orderColumn('evaluation_type_label', 'player_evaluations.evaluation_type $1')
            ->orderColumn('status_label', 'player_evaluations.status $1')
            ->editColumn('evaluated_at', fn (PlayerEvaluation $evaluation) => $evaluation->evaluated_at?->toISOString())
            ->addColumn('player_id', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player_id)
            ->addColumn('player_name', fn (PlayerEvaluation $evaluation) => $this->datatablePlayerName($evaluation))
            ->addColumn('player_code', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player?->unique_code ?? '')
            ->addColumn('player_photo_url', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player?->photo_url ?? '/img/user.webp')
            ->addColumn('training_group_id', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->training_group_id)
            ->addColumn('training_group_name', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->trainingGroup?->name ?? '')
            ->addColumn('period_name', fn (PlayerEvaluation $evaluation) => $evaluation->period?->name ?? '')
            ->addColumn('template_name', fn (PlayerEvaluation $evaluation) => $evaluation->template?->name ?? '')
            ->addColumn('evaluation_type_label', fn (PlayerEvaluation $evaluation) => self::EVALUATION_TYPE_LABELS[$evaluation->evaluation_type] ?? $evaluation->evaluation_type)
            ->addColumn('status_label', fn (PlayerEvaluation $evaluation) => self::EVALUATION_STATUS_LABELS[$evaluation->status] ?? $evaluation->status)
            ->addColumn('is_closed', fn (PlayerEvaluation $evaluation) => (bool) $evaluation->is_closed)
            ->addColumn('urls', fn (PlayerEvaluation $evaluation) => [
                'show' => url("/player-evaluations/{$evaluation->id}"),
                'edit' => url("/player-evaluations/{$evaluation->id}/edit"),
                'pdf' => route('player-evaluations.pdf', $evaluation->id),
            ])
            ->toJson();
    }

    public function inventoryProducts(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $schoolId = (int) getSchool(auth()->user())->id;

        $query = InventoryProduct::query()
            ->where('school_id', $schoolId);

        return datatables()->eloquent($query)
            ->filterColumn('is_active', fn ($query, $keyword) => $query->where('is_active', $keyword))
            ->filterColumn('is_low_stock', function ($query, $keyword) {
                if ($keyword === '1') {
                    $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
                }

                if ($keyword === '0') {
                    $query->whereColumn('stock_quantity', '>', 'minimum_stock');
                }
            })
            ->filterColumn('category', fn ($query, $keyword) => $query->where('category', 'like', "%{$keyword}%"))
            ->orderColumn('is_low_stock', 'stock_quantity $1')
            ->addColumn('is_low_stock', fn (InventoryProduct $product) => $product->is_low_stock)
            ->toJson();
    }

    public function inventoryMovements(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $schoolId = (int) getSchool(auth()->user())->id;

        $query = InventoryMovement::query()
            ->select('inventory_movements.*')
            ->join('inventory_products', 'inventory_products.id', '=', 'inventory_movements.inventory_product_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_movements.user_id')
            ->where('inventory_movements.school_id', $schoolId)
            ->with(['product', 'user']);

        return datatables()->eloquent($query)
            ->filterColumn('movement_date', fn ($query, $keyword) => $query->whereDate('movement_date', $keyword))
            ->filterColumn('type', fn ($query, $keyword) => $query->where('inventory_movements.type', $keyword))
            ->filterColumn('product_name', fn ($query, $keyword) => $query->where('inventory_products.name', 'like', "%{$keyword}%"))
            ->filterColumn('user_name', fn ($query, $keyword) => $query->where('users.name', 'like', "%{$keyword}%"))
            ->orderColumn('product_name', 'inventory_products.name $1')
            ->orderColumn('user_name', 'users.name $1')
            ->addColumn('product_name', fn (InventoryMovement $movement) => $movement->product?->name ?? '')
            ->addColumn('product_sku', fn (InventoryMovement $movement) => $movement->product?->sku ?? '')
            ->addColumn('user_name', fn (InventoryMovement $movement) => $movement->user?->name ?? '')
            ->addColumn('profit_margin', fn (InventoryMovement $movement) => $movement->profit_margin)
            ->toJson();
    }

    private function datatablePlayerName(PlayerEvaluation $evaluation): string
    {
        $player = $evaluation->inscription?->player;

        if (! $player) {
            return '';
        }

        return $player->full_names
            ?? $player->full_name
            ?? $player->name
            ?? ('Jugador #' . $player->id);
    }

    private function matchingEvaluationKeys(array $labels, string $keyword): array
    {
        $normalizedKeyword = Str::of($keyword)->ascii()->lower()->toString();

        return collect($labels)
            ->filter(function (string $label, string $value) use ($normalizedKeyword) {
                return Str::of($label)->ascii()->lower()->contains($normalizedKeyword)
                    || Str::of($value)->ascii()->lower()->contains($normalizedKeyword);
            })
            ->keys()
            ->values()
            ->all();
    }
}
