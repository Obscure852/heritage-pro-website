<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_policies')) {
            return;
        }
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_type_id');
            $table->year('leave_year');
            $table->enum('balance_mode', ['allocation', 'accrual']);
            $table->decimal('accrual_rate', 5, 2)->nullable();
            $table->enum('carry_over_mode', ['none', 'limited', 'full']);
            $table->decimal('carry_over_limit', 5, 2)->nullable();
            $table->integer('carry_over_expiry_months')->nullable();
            $table->boolean('prorate_new_employees')->default(true);
            $table->timestamps();

            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');

            $table->unique(['leave_type_id', 'leave_year']);
            $table->index('leave_type_id');
            $table->index('leave_year');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_policies');
    }
};
