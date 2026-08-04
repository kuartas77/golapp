<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->timestamp('signed_at')->nullable()->after('signature_user_agent');
            $table->json('signed_document_hashes')->nullable()->after('signed_at');
            $table->timestamp('data_processing_policy_accepted_at')->nullable()->after('signed_document_hashes');
            $table->string('data_processing_policy_version', 20)->nullable()->after('data_processing_policy_accepted_at');
            $table->char('data_processing_policy_hash', 64)->nullable()->after('data_processing_policy_version');
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table): void {
            $table->dropColumn([
                'signed_at',
                'signed_document_hashes',
                'data_processing_policy_accepted_at',
                'data_processing_policy_version',
                'data_processing_policy_hash',
            ]);
        });
    }
};
