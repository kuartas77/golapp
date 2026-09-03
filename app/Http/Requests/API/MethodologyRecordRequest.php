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
            'fields.session_date' => ['required', 'date_format:Y-m-d'],
            'fields.*' => ['nullable'],
            'diagrams' => ['nullable', 'array'],
            'diagram_media' => ['nullable', 'array'],
            'diagram_media.*.mode' => ['nullable', 'string', Rule::in(['diagram', 'image'])],
            'diagram_media.*.image_remove' => ['nullable', 'boolean'],
            'diagram_images' => ['nullable', 'array'],
            'diagram_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->input('type');
        $fields = $this->normalizeFields($this->input('fields', []));

        $this->merge([
            'training_group_id' => $this->filled('training_group_id') ? (int) $this->input('training_group_id') : null,
            'title' => $this->normalizeString($this->input('title')),
            'fields' => $this->normalizeReportMonth($type, $fields),
            'diagrams' => $type === MethodologyRecord::TYPE_PLANNING
                ? $this->input('diagrams', [])
                : null,
            'diagram_media' => $type === MethodologyRecord::TYPE_PLANNING
                ? $this->input('diagram_media', [])
                : null,
        ]);
    }

    private function normalizeFields(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        return collect($fields)
            ->map(fn ($value) => is_array($value) ? $this->normalizeFields($value) : $this->normalizeString($value))
            ->all();
    }

    private function normalizeReportMonth(string $type, array $fields): array
    {
        if (! in_array($type, [
            MethodologyRecord::TYPE_MONTHLY_REPORT,
            MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
        ], true)) {
            return $fields;
        }

        $month = (int) substr((string) ($fields['session_date'] ?? ''), 5, 2);
        $fields['report_month'] = config("variables.KEY_MONTHS_INDEX.{$month}");

        return $fields;
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
