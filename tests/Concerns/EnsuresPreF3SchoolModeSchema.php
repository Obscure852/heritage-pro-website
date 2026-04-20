<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait EnsuresPreF3SchoolModeSchema
{
    protected function ensurePreF3SchoolModeSchema(): void
    {
        $this->ensureSchoolSetupTable();
        $this->ensureSmsApiSettingsTable();
        $this->ensureTermsTable();
        $this->ensureUsersTable();
        $this->ensureSponsorsTable();
        $this->ensureEmailsTable();
        $this->ensureDepartmentsTable();
        $this->ensureGradesTable();
        $this->ensureStudentsTable();
        $this->ensureKlassesTable();
        $this->ensureKlassStudentTable();
        $this->ensureStudentTermTable();
        $this->ensureSubjectsTable();
        $this->ensureGradeSubjectTable();
        $this->ensureKlassSubjectTable();
        $this->ensureOptionalSubjectsTable();
        $this->ensureComponentsTable();
        $this->ensureTestsTable();
        $this->ensureValueAdditionSubjectMappingsTable();
        $this->ensurePsleGradesTable();
        $this->ensureJceGradesTable();
    }

    protected function resetPreF3SchoolModeTables(): void
    {
        foreach ([
            'tests',
            'components',
            'grade_subject',
            'optional_subjects',
            'klass_subject',
            'value_addition_subject_mappings',
            'subjects',
            'emails',
            'sponsors',
            'psle_grades',
            'jce_grades',
            'klass_student',
            'klasses',
            'student_term',
            'students',
            'grades',
            'departments',
            'terms',
            'school_setup',
            's_m_s_api_settings',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
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
            $table->string('logo_path')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('fax')->nullable();
            $table->boolean('use_custom_login_image')->default(false);
            $table->string('login_image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureSmsApiSettingsTable(): void
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
            $table->string('validation_rules')->nullable();
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
            $table->unsignedInteger('term');
            $table->unsignedInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('closed')->default(false);
            $table->timestamps();
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
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('area_of_work')->nullable();
            $table->string('position')->nullable();
            $table->unsignedBigInteger('reporting_to')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
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
            $table->timestamps();
        });
    }

    private function ensureSponsorsTable(): void
    {
        if (Schema::hasTable('sponsors')) {
            Schema::table('sponsors', function (Blueprint $table): void {
                if (!Schema::hasColumn('sponsors', 'email')) {
                    $table->string('email')->nullable();
                }
                if (!Schema::hasColumn('sponsors', 'year')) {
                    $table->unsignedInteger('year')->nullable();
                }
            });
            return;
        }

        Schema::create('sponsors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('connect_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureEmailsTable(): void
    {
        if (Schema::hasTable('emails')) {
            return;
        }

        Schema::create('emails', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('receiver_type')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('num_of_recipients')->default(1);
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    private function ensureGradesTable(): void
    {
        if (Schema::hasTable('grades')) {
            return;
        }

        Schema::create('grades', function (Blueprint $table): void {
            $table->id();
            $table->integer('sequence');
            $table->string('name');
            $table->string('promotion')->nullable();
            $table->string('description')->nullable();
            $table->string('level');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureStudentsTable(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table): void {
                if (!Schema::hasColumn('students', 'connect_id')) {
                    $table->unsignedBigInteger('connect_id')->nullable();
                }
                if (!Schema::hasColumn('students', 'sponsor_id')) {
                    $table->unsignedBigInteger('sponsor_id')->nullable();
                }
                if (!Schema::hasColumn('students', 'middle_name')) {
                    $table->string('middle_name')->nullable();
                }
                if (!Schema::hasColumn('students', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable();
                }
                if (!Schema::hasColumn('students', 'id_number')) {
                    $table->string('id_number')->nullable();
                }
                if (!Schema::hasColumn('students', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('students', 'is_boarding')) {
                    $table->boolean('is_boarding')->default(false);
                }
                if (!Schema::hasColumn('students', 'year')) {
                    $table->unsignedInteger('year')->nullable();
                }
            });
            return;
        }

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('connect_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_number')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_boarding')->default(false);
            $table->unsignedInteger('year')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureKlassesTable(): void
    {
        if (Schema::hasTable('klasses')) {
            return;
        }

        Schema::create('klasses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureKlassStudentTable(): void
    {
        if (Schema::hasTable('klass_student')) {
            return;
        }

        Schema::create('klass_student', function (Blueprint $table): void {
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->timestamps();
        });
    }

    private function ensureStudentTermTable(): void
    {
        if (Schema::hasTable('student_term')) {
            return;
        }

        Schema::create('student_term', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureSubjectsTable(): void
    {
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table): void {
                if (!Schema::hasColumn('subjects', 'canonical_key')) {
                    $table->string('canonical_key')->nullable();
                }
                if (!Schema::hasColumn('subjects', 'syllabus_url')) {
                    $table->string('syllabus_url')->nullable();
                }
                if (!Schema::hasColumn('subjects', 'is_double')) {
                    $table->boolean('is_double')->default(false);
                }
            });
            return;
        }

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('abbrev')->nullable();
            $table->string('name');
            $table->string('canonical_key')->nullable();
            $table->string('level');
            $table->boolean('components')->default(false);
            $table->string('description')->nullable();
            $table->string('department')->nullable();
            $table->string('syllabus_url')->nullable();
            $table->boolean('is_double')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureGradeSubjectTable(): void
    {
        if (Schema::hasTable('grade_subject')) {
            return;
        }

        Schema::create('grade_subject', function (Blueprint $table): void {
            $table->id();
            $table->integer('sequence')->default(0);
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->unsignedInteger('year')->nullable();
            $table->string('type')->nullable();
            $table->boolean('mandatory')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureComponentsTable(): void
    {
        if (Schema::hasTable('components')) {
            return;
        }

        Schema::create('components', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('grade_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    private function ensureKlassSubjectTable(): void
    {
        if (Schema::hasTable('klass_subject')) {
            return;
        }

        Schema::create('klass_subject', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assistant_user_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
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
            $table->unsignedBigInteger('grade_subject_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assistant_user_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('grouping')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureTestsTable(): void
    {
        if (Schema::hasTable('tests')) {
            return;
        }

        Schema::create('tests', function (Blueprint $table): void {
            $table->id();
            $table->integer('sequence')->default(1);
            $table->string('name');
            $table->string('abbrev');
            $table->unsignedBigInteger('grade_subject_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->integer('out_of')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->string('type')->nullable();
            $table->boolean('assessment')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureValueAdditionSubjectMappingsTable(): void
    {
        if (Schema::hasTable('value_addition_subject_mappings')) {
            return;
        }

        Schema::create('value_addition_subject_mappings', function (Blueprint $table): void {
            $table->id();
            $table->string('school_type');
            $table->string('exam_type');
            $table->string('source_key');
            $table->string('source_label')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function ensurePsleGradesTable(): void
    {
        if (Schema::hasTable('psle_grades')) {
            return;
        }

        Schema::create('psle_grades', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('overall_grade')->nullable();
            $table->string('agriculture_grade')->nullable();
            $table->string('mathematics_grade')->nullable();
            $table->string('english_grade')->nullable();
            $table->string('science_grade')->nullable();
            $table->string('social_studies_grade')->nullable();
            $table->string('setswana_grade')->nullable();
            $table->string('capa_grade')->nullable();
            $table->string('religious_and_moral_education_grade')->nullable();
            $table->timestamps();
        });
    }

    private function ensureJceGradesTable(): void
    {
        if (Schema::hasTable('jce_grades')) {
            return;
        }

        Schema::create('jce_grades', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('overall')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('english')->nullable();
            $table->string('science')->nullable();
            $table->string('setswana')->nullable();
            $table->string('design_and_technology')->nullable();
            $table->string('home_economics')->nullable();
            $table->string('agriculture')->nullable();
            $table->string('social_studies')->nullable();
            $table->string('moral_education')->nullable();
            $table->string('religious_education')->nullable();
            $table->string('music')->nullable();
            $table->string('physical_education')->nullable();
            $table->string('art')->nullable();
            $table->string('office_procedures')->nullable();
            $table->string('accounting')->nullable();
            $table->string('french')->nullable();
            $table->timestamps();
        });
    }
}
