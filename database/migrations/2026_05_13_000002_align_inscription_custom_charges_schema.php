<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inscription_custom_charges') && ! Schema::hasColumn('inscription_custom_charges', 'deleted_at')) {
            Schema::table('inscription_custom_charges', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('invoice_items') && ! Schema::hasColumn('invoice_items', 'custom_charge_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('custom_charge_id')
                    ->nullable()
                    ->after('uniform_request_id')
                    ->constrained('inscription_custom_charges')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'custom_charge_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('custom_charge_id');
            });
        }

        if (Schema::hasTable('inscription_custom_charges') && Schema::hasColumn('inscription_custom_charges', 'deleted_at')) {
            Schema::table('inscription_custom_charges', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
