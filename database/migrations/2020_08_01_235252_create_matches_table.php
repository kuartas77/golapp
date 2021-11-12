<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('competition_group_id');
            $table->date('date');
            $table->char('hour', 20);
            $table->text('num_match');
            $table->text('place');
            $table->text('rival_name');
            $table->text('final_score');
            $table->text('general_concept');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tournament_id')->references('id')->on('tournaments');
            $table->foreign('competition_group_id')->references('id')->on('competition_groups');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches');
    }
}
