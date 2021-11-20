<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsistUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'assistance_one' => ['nullable', 'string'],
            'assistance_two' => ['nullable', 'string'],
            'assistance_three' => ['nullable', 'string'],
            'assistance_four' => ['nullable', 'string'],
            'assistance_five' => ['nullable', 'string'],
            'assistance_six' => ['nullable', 'string'],
            'assistance_seven' => ['nullable', 'string'],
            'assistance_eight' => ['nullable', 'string'],
            'assistance_nine' => ['nullable', 'string'],
            'assistance_ten' => ['nullable', 'string'],
            'assistance_eleven' => ['nullable', 'string'],
            'assistance_twelve' => ['nullable', 'string'],
            'assistance_thirteen' => ['nullable', 'string'],
            'assistance_fourteen' => ['nullable', 'string'],
            'assistance_fifteen' => ['nullable', 'string'],
            'assistance_sixteen' => ['nullable', 'string'],
            'assistance_seventeen' => ['nullable', 'string'],
            'assistance_eighteen' => ['nullable', 'string'],
            'assistance_nineteen' => ['nullable', 'string'],
            'assistance_twenty' => ['nullable', 'string'],
            'assistance_twenty_one' => ['nullable', 'string'],
            'assistance_twenty_two' => ['nullable', 'string'],
            'assistance_twenty_three' => ['nullable', 'string'],
            'assistance_twenty_four' => ['nullable', 'string'],
            'assistance_twenty_five' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => auth()->user()->school->id
        ]);
    }
}
