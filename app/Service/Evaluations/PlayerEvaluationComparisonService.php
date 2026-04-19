<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlayerEvaluationComparisonService
{
    public function __construct(
        private PlayerEvaluationScoreCalculator $calculator
    ) {}

    public function compare(Inscription $inscription, int $periodAId, int $periodBId): array
    {
        $inscription->loadMissing(['player', 'trainingGroup']);

        $evaluations = $this->findEvaluations($inscription->id, [$periodAId, $periodBId]);
        $evaluationA = $this->resolveEvaluation($evaluations, $periodAId);
        $evaluationB = $this->resolveEvaluation($evaluations, $periodBId);

        $dimensionScoresA = $this->calculator->calculateDimensionScores($evaluationA);
        $dimensionScoresB = $this->calculator->calculateDimensionScores($evaluationB);

        $criteriaA = $this->buildCriteriaMap($evaluationA);
        $criteriaB = $this->buildCriteriaMap($evaluationB);

        return [
            'player' => [
                'id' => $inscription->player?->id,
                'name' => $inscription->player?->full_names
                    ?? $inscription->player?->full_name
                    ?? $inscription->player?->name
                    ?? null,
            ],
            'inscription' => [
                'id' => $inscription->id,
                'training_group_id' => $inscription->training_group_id,
                'training_group_name' => $inscription->trainingGroup?->name,
                'year' => $inscription->year ?? null,
            ],
            'period_a' => $this->serializeEvaluationHeader($evaluationA),
            'period_b' => $this->serializeEvaluationHeader($evaluationB),

            'overall' => [
                'period_a_score' => $evaluationA->overall_score,
                'period_b_score' => $evaluationB->overall_score,
                'delta' => $this->delta($evaluationA->overall_score, $evaluationB->overall_score),
                'trend' => $this->trend($evaluationA->overall_score, $evaluationB->overall_score),
            ],

            'dimensions' => $this->compareDimensions($dimensionScoresA, $dimensionScoresB),
            'criteria' => $this->compareCriteria($criteriaA, $criteriaB),

            'comments' => [
                'period_a' => [
                    'general_comment' => $evaluationA->general_comment,
                    'strengths' => $evaluationA->strengths,
                    'improvement_opportunities' => $evaluationA->improvement_opportunities,
                    'recommendations' => $evaluationA->recommendations,
                ],
                'period_b' => [
                    'general_comment' => $evaluationB->general_comment,
                    'strengths' => $evaluationB->strengths,
                    'improvement_opportunities' => $evaluationB->improvement_opportunities,
                    'recommendations' => $evaluationB->recommendations,
                ],
            ],
        ];
    }

    private function findEvaluations(int $inscriptionId, array $periodIds): Collection
    {
        return PlayerEvaluation::query()
            ->with([
                'period',
                'template',
                'evaluator',
                'scores',
            ])
            ->where('inscription_id', $inscriptionId)
            ->whereIn('evaluation_period_id', array_values(array_unique($periodIds)))
            ->get()
            ->keyBy('evaluation_period_id');
    }

    private function resolveEvaluation(Collection $evaluations, int $periodId): PlayerEvaluation
    {
        $evaluation = $evaluations->get($periodId);

        if (!$evaluation) {
            throw ValidationException::withMessages([
                'period' => [
                    "No existe evaluación para la inscripción en el período {$periodId}.",
                ],
            ]);
        }

        return $evaluation;
    }

    private function serializeEvaluationHeader(PlayerEvaluation $evaluation): array
    {
        return [
            'evaluation_id' => $evaluation->id,
            'period_id' => $evaluation->period?->id,
            'period_name' => $evaluation->period?->name,
            'period_code' => $evaluation->period?->code,
            'year' => $evaluation->period?->year,
            'template_id' => $evaluation->template?->id,
            'template_name' => $evaluation->template?->name,
            'status' => $evaluation->status,
            'evaluation_type' => $evaluation->evaluation_type,
            'evaluated_at' => optional($evaluation->evaluated_at)?->toISOString(),
            'overall_score' => $evaluation->overall_score,
        ];
    }

    private function compareDimensions(array $dimensionScoresA, array $dimensionScoresB): array
    {
        $allDimensions = array_unique(array_merge(
            array_keys($dimensionScoresA),
            array_keys($dimensionScoresB)
        ));

        sort($allDimensions);

        $result = [];

        foreach ($allDimensions as $dimension) {
            $scoreA = $dimensionScoresA[$dimension] ?? null;
            $scoreB = $dimensionScoresB[$dimension] ?? null;

            $result[] = [
                'dimension' => $dimension,
                'period_a_score' => $scoreA,
                'period_b_score' => $scoreB,
                'delta' => $this->delta($scoreA, $scoreB),
                'trend' => $this->trend($scoreA, $scoreB),
            ];
        }

        return $result;
    }

    private function compareCriteria(array $criteriaA, array $criteriaB): array
    {
        $allKeys = array_unique(array_merge(array_keys($criteriaA), array_keys($criteriaB)));
        sort($allKeys);

        $result = [];

        foreach ($allKeys as $key) {
            $itemA = $criteriaA[$key] ?? null;
            $itemB = $criteriaB[$key] ?? null;

            $code = $itemA['code'] ?? $itemB['code'] ?? null;
            $name = $itemA['name'] ?? $itemB['name'] ?? null;
            $dimension = $itemA['dimension'] ?? $itemB['dimension'] ?? null;
            $scoreA = $itemA['score'] ?? null;
            $scoreB = $itemB['score'] ?? null;

            $result[] = [
                'code' => $code,
                'dimension' => $dimension,
                'criterion' => $name,
                'period_a_score' => $scoreA,
                'period_b_score' => $scoreB,
                'delta' => $this->delta($scoreA, $scoreB),
                'trend' => $this->trend($scoreA, $scoreB),
                'period_a_comment' => $itemA['comment'] ?? null,
                'period_b_comment' => $itemB['comment'] ?? null,
            ];
        }

        return $result;
    }

    private function buildCriteriaMap(PlayerEvaluation $evaluation): array
    {
        $map = [];

        foreach ($evaluation->scores as $score) {
            if (!$score->criterion) {
                continue;
            }

            $criterion = $score->criterion;
            $dimension = trim((string) $criterion->dimension);
            $name = trim((string) $criterion->name);
            $code = $criterion->code ? trim((string) $criterion->code) : null;

            $key = $code
                ? Str::lower($code)
                : Str::lower($dimension . '|' . $name);

            $map[$key] = [
                'code' => $code,
                'dimension' => $dimension,
                'name' => $name,
                'score' => $score->score,
                'comment' => $score->comment,
            ];
        }

        return $map;
    }

    private function delta($valueA, $valueB): ?float
    {
        if ($valueA === null || $valueB === null) {
            return null;
        }

        return round(((float) $valueB) - ((float) $valueA), 2);
    }

    private function trend($valueA, $valueB): string
    {
        if ($valueA === null || $valueB === null) {
            return 'neutral';
        }

        if ((float) $valueB > (float) $valueA) {
            return 'up';
        }

        if ((float) $valueB < (float) $valueA) {
            return 'down';
        }

        return 'equal';
    }
}
