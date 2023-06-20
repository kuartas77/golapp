<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitionGroupsInscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition_group_inscription', function (Blueprint $table) {
            $table->unsignedBigInteger('competition_group_id');
            $table->unsignedBigInteger('inscription_id');
            $table->foreign('competition_group_id')->references('id')->on('competition_groups');
            $table->foreign('inscription_id')->references('id')->on('inscriptions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competition_group_inscription');
    }
}
