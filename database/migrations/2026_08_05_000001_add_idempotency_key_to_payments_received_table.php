<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments_received', function (Blueprint $table) {
            $table->string('idempotency_key', 64)
                ->nullable()
                ->unique()
                ->after('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments_received', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
