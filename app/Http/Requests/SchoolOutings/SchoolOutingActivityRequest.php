<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolOutings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolOutingActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        $outing = $this->route('outing');
        $activity = $this->route('activity');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_outing_activities', 'name')
                    ->where('school_outing_id', $outing?->id ?? $outing)
                    ->ignore($activity?->id ?? $activity)
                    ->withoutTrashed(),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
