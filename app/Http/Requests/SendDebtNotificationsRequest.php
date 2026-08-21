<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendDebtNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'string', Rule::in(array_values(config('variables.KEY_INDEX_MONTHS', [])))],
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'Selecciona el mes de la deuda.',
            'month.in' => 'El mes seleccionado no es válido.',
            'payment_ids.required' => 'Selecciona al menos un deportista.',
            'payment_ids.min' => 'Selecciona al menos un deportista.',
        ];
    }
}
