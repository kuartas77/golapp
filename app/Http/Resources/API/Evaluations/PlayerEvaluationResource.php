<?php

namespace App\Http\Resources\API\Evaluations;


use App\Service\Evaluations\PlayerEvaluationScoreCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inscription_id' => $this->inscription_id,

            'evaluation_period' => $this->whenLoaded('period', function () {
                return [
                    'id' => $this->period->id,
                    'name' => $this->period->name,
                    'code' => $this->period->code,
                    'year' => $this->period->year,
                    'starts_at' => optional($this->period->starts_at)->format('Y-m-d'),
                    'ends_at' => optional($this->period->ends_at)->format('Y-m-d'),
                ];
            }),

            'template' => $this->whenLoaded('template', function () {
                return [
                    'id' => $this->template->id,
                    'name' => $this->template->name,
                    'description' => $this->template->description,
                    'year' => $this->template->year,
                    'status' => $this->template->status,
                    'version' => $this->template->version,
                ];
            }),

            'evaluator' => $this->whenLoaded('evaluator', function () {
                return [
                    'id' => $this->evaluator->id,
                    'name' => $this->evaluator->name ?? null,
                    'email' => $this->evaluator->email ?? null,
                ];
            }),

            'inscription' => $this->whenLoaded('inscription', function () {
                return [
                    'id' => $this->inscription->id,
                    'player_id' => $this->inscription->player_id ?? null,
                    'training_group_id' => $this->inscription->training_group_id ?? null,
                    'player' => $this->when(
                        $this->inscription->relationLoaded('player') && $this->inscription->player,
                        function () {
                            return [
                                'id' => $this->inscription->player->id,
                                'name' => $this->inscription->player->name
                                    ?? $this->inscription->player->full_name
                                    ?? null,
                            ];
                        }
                    ),
                    'training_group' => $this->when(
                        $this->inscription->relationLoaded('trainingGroup') && $this->inscription->trainingGroup,
                        function () {
                            return [
                                'id' => $this->inscription->trainingGroup->id,
                                'name' => $this->inscription->trainingGroup->name ?? null,
                            ];
                        }
                    ),
                ];
            }),

            'evaluation_type' => $this->evaluation_type,
            'status' => $this->status,
            'evaluated_at' => optional($this->evaluated_at)->toISOString(),
            'overall_score' => $this->overall_score,

            'general_comment' => $this->general_comment,
            'strengths' => $this->strengths,
            'improvement_opportunities' => $this->improvement_opportunities,
            'recommendations' => $this->recommendations,

            'dimension_scores' => $this->when(
                $this->relationLoaded('scores'),
                function () {
                    return app(PlayerEvaluationScoreCalculator::class)
                        ->calculateDimensionScores($this->resource);
                }
            ),

            'scores' => PlayerEvaluationScoreResource::collection(
                $this->whenLoaded('scores')
            ),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
