<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use App\Rules\UniqueGuardianEmail;
use Illuminate\Foundation\Http\FormRequest;

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
                new UniqueGuardianEmail(ignoreGuardianId: $guardian?->id),
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
}
