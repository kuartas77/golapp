<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;

class PlayerEvaluationScoreCalculator
{
    public function calculate(PlayerEvaluation $evaluation): float
    {
        $evaluation->loadMissing('scores.criterion');

        $scores = $evaluation->scores
            ->filter(function ($item) {
                return $item->score !== null
                    && $item->criterion !== null
                    && $item->criterion->score_type === 'numeric';
            });

        if ($scores->isEmpty()) {
            return 0.00;
        }

        $weightedTotal = 0;
        $weightsTotal = 0;

        foreach ($scores as $score) {
            $weight = (float) ($score->criterion->weight ?? 1);

            $weightedTotal += ((float) $score->score) * $weight;
            $weightsTotal += $weight;
        }

        if ($weightsTotal <= 0) {
            return 0.00;
        }

        return round($weightedTotal / $weightsTotal, 2);
    }

    public function calculateDimensionScores(PlayerEvaluation $evaluation): array
    {
        $evaluation->loadMissing('scores.criterion');

        $grouped = $evaluation->scores
            ->filter(function ($item) {
                return $item->score !== null
                    && $item->criterion !== null
                    && $item->criterion->score_type === 'numeric';
            })
            ->groupBy(fn ($item) => $item->criterion->dimension);

        $result = [];

        foreach ($grouped as $dimension => $scores) {
            $weightedTotal = 0;
            $weightsTotal = 0;

            foreach ($scores as $score) {
                $weight = (float) ($score->criterion->weight ?? 1);

                $weightedTotal += ((float) $score->score) * $weight;
                $weightsTotal += $weight;
            }

            $result[$dimension] = $weightsTotal > 0
                ? round($weightedTotal / $weightsTotal, 2)
                : 0.00;
        }

        return $result;
    }

    public function recalculateAndSave(PlayerEvaluation $evaluation): PlayerEvaluation
    {
        $overallScore = $this->calculate($evaluation);

        $evaluation->overall_score = $overallScore;
        $evaluation->save();

        return $evaluation->refresh();
    }

    public function validateScores(PlayerEvaluation $evaluation): array
    {
        $evaluation->loadMissing('scores.criterion');

        $errors = [];

        foreach ($evaluation->scores as $score) {
            $criterion = $score->criterion;

            if (!$criterion || $score->score === null) {
                continue;
            }

            $value = (float) $score->score;
            $min = $criterion->min_score !== null ? (float) $criterion->min_score : null;
            $max = $criterion->max_score !== null ? (float) $criterion->max_score : null;

            if ($min !== null && $value < $min) {
                $errors[] = "El criterio {$criterion->name} tiene un valor menor al mínimo permitido.";
            }

            if ($max !== null && $value > $max) {
                $errors[] = "El criterio {$criterion->name} tiene un valor mayor al máximo permitido.";
            }
        }

        return $errors;
    }
}
