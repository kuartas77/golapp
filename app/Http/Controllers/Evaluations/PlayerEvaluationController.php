<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\CreatePlayerEvaluationsRequest;
use App\Http\Requests\Evaluations\StorePlayerEvaluationRequest;
use App\Http\Requests\Evaluations\UpdatePlayerEvaluationRequest;
use App\Http\Resources\API\Evaluations\PlayerEvaluationResource;
use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Service\Evaluations\GuardianEvaluationPdfService;
use App\Service\Evaluations\PlayerEvaluationCrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerEvaluationController extends Controller
{
    private const STATUS_OPTIONS = [
        ['value' => 'draft', 'label' => 'Borrador'],
        ['value' => 'completed', 'label' => 'Completada'],
        ['value' => 'closed', 'label' => 'Cerrada'],
    ];

    private const EVALUATION_TYPE_OPTIONS = [
        ['value' => 'initial', 'label' => 'Inicial'],
        ['value' => 'periodic', 'label' => 'Periódica'],
        ['value' => 'final', 'label' => 'Final'],
        ['value' => 'special', 'label' => 'Especial'],
    ];

    public function __construct(
        private PlayerEvaluationCrudService $crudService,
        private GuardianEvaluationPdfService $guardianEvaluationPdfService
    ) {}

    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', 15)));
        $search = trim((string) $request->input('search'));

        $evaluations = PlayerEvaluation::query()
            ->where('school_id', $this->currentSchoolId())
            ->when(isInstructor(), function ($query) {
                $query->whereHas('inscription.trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor());
            })
            ->with([
                'inscription.player',
                'inscription.trainingGroup',
                'period',
                'template.trainingGroup',
                'evaluator',
            ])
            ->when($request->player_id, function ($query) use ($request) {
                $query->whereHas('inscription', function ($subQuery) use ($request) {
                    $subQuery->where('player_id', $request->player_id);
                });
            })
            ->when($request->training_group_id, function ($query) use ($request) {
                $query->whereHas('inscription', function ($subQuery) use ($request) {
                    $subQuery->where('training_group_id', $request->training_group_id);
                });
            })
            ->when($request->evaluation_period_id, function ($query) use ($request) {
                $query->where('evaluation_period_id', $request->evaluation_period_id);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->evaluation_type, function ($query) use ($request) {
                $query->where('evaluation_type', $request->evaluation_type);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->whereHas('inscription.player', function ($subQuery) use ($search) {
                        $subQuery->where('names', 'like', "%{$search}%")
                            ->orWhere('last_names', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(COALESCE(names, ''), ' ', COALESCE(last_names, '')) LIKE ?", ["%{$search}%"])
                            ->orWhere('unique_code', 'like', "%{$search}%");
                    })->orWhereHas('inscription.trainingGroup', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('id')
            ->paginate($perPage);

        return PlayerEvaluationResource::collection($evaluations);
    }

    public function options(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $inscriptionId = $request->integer('inscription_id');

        return response()->json([
            'filters' => [
                'players' => Player::query()
                    ->where('school_id', $schoolId)
                    ->whereHas('inscription', function ($query) use ($schoolId) {
                        $query->where('school_id', $schoolId)
                            ->where('year', now()->year);

                        applyInstructorTrainingGroupFilter($query);
                    }, '=', 1)
                    ->orderBy('names')
                    ->orderBy('last_names')
                    ->get()
                    ->map(fn (Player $player) => [
                        'id' => $player->id,
                        'name' => $this->playerName($player),
                        'unique_code' => $player->unique_code,
                    ])
                    ->values(),
                'training_groups' => TrainingGroup::query()
                    ->where('school_id', $schoolId)
                    ->where('year_active', now()->year)
                    ->when(isInstructor(), fn ($query) => $query->byInstructor())
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (TrainingGroup $group) => [
                        'id' => $group->id,
                        'name' => $group->name,
                    ])
                    ->values(),
                'periods' => $this->periodOptions($schoolId),
                'statuses' => self::STATUS_OPTIONS,
                'evaluation_types' => self::EVALUATION_TYPE_OPTIONS,
            ],
            'selection' => [
                'inscriptions' => $this->inscriptionOptions($schoolId),
                'periods' => $this->periodOptions($schoolId),
                'templates' => $this->templateOptions($schoolId, $inscriptionId ?: null),
                'statuses' => self::STATUS_OPTIONS,
                'evaluation_types' => self::EVALUATION_TYPE_OPTIONS,
            ],
            'scale_options' => config('evaluations.scale_options', []),
        ]);
    }

    public function create(CreatePlayerEvaluationsRequest $request)
    {
        $inscription = $this->scopedInscriptionsQuery($this->currentSchoolId())
            ->with(['player', 'trainingGroup'])
            ->whereKey($request->inscription_id)
            ->firstOrFail();

        $period = EvaluationPeriod::query()
            ->where('school_id', $this->currentSchoolId())
            ->findOrFail($request->evaluation_period_id);

        $template = EvaluationTemplate::query()
            ->with([
                'criteria' => fn ($q) => $q->orderBy('sort_order'),
                'trainingGroup',
            ])
            ->where('school_id', $this->currentSchoolId())
            ->findOrFail($request->evaluation_template_id);

        return response()->json([
            'inscription' => $this->serializeInscription($inscription),
            'period' => $this->serializePeriod($period),
            'template' => $this->serializeTemplate($template),
            'criteria_by_dimension' => $this->criteriaByDimension($template),
            'existingScores' => [],
            'status_options' => self::STATUS_OPTIONS,
            'evaluation_type_options' => self::EVALUATION_TYPE_OPTIONS,
            'scale_options' => config('evaluations.scale_options', []),
        ]);
    }

    public function store(StorePlayerEvaluationRequest $request)
    {
        $data = $request->validated();
        $data['scores'] = $this->normalizeScores($data['scores'] ?? []);

        $inscription = $this->scopedInscriptionsQuery($this->currentSchoolId())
            ->with(['player', 'trainingGroup'])
            ->whereKey($data['inscription_id'])
            ->firstOrFail();

        $evaluation = $this->crudService->create(
            inscription: $inscription,
            data: $data,
            userId: (int) Auth::id()
        );

        $evaluation->load($this->evaluationRelations());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Evaluación creada correctamente.',
                'data' => PlayerEvaluationResource::make($evaluation),
            ], 201);
        }

        return redirect()
            ->to($this->playerEvaluationShowUrl($evaluation->id))
            ->with('success', 'Evaluación creada correctamente.');
    }

    public function show(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);
        $playerEvaluation->load($this->evaluationRelations());

        return PlayerEvaluationResource::make($playerEvaluation);
    }

    public function edit(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);
        $playerEvaluation->load([
            'inscription.player',
            'inscription.trainingGroup',
            'period',
            'template.criteria',
            'template.trainingGroup',
            'scores',
        ]);

        $template = $playerEvaluation->template->load([
            'criteria' => fn ($q) => $q->orderBy('sort_order'),
            'trainingGroup',
        ]);

        $existingScores = $playerEvaluation->scores
            ->keyBy('template_criterion_id')
            ->map(fn ($score) => [
                'template_criterion_id' => $score->template_criterion_id,
                'score' => $score->score,
                'scale_value' => $score->scale_value,
                'comment' => $score->comment,
            ])
            ->toArray();

        return response()->json([
            'evaluation' => PlayerEvaluationResource::make(
                $playerEvaluation->load($this->evaluationRelations())
            ),
            'inscription' => $this->serializeInscription($playerEvaluation->inscription),
            'period' => $this->serializePeriod($playerEvaluation->period),
            'template' => $this->serializeTemplate($template),
            'criteria_by_dimension' => $this->criteriaByDimension($template),
            'existingScores' => $existingScores,
            'status_options' => self::STATUS_OPTIONS,
            'evaluation_type_options' => self::EVALUATION_TYPE_OPTIONS,
            'scale_options' => config('evaluations.scale_options', []),
        ]);
    }

    public function update(UpdatePlayerEvaluationRequest $request, PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);
        $data = $request->validated();
        $data['scores'] = $this->normalizeScores($data['scores'] ?? []);

        $evaluation = $this->crudService->update(
            evaluation: $playerEvaluation,
            data: $data
        );

        $evaluation->load($this->evaluationRelations());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Evaluación actualizada correctamente.',
                'data' => PlayerEvaluationResource::make($evaluation),
            ]);
        }

        return redirect()
            ->to($this->playerEvaluationShowUrl($evaluation->id))
            ->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroy(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);
        $this->crudService->delete($playerEvaluation);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Evaluación eliminada correctamente.',
            ]);
        }

        return redirect()
            ->to($this->playerEvaluationIndexUrl())
            ->with('success', 'Evaluación eliminada correctamente.');
    }

    public function pdf(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);

        return $this->guardianEvaluationPdfService->download($playerEvaluation);
    }

    private function normalizeScores(array $scores): array
    {
        return array_values($scores);
    }

    private function evaluationRelations(): array
    {
        return [
            'inscription.player',
            'inscription.trainingGroup',
            'period',
            'template.trainingGroup',
            'evaluator',
            'scores.criterion',
        ];
    }

    private function currentSchoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }

    private function scopedEvaluation(PlayerEvaluation $playerEvaluation): PlayerEvaluation
    {
        $query = PlayerEvaluation::query()
            ->whereKey($playerEvaluation->id)
            ->where('school_id', $this->currentSchoolId());

        if (isInstructor()) {
            $query->whereHas('inscription.trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor());
        }

        abort_unless($query->exists(), 404);

        return $playerEvaluation;
    }

    private function scopedInscriptionsQuery(int $schoolId)
    {
        return Inscription::query()
            ->where('school_id', $schoolId)
            ->when(isInstructor(), function ($query) {
                $query->whereHas('trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor());
            });
    }

    private function playerEvaluationIndexUrl(): string
    {
        return url('/player-evaluations');
    }

    private function playerEvaluationShowUrl(int $evaluationId): string
    {
        return url("/player-evaluations/{$evaluationId}");
    }

    private function periodOptions(int $schoolId)
    {
        return EvaluationPeriod::query()
            ->where('school_id', $schoolId)
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EvaluationPeriod $period) => $this->serializePeriod($period))
            ->values();
    }

    private function inscriptionOptions(int $schoolId)
    {
        return $this->scopedInscriptionsQuery($schoolId)
            ->with(['player', 'trainingGroup'])
            ->where('year', now()->year)
            ->latest('id')
            ->get()
            ->map(function (Inscription $inscription) {
                return [
                    'id' => $inscription->id,
                    'year' => $inscription->year,
                    'player_id' => $inscription->player_id,
                    'player_name' => $this->playerName($inscription->player),
                    'training_group_id' => $inscription->training_group_id,
                    'training_group_name' => $inscription->trainingGroup?->name,
                    'label' => sprintf(
                        '#%s - %s%s',
                        $inscription->id,
                        $this->playerName($inscription->player),
                        $inscription->trainingGroup?->name ? ' - ' . $inscription->trainingGroup->name : ''
                    ),
                ];
            })
            ->values();
    }

    private function templateOptions(int $schoolId, ?int $inscriptionId = null)
    {
        $trainingGroupId = null;

        if ($inscriptionId) {
            $trainingGroupId = $this->scopedInscriptionsQuery($schoolId)
                ->whereKey($inscriptionId)
                ->value('training_group_id');

            if (!$trainingGroupId) {
                return collect();
            }
        }

        return EvaluationTemplate::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('trainingGroup')
            ->when($trainingGroupId, function ($query) use ($trainingGroupId) {
                $query->where(function ($nestedQuery) use ($trainingGroupId) {
                    $nestedQuery->whereNull('training_group_id')
                        ->orWhere('training_group_id', $trainingGroupId);
                });
            })
            ->orderByDesc('year')
            ->orderBy('name')
            ->get()
            ->map(function (EvaluationTemplate $template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'year' => $template->year,
                    'status' => $template->status,
                    'version' => $template->version,
                    'training_group_id' => $template->training_group_id,
                    'training_group_name' => $template->trainingGroup?->name,
                ];
            })
            ->values();
    }

    private function criteriaByDimension(EvaluationTemplate $template): array
    {
        return $template->criteria
            ->groupBy(fn ($criterion) => $criterion->dimension ?: 'Sin dimensión')
            ->map(function ($criteria) {
                return $criteria->map(fn ($criterion) => [
                    'id' => $criterion->id,
                    'code' => $criterion->code,
                    'dimension' => $criterion->dimension,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'score_type' => $criterion->score_type,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                    'weight' => $criterion->weight,
                    'sort_order' => $criterion->sort_order,
                    'is_required' => (bool) $criterion->is_required,
                    'scale_options' => config('evaluations.scale_options.' . $criterion->score_type, []),
                ])->values();
            })
            ->toArray();
    }

    private function serializeInscription(Inscription $inscription): array
    {
        return [
            'id' => $inscription->id,
            'year' => $inscription->year,
            'player_id' => $inscription->player_id,
            'training_group_id' => $inscription->training_group_id,
            'player' => [
                'id' => $inscription->player?->id,
                'name' => $this->playerName($inscription->player),
                'unique_code' => $inscription->player?->unique_code,
                'photo_url' => $inscription->player?->photo_url,
            ],
            'training_group' => [
                'id' => $inscription->trainingGroup?->id,
                'name' => $inscription->trainingGroup?->name,
            ],
        ];
    }

    private function serializePeriod(EvaluationPeriod $period): array
    {
        return [
            'id' => $period->id,
            'name' => $period->name,
            'code' => $period->code,
            'year' => $period->year,
            'starts_at' => optional($period->starts_at)->format('Y-m-d'),
            'ends_at' => optional($period->ends_at)->format('Y-m-d'),
            'sort_order' => $period->sort_order,
            'is_active' => (bool) $period->is_active,
        ];
    }

    private function serializeTemplate(EvaluationTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'year' => $template->year,
            'status' => $template->status,
            'version' => $template->version,
            'training_group_id' => $template->training_group_id,
            'training_group_name' => $template->trainingGroup?->name,
        ];
    }

    private function playerName(?Player $player): string
    {
        if (!$player) {
            return 'Jugador sin nombre';
        }

        return $player->full_names
            ?? $player->full_name
            ?? $player->name
            ?? ('Jugador #' . $player->id);
    }
}
