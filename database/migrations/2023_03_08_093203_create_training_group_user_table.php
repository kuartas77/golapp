<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_group_id');
            $table->unsignedBigInteger('user_id');
            $table->year('assigned_year');
            $table->timestamps();

            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_group_user');
    }
}
