<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

class TrainingGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isAdmin() || isSchool();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['required', 'array'],
            'name' => ['required'],
            'stage' => ['nullable'],
            'years' => ['required', 'array'],
            'year_two' => ['nullable'],
            'year_three' => ['nullable'],
            'year_four' => ['nullable'],
            'year_five' => ['nullable'],
            'year_six' => ['nullable'],
            'year_seven' => ['nullable'],
            'year_eight' => ['nullable'],
            'year_nine' => ['nullable'],
            'year_ten' => ['nullable'],
            'year_eleven' => ['nullable'],
            'year_twelve' => ['nullable'],
            'category' => ['nullable'],
            'days' => ['required', 'array'],
            'schedules' => ['required', 'array'],
            'school_id' => ['required'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
