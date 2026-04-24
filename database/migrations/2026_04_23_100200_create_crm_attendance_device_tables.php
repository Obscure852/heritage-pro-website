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
                $table->string('brand', 30)->default('zkteco');
                $table->string('model', 80)->nullable();
                $table->string('device_identifier', 50)->unique();
                $table->string('serial_number', 80)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedSmallInteger('port')->nullable();
                $table->string('communication_key', 100)->nullable();
                $table->string('protocol', 20)->default('push');
                $table->string('direction', 10)->default('both');
                $table->string('timezone', 40)->nullable();
                $table->unsignedSmallInteger('heartbeat_interval')->default(60);
                $table->unsignedSmallInteger('push_interval')->default(30);
                $table->string('firmware_version', 60)->nullable();
                $table->unsignedInteger('user_capacity')->nullable();
                $table->unsignedInteger('fingerprint_capacity')->nullable();
                $table->unsignedInteger('face_capacity')->nullable();
                $table->json('supported_verify_methods')->nullable();
                $table->json('device_options')->nullable();
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
                $table->string('card_number', 30)->nullable();
                $table->decimal('temperature', 4, 1)->nullable();
                $table->string('work_code', 20)->nullable();
                $table->decimal('confidence_score', 4, 3)->nullable();
                $table->string('status', 30);
                $table->foreignId('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('attendance_record_id')->nullable()->constrained('crm_attendance_records')->nullOnDelete();
                $table->text('error_message')->nullable();
                $table->text('raw_payload')->nullable();
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
