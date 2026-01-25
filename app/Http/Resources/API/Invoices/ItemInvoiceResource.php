<?php

namespace App\Http\Resources\API\Invoices;

use App\Models\Invoice;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ItemInvoiceResource extends JsonResource
{
    public $resource = Invoice::class;
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
            'invoice_id' => $this->invoice_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'is_paid' => $this->is_paid,
            'description' => $this->description,
        ];
    }
}