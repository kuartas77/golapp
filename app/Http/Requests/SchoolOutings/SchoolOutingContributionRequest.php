<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolOutings;

use Illuminate\Foundation\Http\FormRequest;

class SchoolOutingContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'school_outing_participant_id' => ['required', 'integer', 'exists:school_outing_participants,id'],
            'school_outing_activity_id' => ['required', 'integer', 'exists:school_outing_activities,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'contribution_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => $this->normalizeNumber($this->input('amount')),
            'notes' => blank($this->input('notes')) ? null : $this->input('notes'),
        ]);
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (is_string($value)) {
            return preg_replace('/[^0-9.]/', '', $value);
        }

        return $value;
    }
}
