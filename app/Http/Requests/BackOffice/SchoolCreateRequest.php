<?php

namespace App\Http\Requests\BackOffice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole('super-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email', Rule::unique('schools', 'email'), Rule::unique('users', 'email')],
            // 'password' => ['nullable', 'confirmed'], //Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'name' => ['required', 'string'],
            'agent' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'slug' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->name),
        ]);
    }
}
