<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology_records', function (Blueprint $table): void {
            $table->json('diagram_media')->nullable()->after('diagrams');
        });

        Schema::table('training_session_phases', function (Blueprint $table): void {
            $table->string('visual_mode', 20)->default('diagram')->after('diagram');
            $table->string('image_path')->nullable()->after('visual_mode');
        });
    }

    public function down(): void
    {
        Schema::table('training_session_phases', function (Blueprint $table): void {
            $table->dropColumn(['visual_mode', 'image_path']);
        });

        Schema::table('methodology_records', function (Blueprint $table): void {
            $table->dropColumn('diagram_media');
        });
    }
};
