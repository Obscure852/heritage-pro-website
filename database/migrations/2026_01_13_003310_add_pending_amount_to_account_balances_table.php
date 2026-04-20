<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_balances', function (Blueprint $table) {
            $table->decimal('pending_amount', 10, 2)->default(0)->after('balance_bwp')
                ->comment('Reserved balance for in-flight SMS jobs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_balances', function (Blueprint $table) {
            $table->dropColumn('pending_amount');
        });
    }
};
