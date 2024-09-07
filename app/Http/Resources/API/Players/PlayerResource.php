<?php

namespace App\Http\Resources\API\Players;

use JsonSerializable;
use App\Models\Player;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $resource = Player::class;

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
            'unique_code' => $this->unique_code,
            'names' => $this->names,
            'last_names' => $this->last_names,
            'gender' => $this->gender,
            'date_birth' => $this->date_birth,
            'place_birth' => $this->place_birth,
            'identification_document' => $this->identification_document,
            'rh' => $this->rh,
            'category' => $this->category,
            'address' => $this->address,
            'municipality' => $this->municipality,
            'neighborhood' => $this->neighborhood,
            'phones' => $this->phones,
            'mobile' => $this->mobile,
            'eps' => $this->eps,
            'full_names' => $this->full_names,
            'photo_url' => $this->photo_url,
        ];
    }
}
