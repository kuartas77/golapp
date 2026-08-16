<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('guardians')->check();
    }

    public function rules(): array
    {
        $guardian = auth('guardians')->user();

        return [
            'names' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:50',
                Rule::in([(string) $guardian?->email]),
            ],
            'profession' => ['nullable', 'string', 'max:50'],
            'business' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? mb_strtolower(trim((string) $this->email)) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'email.in' => 'El correo de acceso no se puede cambiar desde el perfil. Contacta a la escuela para actualizarlo de forma segura.',
        ];
    }
}
