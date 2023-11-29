<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('inscription_id');
            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('competition_group_id');
            $table->year('year');
            $table->char('unique_code', 100)->index();
            $table->char('status', 5)->default('0');
            $table->double('value', 8, 2)->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('inscription_id')->references('id')->on('inscriptions');
            $table->foreign('tournament_id')->references('id')->on('tournaments');
            $table->foreign('competition_group_id')->references('id')->on('competition_groups');
            $table->unique(['competition_group_id','inscription_id', 'tournament_id','school_id', 'year'], 'unique_by_school');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_payouts');
    }
}
