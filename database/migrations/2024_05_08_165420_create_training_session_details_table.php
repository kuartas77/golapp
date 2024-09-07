<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_session_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_session_id');
            $table->integer('task_number');
            $table->char('task_name', 10);
            $table->char('general_objective', 50)->nullable();
            $table->char('specific_goal', 50)->nullable();
            $table->char('content_one', 50)->nullable();
            $table->char('content_two', 50)->nullable();
            $table->char('content_three', 50)->nullable();
            $table->char('ts', 10)->nullable();
            $table->char('sr', 10)->nullable();
            $table->char('tt', 10)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('training_session_id')->references('id')->on('training_sessions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_session_details');
    }
};
