<?php

namespace App\Http\Resources\API\Groups;

use Carbon\Carbon;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\Players\PlayersCollection;

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
        $request->merge(['group_id' => $this->id]);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'explode_schedules' => $this->explode_schedules[0],
            'full_schedule_group' => $this->full_schedule_group,
            'full_group' => $this->full_group,
            'player_count' => $this->inscriptions_count,
            'class_days' => $this->getClassDays(),
            'players' => $this->whenLoaded('members', new PlayersCollection($this->members))
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

        return $classDays->map(function ($class)use($date) {
            $name = Str::ucfirst($class['name']);
            return [
                'id' => "{$this->id}{$date->month}{$class['day']}",
                'date' => $class['day'],
                'day' => $name,
                'month' => $date->month,
                'month_name' => getMonth($date->month),
                'column' => $class['column'],
                'group_id' => $this->id,
                'school_id' => getSchool(auth()->user())->id
            ];
        });
    }
}
