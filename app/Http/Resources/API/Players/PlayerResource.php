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
        $photoUrl = str_replace('img/dynamic', 'api/img/dynamic', $this->photo_url);
        return [
            'id' => $this->id,
            'unique_code' => $this->unique_code,
            'names' => $this->names,
            'last_names' => $this->last_names,
            'category' => $this->category,
            'full_names' => $this->full_names,
            'photo_url' => $photoUrl,
            'group_id' => $this->when($request->has('group_id'), $request->group_id),
            'inscription_id' => $this->inscription_id
        ];
    }
}
