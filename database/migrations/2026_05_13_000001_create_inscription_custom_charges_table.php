<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscription_custom_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('inscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_custom_item_id')->nullable()->constrained('invoice_custom_items')->nullOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->string('name');
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('status', ['pending', 'due', 'paid'])->default('pending');
            $table->date('due_date');
            $table->timestamps();

            $table->index(['school_id', 'status', 'due_date']);
            $table->index(['inscription_id', 'invoice_custom_item_id', 'status'], 'icc_inscription_item_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_custom_charges');
    }
};
