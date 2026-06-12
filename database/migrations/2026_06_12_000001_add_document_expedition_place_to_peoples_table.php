<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peoples', function (Blueprint $table) {
            $table->string('document_expedition_place', 100)->nullable()->after('identification_card');
        });
    }

    public function down(): void
    {
        Schema::table('peoples', function (Blueprint $table) {
            $table->dropColumn('document_expedition_place');
        });
    }
};
