<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait EnsuresActivitiesPhaseOneSchema
{
    protected function ensureActivitiesPhaseOneSchema(): void
    {
        $this->ensureRolesTables();
        $this->ensureSchoolSetupTable();
        $this->ensureSystemSettingsTable();
        $this->ensureTermsTable();
        $this->ensureFeeTypesTable();
        $this->ensureActivityLookupTables();
        $this->ensureSponsorTables();
        $this->ensureStudentTables();
        $this->ensureFeeBillingTables();
        $this->ensureActivitiesTables();
        $this->seedModuleVisibilitySettings();
        $this->seedActivitySettings();
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function ensureSchoolSetupTable(): void
    {
        if (Schema::hasTable('school_setup')) {
            return;
        }

        Schema::create('school_setup', function (Blueprint $table): void {
            $table->id();
            $table->string('school_name')->nullable();
            $table->string('school_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureSystemSettingsTable(): void
    {
        if (Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        Schema::create('s_m_s_api_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->text('validation_rules')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    private function ensureTermsTable(): void
    {
        if (Schema::hasTable('terms')) {
            return;
        }

        Schema::create('terms', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('term_type')->nullable();
            $table->unsignedTinyInteger('term')->default(1);
            $table->unsignedSmallInteger('year');
            $table->boolean('closed')->default(false);
            $table->unsignedInteger('extension_days')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureFeeTypesTable(): void
    {
        if (Schema::hasTable('fee_types')) {
            return;
        }

        Schema::create('fee_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('category', 20);
            $table->text('description')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureActivityLookupTables(): void
    {
        if (!Schema::hasTable('student_filters')) {
            Schema::create('student_filters', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table): void {
                $table->id();
                $table->integer('sequence')->default(1);
                $table->string('name');
                $table->string('promotion')->default('Yes');
                $table->string('description')->default('Phase test grade');
                $table->string('level')->default('Junior');
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('term_id');
                $table->unsignedSmallInteger('year');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('houses')) {
            Schema::create('houses', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('color_code', 7)->default('#2563EB');
                $table->unsignedBigInteger('head');
                $table->unsignedBigInteger('assistant');
                $table->unsignedBigInteger('term_id');
                $table->unsignedSmallInteger('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('klasses')) {
            Schema::create('klasses', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedBigInteger('grade_id');
                $table->unsignedBigInteger('monitor_id')->nullable();
                $table->unsignedBigInteger('monitress_id')->nullable();
                $table->boolean('type')->nullable()->default(true);
                $table->boolean('active')->default(true);
                $table->unsignedSmallInteger('year');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function ensureStudentTables(): void
    {
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('connect_id')->nullable();
                $table->unsignedBigInteger('sponsor_id')->nullable();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('middle_name')->nullable();
                $table->string('status')->default('Current');
                $table->string('gender', 4)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('nationality')->nullable();
                $table->string('id_number')->nullable();
                $table->decimal('credit', 10, 2)->default(0);
                $table->boolean('parent_is_staff')->default(false);
                $table->boolean('is_boarding')->default(false);
                $table->unsignedBigInteger('student_filter_id')->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('student_term')) {
            Schema::create('student_term', function (Blueprint $table): void {
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedBigInteger('grade_id');
                $table->unsignedSmallInteger('year');
                $table->string('status')->default('Current');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['student_id', 'term_id']);
                $table->index(['term_id', 'grade_id', 'status']);
            });
        }

        if (!Schema::hasTable('klass_student')) {
            Schema::create('klass_student', function (Blueprint $table): void {
                $table->unsignedBigInteger('klass_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->index(['klass_id', 'term_id']);
                $table->index(['student_id', 'term_id']);
            });
        }

        if (!Schema::hasTable('student_house')) {
            Schema::create('student_house', function (Blueprint $table): void {
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('house_id');
                $table->unsignedBigInteger('term_id');
                $table->timestamps();

                $table->index(['student_id', 'term_id']);
                $table->index(['house_id', 'term_id']);
            });
        }

        if (!Schema::hasTable('user_house')) {
            Schema::create('user_house', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('house_id');
                $table->unsignedBigInteger('term_id');
                $table->timestamps();

                $table->unique(['user_id', 'term_id']);
                $table->index(['house_id', 'term_id']);
                $table->index('term_id');
            });
        }
    }

    private function ensureSponsorTables(): void
    {
        if (Schema::hasTable('sponsors')) {
            return;
        }

        Schema::create('sponsors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('connect_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('relation')->nullable();
            $table->string('status')->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureFeeBillingTables(): void
    {
        if (!Schema::hasTable('student_invoices')) {
            Schema::create('student_invoices', function (Blueprint $table): void {
                $table->id();
                $table->string('invoice_number')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedSmallInteger('year');
                $table->decimal('subtotal_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('balance', 12, 2)->default(0);
                $table->decimal('credit_balance', 12, 2)->default(0);
                $table->string('status', 20)->default('issued');
                $table->timestamp('issued_at')->nullable();
                $table->date('due_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('last_reminder_sent_at')->nullable();
                $table->unsignedInteger('reminder_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['student_id', 'year']);
            });
        }

        if (!Schema::hasTable('student_invoice_items')) {
            Schema::create('student_invoice_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_invoice_id');
                $table->unsignedBigInteger('activity_fee_charge_id')->nullable();
                $table->unsignedBigInteger('fee_structure_id')->nullable();
                $table->string('item_type', 30)->default('fee');
                $table->unsignedSmallInteger('source_year')->nullable();
                $table->string('description', 255);
                $table->decimal('amount', 10, 2);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2);
                $table->timestamps();

                $table->index('student_invoice_id');
                $table->unique('activity_fee_charge_id');
            });

            return;
        }

        $invoiceItemColumns = Schema::getColumnListing('student_invoice_items');

        if (!in_array('activity_fee_charge_id', $invoiceItemColumns, true)) {
            Schema::table('student_invoice_items', function (Blueprint $table): void {
                $table->unsignedBigInteger('activity_fee_charge_id')->nullable()->after('student_invoice_id');
            });
        }

        $invoiceItemColumns = Schema::getColumnListing('student_invoice_items');

        if (!in_array('item_type', $invoiceItemColumns, true)) {
            Schema::table('student_invoice_items', function (Blueprint $table): void {
                $table->string('item_type', 30)->default('fee')->after('fee_structure_id');
            });
        }

        $invoiceItemColumns = Schema::getColumnListing('student_invoice_items');

        if (!in_array('source_year', $invoiceItemColumns, true)) {
            Schema::table('student_invoice_items', function (Blueprint $table): void {
                $table->unsignedSmallInteger('source_year')->nullable()->after('item_type');
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_invoice_items MODIFY COLUMN item_type VARCHAR(30) NOT NULL DEFAULT 'fee'");
        }
    }

    private function ensureActivitiesTables(): void
    {
        if (!Schema::hasTable('activities')) {
            Schema::create('activities', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code', 50);
                $table->string('category', 50);
                $table->string('delivery_mode', 30);
                $table->string('participation_mode', 30);
                $table->string('result_mode', 30);
                $table->text('description')->nullable();
                $table->string('default_location')->nullable();
                $table->unsignedInteger('capacity')->nullable();
                $table->string('gender_policy', 20)->nullable();
                $table->boolean('attendance_required')->default(true);
                $table->boolean('allow_house_linkage')->default(false);
                $table->unsignedBigInteger('fee_type_id')->nullable();
                $table->decimal('default_fee_amount', 10, 2)->nullable();
                $table->string('status', 20)->default('draft');
                $table->unsignedBigInteger('term_id');
                $table->unsignedSmallInteger('year');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['term_id', 'year']);
                $table->index(['status', 'category']);
                $table->unique(['code', 'year']);
            });
        }

        if (!Schema::hasTable('activity_staff_assignments')) {
            Schema::create('activity_staff_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 50);
                $table->boolean('is_primary')->default(false);
                $table->boolean('active')->default(true);
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('removed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_eligibility_targets')) {
            Schema::create('activity_eligibility_targets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('target_type', 50);
                $table->unsignedBigInteger('target_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_enrollments')) {
            Schema::create('activity_enrollments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedSmallInteger('year');
                $table->string('status', 20)->default('active');
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('left_at')->nullable();
                $table->unsignedBigInteger('joined_by')->nullable();
                $table->unsignedBigInteger('left_by')->nullable();
                $table->text('exit_reason')->nullable();
                $table->string('source', 30)->default('manual');
                $table->unsignedBigInteger('grade_id_snapshot')->nullable();
                $table->unsignedBigInteger('klass_id_snapshot')->nullable();
                $table->unsignedBigInteger('house_id_snapshot')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['activity_id', 'term_id', 'status']);
                $table->index(['student_id', 'term_id']);
            });
        }

        if (!Schema::hasTable('activity_schedules')) {
            Schema::create('activity_schedules', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('frequency', 30);
                $table->unsignedTinyInteger('day_of_week')->nullable();
                $table->time('start_time');
                $table->time('end_time')->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('location')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('activity_sessions')) {
            Schema::create('activity_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('activity_schedule_id')->nullable();
                $table->string('session_type', 50);
                $table->date('session_date');
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime')->nullable();
                $table->string('location')->nullable();
                $table->string('status', 20)->default('planned');
                $table->boolean('attendance_locked')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['activity_id', 'session_date']);
                $table->index(['status', 'attendance_locked']);
            });
        }

        if (!Schema::hasTable('activity_session_attendance')) {
            Schema::create('activity_session_attendance', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_session_id');
                $table->unsignedBigInteger('activity_enrollment_id');
                $table->unsignedBigInteger('student_id');
                $table->string('status', 20);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('marked_by')->nullable();
                $table->timestamp('marked_at')->nullable();
                $table->timestamps();

                $table->index(['activity_session_id', 'activity_enrollment_id']);
                $table->index(['student_id', 'status']);
            });
        }

        if (!Schema::hasTable('activity_events')) {
            Schema::create('activity_events', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('title');
                $table->string('event_type', 50);
                $table->text('description')->nullable();
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime')->nullable();
                $table->string('location')->nullable();
                $table->string('opponent_or_partner_name')->nullable();
                $table->boolean('house_linked')->default(false);
                $table->boolean('publish_to_calendar')->default(false);
                $table->string('calendar_sync_status', 30)->default('not_published');
                $table->string('status', 20)->default('scheduled');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['activity_id', 'status']);
                $table->index('start_datetime');
            });
        }

        if (!Schema::hasTable('activity_results')) {
            Schema::create('activity_results', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_event_id');
                $table->string('participant_type', 20);
                $table->unsignedBigInteger('participant_id');
                $table->string('metric_type', 30)->nullable();
                $table->decimal('score_value', 10, 2)->nullable();
                $table->unsignedInteger('placement')->nullable();
                $table->integer('points')->nullable();
                $table->string('award_name')->nullable();
                $table->string('result_label')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['activity_event_id', 'participant_type']);
            });
        }

        if (!Schema::hasTable('activity_fee_charges')) {
            Schema::create('activity_fee_charges', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('activity_enrollment_id')->nullable();
                $table->unsignedBigInteger('activity_event_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('fee_type_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedSmallInteger('year');
                $table->string('charge_type', 30);
                $table->decimal('amount', 10, 2);
                $table->string('billing_status', 20)->default('pending');
                $table->unsignedBigInteger('student_invoice_id')->nullable();
                $table->unsignedBigInteger('student_invoice_item_id')->nullable();
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['activity_id', 'billing_status']);
                $table->index(['student_id', 'term_id', 'year']);
            });
        }

        if (!Schema::hasTable('activity_audit_logs')) {
            Schema::create('activity_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->string('action', 50);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['entity_type', 'entity_id']);
                $table->index('action');
            });
        }
    }

    private function seedModuleVisibilitySettings(): void
    {
        foreach ([
            'modules.activities_visible',
            'modules.assets_visible',
            'modules.communications_visible',
            'modules.contacts_visible',
            'modules.fees_visible',
            'modules.leave_visible',
            'modules.library_visible',
            'modules.lms_visible',
            'modules.schemes_visible',
            'modules.staff_attendance_visible',
            'modules.staff_pdp_visible',
            'modules.timetable_visible',
            'modules.invigilation_visible',
            'modules.welfare_visible',
        ] as $key) {
            DB::table('s_m_s_api_settings')->insertOrIgnore([
                'key' => $key,
                'value' => '1',
                'category' => 'modules',
                'type' => 'boolean',
                'display_name' => $key,
                'is_editable' => true,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedActivitySettings(): void
    {
        $settings = [
            [
                'key' => 'activities.options.categories',
                'value' => json_encode($this->activityOptionRows([
                    'club' => 'Club',
                    'sport' => 'Sport',
                    'society' => 'Society',
                    'arts' => 'Arts',
                    'service' => 'Service',
                    'academic' => 'Academic',
                    'event_program' => 'Event Program',
                    'other' => 'Other',
                ])),
                'display_name' => 'Activities Categories',
                'display_order' => 10,
            ],
            [
                'key' => 'activities.options.delivery_modes',
                'value' => json_encode($this->activityOptionRows([
                    'recurring' => 'Recurring',
                    'one_off' => 'One Off',
                    'hybrid' => 'Hybrid',
                ])),
                'display_name' => 'Activities Delivery Modes',
                'display_order' => 20,
            ],
            [
                'key' => 'activities.options.participation_modes',
                'value' => json_encode($this->activityOptionRows([
                    'individual' => 'Individual',
                    'team' => 'Team',
                    'mixed' => 'Mixed',
                ])),
                'display_name' => 'Activities Participation Modes',
                'display_order' => 30,
            ],
            [
                'key' => 'activities.options.result_modes',
                'value' => json_encode($this->activityOptionRows([
                    'attendance_only' => 'Attendance Only',
                    'placements' => 'Placements',
                    'points' => 'Points',
                    'awards' => 'Awards',
                    'mixed' => 'Mixed',
                ], [
                    'attendance_only' => ['allows_results' => false],
                    'placements' => ['allows_results' => true],
                    'points' => ['allows_results' => true],
                    'awards' => ['allows_results' => true],
                    'mixed' => ['allows_results' => true],
                ])),
                'display_name' => 'Activities Result Modes',
                'display_order' => 40,
            ],
            [
                'key' => 'activities.options.gender_policies',
                'value' => json_encode($this->activityOptionRows([
                    'boys' => 'Boys',
                    'girls' => 'Girls',
                    'mixed' => 'Mixed',
                ])),
                'display_name' => 'Activities Gender Policies',
                'display_order' => 50,
            ],
            [
                'key' => 'activities.options.event_types',
                'value' => json_encode($this->activityOptionRows([
                    'fixture' => 'Fixture',
                    'showcase' => 'Showcase',
                    'competition' => 'Competition',
                    'workshop' => 'Workshop',
                    'exhibition' => 'Exhibition',
                    'other' => 'Other',
                ])),
                'display_name' => 'Activities Event Types',
                'display_order' => 60,
            ],
            [
                'key' => 'activities.defaults.activity',
                'value' => json_encode([
                    'category' => 'club',
                    'delivery_mode' => 'recurring',
                    'participation_mode' => 'team',
                    'result_mode' => 'mixed',
                    'gender_policy' => 'mixed',
                    'capacity' => null,
                    'attendance_required' => true,
                    'allow_house_linkage' => false,
                ]),
                'display_name' => 'Activities Default Activity Settings',
                'display_order' => 70,
            ],
            [
                'key' => 'activities.defaults.events',
                'value' => json_encode([
                    'event_type' => 'fixture',
                    'publish_to_calendar' => false,
                    'house_linked' => false,
                ]),
                'display_name' => 'Activities Default Event Settings',
                'display_order' => 80,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('s_m_s_api_settings')->insertOrIgnore([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'category' => 'activities',
                'type' => 'json',
                'description' => $setting['display_name'],
                'display_name' => $setting['display_name'],
                'is_editable' => true,
                'display_order' => $setting['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function activityOptionRows(array $labels, array $extraByKey = []): array
    {
        $rows = [];

        foreach ($labels as $key => $label) {
            $rows[] = array_merge([
                'key' => $key,
                'label' => $label,
                'active' => true,
                'system' => true,
            ], $extraByKey[$key] ?? []);
        }

        return $rows;
    }
}
