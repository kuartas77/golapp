<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\User;
use App\Support\SchoolModuleAccess;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

trait ValidatesViewerModules
{
    protected function viewerModuleRules(): array
    {
        return [
            'viewer_modules' => [
                Rule::requiredIf(fn (): bool => $this->selectedRoleIsViewer()),
                'array',
                Rule::when($this->selectedRoleIsViewer(), ['min:1']),
            ],
            'viewer_modules.*' => ['string', 'distinct', Rule::in(SchoolModuleAccess::keys())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->selectedRoleIsViewer() || $validator->errors()->has('viewer_modules')) {
                return;
            }

            $school = getSchool($this->user());
            $target = $this->route('user');
            $existingModules = $target instanceof User
                ? SchoolModuleAccess::viewerModules($target)
                : [];

            foreach ($this->input('viewer_modules', []) as $moduleKey) {
                if ($school->hasSchoolPermission($moduleKey) || in_array($moduleKey, $existingModules, true)) {
                    continue;
                }

                $validator->errors()->add(
                    'viewer_modules',
                    'No puedes asignar un módulo que no está habilitado para la escuela.'
                );
                break;
            }
        });
    }

    private function selectedRoleIsViewer(): bool
    {
        return Role::query()
            ->whereKey($this->input('rol_id'))
            ->where('name', User::VIEWER)
            ->exists();
    }
}
