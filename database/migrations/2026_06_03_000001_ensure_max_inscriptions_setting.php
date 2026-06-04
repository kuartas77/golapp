<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->updateOrInsert(
            ['key' => Setting::MAX_INSCRIPTIONS],
            ['public' => false, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('setting_values')
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->where('value', '20')
            ->update([
                'value' => '200',
                'updated_at' => $now,
            ]);

        $configuredSchoolIds = DB::table('setting_values')
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->pluck('school_id');

        DB::table('schools')
            ->whereNotIn('id', $configuredSchoolIds)
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($schoolId) use ($now): void {
                DB::table('setting_values')->insert([
                    'school_id' => $schoolId,
                    'setting_key' => Setting::MAX_INSCRIPTIONS,
                    'value' => '200',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('setting_values')
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->where('value', '200')
            ->update([
                'value' => '20',
                'updated_at' => now(),
            ]);

    }
};
