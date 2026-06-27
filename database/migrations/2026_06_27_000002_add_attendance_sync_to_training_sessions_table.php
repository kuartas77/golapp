<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->json('absence_inscription_ids')->nullable()->after('absences');
            $table->timestamp('attendance_synced_at')->nullable()->after('absence_inscription_ids');
            $table->index(['school_id', 'training_group_id', 'date'], 'training_sessions_group_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->dropIndex('training_sessions_group_date_index');
            $table->dropColumn(['absence_inscription_ids', 'attendance_synced_at']);
        });
    }
};
