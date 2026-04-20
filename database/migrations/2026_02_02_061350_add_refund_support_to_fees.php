<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds refund/credit note support to the fee module.
 *
 * - Creates fee_refunds table to track refund records
 * - Adds credit_balance column to student_invoices for overpayment tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add credit_balance to student_invoices for tracking overpayments
        Schema::table('student_invoices', function (Blueprint $table) {
            $table->decimal('credit_balance', 12, 2)->default(0)->after('balance');
        });

        // Create fee_refunds table for refund records
        Schema::create('fee_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 20)->unique();
            $table->unsignedBigInteger('student_invoice_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_payment_id')->nullable(); // Original payment being refunded
            $table->year('year');
            $table->decimal('amount', 12, 2);
            $table->enum('refund_type', ['full', 'partial', 'credit_note']);
            $table->enum('refund_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'credit_to_account']);
            $table->date('refund_date');
            $table->string('reference_number', 100)->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected'])->default('pending');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_invoice_id')->references('id')->on('student_invoices')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('year');
            $table->index('refund_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_refunds');

        Schema::table('student_invoices', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
