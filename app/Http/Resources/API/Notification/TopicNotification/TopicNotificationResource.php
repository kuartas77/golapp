<?php

namespace App\Http\Resources\API\Notification\TopicNotification;

use App\Models\TopicNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TopicNotificationResource extends JsonResource
{
    public static $wrap = null;
    public $resource = TopicNotification::class;
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
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => null,
            'is_read' => $this->pivot->is_read,
            'image_url' => null,
            'action_url' => null,
            'priority' => $this->priority,
            'created_at' => Carbon::parse($this->created_at)->getPreciseTimestamp(3),
        ];
    }
}