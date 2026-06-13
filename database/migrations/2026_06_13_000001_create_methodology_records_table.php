<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('methodology_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('training_group_id')->nullable()->constrained('training_groups')->nullOnDelete();
            $table->string('type', 60);
            $table->string('title');
            $table->json('fields')->nullable();
            $table->json('diagrams')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'type']);
            $table->index(['school_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('methodology_records');
    }
};
