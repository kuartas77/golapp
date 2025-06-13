<?php

namespace App\Http\Resources\API\Groups;

use JsonSerializable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use App\Models\TrainingGroup;
use App\Models\Assist;

class TrainingGroupStatisticsResource extends JsonResource
{
/**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TrainingGroup::class;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->calculateStatistics();
    }

    private function calculateStatistics(): array
    {
        $date = now();
        $classDays = classDays(
            $date->year,
            $date->month,
            array_map('dayToNumber', $this->explode_days)
        );

        $columns = $classDays->map(fn($classDay) => DB::raw("sum(case when {$classDay['column']} is not null then 1 else 0 end) as {$classDay['column']}"));

        $query = Assist::query()->where('training_group_id', $this->id)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->groupBy('training_group_id')
            ->groupBy('month');

        $assist = $query->select($columns->toArray())->first();

        $assistTaken = !is_null($assist) ? array_sum($assist->toArray()) : 0;

        $noTaken = (($columns->count() * $query->count()) - $assistTaken);
        $total = ($noTaken + $assistTaken);
        $percentage = number_format((float)(($assistTaken * 100) / ($total ?: 1)), 2, '.', ''). " %";

        return [
            'group_id' => $this->id,
            'full_group' => $this->full_group,
            'attendances_taken' => $assistTaken,
            'attendances_total' => $total,
            'attendances_no_taken' => $noTaken,
            'avg' => $percentage
        ];
    }
}
