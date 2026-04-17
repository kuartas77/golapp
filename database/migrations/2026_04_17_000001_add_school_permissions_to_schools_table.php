<?php

declare(strict_types=1);

use App\Models\School;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'school_permissions')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->json('school_permissions')->nullable()->after('inscriptions_enabled');
            });
        }

        $defaults = School::defaultSchoolPermissions();
        $hasSettingValues = Schema::hasTable('setting_values');

        DB::table('schools')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($schools) use ($defaults, $hasSettingValues) {
                foreach ($schools as $school) {
                    $permissions = $defaults;

                    if ($hasSettingValues) {
                        $systemNotify = DB::table('setting_values')
                            ->where('school_id', $school->id)
                            ->where('setting_key', Setting::SYSTEM_NOTIFY)
                            ->value('value');

                        $permissions['school.feature.system_notify'] = filter_var(
                            $systemNotify,
                            FILTER_VALIDATE_BOOLEAN
                        );
                    }

                    DB::table('schools')
                        ->where('id', $school->id)
                        ->update([
                            'school_permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'school_permissions')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('school_permissions');
            });
        }
    }
};
