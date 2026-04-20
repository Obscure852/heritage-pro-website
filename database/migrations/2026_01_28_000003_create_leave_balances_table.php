<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_balances')) {
            return;
        }
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->year('leave_year');
            $table->decimal('entitled', 5, 2)->default(0);
            $table->decimal('carried_over', 5, 2)->default(0);
            $table->decimal('accrued', 5, 2)->default(0);
            $table->decimal('used', 5, 2)->default(0);
            $table->decimal('pending', 5, 2)->default(0);
            $table->decimal('adjusted', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');

            $table->unique(['user_id', 'leave_type_id', 'leave_year']);
            $table->index(['user_id', 'leave_year']);
            $table->index(['leave_type_id', 'leave_year']);
            $table->index('user_id');
            $table->index('leave_type_id');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_balances');
    }
};
