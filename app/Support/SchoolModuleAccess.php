<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\School;
use App\Models\User;

final class SchoolModuleAccess
{
    public const PERMISSION_PREFIX = 'backoffice.module.';

    public const PERMISSION_SUFFIX = '.view';

    public static function catalog(): array
    {
        return collect(School::permissionCatalog())
            ->filter(fn (array $definition, string $key): bool => str_starts_with($key, 'school.module.'))
            ->all();
    }

    public static function keys(): array
    {
        return array_keys(self::catalog());
    }

    public static function permissionName(string $moduleKey): string
    {
        return self::PERMISSION_PREFIX
            .str_replace('school.module.', '', $moduleKey)
            .self::PERMISSION_SUFFIX;
    }

    public static function permissionNames(): array
    {
        return array_map(self::permissionName(...), self::keys());
    }

    public static function viewerModules(User $user): array
    {
        $permissionNames = $user->permissions()
            ->whereIn('name', self::permissionNames())
            ->pluck('name')
            ->all();

        $assigned = array_flip($permissionNames);

        return array_values(array_filter(
            self::keys(),
            fn (string $key): bool => isset($assigned[self::permissionName($key)])
        ));
    }

    public static function canView(User $user, School $school, string $moduleKey): bool
    {
        if (! array_key_exists($moduleKey, self::catalog()) || ! $school->hasSchoolPermission($moduleKey)) {
            return false;
        }

        return ! $user->hasRole(User::VIEWER)
            || $user->hasPermissionTo(self::permissionName($moduleKey));
    }
}
