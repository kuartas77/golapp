<?php

namespace App\Http\Resources\API\Notification\Guardians;

use App\Http\Resources\API\Portal\GuardianPlayerListResource;
use App\Http\Resources\API\Portal\GuardianUserResource;
use App\Models\People;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class LoginGuardianResource extends JsonResource
{
    public static $wrap = null;

    /** @var People */
    public $resource;

    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $date = now()->addDays(7);
        $accessToken = $this->createToken('access_token', array_merge($this->abilities, ['auth']), $date);
        $refreshToken = $this->createToken('refresh_token', ['refresh'], $date->copy()->addDay());

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expires_at' => $date->getTimestampMs(),
            'guardian' => new GuardianUserResource($this),
            'players' => GuardianPlayerListResource::collection($this->notification_players ?? collect())->resolve(),
            'topics' => $this->notification_topics ?? [],
        ];
    }
}
