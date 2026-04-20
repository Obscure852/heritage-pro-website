<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('welfare_audit_log', function (Blueprint $table) {
            $table->id();

            // Term context (nullable for system operations)
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->integer('year')->nullable();

            // What was accessed/modified
            $table->string('table_name', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->unsignedBigInteger('welfare_case_id')->nullable();

            // Polymorphic relation for auditable models
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            // Action type (VARCHAR to support more action types)
            $table->string('action', 50);

            // Who performed the action
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('user_role', 50)->nullable();

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Change details (JSON for flexibility)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Reason for the action
            $table->text('reason')->nullable();

            // Single timestamp - audit logs are immutable
            $table->timestamp('created_at')->useCurrent();

            // Indexes for querying audit history
            $table->index(['table_name', 'record_id'], 'wal_table_record_idx');
            $table->index(['auditable_type', 'auditable_id'], 'wal_auditable_idx');
            $table->index('user_id');
            $table->index('welfare_case_id');
            $table->index('term_id');
            $table->index('action');
            $table->index('created_at');

            // Composite index for common queries
            $table->index(['welfare_case_id', 'created_at'], 'wal_case_time_idx');
            $table->index(['user_id', 'created_at'], 'wal_user_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_audit_log');
    }
};
