<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'tutor_platform')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('tutor_platform')->default(false)->after('is_enable');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'tutor_platform')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('tutor_platform');
            });
        }
    }
};
