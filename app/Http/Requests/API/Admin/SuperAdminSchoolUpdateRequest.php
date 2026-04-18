<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
            'slug' => ['required', 'string', Rule::in([$school->slug])],
            'agent' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::in([$school->email])],
            'is_enable' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'is_campus' => ['nullable', 'boolean'],
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
            'is_campus' => $this->boolean('is_campus'),
            'multiple_schools' => array_values(array_filter(
                Arr::wrap($this->input('multiple_schools', [])),
                static fn ($value) => $value !== null && $value !== ''
            )),
        ]);
    }
}
