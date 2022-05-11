<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionGroupRequest extends FormRequest
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
            'name' => ['required'],
            'year' => ['required'],
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'user_id' => ['required'],
            'category' => ['required'],
            'school_id' => ['required']
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
            'school_id' => auth()->user()->school_id,
            'category' => categoriesName((int)$this->year)
        ]);
    }
}
