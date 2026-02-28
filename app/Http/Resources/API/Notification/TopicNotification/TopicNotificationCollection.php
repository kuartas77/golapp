<?php

namespace App\Http\Resources\API\Notification\TopicNotification;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class TopicNotificationCollection extends ResourceCollection
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return new TopicNotificationResource($item);
        })->toArray();
    }
}