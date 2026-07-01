<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\SettingValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRUNCATED_KEY = 'INSTRUCTOR_MONTHLY_EDIT_LOCK_ENA';

    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED],
            ['public' => false, 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('setting_values')
            ->where('setting_key', self::TRUNCATED_KEY)
            ->orderBy('id')
            ->get()
            ->each(function ($settingValue): void {
                SettingValue::query()->updateOrCreate(
                    [
                        'school_id' => $settingValue->school_id,
                        'setting_key' => Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED,
                    ],
                    [
                        'value' => $settingValue->value,
                    ]
                );
            });

        DB::table('setting_values')
            ->where('setting_key', self::TRUNCATED_KEY)
            ->delete();

        DB::table('settings')
            ->where('key', self::TRUNCATED_KEY)
            ->delete();

        DB::table('schools')
            ->select('id')
            ->chunkById(100, function ($schools): void {
                foreach ($schools as $school) {
                    SettingValue::query()->firstOrCreate(
                        [
                            'school_id' => $school->id,
                            'setting_key' => Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED,
                        ],
                        [
                            'value' => false,
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        SettingValue::query()
            ->where('setting_key', Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED)
            ->delete();

        DB::table('settings')
            ->where('key', Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED)
            ->delete();
    }
};
