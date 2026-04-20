<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_types')) {
            return;
        }
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_entitlement', 5, 2);
            $table->boolean('requires_attachment')->default(false);
            $table->integer('attachment_required_after_days')->nullable();
            $table->enum('gender_restriction', ['male', 'female'])->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('allow_negative_balance')->default(false);
            $table->boolean('allow_half_day')->default(true);
            $table->integer('min_notice_days')->default(0);
            $table->integer('max_consecutive_days')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('sort_order');
        });

        // Insert default leave types
        $now = now();
        DB::table('leave_types')->insert([
            [
                'code' => 'AL',
                'name' => 'Annual Leave',
                'description' => 'Standard paid annual leave entitlement for all employees.',
                'default_entitlement' => 20.00,
                'requires_attachment' => false,
                'attachment_required_after_days' => null,
                'gender_restriction' => null,
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => true,
                'min_notice_days' => 7,
                'max_consecutive_days' => null,
                'color' => '#3b82f6',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SL',
                'name' => 'Sick Leave',
                'description' => 'Leave for illness or medical appointments. Medical certificate required for absences exceeding 2 days.',
                'default_entitlement' => 12.00,
                'requires_attachment' => true,
                'attachment_required_after_days' => 2,
                'gender_restriction' => null,
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => true,
                'min_notice_days' => 0,
                'max_consecutive_days' => null,
                'color' => '#ef4444',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ML',
                'name' => 'Maternity Leave',
                'description' => 'Paid maternity leave for female employees. Medical documentation required.',
                'default_entitlement' => 90.00,
                'requires_attachment' => true,
                'attachment_required_after_days' => null,
                'gender_restriction' => 'female',
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => false,
                'min_notice_days' => 30,
                'max_consecutive_days' => 90,
                'color' => '#ec4899',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PL',
                'name' => 'Paternity Leave',
                'description' => 'Paid paternity leave for male employees following the birth of a child.',
                'default_entitlement' => 5.00,
                'requires_attachment' => true,
                'attachment_required_after_days' => null,
                'gender_restriction' => 'male',
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => false,
                'min_notice_days' => 7,
                'max_consecutive_days' => 5,
                'color' => '#6366f1',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CL',
                'name' => 'Compassionate Leave',
                'description' => 'Leave granted for bereavement or serious family emergencies.',
                'default_entitlement' => 5.00,
                'requires_attachment' => false,
                'attachment_required_after_days' => null,
                'gender_restriction' => null,
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => false,
                'min_notice_days' => 0,
                'max_consecutive_days' => 5,
                'color' => '#8b5cf6',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'STL',
                'name' => 'Study Leave',
                'description' => 'Leave for educational purposes, examinations, or professional development.',
                'default_entitlement' => 5.00,
                'requires_attachment' => true,
                'attachment_required_after_days' => null,
                'gender_restriction' => null,
                'is_paid' => true,
                'allow_negative_balance' => false,
                'allow_half_day' => true,
                'min_notice_days' => 14,
                'max_consecutive_days' => null,
                'color' => '#14b8a6',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'UL',
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay for personal reasons when paid leave is exhausted.',
                'default_entitlement' => 30.00,
                'requires_attachment' => false,
                'attachment_required_after_days' => null,
                'gender_restriction' => null,
                'is_paid' => false,
                'allow_negative_balance' => false,
                'allow_half_day' => true,
                'min_notice_days' => 7,
                'max_consecutive_days' => 30,
                'color' => '#6b7280',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(){
        Schema::dropIfExists('leave_types');
    }
};
