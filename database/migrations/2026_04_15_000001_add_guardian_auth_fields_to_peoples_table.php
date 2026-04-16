<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peoples', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable()->after('remember_token');
            $table->timestamp('invited_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('invited_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('peoples', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn([
                'password',
                'remember_token',
                'email_verified_at',
                'invited_at',
                'last_login_at',
            ]);
        });
    }
};
