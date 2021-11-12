<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_created_id');
            $table->unsignedBigInteger('user_incident_id');

            $table->string('incidence');
            $table->text('description');
            $table->string('slug_name');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_created_id')->references('id')->on('users');
            $table->foreign('user_incident_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incidents');
    }
}
