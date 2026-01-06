<?php

namespace App\Http\Resources\API;

use App\Http\Resources\API\Players\PlayerResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class LoginPlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $date = now()->addDays(7);
        $accessToken = $this->createToken('access_token', array_merge($this->abilities, ['auth']), $date);
        $refreshToken = $this->createToken('refresh_token', ['refresh'], $date->copy()->addDay());
        parent::wrap(null);

        $loadRelations = $this->relationLoaded('schoolData') && $this->relationLoaded('inscription');

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expires_at' => $date->getTimestampMs(),
            'player' => new PlayerResource($this, $loadRelations)
        ];
    }
}