<?php

namespace App\Http\Resources\API\Notification\UniformRequest;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UniformRequestStatistcsResource extends JsonResource
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
        return [
            'total' => $this->count(),
            'pending' => $this->where('status', 'PENDING')->count(),
            'approved' => $this->where('status', 'APPROVED')->count(),
            'cancelled' => $this->where('status', 'CANCELLED')->count(),
        ];
    }
}
