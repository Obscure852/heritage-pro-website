<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('library_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('borrower_type');
            $table->unsignedBigInteger('borrower_id');
            $table->enum('status', ['pending', 'ready', 'fulfilled', 'expired', 'cancelled'])->default('pending');
            $table->unsignedInteger('queue_position');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'status']);
            $table->index(['borrower_type', 'borrower_id']);
            $table->index('queue_position');
        });
    }

    public function down(): void {
        Schema::dropIfExists('library_reservations');
    }
};
