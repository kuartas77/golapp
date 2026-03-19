<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Diagnóstico inicial, Corte 1, Final
            $table->string('code', 50); // Ej: DIAG, T1, T2, FINAL
            $table->unsignedInteger('year');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['year', 'code', 'school_id']);
            $table->index(['year', 'is_active', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_periods');
    }
};