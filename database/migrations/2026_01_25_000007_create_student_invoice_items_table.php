<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('student_invoice_items')) {
            return;
        }
        Schema::create('student_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_invoice_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->timestamps();

            $table->foreign('student_invoice_id')->references('id')->on('student_invoices')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');

            $table->index('student_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_invoice_items');
    }
};
