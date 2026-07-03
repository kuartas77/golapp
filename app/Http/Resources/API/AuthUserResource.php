<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use App\Service\Auth\AuthUserContext;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AuthUserResource extends JsonResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return app(AuthUserContext::class)->get($this->resource);
    }
}
