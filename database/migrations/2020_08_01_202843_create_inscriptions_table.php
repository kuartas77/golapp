<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('unique_code');
            $table->year('year');
            $table->date('start_date');
            $table->unsignedBigInteger('training_group_id');
            $table->unsignedBigInteger('competition_group_id')->nullable();
            $table->boolean('scholarship')->nullable()->default(0);
            $table->string('category')->nullable();
            $table->boolean('photos')->nullable();
            $table->boolean('copy_identification_document')->nullable();
            $table->boolean('eps_certificate')->nullable();
            $table->boolean('medic_certificate')->nullable();
            $table->boolean('study_certificate')->nullable();
            $table->boolean('overalls')->nullable();
            $table->boolean('ball')->nullable();
            $table->boolean('bag')->nullable();
            $table->boolean('presentation_uniform')->nullable();
            $table->boolean('competition_uniform')->nullable();
            $table->boolean('tournament_pay')->nullable();
            $table->string('period_one')->nullable();
            $table->string('period_two')->nullable();
            $table->string('period_three')->nullable();
            $table->string('period_four')->nullable();
            $table->boolean('pre_inscription')->nullable()->default(false);

            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->foreign('competition_group_id')->references('id')->on('competition_groups');
            $table->foreign('player_id')->references('id')->on('players');
            $table->unique(['unique_code','year']);
            // $table->unique(['unique_code','year', 'school_id]);

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
        Schema::dropIfExists('inscriptions');
    }
}
