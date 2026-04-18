<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const BROTHER_MONTHLY_PAYMENT = 'BROTHER_MONTHLY_PAYMENT';

    private const MONTHLY_PAYMENT = 'MONTHLY_PAYMENT';

    public function up(): void
    {
        $now = now();

        DB::table('settings')->updateOrInsert(
            ['key' => self::BROTHER_MONTHLY_PAYMENT],
            [
                'public' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $schoolIds = DB::table('schools')
            ->orderBy('id')
            ->pluck('id');

        foreach ($schoolIds as $schoolId) {
            $monthlyPayment = DB::table('setting_values')
                ->where('school_id', $schoolId)
                ->where('setting_key', self::MONTHLY_PAYMENT)
                ->value('value') ?? '50000';

            DB::table('setting_values')->updateOrInsert(
                [
                    'school_id' => $schoolId,
                    'setting_key' => self::BROTHER_MONTHLY_PAYMENT,
                ],
                [
                    'value' => (string) $monthlyPayment,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('setting_values')
            ->where('setting_key', self::BROTHER_MONTHLY_PAYMENT)
            ->delete();

        DB::table('settings')
            ->where('key', self::BROTHER_MONTHLY_PAYMENT)
            ->delete();
    }
};
