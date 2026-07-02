<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments_received', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'check', 'other']);
            $table->string('reference', 100)->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite' && Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreign('payment_received_id')->references('id')->on('payments_received')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite' && Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropForeign(['payment_received_id']);
            });
        }

        Schema::dropIfExists('payments_received');
    }
};
