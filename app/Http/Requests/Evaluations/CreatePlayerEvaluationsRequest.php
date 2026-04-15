<?php

namespace App\Http\Requests\Evaluations;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlayerEvaluationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inscription_id' => ['required', 'exists:inscriptions,id'],
            'evaluation_period_id' => ['required', 'exists:evaluation_periods,id'],
            'evaluation_template_id' => ['required', 'exists:evaluation_templates,id'],
        ];
    }
}
