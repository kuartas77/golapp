<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_template_criteria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_template_id')
                ->constrained('evaluation_templates')
                ->cascadeOnDelete();

                $table->string('code', 100)->nullable()->index();// technical_pass, technical_control, tactical_positioning
            $table->string('dimension', 100); // Técnica, Táctica, Física, Mental, Disciplina
            $table->string('name'); // Pase, Control, Remate, etc.
            $table->text('description')->nullable();

            $table->string('score_type', 20)->default('numeric'); // numeric, scale, boolean
            $table->decimal('min_score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->default(1);

            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->index(['evaluation_template_id', 'sort_order'], 'etc_template_sort_idx');
            $table->index(['evaluation_template_id', 'dimension'], 'etc_template_dimension_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_template_criteria');
    }
};