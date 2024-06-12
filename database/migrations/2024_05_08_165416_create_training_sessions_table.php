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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('training_group_id')->nullable();
            $table->year('year');
            $table->string('period', 100);
            $table->string('session', 100);
            $table->date('date');
            $table->char('hour', 20);
            $table->string('training_ground', 100)->nullable();
            $table->text('warm_up')->nullable();
            $table->text('coaches')->nullable();
            $table->text('material')->nullable();
            $table->text('feedback')->nullable();
            $table->text('incidents')->nullable();
            $table->text('players')->nullable();
            $table->text('absences')->nullable();
            $table->char('back_to_calm', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_sessions');
    }
};
