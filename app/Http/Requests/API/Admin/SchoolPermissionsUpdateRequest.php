<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SchoolPermissionsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $permissions = $this->input('permissions', []);
            $allowedPermissions = array_keys(School::permissionCatalog());
            $unknownPermissions = array_diff(array_keys($permissions), $allowedPermissions);

            if (!empty($unknownPermissions)) {
                $validator->errors()->add(
                    'permissions',
                    'Se encontraron permisos de escuela no válidos.'
                );
            }
        });
    }
}
