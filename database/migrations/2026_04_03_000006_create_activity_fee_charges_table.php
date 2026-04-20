<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_fee_charges')) {
            return;
        }

        Schema::create('activity_fee_charges', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('activity_enrollment_id')->nullable();
            $table->unsignedBigInteger('activity_event_id')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_type_id');
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->string('charge_type', 30);
            $table->decimal('amount', 10, 2);
            $table->string('billing_status', 20)->default('pending');
            $table->unsignedBigInteger('student_invoice_id')->nullable();
            $table->unsignedBigInteger('student_invoice_item_id')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
            $table->foreign('activity_enrollment_id')->references('id')->on('activity_enrollments')->nullOnDelete();
            $table->foreign('activity_event_id')->references('id')->on('activity_events')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->restrictOnDelete();
            $table->foreign('term_id')->references('id')->on('terms')->restrictOnDelete();
            $table->foreign('student_invoice_id')->references('id')->on('student_invoices')->nullOnDelete();
            $table->foreign('student_invoice_item_id')->references('id')->on('student_invoice_items')->nullOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['activity_id', 'billing_status']);
            $table->index(['student_id', 'term_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_fee_charges');
    }
};
