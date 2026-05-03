<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('peoples', 'password')) {
            Schema::table('peoples', function (Blueprint $table) {
                $table->string('password')->nullable();
            });
        }

        if (!Schema::hasColumn('peoples', 'remember_token')) {
            Schema::table('peoples', function (Blueprint $table) {
                $table->rememberToken();
            });
        }

        if (!Schema::hasColumn('peoples', 'email_verified_at')) {
            Schema::table('peoples', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable();
            });
        }

        if (!Schema::hasColumn('peoples', 'invited_at')) {
            Schema::table('peoples', function (Blueprint $table) {
                $table->timestamp('invited_at')->nullable();
            });
        }

        if (!Schema::hasColumn('peoples', 'last_login_at')) {
            Schema::table('peoples', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = collect([
            'password',
            'remember_token',
            'email_verified_at',
            'invited_at',
            'last_login_at',
        ])->filter(fn (string $column) => Schema::hasColumn('peoples', $column))->values()->all();

        if ($columns !== []) {
            Schema::table('peoples', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
