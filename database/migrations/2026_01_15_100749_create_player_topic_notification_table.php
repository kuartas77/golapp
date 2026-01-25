<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_topic_notification', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_notification_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('player_id');
            $table->boolean('is_read');
            $table->timestamps();

            $table->foreign('topic_notification_id')->references('id')->on('topic_notifications')->constrained()->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->constrained()->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('players')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_topic_notification');
    }
};
