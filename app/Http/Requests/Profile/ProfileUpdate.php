<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

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
            'date_birth' => 'nullable',
            'identification_document' => 'nullable',
            'gender' => 'nullable',
            'address' => 'nullable',
            'phone' => 'nullable',
            'mobile' => 'nullable',
            'studies' => 'nullable',
            'references' => 'nullable',
            'contacts' => 'nullable',
            'experience' => 'nullable',
            'position' => 'nullable',
            'aptitude' => 'nullable',
        ];
    }
}
