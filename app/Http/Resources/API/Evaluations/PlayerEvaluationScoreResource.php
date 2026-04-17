<?php

namespace App\Http\Resources\API\Evaluations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerEvaluationScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_criterion_id' => $this->template_criterion_id,
            'score' => $this->score,
            'scale_value' => $this->scale_value,
            'scale_label' => $this->scale_label,
            'comment' => $this->comment,
            'criterion' => $this->whenLoaded('criterion', function () {
                return [
                    'id' => $this->criterion->id,
                    'dimension' => $this->criterion->dimension,
                    'name' => $this->criterion->name,
                    'score_type' => $this->criterion->score_type,
                    'min_score' => $this->criterion->min_score,
                    'max_score' => $this->criterion->max_score,
                    'weight' => $this->criterion->weight,
                    'sort_order' => $this->criterion->sort_order,
                    'is_required' => $this->criterion->is_required,
                    'scale_options' => config('evaluations.scale_options.' . $this->criterion->score_type, []),
                ];
            }),
        ];
    }
}
