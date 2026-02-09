<?php

namespace App\Http\Resources\API\Notification\Invoices;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

class InvoiceResource extends JsonResource
{
    public static $wrap = null;
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
            'invoice_id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'amount' => $this->total_amount,
            'status' => Str::upper($this->status), //PENDING, PARTIAL, PAID, CANCELLED
            'image_url' => null,
            'due_date' => Carbon::parse($this->due_date)->getPreciseTimestamp(3),
            'created_at' => Carbon::parse($this->created_at)->getPreciseTimestamp(3),
            'updated_at' => Carbon::parse($this->updated_at)->getPreciseTimestamp(3),
            'items' => $this->whenLoaded('items', new ItemInvoiceCollection($this->items))
        ];
    }
}