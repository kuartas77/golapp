<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeoplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peoples', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_tutor')->default(false);
            $table->integer('relationship');
            $table->string('names');
            $table->char('phone', 120)->nullable();
            $table->char('mobile', 120)->nullable();
            $table->char('identification_card', 50)->nullable();
            $table->char('neighborhood', 120)->nullable();
            $table->string('email')->nullable();
            $table->char('profession', 120)->nullable();
            $table->char('business', 120)->nullable();
            $table->char('position', 120)->nullable();
            $table->char('relationship_name', 120)->nullable();
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
        Schema::dropIfExists('peoples');
    }
}
