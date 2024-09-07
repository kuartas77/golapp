<?php

namespace App\Http\Resources\API\TournamentPays;

use JsonSerializable;
use App\Models\TournamentPayout;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\Players\PlayerResource;

class TournamentPaymentResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TournamentPayout::class;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializables
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'status' => $this->status,
            'tournament_id' => $this->tournament_id,
            'unique_code' => $this->unique_code,
            'year' => $this->year,
            'player' => new PlayerResource($this->inscription->player)
        ];
    }
}
