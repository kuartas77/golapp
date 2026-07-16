<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_credit_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->unsignedInteger('amount');
            $table->date('movement_date');
            $table->string('concept', 150);
            $table->text('notes')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('payment_field', 30)->nullable();
            $table->unsignedTinyInteger('previous_payment_status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'player_id']);
            $table->index(['school_id', 'movement_date']);
            $table->index(['payment_id', 'payment_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_credit_movements');
    }
};
