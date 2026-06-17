<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        $schoolId = getSchool(auth()->user())->id;
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_products', 'sku')
                    ->where('school_id', $schoolId)
                    ->ignore($productId)
                    ->withoutTrashed(),
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'entry_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => blank($this->input('sku')) ? null : trim((string) $this->input('sku')),
            'entry_price' => $this->normalizeNumber($this->input('entry_price')),
            'unit_price' => $this->normalizeNumber($this->input('unit_price')),
            'stock_quantity' => $this->input('stock_quantity', 0),
            'minimum_stock' => $this->input('minimum_stock', 0),
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        ]);
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (is_string($value)) {
            return preg_replace('/[^0-9.]/', '', $value);
        }

        return $value;
    }
}
