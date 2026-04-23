<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_attendance_codes')) {
            Schema::create('crm_attendance_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 8)->unique();
                $table->string('label', 100);
                $table->string('color', 7)->default('#64748b');
                $table->string('category', 20);
                $table->decimal('counts_as_working', 3, 2)->default(0.00);
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['category', 'is_active']);
            });

            DB::table('crm_attendance_codes')->insert([
                ['code' => 'P', 'label' => 'Present', 'color' => '#0ab39c', 'category' => 'presence', 'counts_as_working' => 1.00, 'is_system' => true, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'A', 'label' => 'Absent', 'color' => '#f06548', 'category' => 'absence', 'counts_as_working' => 0.00, 'is_system' => true, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'LA', 'label' => 'Late Arrival', 'color' => '#f7b84b', 'category' => 'presence', 'counts_as_working' => 1.00, 'is_system' => true, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'EO', 'label' => 'Early Out', 'color' => '#f7b84b', 'category' => 'presence', 'counts_as_working' => 1.00, 'is_system' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'H', 'label' => 'Holiday', 'color' => '#6559cc', 'category' => 'holiday', 'counts_as_working' => 0.00, 'is_system' => true, 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'WFH', 'label' => 'Work From Home', 'color' => '#0ab39c', 'category' => 'presence', 'counts_as_working' => 1.00, 'is_system' => false, 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'HD', 'label' => 'Half Day', 'color' => '#299cdb', 'category' => 'presence', 'counts_as_working' => 0.50, 'is_system' => false, 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'T', 'label' => 'Training', 'color' => '#405189', 'category' => 'duty', 'counts_as_working' => 1.00, 'is_system' => false, 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'OT', 'label' => 'Overtime', 'color' => '#0d6efd', 'category' => 'presence', 'counts_as_working' => 1.00, 'is_system' => false, 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'SU', 'label' => 'Suspended', 'color' => '#343a40', 'category' => 'absence', 'counts_as_working' => 0.00, 'is_system' => false, 'is_active' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'L', 'label' => 'Leave', 'color' => '#299cdb', 'category' => 'leave', 'counts_as_working' => 0.00, 'is_system' => true, 'is_active' => true, 'sort_order' => 11, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (! Schema::hasTable('crm_attendance_shifts')) {
            Schema::create('crm_attendance_shifts', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->boolean('is_default')->default(false);
                $table->unsignedSmallInteger('grace_minutes')->default(15);
                $table->unsignedSmallInteger('early_out_minutes')->default(15);
                $table->unsignedSmallInteger('overtime_after_minutes')->default(30);
                $table->time('earliest_clock_in')->nullable();
                $table->time('latest_clock_in')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $shiftId = DB::table('crm_attendance_shifts')->insertGetId([
                'name' => 'Standard Office',
                'is_default' => true,
                'grace_minutes' => 15,
                'early_out_minutes' => 15,
                'overtime_after_minutes' => 30,
                'earliest_clock_in' => '06:00:00',
                'latest_clock_in' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Schema::create('crm_attendance_shift_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shift_id')->constrained('crm_attendance_shifts')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_working_day')->default(true);

                $table->unique(['shift_id', 'day_of_week']);
            });

            foreach (range(0, 6) as $day) {
                DB::table('crm_attendance_shift_days')->insert([
                    'shift_id' => $shiftId,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                    'is_working_day' => $day <= 4,
                ]);
            }
        }

        if (! Schema::hasTable('crm_attendance_shift_overrides')) {
            Schema::create('crm_attendance_shift_overrides', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('shift_id')->constrained('crm_attendance_shifts')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'start_date', 'end_date']);
            });
        }

        if (! Schema::hasTable('crm_attendance_records')) {
            Schema::create('crm_attendance_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('date');
                $table->foreignId('attendance_code_id')->constrained('crm_attendance_codes');
                $table->dateTime('clocked_in_at')->nullable();
                $table->dateTime('clocked_out_at')->nullable();
                $table->string('source', 20)->default('manual');
                $table->text('clock_in_note')->nullable();
                $table->text('clock_out_note')->nullable();
                $table->unsignedInteger('total_minutes')->nullable();
                $table->unsignedInteger('overtime_minutes')->default(0);
                $table->boolean('is_late')->default(false);
                $table->boolean('is_early_out')->default(false);
                $table->boolean('auto_closed')->default(false);
                $table->string('status', 30)->default('active');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'date']);
                $table->index(['date', 'attendance_code_id']);
                $table->index(['user_id', 'date', 'status']);
            });
        }

        if (! Schema::hasTable('crm_attendance_corrections')) {
            Schema::create('crm_attendance_corrections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_record_id')->constrained('crm_attendance_records')->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->json('original_values');
                $table->dateTime('proposed_clock_in')->nullable();
                $table->dateTime('proposed_clock_out')->nullable();
                $table->foreignId('proposed_code_id')->nullable()->constrained('crm_attendance_codes')->nullOnDelete();
                $table->text('reason');
                $table->string('status', 20)->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['attendance_record_id', 'status']);
            });
        }

        if (! Schema::hasTable('crm_attendance_holidays')) {
            Schema::create('crm_attendance_holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->date('date');
                $table->boolean('is_recurring')->default(false);
                $table->string('applies_to', 20)->default('all');
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['date', 'is_active']);
                $table->index(['applies_to', 'scope_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_attendance_holidays');
        Schema::dropIfExists('crm_attendance_corrections');
        Schema::dropIfExists('crm_attendance_records');
        Schema::dropIfExists('crm_attendance_shift_overrides');
        Schema::dropIfExists('crm_attendance_shift_days');
        Schema::dropIfExists('crm_attendance_shifts');
        Schema::dropIfExists('crm_attendance_codes');
    }
};
