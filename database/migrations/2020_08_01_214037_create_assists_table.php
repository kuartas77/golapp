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
            $table->char('month',2);
            $table->char('assistance_one', 5)->nullable();
            $table->char('assistance_two', 5)->nullable();
            $table->char('assistance_three', 5)->nullable();
            $table->char('assistance_four', 5)->nullable();
            $table->char('assistance_five', 5)->nullable();
            $table->char('assistance_six', 5)->nullable();
            $table->char('assistance_seven', 5)->nullable();
            $table->char('assistance_eight', 5)->nullable();
            $table->char('assistance_nine', 5)->nullable();
            $table->char('assistance_ten', 5)->nullable();
            $table->char('assistance_eleven', 5)->nullable();
            $table->char('assistance_twelve', 5)->nullable();
            $table->char('assistance_thirteen', 5)->nullable();
            $table->char('assistance_fourteen', 5)->nullable();
            $table->char('assistance_fifteen', 5)->nullable();
            $table->char('assistance_sixteen', 5)->nullable();
            $table->char('assistance_seventeen', 5)->nullable();
            $table->char('assistance_eighteen', 5)->nullable();
            $table->char('assistance_nineteen', 5)->nullable();
            $table->char('assistance_twenty', 5)->nullable();
            $table->char('assistance_twenty_one', 5)->nullable();
            $table->char('assistance_twenty_two', 5)->nullable();
            $table->char('assistance_twenty_three', 5)->nullable();
            $table->char('assistance_twenty_four', 5)->nullable();
            $table->char('assistance_twenty_five', 5)->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('training_group_id')->references('id')->on('training_groups');
            $table->foreign('inscription_id')->references('id')->on('inscriptions');
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
