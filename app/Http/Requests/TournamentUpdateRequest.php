<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => 'required',
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Str::upper($this->name),
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
