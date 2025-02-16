<?php

namespace App\Http\Resources\API\Assists;

use JsonSerializable;
use App\Models\Assist;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\Players\PlayerResource;

class AssistResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = Assist::class;

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
            'school_id' => $this->school_id,
            'training_group_id' => $this->training_group_id,
            'inscription_id' => $this->inscription_id,
            'year' => $this->year,
            'month' => $request->month,
            'column' => $request->column,
            'value' => $this->{$request->column},
            'player_id' => $this->whenLoaded('player', $this->player->id)
        ];
    }
}
