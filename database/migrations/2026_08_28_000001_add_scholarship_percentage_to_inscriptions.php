<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->unsignedTinyInteger('scholarship_percentage')
                ->nullable()
                ->after('scholarship');
        });

        DB::table('inscriptions')
            ->where('scholarship', true)
            ->update(['scholarship_percentage' => 100]);
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->dropColumn('scholarship_percentage');
        });
    }
};
