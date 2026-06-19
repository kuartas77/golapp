<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date_birth' => ['nullable', 'date'],
            'identification_document' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', Rule::in(array_keys(config('variables.KEY_GENDERS', [])))],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'studies' => ['nullable', 'string', 'max:2000'],
            'references' => ['nullable', 'string', 'max:2000'],
            'contacts' => ['nullable', 'string', 'max:2000'],
            'experience' => ['nullable', 'string', 'max:5000'],
            'position' => ['nullable', 'string', 'max:50', Rule::in(array_keys(config('variables.KEY_POSITIONS_ASSIGNED', [])))],
            'aptitude' => ['nullable', 'string', 'max:2000']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // 
    }
}
