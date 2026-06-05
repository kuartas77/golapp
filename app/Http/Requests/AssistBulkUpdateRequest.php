<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssistBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assist_ids' => ['required', 'array', 'min:1'],
            'assist_ids.*' => ['required', 'integer', 'distinct'],
            'training_group_id' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'column' => ['required', 'string', Rule::in($this->getColumns())],
            'value' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'school_id' => ['required', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
        ]);
    }

    private function getColumns(): array
    {
        return [
            'assistance_one',
            'assistance_two',
            'assistance_three',
            'assistance_four',
            'assistance_five',
            'assistance_six',
            'assistance_seven',
            'assistance_eight',
            'assistance_nine',
            'assistance_ten',
            'assistance_eleven',
            'assistance_twelve',
            'assistance_thirteen',
            'assistance_fourteen',
            'assistance_fifteen',
            'assistance_sixteen',
            'assistance_seventeen',
            'assistance_eighteen',
            'assistance_nineteen',
            'assistance_twenty',
            'assistance_twenty_one',
            'assistance_twenty_two',
            'assistance_twenty_three',
            'assistance_twenty_four',
            'assistance_twenty_five',
        ];
    }
}
