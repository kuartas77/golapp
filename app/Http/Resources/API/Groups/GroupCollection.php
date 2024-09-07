<?php

namespace App\Http\Resources\API\Groups;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($group) {
            return new TrainingGroupResource($group);
        })->toArray();
    }
}
