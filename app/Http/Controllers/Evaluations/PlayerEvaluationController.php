<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\CreatePlayerEvaluationsRequest;
use App\Http\Requests\Evaluations\StorePlayerEvaluationRequest;
use App\Http\Requests\Evaluations\UpdatePlayerEvaluationRequest;
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
    public function __construct(
        private PlayerEvaluationCrudService $crudService,
        private GuardianEvaluationPdfService $guardianEvaluationPdfService
    ) {}

    public function index(Request $request)
    {
        $evaluations = PlayerEvaluation::query()
            ->with([
                'inscription.player',
                'inscription.trainingGroup',
                'period',
                'template',
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
            ->latest('id')
            ->paginate(15);

        return view('player_evaluations.index', [
            'evaluations' => $evaluations,
            'players' => Player::query()->orderBy('names')->get(),
            'trainingGroups' => TrainingGroup::query()->orderBy('name')->get(),
            'periods' => EvaluationPeriod::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(CreatePlayerEvaluationsRequest $request)
    {
        $inscription = Inscription::query()
            ->with(['player', 'trainingGroup'])
            ->findOrFail($request->inscription_id);

        $period = EvaluationPeriod::findOrFail($request->evaluation_period_id);

        $template = EvaluationTemplate::query()
            ->with(['criteria' => fn ($q) => $q->orderBy('sort_order')])
            ->findOrFail($request->evaluation_template_id);

        return view('player_evaluations.create', [
            'inscription' => $inscription,
            'period' => $period,
            'template' => $template,
            'criteriaByDimension' => $template->criteria->groupBy('dimension'),
            'existingScores' => [],
        ]);
    }

    public function store(StorePlayerEvaluationRequest $request)
    {
        $data = $request->validated();
        $data['scores'] = $this->normalizeScores($data['scores'] ?? []);

        $inscription = Inscription::query()
            ->with(['player', 'trainingGroup'])
            ->findOrFail($data['inscription_id']);

        $evaluation = $this->crudService->create(
            inscription: $inscription,
            data: $data,
            userId: (int) Auth::id()
        );

        return redirect()
            ->route('player-evaluations.show', $evaluation->id)
            ->with('success', 'Evaluación creada correctamente.');
    }

    public function show(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation->load([
            'inscription.player',
            'inscription.trainingGroup',
            'period',
            'template',
            'evaluator',
            'scores.criterion',
        ]);

        $scoresByDimension = $playerEvaluation->scores
            ->sortBy(fn ($score) => optional($score->criterion)->sort_order ?? 9999)
            ->groupBy(fn ($score) => optional($score->criterion)->dimension ?: 'Sin dimensión');

        return view('player_evaluations.show', [
            'playerEvaluation' => $playerEvaluation,
            'scoresByDimension' => $scoresByDimension,
        ]);
    }

    public function edit(PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation->load([
            'inscription.player',
            'inscription.trainingGroup',
            'period',
            'template.criteria',
            'scores',
        ]);

        $template = $playerEvaluation->template->load([
            'criteria' => fn ($q) => $q->orderBy('sort_order')
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

        return view('player_evaluations.edit', [
            'playerEvaluation' => $playerEvaluation,
            'inscription' => $playerEvaluation->inscription,
            'period' => $playerEvaluation->period,
            'template' => $template,
            'criteriaByDimension' => $template->criteria->groupBy('dimension'),
            'existingScores' => $existingScores,
        ]);
    }

    public function update(UpdatePlayerEvaluationRequest $request, PlayerEvaluation $playerEvaluation)
    {
        $data = $request->validated();
        $data['scores'] = $this->normalizeScores($data['scores'] ?? []);

        $evaluation = $this->crudService->update(
            evaluation: $playerEvaluation,
            data: $data
        );

        return redirect()
            ->route('player-evaluations.show', $evaluation->id)
            ->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroy(PlayerEvaluation $playerEvaluation)
    {
        $this->crudService->delete($playerEvaluation);

        return redirect()
            ->route('player-evaluations.index')
            ->with('success', 'Evaluación eliminada correctamente.');
    }

    public function pdf(PlayerEvaluation $playerEvaluation)
    {
        return $this->guardianEvaluationPdfService->download($playerEvaluation);
    }

    private function normalizeScores(array $scores): array
    {
        return array_values($scores);
    }
}
