<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
            'token' => ['required', 'string', 'max:512'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'platform' => is_string($this->platform) ? mb_strtolower(trim($this->platform)) : $this->platform,
            'token' => is_string($this->token) ? trim($this->token) : $this->token,
        ]);
    }
}
