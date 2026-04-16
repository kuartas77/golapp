<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('agent')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_enable')->default(false);
            $table->boolean('create_contract')->default(false);
            $table->boolean('send_documents')->default(false);
            $table->boolean('tutor_platform')->default(false);
            $table->boolean('sign_player')->default(false);
            $table->boolean('inscriptions_enabled')->default(false);
            $table->string('logo')->nullable();
            $table->string('short_name')->nullable();
            $table->string('email_info')->nullable();
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
        Schema::dropIfExists('schools');
    }
}
