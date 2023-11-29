<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthAmountPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->float('enrollment_amount', 8, 2)->default(0)->nullable()->after('december');
            $table->float('january_amount', 8, 2)->default(0)->nullable()->after('enrollment_amount');
            $table->float('february_amount', 8, 2)->default(0)->nullable()->after('january_amount');
            $table->float('march_amount', 8, 2)->default(0)->nullable()->after('february_amount');
            $table->float('april_amount', 8, 2)->default(0)->nullable()->after('march_amount');
            $table->float('may_amount', 8, 2)->default(0)->nullable()->after('april_amount');
            $table->float('june_amount', 8, 2)->default(0)->nullable()->after('may_amount');
            $table->float('july_amount', 8, 2)->default(0)->nullable()->after('june_amount');
            $table->float('august_amount', 8, 2)->default(0)->nullable()->after('july_amount');
            $table->float('september_amount', 8, 2)->default(0)->nullable()->after('august_amount');
            $table->float('october_amount', 8, 2)->default(0)->nullable()->after('september_amount');
            $table->float('november_amount', 8, 2)->default(0)->nullable()->after('october_amount');
            $table->float('december_amount', 8, 2)->default(0)->nullable()->after('november_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('enrollment_amount');
            $table->dropColumn('january_amount');
            $table->dropColumn('february_amount');
            $table->dropColumn('march_amount');
            $table->dropColumn('april_amount');
            $table->dropColumn('may_amount');
            $table->dropColumn('june_amount');
            $table->dropColumn('july_amount');
            $table->dropColumn('august_amount');
            $table->dropColumn('september_amount');
            $table->dropColumn('october_amount');
            $table->dropColumn('november_amount');
            $table->dropColumn('december_amount');
        });
    }
}
