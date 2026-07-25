<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inscriptions') || Schema::hasColumn('inscriptions', 'sport')) {
            return;
        }

        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->string('sport', 32)
                ->default(config('sports.default_sport', 'football'))
                ->after('school_id');
        });

        DB::table('inscriptions')
            ->orderBy('id')
            ->chunkById(500, function ($inscriptions): void {
                $sportsByTrainingGroup = DB::table('training_groups')
                    ->whereIn('id', $inscriptions->pluck('training_group_id')->filter()->unique()->values())
                    ->pluck('sport', 'id');

                foreach ($inscriptions as $inscription) {
                    $sport = $sportsByTrainingGroup[$inscription->training_group_id] ?? null;

                    if (! $sport) {
                        continue;
                    }

                    DB::table('inscriptions')
                        ->where('id', $inscription->id)
                        ->update(['sport' => $sport]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inscriptions') || ! Schema::hasColumn('inscriptions', 'sport')) {
            return;
        }

        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->dropColumn('sport');
        });
    }
};
