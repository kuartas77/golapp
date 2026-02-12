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
    public function up(): void
    {
        Schema::create('invoice_custom_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['UNIFORM', 'BALL', 'SOCKS', 'SHOES', 'SHORTS', 'JERSEY', 'OTHER']);
            $table->string('name');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedBigInteger('school_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('cascade');
        });

        /**
         * Columna generada:
         * - NULL si deleted_at IS NOT NULL (soft-deleted)
         * - NULL si type = OTHER (permite muchos OTHER)
         * - type en cualquier otro caso (aplica unicidad por escuela)
         *
         * UNIQUE (school_id, unique_type_key) => permite múltiples NULL, pero solo 1 valor no-null por type
         */
        DB::statement("
            ALTER TABLE invoice_custom_items
            ADD COLUMN unique_type_key VARCHAR(20)
            GENERATED ALWAYS AS (
              CASE
                WHEN deleted_at IS NOT NULL THEN NULL
                WHEN type = 'OTHER' THEN NULL
                ELSE type
              END
            ) STORED
        ");

        DB::statement("
            CREATE UNIQUE INDEX uniq_school_type_non_other_active
            ON invoice_custom_items (school_id, unique_type_key)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("DROP INDEX `uniq_school_type_non_other_active` ON `invoice_custom_items`");
        } catch (\Throwable $e) {
            // Ignorar si no existe
        }
        Schema::dropIfExists('invoice_custom_items');
    }
};
