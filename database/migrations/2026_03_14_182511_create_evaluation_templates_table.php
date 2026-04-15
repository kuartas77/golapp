<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('year')->nullable();

            $table->foreignId('training_group_id')
                ->nullable()
                ->constrained('training_groups')
                ->nullOnDelete();

            $table->string('status', 20)->default('active'); // draft, active, inactive
            $table->unsignedInteger('version')->default(1);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->index(['training_group_id', 'status', 'school_id']);
            $table->index(['year', 'status', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_templates');
    }
};