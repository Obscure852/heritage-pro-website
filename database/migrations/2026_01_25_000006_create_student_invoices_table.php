<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student invoices are generated once per student per year (annual invoice).
 * Contains the full annual fee amount. Multiple payments recorded against it.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('student_invoices')) {
            return;
        }
        Schema::create('student_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique();
            $table->unsignedBigInteger('student_id');
            $table->year('year');
            $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->enum('status', ['draft', 'issued', 'partial', 'paid', 'overdue', 'cancelled']);
            $table->datetime('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // One invoice per student per year
            $table->unique(['student_id', 'year']);
            $table->index('student_id');
            $table->index('status');
            $table->index('invoice_number');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_invoices');
    }
};
