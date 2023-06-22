<?php

namespace App\Http\Resources\API\Assists;

use App\Models\Assist;
use App\Http\Resources\API\Assists\AssistResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AssitsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($assist) {
            return new AssistResource($assist);
        })->toArray();
    }
}
