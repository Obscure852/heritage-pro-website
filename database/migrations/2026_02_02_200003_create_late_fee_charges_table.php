<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_fee_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_invoice_id')->constrained('student_invoices')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->date('applied_date');
            $table->unsignedInteger('days_overdue');
            $table->boolean('waived')->default(false);
            $table->timestamp('waived_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('waived_reason')->nullable();
            $table->timestamps();

            $table->index(['student_invoice_id', 'applied_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_fee_charges');
    }
};
