<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_user_departments')) {
            Schema::create('crm_user_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedInteger('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crm_user_positions')) {
            Schema::create('crm_user_positions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedInteger('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crm_user_filters')) {
            Schema::create('crm_user_filters', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedInteger('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('active')->default(true);
                $table->string('role', 20)->default('rep');
                $table->date('date_of_birth')->nullable();
                $table->string('gender', 40)->nullable();
                $table->string('nationality', 120)->nullable();
                $table->string('id_number', 80)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('employment_status', 40)->nullable();
                $table->foreignId('department_id')->nullable()->constrained('crm_user_departments')->nullOnDelete();
                $table->foreignId('position_id')->nullable()->constrained('crm_user_positions')->nullOnDelete();
                $table->foreignId('reports_to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('personal_payroll_number', 80)->nullable();
                $table->date('date_of_appointment')->nullable();
                $table->string('avatar_path')->nullable();
                $table->timestamp('crm_onboarding_required_at')->nullable();
                $table->unsignedTinyInteger('crm_onboarding_step')->nullable();
                $table->timestamp('crm_onboarded_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();

                $table->index('id_number');
                $table->index('phone');
                $table->index('employment_status');
                $table->index('personal_payroll_number', 'users_payroll_number_index');
            });
        }

        if (! Schema::hasTable('crm_user_filter_user')) {
            Schema::create('crm_user_filter_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('crm_user_filter_id')->constrained('crm_user_filters')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'crm_user_filter_id'], 'crm_user_filter_user_unique');
            });
        }

        if (! Schema::hasTable('crm_user_qualifications')) {
            Schema::create('crm_user_qualifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->string('level')->nullable();
                $table->string('institution')->nullable();
                $table->date('start_date')->nullable();
                $table->date('completion_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'completion_date'], 'crm_user_qualifications_user_completion_index');
            });
        }

        if (! Schema::hasTable('crm_user_qualification_attachments')) {
            Schema::create('crm_user_qualification_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('qualification_id')->constrained('crm_user_qualifications')->cascadeOnDelete();
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('disk', 40)->default('documents');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type', 150)->nullable();
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();

                $table->index(['qualification_id', 'created_at'], 'crm_user_qualification_attachment_idx');
            });
        }

        if (! Schema::hasTable('crm_user_signatures')) {
            Schema::create('crm_user_signatures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('label');
                $table->string('disk', 40)->default('documents');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type', 150)->nullable();
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default'], 'crm_user_signatures_user_default_idx');
            });
        }

        if (! Schema::hasTable('crm_user_login_events')) {
            Schema::create('crm_user_login_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event_type', 40);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();

                $table->index(['user_id', 'occurred_at'], 'crm_user_login_events_user_occurred_idx');
            });
        }

        if (! Schema::hasTable('crm_user_module_permissions')) {
            Schema::create('crm_user_module_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('module_key', 40);
                $table->string('permission_level', 10);
                $table->timestamps();

                $table->unique(['user_id', 'module_key'], 'crm_user_module_permissions_unique');
            });
        }

        $this->createDefaultAdminUser();
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_user_module_permissions');
        Schema::dropIfExists('crm_user_login_events');
        Schema::dropIfExists('crm_user_signatures');
        Schema::dropIfExists('crm_user_qualification_attachments');
        Schema::dropIfExists('crm_user_qualifications');
        Schema::dropIfExists('crm_user_filter_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('crm_user_filters');
        Schema::dropIfExists('crm_user_positions');
        Schema::dropIfExists('crm_user_departments');
    }

    private function createDefaultAdminUser(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $timestamp = now();
        $departmentId = $this->ensureLookupRecord('crm_user_departments', 'Administration', $timestamp);
        $positionId = $this->ensureLookupRecord('crm_user_positions', 'System Administrator', $timestamp);
        $adminEmail = 'admin@heritage.local';
        $adminPayload = [
            'name' => 'System Administrator',
            'email' => $adminEmail,
            'email_verified_at' => $timestamp,
            'password' => Hash::make('Admin@123456'),
            'active' => true,
            'role' => 'admin',
            'date_of_birth' => '1987-01-01',
            'gender' => 'prefer_not_to_say',
            'nationality' => 'Motswana',
            'id_number' => 'ADMIN-0001',
            'phone' => '+26771000000',
            'employment_status' => 'active',
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'reports_to_user_id' => null,
            'personal_payroll_number' => 'PAY-ADMIN-0001',
            'date_of_appointment' => $timestamp->toDateString(),
            'avatar_path' => null,
            'crm_onboarding_required_at' => null,
            'crm_onboarding_step' => null,
            'crm_onboarded_at' => $timestamp,
            'remember_token' => null,
            'updated_at' => $timestamp,
        ];

        $adminId = DB::table('users')
            ->where('email', $adminEmail)
            ->value('id');

        if ($adminId === null) {
            $adminId = DB::table('users')->insertGetId($adminPayload + [
                'created_at' => $timestamp,
            ]);
        } else {
            DB::table('users')
                ->where('id', $adminId)
                ->update($adminPayload);
        }

        $this->syncAdminModulePermissions((int) $adminId, $timestamp);
    }

    private function ensureLookupRecord(string $table, string $name, $timestamp): int
    {
        $existingId = DB::table($table)
            ->where('name', $name)
            ->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $sortOrder = (int) DB::table($table)->max('sort_order') + 1;

        return (int) DB::table($table)->insertGetId([
            'name' => $name,
            'sort_order' => $sortOrder > 0 ? $sortOrder : 1,
            'is_active' => true,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function syncAdminModulePermissions(int $adminId, $timestamp): void
    {
        if ($adminId <= 0 || ! Schema::hasTable('crm_user_module_permissions')) {
            return;
        }

        $rows = collect(config('heritage_crm.modules', []))
            ->map(function (array $module, string $moduleKey) use ($adminId, $timestamp) {
                $permissionLevel = $module['default_permissions']['admin'] ?? null;

                if (! is_string($permissionLevel) || $permissionLevel === '') {
                    return null;
                }

                return [
                    'user_id' => $adminId,
                    'module_key' => $moduleKey,
                    'permission_level' => $permissionLevel,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        DB::table('crm_user_module_permissions')->upsert(
            $rows,
            ['user_id', 'module_key'],
            ['permission_level', 'updated_at']
        );
    }
};
