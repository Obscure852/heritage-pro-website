<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fee balance carryovers track outstanding balances from one year to the next.
 * If a student has unpaid fees from 2025, the balance carries to 2026.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fee_balance_carryovers')) {
            return;
        }
        Schema::create('fee_balance_carryovers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->year('from_year');
            $table->year('to_year');
            $table->decimal('balance_amount', 12, 2);
            $table->datetime('carried_at');
            $table->unsignedBigInteger('carried_by');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('carried_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('student_id');
            $table->index(['from_year', 'to_year']);
            // Prevent duplicate carryovers for same student between same years
            $table->unique(['student_id', 'from_year', 'to_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_balance_carryovers');
    }
};
