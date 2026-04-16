<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use Illuminate\Foundation\Http\FormRequest;

class GuardianForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? mb_strtolower(trim((string) $this->email)) : null,
        ]);
    }
}
