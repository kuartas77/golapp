<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('inscription_id')->nullable()->constrained('inscriptions')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('field', 30);
            $table->unsignedTinyInteger('old_status')->nullable();
            $table->unsignedTinyInteger('new_status')->nullable();
            $table->unsignedInteger('old_amount')->default(0);
            $table->unsignedInteger('new_amount')->default(0);
            $table->string('source', 30)->default('manual');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'year', 'field']);
            $table->index(['payment_id', 'field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_change_logs');
    }
};
