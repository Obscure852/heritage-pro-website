<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountBalancesTable extends Migration
{

    public function up()
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sms_credits_package', 191)->nullable();
            $table->decimal('package_amount', 10, 2)->default(0.00);
            $table->decimal('amount_used_bwp', 10, 2)->default(0.00);
            $table->decimal('balance_bwp', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_balances');
    }
}
