<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\InventoryMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([
                InventoryMovement::TYPE_ENTRY,
                InventoryMovement::TYPE_EXIT,
                InventoryMovement::TYPE_ADJUSTMENT,
            ])],
            'quantity' => ['required', 'integer', 'min:0'],
            'price_snapshot' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'movement_date' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->input('quantity', 0),
            'price_snapshot' => $this->normalizeNumber($this->input('price_snapshot')),
            'movement_date' => $this->input('movement_date') ?: now()->toDateString(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('type') !== InventoryMovement::TYPE_ADJUSTMENT && (int) $this->input('quantity') < 1) {
                $validator->errors()->add('quantity', 'La cantidad debe ser mayor a cero.');
            }
        });
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (is_string($value)) {
            return preg_replace('/[^0-9.]/', '', $value);
        }

        return $value;
    }
}
