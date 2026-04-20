<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('library_overdue_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_transaction_id')->constrained('library_transactions')->cascadeOnDelete();
            $table->string('borrower_type');
            $table->unsignedBigInteger('borrower_id');
            $table->string('notice_type'); // overdue_reminder, escalation, lost_declaration
            $table->string('channel'); // in_app, email, sms
            $table->integer('days_overdue');
            $table->string('escalated_to')->nullable(); // class_teacher, hod, null
            $table->timestamp('sent_at');
            $table->timestamps();

            // Dedup lookup: prevent duplicate notices for same transaction+type+days combo
            $table->index(
                ['library_transaction_id', 'notice_type', 'days_overdue'],
                'overdue_notices_dedup_idx'
            );

            // Borrower history queries
            $table->index(
                ['borrower_type', 'borrower_id'],
                'overdue_notices_borrower_idx'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('library_overdue_notices');
    }
};
