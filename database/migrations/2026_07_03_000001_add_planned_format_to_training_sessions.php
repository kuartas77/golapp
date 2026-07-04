<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->string('format', 20)->default('standard')->after('training_group_id')->index();
        });

        Schema::create('training_session_phases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('name', 100);
            $table->string('time', 50)->nullable();
            $table->text('dosage')->nullable();
            $table->text('description')->nullable();
            $table->json('diagram')->nullable();
            $table->timestamps();

            $table->unique(['training_session_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_session_phases');
        Schema::table('training_sessions', fn (Blueprint $table) => $table->dropColumn('format'));
    }
};
