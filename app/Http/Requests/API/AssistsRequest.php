<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AssistsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return isInstructor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'numeric'],
            'training_group_id' => ['required', 'numeric'],
            'month' => ['required', Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12])],
            'column' => ['required', 'string', Rule::in($this->getColumns())],
            'year' => ['nullable', 'numeric'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'year' => $this->year ?? now()->year,
            'school_id' => auth()->user()->school_id
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
            'assistance_twenty_five'
        ];
    }
}
