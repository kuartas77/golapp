<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->unsignedBigInteger('training_group_id');
            $table->unsignedBigInteger('inscription_id');
            $table->string('unique_code');

            $table->string('january', 20)->nullable()->default(0);
            $table->string('february', 20)->nullable()->default(0);
            $table->string('march', 20)->nullable()->default(0);
            $table->string('april', 20)->nullable()->default(0);
            $table->string('may', 20)->nullable()->default(0);
            $table->string('june', 20)->nullable()->default(0);
            $table->string('july', 20)->nullable()->default(0);
            $table->string('august', 20)->nullable()->default(0);
            $table->string('september', 20)->nullable()->default(0);
            $table->string('october', 20)->nullable()->default(0);
            $table->string('november', 20)->nullable()->default(0);
            $table->string('december', 20)->nullable()->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year','training_group_id','inscription_id']);

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
        Schema::dropIfExists('payments');
    }
}
