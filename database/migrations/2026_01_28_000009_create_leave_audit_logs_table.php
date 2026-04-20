<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the leave_audit_logs table for tracking all leave-related operations.
     * Stores polymorphic references to auditable models (LeaveRequest, LeaveBalance).
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('leave_audit_logs')) {
            return;
        }
        Schema::create('leave_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type');                          // Model class name (polymorphic)
            $table->unsignedBigInteger('auditable_id');                // Model ID (polymorphic)
            $table->string('action', 50);                              // Action type (create, update, approve, etc.)
            $table->unsignedBigInteger('user_id')->nullable();         // User who performed the action
            $table->json('old_values')->nullable();                    // State before the action
            $table->json('new_values')->nullable();                    // State after the action
            $table->text('notes')->nullable();                         // Additional context or comments
            $table->string('ip_address', 45)->nullable();              // IPv4 or IPv6 address
            $table->timestamp('created_at');                           // When the action occurred

            // Indexes for efficient querying
            $table->index(['auditable_type', 'auditable_id'], 'leave_audit_logs_auditable_index');
            $table->index('user_id', 'leave_audit_logs_user_index');
            $table->index('action', 'leave_audit_logs_action_index');
            $table->index('created_at', 'leave_audit_logs_created_index');

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_audit_logs');
    }
};
