<?php

namespace App\Http\Requests\Evaluations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class UpsertEvaluationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'training_group_id' => [
                'nullable',
                'integer',
                Rule::exists('training_groups', 'id')->where(fn ($query) => $query->where('school_id', $this->currentSchoolId())),
            ],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.dimension' => ['required', 'string', 'max:100'],
            'criteria.*.name' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
            'criteria.*.score_type' => ['required', Rule::in(['numeric', 'scale'])],
            'criteria.*.min_score' => ['nullable', 'numeric'],
            'criteria.*.max_score' => ['nullable', 'numeric'],
            'criteria.*.weight' => ['required', 'numeric', 'gt:0'],
            'criteria.*.sort_order' => ['required', 'integer', 'min:1'],
            'criteria.*.is_required' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('criteria', []) as $index => $criterion) {
                $scoreType = data_get($criterion, 'score_type');
                $minScore = data_get($criterion, 'min_score');
                $maxScore = data_get($criterion, 'max_score');
                $baseKey = "criteria.{$index}";

                if ($scoreType === 'numeric') {
                    if ($minScore === null || $minScore === '') {
                        $validator->errors()->add("{$baseKey}.min_score", 'El puntaje mínimo es obligatorio para criterios numéricos.');
                    }

                    if ($maxScore === null || $maxScore === '') {
                        $validator->errors()->add("{$baseKey}.max_score", 'El puntaje máximo es obligatorio para criterios numéricos.');
                    }

                    if (
                        $minScore !== null && $minScore !== ''
                        && $maxScore !== null && $maxScore !== ''
                        && (float) $maxScore < (float) $minScore
                    ) {
                        $validator->errors()->add("{$baseKey}.max_score", 'El puntaje máximo debe ser mayor o igual al mínimo.');
                    }
                }

                if ($scoreType === 'scale' && (($minScore !== null && $minScore !== '') || ($maxScore !== null && $maxScore !== ''))) {
                    $validator->errors()->add("{$baseKey}.score_type", 'Los criterios de escala no deben enviar rango numérico.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $criteria = collect($this->input('criteria', []))
            ->map(function ($criterion, $index) {
                $scoreType = data_get($criterion, 'score_type', 'numeric');

                return [
                    'dimension' => trim((string) data_get($criterion, 'dimension', '')),
                    'name' => trim((string) data_get($criterion, 'name', '')),
                    'description' => $this->normalizeNullableString(data_get($criterion, 'description')),
                    'score_type' => $scoreType,
                    'min_score' => $scoreType === 'scale' ? null : $this->normalizeNullableNumber(data_get($criterion, 'min_score')),
                    'max_score' => $scoreType === 'scale' ? null : $this->normalizeNullableNumber(data_get($criterion, 'max_score')),
                    'weight' => $this->normalizeNullableNumber(data_get($criterion, 'weight')),
                    'sort_order' => (int) data_get($criterion, 'sort_order', $index + 1),
                    'is_required' => filter_var(data_get($criterion, 'is_required', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'description' => $this->normalizeNullableString($this->input('description')),
            'training_group_id' => $this->normalizeNullableInteger($this->input('training_group_id')),
            'year' => (int) $this->input('year'),
            'criteria' => $criteria,
        ]);
    }

    private function currentSchoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }

    private function normalizeNullableInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeNullableNumber($value): float|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_int($value) ? $value : (float) $value;
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
