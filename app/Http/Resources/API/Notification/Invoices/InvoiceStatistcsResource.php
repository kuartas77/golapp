<?php

namespace App\Http\Resources\API\Notification\Invoices;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class InvoiceStatistcsResource extends JsonResource
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
            'pending' => $this->where('status', 'pending')->count(),
            'paid' => $this->where('status', 'paid')->count(),
            'partial' => $this->where('status', 'partial')->count(),
            'cancelled' => $this->where('status', 'cancelled')->count(),
            'total_amount' => $this->where('status', 'paid')->sum('total_amount'),
        ];
    }
}
