<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the staff_attendance_settings table for module configuration.
     * Key-value store for attendance module settings (following leave_settings pattern).
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('staff_attendance_settings')) {
            return;
        }
        Schema::create('staff_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();                                // Setting key
            $table->json('value');                                          // Setting value (JSON)
            $table->text('description')->nullable();                        // Setting description
            $table->unsignedBigInteger('updated_by')->nullable();           // User who last updated
            $table->timestamp('updated_at')->nullable();                    // Last update time

            // Index for efficient lookup
            $table->index('key', 'staff_attendance_settings_key_index');

            // Foreign key constraint
            $table->foreign('updated_by')
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
        Schema::dropIfExists('staff_attendance_settings');
    }
};
