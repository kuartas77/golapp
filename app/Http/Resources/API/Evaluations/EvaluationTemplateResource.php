<?php

namespace App\Http\Resources\API\Evaluations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $evaluationsCount = $this->whenCounted('playerEvaluations', fn () => (int) $this->player_evaluations_count);
        $criteriaCount = $this->whenCounted('criteria', fn () => (int) $this->criteria_count);
        $isInUse = $this->resource->isInUse();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'year' => $this->year,
            'training_group_id' => $this->training_group_id,
            'training_group_name' => $this->whenLoaded('trainingGroup', fn () => $this->trainingGroup?->name),
            'status' => $this->status,
            'version' => $this->version,
            'created_by' => $this->created_by,
            'creator_name' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'criteria_count' => $criteriaCount,
            'evaluations_count' => $evaluationsCount,
            'is_in_use' => $isInUse,
            'is_editable' => !$isInUse,
            'can_delete' => !$isInUse,
            'can_duplicate' => true,
            'can_activate' => $this->status !== 'active',
            'can_inactivate' => $this->status !== 'inactive',
            'criteria' => $this->whenLoaded('criteria', function () {
                return $this->criteria
                    ->sortBy('sort_order')
                    ->values()
                    ->map(function ($criterion) {
                        return [
                            'id' => $criterion->id,
                            'code' => $criterion->code,
                            'dimension' => $criterion->dimension,
                            'name' => $criterion->name,
                            'description' => $criterion->description,
                            'score_type' => $criterion->score_type,
                            'min_score' => $criterion->min_score,
                            'max_score' => $criterion->max_score,
                            'weight' => $criterion->weight,
                            'sort_order' => $criterion->sort_order,
                            'is_required' => (bool) $criterion->is_required,
                        ];
                    })
                    ->all();
            }),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
