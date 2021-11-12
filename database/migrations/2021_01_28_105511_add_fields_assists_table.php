<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAssistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assists', function (Blueprint $table) {
            $table->char('assistance_sixteen', 5)->nullable()->after('assistance_fifteen');
            $table->char('assistance_seventeen', 5)->nullable()->after('assistance_sixteen');
            $table->char('assistance_eighteen', 5)->nullable()->after('assistance_seventeen');
            $table->char('assistance_nineteen', 5)->nullable()->after('assistance_eighteen');
            $table->char('assistance_twenty', 5)->nullable()->after('assistance_nineteen');
            $table->char('assistance_twenty_one', 5)->nullable()->after('assistance_twenty');
            $table->char('assistance_twenty_two', 5)->nullable()->after('assistance_twenty_one');
            $table->char('assistance_twenty_three', 5)->nullable()->after('assistance_twenty_two');
            $table->char('assistance_twenty_four', 5)->nullable()->after('assistance_twenty_three');
            $table->char('assistance_twenty_five', 5)->nullable()->after('assistance_twenty_four');
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
            $table->dropColumn([
                'assistance_sixteen',
                'assistance_seventeen',
                'assistance_eighteen',
                'assistance_nineteen',
                'assistance_twenty',
                'assistance_twenty_one',
                'assistance_twenty_two',
                'assistance_twenty_three',
                'assistance_twenty_four',
                'assistance_twenty_five'
            ]);
        });
    }
}
