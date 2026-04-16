<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class GuardianResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? mb_strtolower(trim((string) $this->email)) : null,
        ]);
    }
}
