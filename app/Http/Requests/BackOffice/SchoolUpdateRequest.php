<?php

namespace App\Http\Requests\BackOffice;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SchoolUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::exists('schools', 'name')],
            'agent' => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'string'],
            'is_enable' => ['required', 'bool'],
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg'],
            'logo_min' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
        ];
    }
}
