<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Models\User;
use App\Service\School\CurrentSchoolContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

final class AuthUserContext
{
    private const CACHE_TTL_MINUTES = 10;

    public function get(User $user): array
    {
        $schoolId = $this->selectedSchoolId($user);
        $cacheKey = sprintf(
            'auth-user-context:%d:%d:u%d:s%d',
            $user->id,
            $schoolId,
            $this->version(self::userVersionKey($user->id)),
            $this->version(self::schoolVersionKey($schoolId)),
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => $this->resolve($user),
        );
    }

    public static function forgetUser(int $userId): void
    {
        self::incrementVersion(self::userVersionKey($userId));
    }

    public static function forgetSchool(int $schoolId): void
    {
        self::incrementVersion(self::schoolVersionKey($schoolId));
    }

    private function resolve(User $user): array
    {
        $school = getSchool($user);
        $user->loadMissing(['permissions', 'roles.permissions']);

        $schoolPermissions = $school->getResolvedSchoolPermissions();
        $enabledSchoolPermissions = collect($schoolPermissions)
            ->filter(fn (bool $enabled) => $enabled)
            ->keys();

        $permissions = $user->getAllPermissions()
            ->pluck('name')
            ->merge($enabledSchoolPermissions)
            ->unique()
            ->values()
            ->all();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'school_slug' => $school->slug,
            'school_logo' => $school->logo_file,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $permissions,
            'school_permissions' => $schoolPermissions,
            'system_notify' => $school->hasSchoolPermission('school.feature.system_notify'),
        ];
    }

    private function version(string $key): int
    {
        return max(1, (int) Cache::get($key, 1));
    }

    private function selectedSchoolId(User $user): int
    {
        $value = Session::get(CurrentSchoolContext::SESSION_KEY, $user->school_id);
        $schoolId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $schoolId !== false ? (int) $schoolId : max(1, (int) $user->school_id);
    }

    private static function incrementVersion(string $key): void
    {
        Cache::forever($key, max(1, (int) Cache::get($key, 1)) + 1);
    }

    private static function userVersionKey(int $userId): string
    {
        return "auth-user-context-version:user:{$userId}";
    }

    private static function schoolVersionKey(int $schoolId): string
    {
        return "auth-user-context-version:school:{$schoolId}";
    }
}
