<?php

namespace App\Http\Resources\API\Groups;

use App\Http\Resources\API\Groups\TrainingGroupResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($group) {
            return new TrainingGroupResource($group);
        })->toArray();
    }
}
