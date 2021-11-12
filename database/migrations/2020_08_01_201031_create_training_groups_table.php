<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_groups', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('name', 100);
            $table->string('stage', 100)->nullable();
            $table->string('year', 100);
            $table->string('year_two', 100)->nullable();
            $table->string('year_three', 100)->nullable();
            $table->string('year_four', 100)->nullable();
            $table->string('year_five', 100)->nullable();
            $table->string('year_six', 100)->nullable();
            $table->string('year_seven', 100)->nullable();
            $table->string('year_eight', 100)->nullable();
            $table->string('year_nine', 100)->nullable();
            $table->string('year_ten', 100)->nullable();
            $table->string('year_eleven', 100)->nullable();
            $table->string('year_twelve', 100)->nullable();
            $table->text('category')->nullable();
            $table->unsignedBigInteger('day_id');
            $table->unsignedBigInteger('schedule_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_groups');
    }
}
