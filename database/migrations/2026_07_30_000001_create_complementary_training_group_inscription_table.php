<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complementary_training_group_inscription', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('inscription_id');
            $table->unsignedBigInteger('training_group_id');
            $table->timestamps();

            $table->foreign('school_id', 'ctgi_school_fk')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('inscription_id', 'ctgi_inscription_fk')->references('id')->on('inscriptions')->cascadeOnDelete();
            $table->foreign('training_group_id', 'ctgi_group_fk')->references('id')->on('training_groups')->cascadeOnDelete();
            $table->unique(['inscription_id', 'training_group_id'], 'ctgi_inscription_group_unique');
            $table->index(['school_id', 'training_group_id'], 'ctgi_school_group_index');
        });

        DB::table('inscriptions')
            ->whereNotNull('complementary_group_id')
            ->orderBy('id')
            ->select(['school_id', 'id', 'complementary_group_id', 'created_at', 'updated_at'])
            ->chunkById(500, function ($inscriptions): void {
                $now = now();
                $rows = $inscriptions->map(fn ($inscription) => [
                    'school_id' => $inscription->school_id,
                    'inscription_id' => $inscription->id,
                    'training_group_id' => $inscription->complementary_group_id,
                    'created_at' => $inscription->created_at ?? $now,
                    'updated_at' => $inscription->updated_at ?? $now,
                ])->all();

                DB::table('complementary_training_group_inscription')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('complementary_training_group_inscription');
    }
};
