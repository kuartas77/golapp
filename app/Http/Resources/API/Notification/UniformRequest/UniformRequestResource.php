<?php

namespace App\Http\Resources\API\Notification\UniformRequest;

use App\Models\UniformRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UniformRequestResource extends JsonResource
{
    public static $wrap = null;
    public $resource = UniformRequest::class;
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $rejectedAt = is_null($this->rejected_at) ? null : Carbon::parse($this->rejected_at)->getPreciseTimestamp(3);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'additional_notes' => $this->additional_notes,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->getPreciseTimestamp(3),
            'updated_at' => Carbon::parse($this->updated_at)->getPreciseTimestamp(3),
            'rejected_at' => $rejectedAt,
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}