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
            'inscription_id' => ['required', 'integer', 'exists:inscriptions,id'],
            'period_a_id' => ['required', 'integer', 'exists:evaluation_periods,id'],
            'period_b_id' => ['required', 'integer', 'exists:evaluation_periods,id', 'different:period_a_id'],
        ];
    }
}
