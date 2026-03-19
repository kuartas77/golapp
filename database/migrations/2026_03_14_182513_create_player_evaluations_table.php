<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('inscription_id')
                ->constrained('inscriptions')
                ->cascadeOnDelete();

            $table->foreignId('evaluation_period_id')
                ->constrained('evaluation_periods')
                ->restrictOnDelete();

            $table->foreignId('evaluation_template_id')
                ->constrained('evaluation_templates')
                ->restrictOnDelete();

            $table->foreignId('evaluator_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('evaluation_type', 20)->default('periodic'); // initial, periodic, final, special
            $table->string('status', 20)->default('draft'); // draft, completed, closed

            $table->timestamp('evaluated_at')->nullable();

            $table->text('general_comment')->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvement_opportunities')->nullable();
            $table->text('recommendations')->nullable();

            $table->decimal('overall_score', 5, 2)->nullable();

            $table->timestamps();

            $table->unique(['school_id', 'inscription_id', 'evaluation_period_id', 'evaluation_type'], 'pe_inscription_period_type_unique');
            $table->index(['evaluation_template_id'], 'pe_template_idx');
            $table->index(['evaluator_user_id'], 'pe_evaluator_idx');
            $table->index(['status', 'evaluation_type'], 'pe_status_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_evaluations');
    }
};