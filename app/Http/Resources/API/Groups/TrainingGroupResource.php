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
use App\Repositories\TrainingGroupRepository;

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
            'class_days' => TrainingGroupRepository::getClassDays($this),
            'players' => $this->whenLoaded('members', new PlayersCollection($this->members))
        ];
    }
}
