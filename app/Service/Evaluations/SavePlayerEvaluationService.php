<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Evaluations\PlayerEvaluationScore;
use Illuminate\Support\Facades\DB;

class SavePlayerEvaluationService
{
    public function __construct(
        private PlayerEvaluationScoreCalculator $calculator
    ) {}

    public function execute(int $inscriptionId, int $evaluatorUserId, array $data): PlayerEvaluation
    {
        return DB::transaction(function () use ($inscriptionId, $evaluatorUserId, $data) {
            $evaluation = PlayerEvaluation::updateOrCreate(
                [
                    'inscription_id' => $inscriptionId,
                    'evaluation_period_id' => $data['evaluation_period_id'],
                    'school_id' => $data['school_id']
                ],
                [
                    'evaluation_template_id' => $data['evaluation_template_id'],
                    'evaluator_user_id' => $evaluatorUserId,
                    'evaluation_type' => $data['evaluation_type'] ?? 'periodic',
                    'status' => $data['status'] ?? 'draft',
                    'evaluated_at' => $data['evaluated_at'] ?? now(),
                    'general_comment' => $data['general_comment'] ?? null,
                    'strengths' => $data['strengths'] ?? null,
                    'improvement_opportunities' => $data['improvement_opportunities'] ?? null,
                    'recommendations' => $data['recommendations'] ?? null,
                ]
            );

            foreach ($data['scores'] ?? [] as $scoreData) {
                PlayerEvaluationScore::updateOrCreate(
                    [
                        'player_evaluation_id' => $evaluation->id,
                        'template_criterion_id' => $scoreData['template_criterion_id'],
                    ],
                    [
                        'score' => $scoreData['score'] ?? null,
                        'comment' => $scoreData['comment'] ?? null,
                        'scale_value' => $scoreData['scale_value'] ?? null,
                    ]
                );
            }

            $evaluation->load('scores.criterion');

            $errors = $this->calculator->validateScores($evaluation);

            if (!empty($errors)) {
                throw new \DomainException(implode(' ', $errors));
            }

            return $this->calculator->recalculateAndSave($evaluation);
        });
    }
}
