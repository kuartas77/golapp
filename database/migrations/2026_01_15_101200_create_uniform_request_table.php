<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uniform_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['UNIFORM', 'BALL', 'SOCKS', 'SHOES', 'SHORTS', 'JERSEY', 'OTHER']);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED','CANCELLED']);
            $table->smallInteger(column:'quantity', unsigned:true)->default(1);
            $table->string('size', 10)->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uniform_request');
    }
};
