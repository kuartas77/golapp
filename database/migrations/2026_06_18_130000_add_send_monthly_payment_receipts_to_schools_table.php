<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'send_monthly_payment_receipts')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('send_monthly_payment_receipts')
                    ->default(false)
                    ->after('send_documents');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'send_monthly_payment_receipts')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('send_monthly_payment_receipts');
            });
        }
    }
};
