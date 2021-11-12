<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date_birth')->nullable();
            $table->char('identification_document', 20)->nullable();
            $table->char('gender', 5)->nullable();
            $table->string('address')->nullable();
            $table->text('studies')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('references')->nullable();
            $table->text('contacts')->nullable();
            $table->longText('experience')->nullable();
            $table->string('position', 50)->nullable();
            $table->text('aptitude')->nullable();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('profiles');
    }
}
