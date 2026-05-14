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
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('invoice_custom_item_id')->nullable()->constrained('invoice_custom_items')->nullOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->string('name');
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('status', ['pending', 'due', 'paid'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['inscription_id', 'status']);
            $table->index(['player_id', 'status']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('custom_charge_id')
                ->nullable()
                ->after('uniform_request_id')
                ->constrained('inscription_custom_charges')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_charge_id');
        });

        Schema::dropIfExists('inscription_custom_charges');
    }
};
