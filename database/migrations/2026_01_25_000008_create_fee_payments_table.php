<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fee payments are recorded against annual invoices.
 * Year is derived from the linked invoice.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fee_payments')) {
            return;
        }
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 20)->unique();
            $table->unsignedBigInteger('student_invoice_id');
            $table->unsignedBigInteger('student_id');
            $table->year('year');  // Denormalized for easier reporting
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque']);
            $table->date('payment_date');
            $table->string('reference_number', 100)->nullable();
            $table->string('cheque_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->boolean('voided')->default(false);
            $table->datetime('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_invoice_id')->references('id')->on('student_invoices')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('voided_by')->references('id')->on('users')->onDelete('set null');

            $table->index('student_id');
            $table->index('year');
            $table->index('payment_date');
            $table->index('receipt_number');
            $table->index('voided');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
