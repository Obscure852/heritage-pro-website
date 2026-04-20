<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('library_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_id')->constrained('copies')->cascadeOnDelete();
            $table->string('borrower_type');
            $table->unsignedBigInteger('borrower_id');
            $table->date('checkout_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['checked_out', 'returned', 'overdue', 'lost'])->default('checked_out');
            $table->unsignedTinyInteger('renewal_count')->default(0);
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['borrower_type', 'borrower_id']);
            $table->index(['copy_id', 'status']);
            $table->index('due_date');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('library_transactions');
    }
};
