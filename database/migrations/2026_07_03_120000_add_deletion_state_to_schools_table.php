<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->string('deletion_status')->nullable()->after('is_enable');
            $table->text('deletion_error')->nullable()->after('deletion_status');
            $table->timestamp('deletion_requested_at')->nullable()->after('deletion_error');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn(['deletion_status', 'deletion_error', 'deletion_requested_at']);
        });
    }
};
