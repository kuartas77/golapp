<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->decimal('entry_price', 12, 2)->default(0)->after('description');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('entry_price_snapshot', 12, 2)->default(0)->after('quantity');
            $table->decimal('sale_price_snapshot', 12, 2)->default(0)->after('entry_price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['entry_price_snapshot', 'sale_price_snapshot']);
        });

        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn('entry_price');
        });
    }
};
