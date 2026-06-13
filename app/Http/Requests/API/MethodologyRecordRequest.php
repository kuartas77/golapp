<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use App\Models\MethodologyRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MethodologyRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool() || isInstructor();
    }

    public function rules(): array
    {
        $schoolId = getSchool(auth()->user())->id;

        return [
            'training_group_id' => [
                'nullable',
                'integer',
                Rule::exists('training_groups', 'id')->where(
                    fn ($query) => $query->where('school_id', $schoolId)
                ),
            ],
            'type' => ['required', 'string', Rule::in(MethodologyRecord::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['nullable'],
            'diagrams' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->input('type');

        $this->merge([
            'training_group_id' => $this->filled('training_group_id') ? (int) $this->input('training_group_id') : null,
            'title' => $this->normalizeString($this->input('title')),
            'fields' => $this->normalizeFields($this->input('fields', [])),
            'diagrams' => $type === MethodologyRecord::TYPE_PLANNING
                ? $this->input('diagrams', [])
                : null,
        ]);
    }

    private function normalizeFields(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        return collect($fields)
            ->map(fn ($value) => is_array($value) ? $this->normalizeFields($value) : $this->normalizeString($value))
            ->all();
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
