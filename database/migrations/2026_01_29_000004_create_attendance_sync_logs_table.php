<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the attendance_sync_logs table for tracking device synchronization operations.
     * DEV-07 requirement: error_message, error_details, retry_count.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_sync_logs')) {
            return;
        }
        Schema::create('attendance_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');                        // Device being synced
            $table->string('sync_type', 30);                                // 'pull_events', 'push_users', 'full_sync'
            $table->string('status', 20);                                   // 'success', 'failed', 'partial', 'running'
            $table->timestamp('started_at');                                // Sync start time
            $table->timestamp('completed_at')->nullable();                  // Sync completion time
            $table->integer('records_processed')->default(0);               // Number of records processed
            $table->integer('records_failed')->default(0);                  // Number of failed records
            $table->text('error_message')->nullable();                      // DEV-07: Error message
            $table->json('error_details')->nullable();                      // DEV-07: Detailed error info
            $table->unsignedInteger('retry_count')->default(0);             // DEV-07: Number of retries
            $table->timestamp('last_retry_at')->nullable();                 // Last retry attempt time
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('device_id', 'attendance_sync_logs_device_index');
            $table->index('status', 'attendance_sync_logs_status_index');
            $table->index('created_at', 'attendance_sync_logs_created_index');
            $table->index(['device_id', 'status'], 'attendance_sync_logs_device_status_index');

            // Foreign key constraint
            $table->foreign('device_id')
                ->references('id')
                ->on('attendance_devices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sync_logs');
    }
};
