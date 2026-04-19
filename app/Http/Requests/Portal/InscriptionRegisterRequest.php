<?php

namespace App\Http\Requests\Portal;

use App\Models\School;
use App\Rules\UniqueGuardianEmail;
use Closure;
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
            // Step 1
            'names' => ['required', 'string', 'max:50'],
            'last_names' => ['required', 'string', 'max:50'],
            'date_birth' => ['required', 'date_format:Y-m-d'],
            'place_birth' => ['required', 'string', 'max:100'],
            'identification_document' => ['required', 'string', 'max:50'],
            'document_type' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email:rfc'],
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
            'tutor_email' => [
                'required',
                'string',
                'email:rfc',
                'max:50',
                new UniqueGuardianEmail($this->input('tutor_num_doc')),
            ],
            // Step 4
            'photo' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg'],
            'player_document' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'medical_certificate' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'tutor_document' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],
            'payment_receipt' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,application/pdf'],

            'year' => ['required', 'string'],

            'signatureTutor'  => ['nullable', 'string'],
            'signatureAlumno' => ['nullable', 'string'],
            'school_data' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (!$value instanceof School || !$value->is_enable) {
                        $fail('La escuela no está disponible para inscripciones en el portal.');
                        return;
                    }

                    if (!$value->inscriptions_enabled) {
                        $fail('Las inscripciones están deshabilitadas para esta escuela en el portal.');
                    }
                },
            ],
        ];

        if ($this->shouldValidateRecaptcha()) {
            $rules['g-recaptcha-response'] = 'required|recaptchav3:inscriptions,0.5';
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => Date::parse($this->date_birth)->year,
            'school_data' => School::firstWhere('slug', request()->segments()[3]),
            'tutor_doc' => $this->tutor_num_doc,
            'tutor_email' => filled($this->tutor_email) ? mb_strtolower(trim((string) $this->tutor_email)) : null,
        ]);

    }

    private function shouldValidateRecaptcha(): bool
    {
        return !app()->environment('local')
            && filled(config('recaptchav3.sitekey'))
            && filled(config('recaptchav3.secret'));
    }
}
