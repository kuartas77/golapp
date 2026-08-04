<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->string('signature_ip_address', 45)->nullable()->after('pre_inscription');
            $table->string('signature_user_agent', 500)->nullable()->after('signature_ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->dropColumn(['signature_ip_address', 'signature_user_agent']);
        });
    }
};
