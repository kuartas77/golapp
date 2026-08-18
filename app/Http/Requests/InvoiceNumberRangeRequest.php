<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceNumberRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'resolution_number' => ['required', 'string', 'max:100'],
            'resolution_date' => ['required', 'date'],
            'prefix' => ['nullable', 'regex:/^[A-Z0-9]{1,4}$/'],
            'range_start' => ['required', 'integer', 'min:1'],
            'range_end' => ['required', 'integer', 'gte:range_start'],
            'next_number' => ['required', 'integer', 'gte:range_start', 'lte:range_end'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'technical_key' => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $prefix = strtoupper(trim((string) $this->input('prefix')));
        $this->merge(['prefix' => $prefix !== '' ? $prefix : null]);
    }
}
