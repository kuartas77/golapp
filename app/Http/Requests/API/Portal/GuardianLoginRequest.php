<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use Illuminate\Foundation\Http\FormRequest;

class GuardianLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ];

        if ($this->shouldValidateRecaptcha()) {
            $rules['g-recaptcha-response'] = ['required', 'recaptchav3:guardian_login,0.5'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? mb_strtolower(trim((string) $this->email)) : null,
        ]);
    }

    private function shouldValidateRecaptcha(): bool
    {
        return !app()->environment('local')
            && filled(config('recaptchav3.sitekey'))
            && filled(config('recaptchav3.secret'));
    }
}
