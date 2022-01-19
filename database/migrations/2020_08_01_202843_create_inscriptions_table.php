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

            $table->unsignedBigInteger('training_group_id')->nullable();
            $table->unsignedBigInteger('competition_group_id')->nullable();

            $table->string('unique_code')->unique();
            $table->date('start_date');
            $table->string('names');
            $table->string('last_names');
            $table->string('gender');
            $table->date('date_birth');
            $table->string('place_birth');
            $table->string('identification_document');
            $table->string('rh')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('scholarship')->nullable()->default(0);
            $table->string('category')->nullable();
            $table->string('position_field')->nullable();
            $table->string('dominant_profile')->nullable();

            $table->string('school');
            $table->string('degree');
            $table->string('address');
            $table->string('municipality');
            $table->string('neighborhood');
            $table->string('zone')->nullable();
            $table->string('commune')->nullable();
            $table->string('phones');
            $table->string('email');
            $table->string('mobile');
            $table->string('eps')->nullable();

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

            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->foreign('competition_group_id')->references('id')->on('competition_groups');

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
