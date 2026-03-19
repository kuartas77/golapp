<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\StorePlayerEvaluationRequest;
use App\Http\Requests\Evaluations\UpdatePlayerEvaluationRequest;
use App\Http\Resources\API\Evaluations\PlayerEvaluationResource;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Service\Evaluations\PlayerEvaluationCrudService;
use Illuminate\Http\Request;


class PlayerEvaluationController extends Controller
{
    private array $indexRelations = [
        'period',
        'template',
        'evaluator',
    ];

    private array $detailRelations = [
        'inscription.player',
        'inscription.trainingGroup',
        'period',
        'template.criteria',
        'scores.criterion',
        'evaluator',
    ];

    public function index(Request $request, Inscription $inscription)
    {


    
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);

        $query = $inscription->playerEvaluations()
            ->with($this->indexRelations)
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id');

        if ($request->filled('evaluation_period_id')) {
            $query->where('evaluation_period_id', $request->integer('evaluation_period_id'));
        }

        if ($request->filled('evaluation_template_id')) {
            $query->where('evaluation_template_id', $request->integer('evaluation_template_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('evaluation_type')) {
            $query->where('evaluation_type', $request->string('evaluation_type')->toString());
        }

        $evaluations = $query->paginate($perPage)->appends($request->query());

        return PlayerEvaluationResource::collection($evaluations);
    }

    public function store(
        StorePlayerEvaluationRequest $request,
        Inscription $inscription,
        PlayerEvaluationCrudService $service
    ) {
        $evaluation = $service->create(
            inscription: $inscription,
            data: $request->validated(),
            userId: (int) auth()->id()
        );

        $evaluation->load($this->detailRelations);

        return (new PlayerEvaluationResource($evaluation))
            ->response();
            // ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Inscription $inscription, PlayerEvaluation $evaluation)
    {
        $this->ensureBelongsToInscription($inscription, $evaluation);

        $evaluation->load($this->detailRelations);

        return new PlayerEvaluationResource($evaluation);
    }

    public function update(
        UpdatePlayerEvaluationRequest $request,
        Inscription $inscription,
        PlayerEvaluation $evaluation,
        PlayerEvaluationCrudService $service
    ) {
        $this->ensureBelongsToInscription($inscription, $evaluation);

        $evaluation->loadMissing('inscription');

        $evaluation = $service->update(
            evaluation: $evaluation,
            data: $request->validated()
        );

        $evaluation->load($this->detailRelations);

        return new PlayerEvaluationResource($evaluation);
    }

    public function destroy(
        Inscription $inscription,
        PlayerEvaluation $evaluation,
        PlayerEvaluationCrudService $service
    ) {
        $this->ensureBelongsToInscription($inscription, $evaluation);

        $service->delete($evaluation);

        return response()->noContent();
    }

    private function ensureBelongsToInscription(Inscription $inscription, PlayerEvaluation $evaluation): void
    {
        abort_if(
            (int) $evaluation->inscription_id !== (int) $inscription->id,
            404,
            'La evaluación no pertenece a la inscripción indicada.'
        );
    }
}
