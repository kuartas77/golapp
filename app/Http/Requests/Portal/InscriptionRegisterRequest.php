<?php

namespace App\Http\Requests\Portal;

use App\Models\School;
use App\Rules\UniqueGuardianEmail;
use App\Service\Contracts\ContractTemplateService;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class InscriptionRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = $this->input('school_data');
        $documentPresenceRule = $school instanceof School && $school->send_documents
            ? 'required'
            : 'nullable';

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
            'tutor_doc_exp' => ['required', 'string', 'max:100'],
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
            'photo' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png'])
                    ->extensions(['jpg', 'jpeg', 'png'])
                    ->max(3 * 1024),
            ],
            'player_document' => [
                $documentPresenceRule,
                $this->documentFileRule(),
            ],
            'medical_certificate' => [
                $documentPresenceRule,
                $this->documentFileRule(),
            ],
            'tutor_document' => [
                $documentPresenceRule,
                $this->documentFileRule(),
            ],
            'payment_receipt' => [
                'nullable',
                $this->documentFileRule(),
            ],

            'year' => ['required', 'string'],

            'signatureTutor' => ['nullable', 'string'],
            'signatureAlumno' => ['nullable', 'string'],
            'school_data' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $value instanceof School || ! $value->is_enable) {
                        $fail('La escuela no está disponible para inscripciones en el portal.');

                        return;
                    }

                    if (! $value->inscriptions_enabled) {
                        $fail('Las inscripciones están deshabilitadas para esta escuela en el portal.');
                    }
                },
            ],
        ];

        if ($this->shouldValidateRecaptcha()) {
            $rules['g-recaptcha-response'] = 'required|recaptchav3:inscriptions,0.5';
        }

        $contractTemplateService = app(ContractTemplateService::class);

        if ($school instanceof School && $school->create_contract) {
            $availableContracts = $contractTemplateService->availablePortalContracts($school);

            foreach ($contractTemplateService->acceptanceFields($availableContracts) as $field) {
                $rules[$field] = ['accepted'];
            }

            if ($contractTemplateService->requiresTutorSignature($availableContracts)) {
                $rules['signatureTutor'] = ['required', 'string'];
            }

            if ($contractTemplateService->requiresPlayerSignature($availableContracts)) {
                $rules['signatureAlumno'] = ['required', 'string'];
            }
        }

        return $rules;
    }

    private function documentFileRule(): File
    {
        return File::types(['jpg', 'jpeg', 'png', 'pdf'])
            ->extensions(['jpg', 'jpeg', 'png', 'pdf'])
            ->max(3 * 1024);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => categoriesName(Carbon::parse($this->date_birth)->year),
            'school_data' => School::firstWhere('slug', request()->segments()[3]),
            'tutor_doc' => $this->tutor_num_doc,
            'tutor_email' => filled($this->tutor_email) ? mb_strtolower(trim((string) $this->tutor_email)) : null,
        ]);
    }

    private function shouldValidateRecaptcha(): bool
    {
        return ! app()->environment('local')
            && filled(config('recaptchav3.sitekey'))
            && filled(config('recaptchav3.secret'));
    }
}
