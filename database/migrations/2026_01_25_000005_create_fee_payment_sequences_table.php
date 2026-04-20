<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fee_payment_sequences')) {
            return;
        }
        Schema::create('fee_payment_sequences', function (Blueprint $table) {
            $table->year('year')->primary();
            $table->integer('last_invoice_sequence')->default(0);
            $table->integer('last_receipt_sequence')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payment_sequences');
    }
};
