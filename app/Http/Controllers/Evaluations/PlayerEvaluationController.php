<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\CreatePlayerEvaluationsRequest;
use App\Http\Requests\Evaluations\StorePlayerEvaluationRequest;
use App\Http\Requests\Evaluations\UpdatePlayerEvaluationRequest;
use App\Http\Resources\API\Evaluations\PlayerEvaluationResource;
use App\Models\Evaluations\PlayerEvaluation;
use App\Service\Evaluations\GuardianEvaluationPdfService;
use App\Service\Evaluations\PlayerEvaluationCrudService;
use App\Service\Evaluations\PlayerEvaluationFormService;
use App\Service\Evaluations\PlayerEvaluationQueryService;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerEvaluationController extends Controller
{
    public function __construct(
        private PlayerEvaluationCrudService $crudService,
        private GuardianEvaluationPdfService $guardianEvaluationPdfService,
        private InstructorPeriodEditPolicy $periodEditPolicy,
        private PlayerEvaluationQueryService $queries,
        private PlayerEvaluationFormService $forms,
    ) {}

    public function index(Request $request)
    {
        $evaluations = $this->queries->paginate($this->currentSchoolId(), $request->only([
            'per_page', 'search', 'player_id', 'training_group_id', 'evaluation_period_id', 'status', 'evaluation_type',
        ]));

        return PlayerEvaluationResource::collection($evaluations);
    }

    public function options(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $inscriptionId = $request->integer('inscription_id');

        return response()->json($this->forms->options($schoolId, $inscriptionId ?: null));
    }

    public function create(CreatePlayerEvaluationsRequest $request)
    {
        return response()->json($this->forms->createContext(
            $this->currentSchoolId(),
            (int) $request->inscription_id,
            (int) $request->evaluation_period_id,
            (int) $request->evaluation_template_id,
        ));
    }

    public function store(StorePlayerEvaluationRequest $request)
    {
        $data = $request->validated();
        $data['scores'] = $this->forms->normalizeScores($data['scores'] ?? []);
        $data['evaluated_at'] = $data['evaluated_at'] ?? now();
        $this->periodEditPolicy->assertCanMutateDate($data['evaluated_at'] ?? now(), 'evaluated_at');

        $inscription = $this->queries->scopedInscriptions($this->currentSchoolId())
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

        return response()->json(array_merge([
            'evaluation' => PlayerEvaluationResource::make(
                $playerEvaluation->load($this->evaluationRelations())
            ),
        ], $this->forms->editContext($playerEvaluation)));
    }

    public function update(UpdatePlayerEvaluationRequest $request, PlayerEvaluation $playerEvaluation)
    {
        $playerEvaluation = $this->scopedEvaluation($playerEvaluation);
        $data = $request->validated();
        $data['scores'] = $this->forms->normalizeScores($data['scores'] ?? []);
        if (array_key_exists('evaluated_at', $data) && empty($data['evaluated_at'])) {
            $data['evaluated_at'] = now();
        }
        $this->periodEditPolicy->assertCanMutateDate($playerEvaluation->evaluated_at ?? now(), 'evaluated_at');
        $this->periodEditPolicy->assertCanMutateDate($data['evaluated_at'] ?? $playerEvaluation->evaluated_at ?? now(), 'evaluated_at');

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
        $this->periodEditPolicy->assertCanMutateDate($playerEvaluation->evaluated_at ?? now(), 'evaluated_at');
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

    private function evaluationRelations(): array
    {
        return $this->queries->relations();
    }

    private function currentSchoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }

    private function scopedEvaluation(PlayerEvaluation $playerEvaluation): PlayerEvaluation
    {
        return $this->queries->scopedEvaluation($playerEvaluation, $this->currentSchoolId());
    }

    private function playerEvaluationIndexUrl(): string
    {
        return url('/player-evaluations');
    }

    private function playerEvaluationShowUrl(int $evaluationId): string
    {
        return url("/player-evaluations/{$evaluationId}");
    }
}
