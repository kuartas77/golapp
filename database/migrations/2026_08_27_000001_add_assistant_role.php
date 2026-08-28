<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['super-admin', 'school', 'instructor'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        Role::query()->firstOrCreate([
            'name' => User::ASSISTANT,
            'guard_name' => 'web',
        ]);
    }

    public function down(): void
    {
        $role = Role::query()
            ->where('name', User::ASSISTANT)
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            return;
        }

        $rolePivotKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $hasAssignments = DB::table(config('permission.table_names.model_has_roles'))
            ->where($rolePivotKey, $role->id)
            ->exists();

        if (! $hasAssignments) {
            $role->delete();
        }
    }
};
