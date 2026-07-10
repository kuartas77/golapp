<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('people_id')->constrained('peoples')->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('token', 512)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_device_tokens');
    }
};
