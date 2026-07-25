<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TournamentUpdateRequest extends FormRequest
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
            'name' => 'required',
            'sport' => ['required', 'string', Rule::in($sports)],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Str::upper($this->name),
            'sport' => $this->input('sport', config('sports.default_sport', 'football')),
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
