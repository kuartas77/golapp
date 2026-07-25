<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminSchoolStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        $isCampus = $this->boolean('is_campus');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('schools', 'name')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('schools', 'slug')],
            'organization_type' => ['nullable', 'string', Rule::in(array_keys(config('sports.organization_types', [])))],
            'agent' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => array_values(array_filter([
                'required',
                'email',
                'max:255',
                $isCampus
                    ? Rule::exists('users', 'email')
                    : Rule::unique('schools', 'email'),
                $isCampus ? null : Rule::unique('users', 'email'),
            ])),
            'is_enable' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'max_inscriptions' => ['nullable', 'integer', 'min:0'],
            'is_campus' => ['nullable', 'boolean'],
            'create_contract' => ['nullable', 'boolean'],
            'send_documents' => ['nullable', 'boolean'],
            'send_monthly_payment_receipts' => ['nullable', 'boolean'],
            'tutor_platform' => ['nullable', 'boolean'],
            'sign_player' => ['nullable', 'boolean'],
            'inscriptions_enabled' => ['nullable', 'boolean'],
            'instructor_monthly_edit_lock_enabled' => ['nullable', 'boolean'],
            'enabled_sports' => ['nullable', 'array', 'min:1'],
            'enabled_sports.*' => ['string', 'distinct', Rule::in(array_keys(config('sports.sports', [])))],
            'multiple_schools' => array_values(array_filter([
                $isCampus ? 'required' : 'nullable',
                'array',
                $isCampus ? 'min:1' : null,
            ])),
            'multiple_schools.*' => ['integer', 'distinct', Rule::exists('schools', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('name')),
            'organization_type' => $this->input('organization_type', config('sports.default_organization_type', 'school')),
            'max_inscriptions' => $this->input('max_inscriptions', 200),
            'enabled_sports' => array_values(array_filter(
                Arr::wrap($this->input('enabled_sports', [config('sports.default_sport', 'football')])),
                static fn ($value) => $value !== null && $value !== ''
            )),
            'is_campus' => $this->boolean('is_campus'),
            'multiple_schools' => array_values(array_filter(
                Arr::wrap($this->input('multiple_schools', [])),
                static fn ($value) => $value !== null && $value !== ''
            )),
        ]);

        $this->merge($this->booleanPlatformOptions());
    }

    private function booleanPlatformOptions(): array
    {
        $data = [];

        foreach ([
            'create_contract',
            'send_documents',
            'send_monthly_payment_receipts',
            'tutor_platform',
            'sign_player',
            'inscriptions_enabled',
            'instructor_monthly_edit_lock_enabled',
        ] as $field) {
            if ($this->has($field)) {
                $data[$field] = $this->boolean($field);
            }
        }

        return $data;
    }
}
