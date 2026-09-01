<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\SchoolModuleAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Role::query()->firstOrCreate([
            'name' => User::VIEWER,
            'guard_name' => 'web',
        ]);

        foreach (SchoolModuleAccess::permissionNames() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $viewer = Role::query()
            ->where('name', User::VIEWER)
            ->where('guard_name', 'web')
            ->first();

        if ($viewer) {
            $rolePivotKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
            $hasAssignments = DB::table(config('permission.table_names.model_has_roles'))
                ->where($rolePivotKey, $viewer->id)
                ->exists();

            if (! $hasAssignments) {
                $viewer->delete();
            }
        }

        $assignedPermissionIds = DB::table(config('permission.table_names.model_has_permissions'))
            ->pluck(config('permission.column_names.permission_pivot_key') ?: 'permission_id');

        Permission::query()
            ->whereIn('name', SchoolModuleAccess::permissionNames())
            ->whereNotIn('id', $assignedPermissionIds)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
