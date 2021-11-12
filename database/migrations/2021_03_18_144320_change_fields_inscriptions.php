<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldsInscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'names', 'last_names',
                'gender', 'date_birth',
                'place_birth', 'identification_document',
                'rh', 'photo',
                'position_field', 'dominant_profile',
                'school', 'degree',
                'address', 'municipality',
                'neighborhood', 'zone',
                'commune', 'phones',
                'email', 'mobile', 'eps'
            ]);
            $table->dropUnique('inscriptions_unique_code_unique');
            $table->unsignedBigInteger('player_id')->after('unique_code');
            $table->year('year')->after('player_id');

            $table->unique(['unique_code','year']);

            $table->foreign('player_id')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn('player_id');
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
        });
    }
}
