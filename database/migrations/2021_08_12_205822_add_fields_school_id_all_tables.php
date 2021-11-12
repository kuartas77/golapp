<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsSchoolIdAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('training_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('skills_control', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('tournaments', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('competition_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
        Schema::table('assists', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assists', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('competition_groups', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('skills_control', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('training_groups', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign('school_id');
        });
    }
}
