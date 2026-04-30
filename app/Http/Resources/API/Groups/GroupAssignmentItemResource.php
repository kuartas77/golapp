<?php

declare(strict_types=1);

namespace App\Http\Resources\API\Groups;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class GroupAssignmentItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        $player = $this->player;
        $fullNames = $player?->full_names ?? 'Sin nombre';
        $category = (string) ($this->category ?? '');

        return [
            'id' => $this->id,
            'full_names' => $fullNames,
            'photo_url' => $player?->photo_url ?? url('img/user.webp'),
            'category' => $category,
            'search_text' => trim(sprintf('%s %s', $fullNames, $category)),
        ];
    }
}
