<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\ComparePlayerEvaluationsRequest;
use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Inscription;
use App\Service\Evaluations\GuardianEvaluationComparisonPdfService;
use App\Service\Evaluations\PlayerEvaluationComparisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerEvaluationComparisonController extends Controller
{
    public function __construct(
        private PlayerEvaluationComparisonService $comparisonService,
        private GuardianEvaluationComparisonPdfService $comparisonPdfService
    ) {}

    public function index(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $comparison = null;

        if ($request->filled(['inscription_id', 'period_a_id', 'period_b_id'])) {
            $validated = Validator::make($request->all(), [
                'inscription_id' => ['required', 'integer', 'exists:inscriptions,id'],
                'period_a_id' => ['required', 'integer', 'exists:evaluation_periods,id'],
                'period_b_id' => ['required', 'integer', 'exists:evaluation_periods,id', 'different:period_a_id'],
            ])->validate();

            $inscription = Inscription::query()
                ->where('school_id', $schoolId)
                ->with(['player', 'trainingGroup'])
                ->findOrFail($validated['inscription_id']);

            $comparison = $this->comparisonService->compare(
                inscription: $inscription,
                periodAId: (int) $validated['period_a_id'],
                periodBId: (int) $validated['period_b_id']
            );
        }

        return response()->json([
            'comparison' => $comparison,
            'filters' => [
                'inscriptions' => Inscription::query()
                    ->where('school_id', $schoolId)
                    ->with(['player', 'trainingGroup'])
                    ->latest('id')
                    ->get()
                    ->map(function (Inscription $inscription) {
                        $playerName = $inscription->player?->full_names
                            ?? $inscription->player?->full_name
                            ?? $inscription->player?->name
                            ?? 'Jugador';

                        return [
                            'id' => $inscription->id,
                            'player_id' => $inscription->player_id,
                            'player_name' => $playerName,
                            'training_group_id' => $inscription->training_group_id,
                            'training_group_name' => $inscription->trainingGroup?->name,
                            'year' => $inscription->year,
                            'label' => sprintf(
                                '#%s - %s%s',
                                $inscription->id,
                                $playerName,
                                $inscription->trainingGroup?->name ? ' - ' . $inscription->trainingGroup->name : ''
                            ),
                        ];
                    })
                    ->values(),
                'periods' => EvaluationPeriod::query()
                    ->where('school_id', $schoolId)
                    ->orderByDesc('year')
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn (EvaluationPeriod $period) => [
                        'id' => $period->id,
                        'name' => $period->name,
                        'code' => $period->code,
                        'year' => $period->year,
                        'starts_at' => optional($period->starts_at)->format('Y-m-d'),
                        'ends_at' => optional($period->ends_at)->format('Y-m-d'),
                        'sort_order' => $period->sort_order,
                        'is_active' => (bool) $period->is_active,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function pdf(ComparePlayerEvaluationsRequest $request)
    {
        $inscription = Inscription::query()
            ->where('school_id', $this->currentSchoolId())
            ->with(['player', 'trainingGroup'])
            ->findOrFail($request->inscription_id);

        return $this->comparisonPdfService->download(
            inscription: $inscription,
            periodAId: (int) $request->period_a_id,
            periodBId: (int) $request->period_b_id
        );
    }

    private function currentSchoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }
}
