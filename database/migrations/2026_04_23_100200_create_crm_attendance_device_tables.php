<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_attendance_devices')) {
            Schema::create('crm_attendance_devices', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('device_identifier', 50)->unique();
                $table->string('location', 200)->nullable();
                $table->foreignId('api_token_id')->nullable()->constrained('personal_access_tokens')->nullOnDelete();
                $table->decimal('min_confidence', 3, 2)->default(0.80);
                $table->boolean('is_active')->default(true);
                $table->dateTime('last_heartbeat_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crm_attendance_device_logs')) {
            Schema::create('crm_attendance_device_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')->constrained('crm_attendance_devices')->cascadeOnDelete();
                $table->string('employee_identifier', 50);
                $table->string('event_type', 20);
                $table->dateTime('captured_at');
                $table->string('verification_method', 20)->nullable();
                $table->decimal('confidence_score', 4, 3)->nullable();
                $table->string('status', 30);
                $table->foreignId('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('attendance_record_id')->nullable()->constrained('crm_attendance_records')->nullOnDelete();
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['device_id', 'status']);
                $table->index(['employee_identifier', 'captured_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_attendance_device_logs');
        Schema::dropIfExists('crm_attendance_devices');
    }
};
