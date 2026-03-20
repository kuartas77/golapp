<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\ComparePlayerEvaluationsRequest;
use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Inscription;
use App\Models\Player;
use App\Service\Evaluations\GuardianEvaluationComparisonPdfService;
use App\Service\Evaluations\PlayerEvaluationComparisonService;

class PlayerEvaluationComparisonController extends Controller
{
    public function __construct(
        private PlayerEvaluationComparisonService $comparisonService,
        private GuardianEvaluationComparisonPdfService $comparisonPdfService
    ) {}

    public function index(ComparePlayerEvaluationsRequest $request)
    {
        $inscription = Inscription::query()
            ->with(['player', 'trainingGroup'])
            ->findOrFail($request->inscription_id);

        $comparison = $this->comparisonService->compare(
            inscription: $inscription,
            periodAId: (int) $request->period_a_id,
            periodBId: (int) $request->period_b_id
        );

        return view('player_evaluations.comparison', [
            'comparison' => $comparison,
            'inscriptions' => Inscription::query()
                ->with(['player', 'trainingGroup'])
                ->latest('id')
                ->get(),
            // 'players' => Player::query()->orderBy('first_name')->get(),
            'periods' => EvaluationPeriod::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function pdf(ComparePlayerEvaluationsRequest $request)
    {
        $inscription = Inscription::query()
            ->with(['player', 'trainingGroup'])
            ->findOrFail($request->inscription_id);

        return $this->comparisonPdfService->download(
            inscription: $inscription,
            periodAId: (int) $request->period_a_id,
            periodBId: (int) $request->period_b_id
        );
    }
}
