<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the attendance_devices table for storing biometric device configurations.
     * Supports Hikvision and ZKTeco devices for staff attendance tracking.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_devices')) {
            return;
        }
        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                                    // Device display name
            $table->string('type', 30);                                     // Device type: 'hikvision', 'zkteco'
            $table->string('ip_address', 45)->unique();                     // IPv4 or IPv6 address
            $table->integer('port')->default(80);                           // Connection port
            $table->string('username')->nullable();                         // Authentication username
            $table->string('password')->nullable();                         // Encrypted credentials
            $table->string('serial_number', 50)->nullable();                // Device serial number
            $table->string('location')->nullable();                         // Physical location description
            $table->string('timezone', 50)->default('Africa/Gaborone');     // Device timezone
            $table->boolean('is_active')->default(true);                    // Whether device is active
            $table->timestamp('last_sync_at')->nullable();                  // Last successful sync time
            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficient querying
            $table->index('type', 'attendance_devices_type_index');
            $table->index('is_active', 'attendance_devices_active_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_devices');
    }
};
