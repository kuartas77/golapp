<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('schools', 'auto_invoice')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table): void {
            $table->boolean('auto_invoice')->default(false)->after('inscriptions_enabled');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('schools', 'auto_invoice')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn('auto_invoice');
        });
    }
};
