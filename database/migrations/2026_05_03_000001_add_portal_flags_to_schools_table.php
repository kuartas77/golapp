<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'create_contract')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('create_contract')->default(false);
            });
        }

        if (!Schema::hasColumn('schools', 'send_documents')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('send_documents')->default(false);
            });
        }

        if (!Schema::hasColumn('schools', 'tutor_platform')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('tutor_platform')->default(false);
            });
        }

        if (!Schema::hasColumn('schools', 'sign_player')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('sign_player')->default(false);
            });
        }

        if (!Schema::hasColumn('schools', 'inscriptions_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('inscriptions_enabled')->default(false);
            });
        }

        if (!Schema::hasColumn('schools', 'short_name')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->string('short_name')->nullable();
            });
        }

        if (!Schema::hasColumn('schools', 'email_info')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->string('email_info')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = collect([
            'create_contract',
            'send_documents',
            'tutor_platform',
            'sign_player',
            'inscriptions_enabled',
            'short_name',
            'email_info',
        ])->filter(fn (string $column) => Schema::hasColumn('schools', $column))->values()->all();

        if ($columns !== []) {
            Schema::table('schools', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
