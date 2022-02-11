<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills_control', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('inscription_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('inscription_id')->references('id')->on('inscriptions');

            $table->boolean('assistance')->default(false);
            $table->boolean('titular')->default(false);
            $table->smallInteger('played_approx')->default(0);
            $table->string('position')->nullable();
            $table->smallInteger('goals')->default(0);
            $table->smallInteger('yellow_cards')->default(0);
            $table->smallInteger('red_cards')->default(0);
            $table->string('qualification')->nullable()->default(1);
            $table->string('observation')->nullable();

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
        Schema::dropIfExists('skills_control');
    }
}
