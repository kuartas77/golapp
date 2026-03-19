<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_evaluation_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_evaluation_id')
                ->constrained('player_evaluations')
                ->cascadeOnDelete();

            $table->foreignId('template_criterion_id')
                ->constrained('evaluation_template_criteria')
                ->cascadeOnDelete();

            $table->decimal('score', 5, 2)->nullable();
            $table->string('scale_value', 50)->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(
                ['player_evaluation_id', 'template_criterion_id'],
                'pes_evaluation_criterion_unique'
            );

            $table->index(['player_evaluation_id'], 'pes_evaluation_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_evaluation_scores');
    }
};