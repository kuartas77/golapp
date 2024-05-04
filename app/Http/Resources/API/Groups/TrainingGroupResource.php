<?php

namespace App\Http\Resources\API\Groups;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingGroupResource extends JsonResource
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'explode_schedules' => $this->explode_schedules[0],
            'full_schedule_group' => $this->full_schedule_group,
            'full_group' => $this->full_group,
            'class_days' => $this->getClassDays()
        ];
    }

    private function getClassDays(): Collection
    {
        $date = Carbon::now();
        $classDays = classDays(
            $date->year,
            $date->month,
            array_map('dayToNumber', $this->explode_name['days'])
        );

        return $classDays->map(function ($class) {
            $name = Str::ucfirst($class['name']);
            return [
                'id' => $class['day'],
                'name' => "{$class['day']} - {$name}",
                'index' => $class['column']
            ];
        });
    }
}
