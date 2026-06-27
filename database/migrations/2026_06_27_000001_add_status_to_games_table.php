<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->string('status', 20)->default('scheduled')->after('rival_name')->index();
            $table->text('final_score')->nullable()->change();
        });

        DB::table('games')
            ->whereDate('date', '<=', now()->toDateString())
            ->update(['status' => 'played']);
    }

    public function down(): void
    {
        DB::table('games')
            ->whereNull('final_score')
            ->update(['final_score' => json_encode(['soccer' => 0, 'rival' => 0])]);

        Schema::table('games', function (Blueprint $table): void {
            $table->text('final_score')->nullable(false)->change();
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
