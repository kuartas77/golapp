<?php

use App\Models\Setting;
use App\Models\SettingValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMonthlyPaymentOptionsToInscriptions extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->string('monthly_payment_type')
                ->default(Setting::MONTHLY_PAYMENT)
                ->after('brother_payment');
            $table->unsignedInteger('monthly_payment_amount')
                ->nullable()
                ->after('monthly_payment_type');
        });

        foreach ([
            Setting::MONTHLY_PAYMENT_OPTION_1,
            Setting::MONTHLY_PAYMENT_OPTION_2,
            Setting::MONTHLY_PAYMENT_OPTION_3,
        ] as $key) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['public' => false, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $schoolIds = DB::table('schools')->pluck('id');

        foreach ($schoolIds as $schoolId) {
            $monthlyPayment = DB::table('setting_values')
                ->where('school_id', $schoolId)
                ->where('setting_key', Setting::MONTHLY_PAYMENT)
                ->value('value') ?? 50000;

            foreach ([
                Setting::MONTHLY_PAYMENT_OPTION_1,
                Setting::MONTHLY_PAYMENT_OPTION_2,
                Setting::MONTHLY_PAYMENT_OPTION_3,
            ] as $key) {
                SettingValue::query()->updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'setting_key' => $key,
                    ],
                    [
                        'value' => $monthlyPayment,
                    ]
                );
            }
        }

        DB::table('inscriptions')
            ->where('brother_payment', true)
            ->update(['monthly_payment_type' => Setting::BROTHER_MONTHLY_PAYMENT]);
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn(['monthly_payment_type', 'monthly_payment_amount']);
        });

        DB::table('setting_values')
            ->whereIn('setting_key', [
                Setting::MONTHLY_PAYMENT_OPTION_1,
                Setting::MONTHLY_PAYMENT_OPTION_2,
                Setting::MONTHLY_PAYMENT_OPTION_3,
            ])
            ->delete();

        DB::table('settings')
            ->whereIn('key', [
                Setting::MONTHLY_PAYMENT_OPTION_1,
                Setting::MONTHLY_PAYMENT_OPTION_2,
                Setting::MONTHLY_PAYMENT_OPTION_3,
            ])
            ->delete();
    }
}
