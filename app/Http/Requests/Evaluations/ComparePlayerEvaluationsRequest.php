<?php

namespace App\Http\Requests\Evaluations;

use Illuminate\Foundation\Http\FormRequest;

class ComparePlayerEvaluationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_a_id' => ['required', 'integer', 'exists:evaluation_periods,id', 'different:period_b_id'],
            'period_b_id' => ['required', 'integer', 'exists:evaluation_periods,id'],
        ];
    }
}
