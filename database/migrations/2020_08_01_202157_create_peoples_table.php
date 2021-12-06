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
            $table->boolean('tutor')->default(false);
            $table->string('identification_card', 50)->unique()->index();
            $table->string('names', 50);
            $table->string('relationship', 50);
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('neighborhood', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('profession', 50)->nullable();
            $table->string('business', 50)->nullable();
            $table->string('position', 50)->nullable();
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
