<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use App\Models\School;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AuthUserResource extends JsonResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;
        $school = getSchool($user);
        $schoolPermissions = $school instanceof School ? $school->getResolvedSchoolPermissions() : [];
        $enabledSchoolPermissions = collect($schoolPermissions)
            ->filter(fn (bool $enabled) => $enabled)
            ->keys();

        $permissions = $user->getAllPermissions()
            ->pluck('name')
            ->merge($enabledSchoolPermissions)
            ->unique()
            ->values();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'school_id' => $school?->id,
            'school_name' => $school?->name,
            'school_slug' => $school?->slug,
            'school_logo' => $school?->logo_file,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $permissions,
            'school_permissions' => $schoolPermissions,
            'system_notify' => $school?->hasSchoolPermission('school.feature.system_notify') ?? false,
        ];
    }
}
