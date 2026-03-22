<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_group_id');
            $table->unsignedBigInteger('inscription_id');
            $table->year('year');
            $table->tinyInteger('month');
            $table->tinyInteger('assistance_one')->nullable();
            $table->tinyInteger('assistance_two')->nullable();
            $table->tinyInteger('assistance_three')->nullable();
            $table->tinyInteger('assistance_four')->nullable();
            $table->tinyInteger('assistance_five')->nullable();
            $table->tinyInteger('assistance_six')->nullable();
            $table->tinyInteger('assistance_seven')->nullable();
            $table->tinyInteger('assistance_eight')->nullable();
            $table->tinyInteger('assistance_nine')->nullable();
            $table->tinyInteger('assistance_ten')->nullable();
            $table->tinyInteger('assistance_eleven')->nullable();
            $table->tinyInteger('assistance_twelve')->nullable();
            $table->tinyInteger('assistance_thirteen')->nullable();
            $table->tinyInteger('assistance_fourteen')->nullable();
            $table->tinyInteger('assistance_fifteen')->nullable();
            $table->tinyInteger('assistance_sixteen')->nullable();
            $table->tinyInteger('assistance_seventeen')->nullable();
            $table->tinyInteger('assistance_eighteen')->nullable();
            $table->tinyInteger('assistance_nineteen')->nullable();
            $table->tinyInteger('assistance_twenty')->nullable();
            $table->tinyInteger('assistance_twenty_one')->nullable();
            $table->tinyInteger('assistance_twenty_two')->nullable();
            $table->tinyInteger('assistance_twenty_three')->nullable();
            $table->tinyInteger('assistance_twenty_four')->nullable();
            $table->tinyInteger('assistance_twenty_five')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->foreign('inscription_id')->references('id')->on('inscriptions');
            $table->unique(['training_group_id', 'inscription_id','year', 'month'], 'uk_assists_unique_month');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assists');
    }
}
