<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class KpiFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isSchool() || isInstructor() || isAdmin();
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ];
    }
}
