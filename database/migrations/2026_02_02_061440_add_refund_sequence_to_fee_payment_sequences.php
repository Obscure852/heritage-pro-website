<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds refund sequence tracking to fee_payment_sequences table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payment_sequences', function (Blueprint $table) {
            $table->unsignedInteger('last_refund_sequence')->default(0)->after('last_receipt_sequence');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payment_sequences', function (Blueprint $table) {
            $table->dropColumn('last_refund_sequence');
        });
    }
};
