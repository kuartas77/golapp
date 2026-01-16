<?php

namespace App\Http\Requests\Portal;

use App\Models\School;
use Jenssegers\Date\Date;
use Illuminate\Foundation\Http\FormRequest;

class InscriptionRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'g-recaptcha-response' => 'required|recaptchav3:inscriptions,0.5',
            // Step 1
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
            // Step 2
            'school' => ['required', 'string', 'max:50'],
            'degree' => ['required', 'string', 'max:50'],
            'jornada' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:50'],
            'municipality' => ['required', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:50'],
            'rh' => ['required', 'string', 'max:50'],
            'eps' => ['required', 'string', 'max:50'],
            'student_insurance' => ['nullable', 'string', 'max:50'],
            // Step 3
            'tutor_name' => ['required', 'string', 'max:50'],
            'tutor_doc' => ['required', 'string', 'max:50'],
            'tutor_relationship' => ['required', 'string', 'max:50'],
            'tutor_phone' => ['required', 'string', 'max:50'],
            'tutor_work' => ['required', 'string', 'max:50'],
            'tutor_position_held' => ['required', 'string', 'max:50'],
            'tutor_email' => ['required', 'string', 'max:50'],
            'dad_name' => ['nullable', 'string', 'max:50'],
            'dad_doc' => ['nullable', 'string', 'max:50'],
            'dad_phone' => ['nullable', 'string', 'max:50'],
            'dad_work' => ['nullable', 'string', 'max:50'],
            'relationship_dad' => ['nullable', 'numeric'],
            'mom_name' => ['nullable', 'string', 'max:50'],
            'mom_doc' => ['nullable', 'string', 'max:50'],
            'mom_phone' => ['nullable', 'string', 'max:50'],
            'mom_work' => ['nullable', 'string', 'max:50'],
            'relationship_mom' => ['nullable', 'numeric'],
            // Step 4
            'photo' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg'],
            'player_document' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'medical_certificate' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'tutor_document' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'payment_receipt' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],

            'year' => ['required', 'string'],

            'signatureTutor'  => ['nullable', 'string'],
            'signatureAlumno' => ['nullable', 'string'],
            'school_data' => ['required'],
        ];

        if (env('APP_ENV', null) == 'local') {
            unset($rules['g-recaptcha-response']);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => Date::parse($this->date_birth)->year,
            'school_data' => School::firstWhere('slug', request()->segments()[1])
        ]);

    }
}