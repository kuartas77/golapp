<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code')->unique();
            $table->string('names');
            $table->string('last_names');
            $table->string('gender');
            $table->date('date_birth');
            $table->string('place_birth');
            $table->string('identification_document');
            $table->string('rh')->nullable();
            $table->string('photo')->nullable();
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
            $table->string('password')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('players');
    }
}
