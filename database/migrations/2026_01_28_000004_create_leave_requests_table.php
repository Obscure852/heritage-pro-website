<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_requests')) {
            return;
        }
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ulid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->unsignedBigInteger('leave_balance_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('start_half_day', ['am', 'pm'])->nullable();
            $table->enum('end_half_day', ['am', 'pm'])->nullable();
            $table->decimal('total_days', 5, 2);
            $table->text('reason');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled']);
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_comments')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('leave_balance_id')->references('id')->on('leave_balances')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['approved_by', 'status']);
            $table->index('user_id');
            $table->index('leave_type_id');
            $table->index('leave_balance_id');
            $table->index('status');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_requests');
    }
};
