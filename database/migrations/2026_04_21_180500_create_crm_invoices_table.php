<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_invoices')) {
            return;
        }

        Schema::create('crm_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
            $table->string('invoice_number', 40)->unique();
            $table->string('status', 20)->default('draft');
            $table->string('subject')->nullable();
            $table->date('invoice_date');
            $table->string('currency_code', 10);
            $table->string('currency_symbol', 12);
            $table->string('currency_position', 12)->default('before');
            $table->unsignedTinyInteger('currency_precision')->default(2);
            $table->string('document_discount_type', 10)->default('none');
            $table->decimal('document_discount_value', 12, 2)->default(0);
            $table->decimal('document_discount_amount', 14, 2)->default(0);
            $table->decimal('subtotal_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('shared_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
            $table->index(['lead_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('request_id');
            $table->index('invoice_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_invoices');
    }
};
