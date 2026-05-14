<?php

namespace App\Http\Requests;

use App\Models\InscriptionCustomCharge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InscriptionCustomChargeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                InscriptionCustomCharge::STATUS_PENDING,
                InscriptionCustomCharge::STATUS_DUE,
                InscriptionCustomCharge::STATUS_PAID,
            ])],
            'due_date' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'value' => preg_replace('/[^0-9]/', '', (string) $this->input('value', 0)),
        ]);
    }
}
