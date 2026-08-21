<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DebtNotificationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'string', Rule::in(array_values(config('variables.KEY_INDEX_MONTHS', [])))],
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'training_group_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'Selecciona el mes que deseas consultar.',
            'month.in' => 'El mes seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = $this->input('search');

        if (is_array($search)) {
            $value = trim((string) data_get($search, 'value', ''));

            $this->merge([
                'search' => $value !== '' ? $value : null,
            ]);
        }
    }
}
