<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->boolean('training_group_monthly_payment_enabled')
                ->default(false)
                ->after('send_debt_notifications');
        });

        Schema::table('training_groups', function (Blueprint $table): void {
            $table->unsignedInteger('monthly_payment_amount')
                ->nullable()
                ->after('is_complementary');
        });
    }

    public function down(): void
    {
        Schema::table('training_groups', function (Blueprint $table): void {
            $table->dropColumn('monthly_payment_amount');
        });

        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn('training_group_monthly_payment_enabled');
        });
    }
};
