<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['training_groups', 'competition_groups', 'tournaments'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'sport')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->string('sport', 32)
                    ->default(config('sports.default_sport', 'football'))
                    ->after($tableName === 'tournaments' ? 'name' : 'school_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['training_groups', 'competition_groups', 'tournaments'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'sport')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('sport');
            });
        }
    }
};
