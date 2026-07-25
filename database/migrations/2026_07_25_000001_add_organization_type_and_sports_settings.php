<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'organization_type')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->string('organization_type', 32)
                    ->default('school')
                    ->after('name');
            });
        }

        DB::table('settings')->updateOrInsert(
            ['key' => Setting::SPORTS_ENABLED],
            ['public' => false]
        );

        $defaultSports = json_encode([config('sports.default_sport', 'football')], JSON_THROW_ON_ERROR);

        DB::table('schools')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($schools) use ($defaultSports): void {
                foreach ($schools as $school) {
                    DB::table('setting_values')->updateOrInsert(
                        [
                            'school_id' => $school->id,
                            'setting_key' => Setting::SPORTS_ENABLED,
                        ],
                        ['value' => $defaultSports]
                    );
                }
            });
    }

    public function down(): void
    {
        DB::table('setting_values')
            ->where('setting_key', Setting::SPORTS_ENABLED)
            ->delete();

        DB::table('settings')
            ->where('key', Setting::SPORTS_ENABLED)
            ->delete();

        if (Schema::hasColumn('schools', 'organization_type')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->dropColumn('organization_type');
            });
        }
    }
};
