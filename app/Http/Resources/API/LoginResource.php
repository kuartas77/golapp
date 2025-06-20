<?php

namespace App\Http\Resources\API;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $date = now()->addWeeks(2);
        $accessToken = $this->createToken('access_token', array_merge($this->abilities, ['auth']), now()->addWeeks(2));
        $refreshToken = $this->createToken('refresh_token', ['refresh'], now()->addWeeks(3));
        parent::wrap(null);
        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expiration' => $date->getTimestampMs(),
            'user' => new UserResource($this)
        ];
    }
}