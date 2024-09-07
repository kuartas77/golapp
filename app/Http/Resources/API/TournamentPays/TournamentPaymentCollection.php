<?php

namespace App\Http\Resources\API\TournamentPays;

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TournamentPaymentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection['rows']->map(function ($tournamentPayment) {
            return new TournamentPaymentResource($tournamentPayment);
        })->toArray();
    }
}
