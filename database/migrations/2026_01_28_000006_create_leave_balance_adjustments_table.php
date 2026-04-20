<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_balance_adjustments')) {
            return;
        }
        Schema::create('leave_balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_balance_id');
            $table->enum('adjustment_type', ['credit', 'debit', 'correction']);
            $table->decimal('days', 5, 2);
            $table->text('reason');
            $table->unsignedBigInteger('adjusted_by');
            $table->timestamp('created_at')->nullable();

            $table->foreign('leave_balance_id')->references('id')->on('leave_balances')->onDelete('cascade');
            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('leave_balance_id');
            $table->index('adjusted_by');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_balance_adjustments');
    }
};
