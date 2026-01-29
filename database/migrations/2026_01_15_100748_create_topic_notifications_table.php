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
        Schema::create('topic_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('topic');
            $table->enum('type', ['REQUEST_UPDATE','PAYMENT_CONFIRMATION','SYSTEM_ALERT','REMINDER','GENERAL'])->default('GENERAL');
            $table->enum('priority', ['LOW','NORMAL','HIGH','URGENT'])->default('NORMAL');
            $table->string('title');
            $table->text('body');
            $table->string('image_url')->nullable();
            $table->smallInteger(column:'tries', unsigned:true)->default(3);
            $table->smallInteger(column:'tries_count', unsigned:true)->default(0);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_notifications');
    }
};
