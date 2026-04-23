<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_leave_types')) {
            Schema::create('crm_leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 10)->unique();
                $table->string('color', 7)->default('#299cdb');
                $table->decimal('default_days_per_year', 5, 1)->nullable();
                $table->boolean('requires_attachment')->default(false);
                $table->unsignedSmallInteger('attachment_required_after_days')->nullable();
                $table->unsignedSmallInteger('max_consecutive_days')->nullable();
                $table->unsignedSmallInteger('min_notice_days')->default(0);
                $table->boolean('allow_half_day')->default(true);
                $table->boolean('is_paid')->default(true);
                $table->decimal('counts_as_working', 3, 2)->default(0.00);
                $table->decimal('carry_over_limit', 5, 1)->nullable();
                $table->string('gender_restriction', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            DB::table('crm_leave_types')->insert([
                ['code' => 'AL', 'name' => 'Annual Leave', 'color' => '#0ab39c', 'default_days_per_year' => 21.0, 'requires_attachment' => false, 'attachment_required_after_days' => null, 'max_consecutive_days' => null, 'min_notice_days' => 3, 'allow_half_day' => true, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => 5.0, 'gender_restriction' => null, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'SL', 'name' => 'Sick Leave', 'color' => '#f06548', 'default_days_per_year' => 10.0, 'requires_attachment' => true, 'attachment_required_after_days' => 2, 'max_consecutive_days' => null, 'min_notice_days' => 0, 'allow_half_day' => true, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => null, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'ML', 'name' => 'Maternity Leave', 'color' => '#6559cc', 'default_days_per_year' => 90.0, 'requires_attachment' => true, 'attachment_required_after_days' => null, 'max_consecutive_days' => 90, 'min_notice_days' => 30, 'allow_half_day' => false, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => 'female', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'PL', 'name' => 'Paternity Leave', 'color' => '#405189', 'default_days_per_year' => 14.0, 'requires_attachment' => true, 'attachment_required_after_days' => null, 'max_consecutive_days' => 14, 'min_notice_days' => 7, 'allow_half_day' => false, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => 'male', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'CL', 'name' => 'Compassionate Leave', 'color' => '#f7b84b', 'default_days_per_year' => 5.0, 'requires_attachment' => false, 'attachment_required_after_days' => null, 'max_consecutive_days' => 5, 'min_notice_days' => 0, 'allow_half_day' => false, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => null, 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'STL', 'name' => 'Study Leave', 'color' => '#0d6efd', 'default_days_per_year' => 10.0, 'requires_attachment' => true, 'attachment_required_after_days' => null, 'max_consecutive_days' => null, 'min_notice_days' => 7, 'allow_half_day' => true, 'is_paid' => true, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => null, 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'UL', 'name' => 'Unpaid Leave', 'color' => '#343a40', 'default_days_per_year' => null, 'requires_attachment' => false, 'attachment_required_after_days' => null, 'max_consecutive_days' => null, 'min_notice_days' => 5, 'allow_half_day' => true, 'is_paid' => false, 'counts_as_working' => 0.00, 'carry_over_limit' => null, 'gender_restriction' => null, 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (! Schema::hasTable('crm_leave_balances')) {
            Schema::create('crm_leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('crm_leave_types')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->decimal('entitled_days', 5, 1)->default(0.0);
                $table->decimal('carried_over_days', 5, 1)->default(0.0);
                $table->decimal('adjustment_days', 5, 1)->default(0.0);
                $table->decimal('used_days', 5, 1)->default(0.0);
                $table->decimal('pending_days', 5, 1)->default(0.0);
                $table->timestamps();

                $table->unique(['user_id', 'leave_type_id', 'year']);
            });
        }

        if (! Schema::hasTable('crm_leave_requests')) {
            Schema::create('crm_leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('crm_leave_types');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('start_half', 20)->default('full');
                $table->string('end_half', 20)->default('full');
                $table->decimal('total_days', 5, 1);
                $table->text('reason');
                $table->string('status', 20)->default('draft');
                $table->dateTime('submitted_at')->nullable();
                $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedTinyInteger('escalation_level')->default(1);
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->boolean('attendance_synced')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'status']);
                $table->index(['current_approver_id', 'status']);
                $table->index(['start_date', 'end_date']);
            });
        }

        if (! Schema::hasTable('crm_leave_request_attachments')) {
            Schema::create('crm_leave_request_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_request_id')->constrained('crm_leave_requests')->cascadeOnDelete();
                $table->string('file_path', 500);
                $table->string('original_name', 255);
                $table->string('mime_type', 100);
                $table->unsignedInteger('size_bytes');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crm_leave_approval_trail')) {
            Schema::create('crm_leave_approval_trail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_request_id')->constrained('crm_leave_requests')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action', 20);
                $table->unsignedTinyInteger('level')->default(1);
                $table->text('comment')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['leave_request_id', 'action']);
            });
        }

        if (! Schema::hasTable('crm_leave_settings')) {
            Schema::create('crm_leave_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('attendance_integration_enabled')->default(false);
                $table->boolean('auto_mark_attendance_on_approve')->default(true);
                $table->boolean('auto_clear_attendance_on_cancel')->default(true);
                $table->unsignedSmallInteger('approval_reminder_hours')->default(48);
                $table->unsignedTinyInteger('max_escalation_levels')->default(2);
                $table->unsignedSmallInteger('escalation_after_hours')->default(72);
                $table->boolean('allow_retroactive_leave')->default(false);
                $table->unsignedSmallInteger('retroactive_limit_days')->default(5);
                $table->unsignedTinyInteger('balance_year_start_month')->default(1);
                $table->timestamps();
            });

            DB::table('crm_leave_settings')->insert([
                'attendance_integration_enabled' => false,
                'auto_mark_attendance_on_approve' => true,
                'auto_clear_attendance_on_cancel' => true,
                'approval_reminder_hours' => 48,
                'max_escalation_levels' => 2,
                'escalation_after_hours' => 72,
                'allow_retroactive_leave' => false,
                'retroactive_limit_days' => 5,
                'balance_year_start_month' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add leave_request_id to attendance records for traceability
        if (Schema::hasTable('crm_attendance_records') && ! Schema::hasColumn('crm_attendance_records', 'leave_request_id')) {
            Schema::table('crm_attendance_records', function (Blueprint $table) {
                $table->foreignId('leave_request_id')->nullable()->after('approved_at')
                    ->constrained('crm_leave_requests')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_attendance_records') && Schema::hasColumn('crm_attendance_records', 'leave_request_id')) {
            Schema::table('crm_attendance_records', function (Blueprint $table) {
                $table->dropConstrainedForeignId('leave_request_id');
            });
        }

        Schema::dropIfExists('crm_leave_approval_trail');
        Schema::dropIfExists('crm_leave_request_attachments');
        Schema::dropIfExists('crm_leave_requests');
        Schema::dropIfExists('crm_leave_balances');
        Schema::dropIfExists('crm_leave_settings');
        Schema::dropIfExists('crm_leave_types');
    }
};
