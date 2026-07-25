<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $sports = getSchool(auth()->user())->enabledSportsSupportingModule('competition_groups');

        return [
            'name' => ['required'],
            'year' => ['required'],
            'tournament_id' => [
                'required',
                Rule::exists('tournaments', 'id')->where(fn ($query) => $query
                    ->where('school_id', getSchool(auth()->user())->id)
                    ->where('sport', $this->input('sport', config('sports.default_sport', 'football')))),
            ],
            'user_id' => ['required'],
            'category' => ['required'],
            'school_id' => ['required'],
            'sport' => ['required', 'string', Rule::in($sports)],
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
            'school_id' => getSchool(auth()->user())->id,
            'category' => $this->year,
            'sport' => $this->input('sport', config('sports.default_sport', 'football')),
        ]);
    }
}
