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
        // Drop in order respecting foreign key constraints
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('charge_payments');
        Schema::dropIfExists('student_fees');
        Schema::dropIfExists('student_charges');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('charges');
        Schema::dropIfExists('condition_set_conditions');
        Schema::dropIfExists('condition_sets');
        Schema::dropIfExists('fee_types');
        Schema::dropIfExists('charge_types');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
