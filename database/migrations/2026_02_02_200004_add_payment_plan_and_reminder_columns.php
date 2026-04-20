<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add installment tracking to fee_payments
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->foreignId('payment_plan_installment_id')
                  ->nullable()
                  ->after('student_invoice_id')
                  ->constrained('payment_plan_installments')
                  ->nullOnDelete();
        });

        // Add reminder tracking to student_invoices
        Schema::table('student_invoices', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('notes');
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('last_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_plan_installment_id');
        });

        Schema::table('student_invoices', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_sent_at', 'reminder_count']);
        });
    }
};
