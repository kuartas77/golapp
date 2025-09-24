<?php

namespace App\Http\Resources\API;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'name' => $this->name,
            'school_id' => $this->school_id,
            'school_name' => $this->school->name,
            'school_slug' => $this->school->slug,
            'school_logo' => $this->school->logo_file,
            'role' => $this->whenLoaded('roles')->first()
        ];
    }
}
