<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills_control', function (Blueprint $table): void {
            if (!Schema::hasColumn('skills_control', 'goal_assists')) {
                $table->smallInteger('goal_assists')->default(0);
            }

            if (!Schema::hasColumn('skills_control', 'goal_saves')) {
                $table->smallInteger('goal_saves')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('skills_control', function (Blueprint $table): void {
            if (Schema::hasColumn('skills_control', 'goal_assists')) {
                $table->dropColumn('goal_assists');
            }

            if (Schema::hasColumn('skills_control', 'goal_saves')) {
                $table->dropColumn('goal_saves');
            }
        });
    }
};
