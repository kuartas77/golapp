<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpsertContractTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'school']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'header' => ['required', 'string'],
            'body' => ['required', 'string'],
            'footer' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'header' => trim((string) $this->input('header')),
            'body' => trim((string) $this->input('body')),
            'footer' => trim((string) $this->input('footer')),
        ]);
    }
}
