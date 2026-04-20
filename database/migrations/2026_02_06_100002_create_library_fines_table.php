<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('library_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_transaction_id')->constrained('library_transactions')->cascadeOnDelete();
            $table->string('borrower_type');
            $table->unsignedBigInteger('borrower_id');
            $table->enum('fine_type', ['overdue', 'lost', 'damage'])->default('overdue');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_waived', 10, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->date('fine_date');
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('waiver_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['borrower_type', 'borrower_id']);
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('library_fines');
    }
};
