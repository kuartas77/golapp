<?php

namespace App\Http\Requests\Portal;

use Jenssegers\Date\Date;
use Illuminate\Foundation\Http\FormRequest;

class PlayerPortalUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg'],
            'names' => ['required', 'string', 'max:50'],
            'last_names' => ['required', 'string', 'max:50'],
            'date_birth' => ['required', 'date_format:Y-m-d'],
            'place_birth' => ['required', 'string', 'max:100'],
            'identification_document' => ['required', 'string', 'max:50'],
            'document_type' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email:rfc,dns'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'medical_history' => ['nullable', 'string', 'max:200'],
            'category' => ['required'],

            'school' => ['required', 'string', 'max:50'],
            'degree' => ['required', 'string', 'max:50'],
            'jornada' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:50'],
            'municipality' => ['required', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:50'],
            'rh' => ['required', 'string', 'max:50'],
            'eps' => ['required', 'string', 'max:50'],
            'student_insurance' => ['nullable', 'string', 'max:50'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => categoriesName(Date::parse($this->date_birth)->year),
            'school_id' => auth()->user()->school_id
        ]);
    }
}