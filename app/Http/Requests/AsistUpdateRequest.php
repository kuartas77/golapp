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
            'id' => ['required'],
            'assistance_one' => ['nullable', 'numeric'],
            'assistance_two' => ['nullable', 'numeric'],
            'assistance_three' => ['nullable', 'numeric'],
            'assistance_four' => ['nullable', 'numeric'],
            'assistance_five' => ['nullable', 'numeric'],
            'assistance_six' => ['nullable', 'numeric'],
            'assistance_seven' => ['nullable', 'numeric'],
            'assistance_eight' => ['nullable', 'numeric'],
            'assistance_nine' => ['nullable', 'numeric'],
            'assistance_ten' => ['nullable', 'numeric'],
            'assistance_eleven' => ['nullable', 'numeric'],
            'assistance_twelve' => ['nullable', 'numeric'],
            'assistance_thirteen' => ['nullable', 'numeric'],
            'assistance_fourteen' => ['nullable', 'numeric'],
            'assistance_fifteen' => ['nullable', 'numeric'],
            'assistance_sixteen' => ['nullable', 'numeric'],
            'assistance_seventeen' => ['nullable', 'numeric'],
            'assistance_eighteen' => ['nullable', 'numeric'],
            'assistance_nineteen' => ['nullable', 'numeric'],
            'assistance_twenty' => ['nullable', 'numeric'],
            'assistance_twenty_one' => ['nullable', 'numeric'],
            'assistance_twenty_two' => ['nullable', 'numeric'],
            'assistance_twenty_three' => ['nullable', 'numeric'],
            'assistance_twenty_four' => ['nullable', 'numeric'],
            'assistance_twenty_five' => ['nullable', 'numeric'],
            'observations' => ['nullable', 'string'],
            'attendance_date' => ['nullable', 'string'],
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
