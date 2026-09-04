<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use App\Models\School;
use App\Service\Category\CategoryFormatService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SuperAdminSchoolUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        /** @var School $school */
        $school = $this->route('school');
        $isCampus = $this->boolean('is_campus');

        return [
            'name' => ['required', 'string', Rule::in([$school->name])],
            'agent' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::in([$school->email])],
            'is_enable' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'max_inscriptions' => ['nullable', 'integer', 'min:0'],
            'is_campus' => ['nullable', 'boolean'],
            'create_contract' => ['nullable', 'boolean'],
            'send_documents' => ['nullable', 'boolean'],
            'send_monthly_payment_receipts' => ['nullable', 'boolean'],
            'send_invoice_receipts' => ['nullable', 'boolean'],
            'send_debt_notifications' => ['nullable', 'boolean'],
            'training_group_monthly_payment_enabled' => ['nullable', 'boolean'],
            'tutor_platform' => ['nullable', 'boolean'],
            'sign_player' => ['nullable', 'boolean'],
            'inscriptions_enabled' => ['nullable', 'boolean'],
            'instructor_monthly_edit_lock_enabled' => ['nullable', 'boolean'],
            'category_format' => ['sometimes', Rule::in(CategoryFormatService::FORMATS)],
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
            'send_invoice_receipts',
            'send_debt_notifications',
            'training_group_monthly_payment_enabled',
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
