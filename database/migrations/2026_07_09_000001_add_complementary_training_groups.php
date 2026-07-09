<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_groups', function (Blueprint $table): void {
            $table->boolean('is_complementary')->default(false)->after('year_active');
        });

        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->unsignedBigInteger('complementary_group_id')->nullable()->after('training_group_id');
            $table->foreign('complementary_group_id')
                ->references('id')
                ->on('training_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->dropForeign(['complementary_group_id']);
            $table->dropColumn('complementary_group_id');
        });

        Schema::table('training_groups', function (Blueprint $table): void {
            $table->dropColumn('is_complementary');
        });
    }
};
