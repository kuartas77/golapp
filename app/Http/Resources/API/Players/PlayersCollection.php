<?php

namespace App\Http\Resources\API\Players;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlayersCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($player) {
            return new PlayerResource($player);
        })->toArray();
    }
}
