<?php

namespace App\Http\Resources\API\Notification\Invoices;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class InvoiceCollection extends ResourceCollection
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
            return new InvoiceResource($item);
        })->toArray();
    }
}