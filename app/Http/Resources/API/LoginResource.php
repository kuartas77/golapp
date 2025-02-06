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
        $accessToken = $this->createToken('access_token', $this->abilities, $date);
        // $refreshToken = $this->createToken('refresh_token', $this->abilities, $date);

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken->plainTextToken,
            'expiration' => $date->getTimestampMs(),
            'user' => new UserResource($this)
        ];
    }
}