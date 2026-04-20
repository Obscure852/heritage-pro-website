<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait EnsuresInvigilationSchema
{
    private function ensureInvigilationSchema(): void
    {
        $this->ensureSchoolSetupTable();
        $this->ensureTermsTable();
        $this->ensureUsersTable();
        $this->ensureGradesTable();
        $this->ensureSubjectsTable();
        $this->ensureDepartmentsTable();
        $this->ensureGradeSubjectsTable();
        $this->ensureVenuesTable();
        $this->ensureKlassSubjectTable();
        $this->ensureOptionalSubjectsTable();
        $this->ensureInvigilationTables();
        $this->ensureTimetableTables();
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

    private function ensureTermsTable(): void
    {
        if (Schema::hasTable('terms')) {
            return;
        }

        Schema::create('terms', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('term')->default(1);
            $table->unsignedInteger('year')->default((int) now()->year);
            $table->boolean('closed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('area_of_work')->nullable();
            $table->string('status')->nullable();
            $table->string('position')->nullable();
            $table->unsignedBigInteger('reporting_to')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureGradesTable(): void
    {
        if (Schema::hasTable('grades')) {
            return;
        }

        Schema::create('grades', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('level')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureSubjectsTable(): void
    {
        if (Schema::hasTable('subjects')) {
            return;
        }

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('abbrev')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureDepartmentsTable(): void
    {
        if (Schema::hasTable('departments')) {
            return;
        }

        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('department_head')->nullable();
            $table->unsignedBigInteger('assistant')->nullable();
            $table->timestamps();
        });
    }

    private function ensureGradeSubjectsTable(): void
    {
        if (Schema::hasTable('grade_subject')) {
            return;
        }

        Schema::create('grade_subject', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('sequence')->default(1);
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->string('type')->nullable();
            $table->boolean('mandatory')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureVenuesTable(): void
    {
        if (Schema::hasTable('venues')) {
            return;
        }

        Schema::create('venues', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureKlassSubjectTable(): void
    {
        if (Schema::hasTable('klass_subject')) {
            return;
        }

        Schema::create('klass_subject', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('klass_id')->nullable();
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assistant_user_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureOptionalSubjectsTable(): void
    {
        if (Schema::hasTable('optional_subjects')) {
            return;
        }

        Schema::create('optional_subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assistant_user_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->string('grouping')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureInvigilationTables(): void
    {
        if (!Schema::hasTable('invigilation_series')) {
            Schema::create('invigilation_series', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->unsignedBigInteger('term_id');
                $table->string('status');
                $table->string('eligibility_policy');
                $table->string('timetable_conflict_policy');
                $table->string('balancing_policy')->default('balanced');
                $table->unsignedInteger('default_required_invigilators')->default(1);
                $table->text('notes')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invigilation_sessions')) {
            Schema::create('invigilation_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('series_id');
                $table->unsignedBigInteger('grade_subject_id');
                $table->string('paper_label')->nullable();
                $table->date('exam_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('day_of_cycle')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invigilation_session_rooms')) {
            Schema::create('invigilation_session_rooms', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('venue_id');
                $table->string('source_type');
                $table->unsignedBigInteger('klass_subject_id')->nullable();
                $table->unsignedBigInteger('optional_subject_id')->nullable();
                $table->string('group_label')->nullable();
                $table->unsignedInteger('candidate_count')->default(0);
                $table->unsignedInteger('required_invigilators')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invigilation_assignments')) {
            Schema::create('invigilation_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_room_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('assignment_order')->default(1);
                $table->string('assignment_source')->default('manual');
                $table->boolean('locked')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    private function ensureTimetableTables(): void
    {
        if (!Schema::hasTable('timetables')) {
            Schema::create('timetables', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('term_id');
                $table->string('name');
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('timetable_slots')) {
            Schema::create('timetable_slots', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('timetable_id');
                $table->unsignedBigInteger('klass_subject_id')->nullable();
                $table->unsignedBigInteger('optional_subject_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('venue_id')->nullable();
                $table->unsignedBigInteger('assistant_teacher_id')->nullable();
                $table->unsignedInteger('day_of_cycle');
                $table->unsignedInteger('period_number');
                $table->unsignedInteger('duration')->default(1);
                $table->boolean('is_locked')->default(false);
                $table->string('block_id')->nullable();
                $table->string('coupling_group_key')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('timetable_settings')) {
            Schema::create('timetable_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }
}
