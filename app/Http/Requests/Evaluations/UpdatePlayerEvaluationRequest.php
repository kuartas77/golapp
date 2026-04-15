<?php

namespace App\Http\Requests\Evaluations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'evaluation_period_id' => ['sometimes', 'integer', 'exists:evaluation_periods,id'],
            'evaluation_template_id' => ['sometimes', 'integer', 'exists:evaluation_templates,id'],
            'evaluation_type' => ['sometimes', Rule::in(['initial', 'periodic', 'final', 'special'])],
            'status' => ['sometimes', Rule::in(['draft', 'completed', 'closed'])],
            'evaluated_at' => ['nullable', 'date'],
            'general_comment' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'improvement_opportunities' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],

            'scores' => ['sometimes', 'array'],
            'scores.*.template_criterion_id' => [
                'required',
                'integer',
                'distinct',
                'exists:evaluation_template_criteria,id',
            ],
            'scores.*.score' => ['nullable', 'numeric'],
            'scores.*.scale_value' => ['nullable', 'string', 'max:50'],
            'scores.*.comment' => ['nullable', 'string'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
