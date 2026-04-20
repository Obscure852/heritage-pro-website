<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class StudentProfileEnrollmentTabsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensurePreF3SchoolModeSchema();
        $this->ensureStudentProfileSupportTables();
        $this->ensureRoleTables();
        $this->resetPreF3SchoolModeTables();
        $this->resetStudentProfileTables();
        Cache::flush();
        view()->share('errors', new ViewErrorBag());

        session(['selected_term_id' => 1]);

        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Enrollment Aware School',
            'type' => SchoolSetup::TYPE_PRE_F3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('terms')->insert([
            'id' => 1,
            'term' => 1,
            'year' => 2026,
            'start_date' => '2026-01-10',
            'end_date' => '2026-04-10',
            'closed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sponsors')->insert([
            'id' => 1,
            'connect_id' => 111111,
            'first_name' => 'Parent',
            'last_name' => 'One',
            'email' => 'parent@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'area_of_work' => 'Management',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_users')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('nationalities')->insert([
            'name' => 'Motswana',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('grades')->insert([
            [
                'id' => 1,
                'sequence' => 2,
                'name' => 'STD 4',
                'promotion' => 'STD 5',
                'description' => 'Standard 4',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 10,
                'name' => 'F2',
                'promotion' => 'F3',
                'description' => 'Form 2',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 12,
                'name' => 'F4',
                'promotion' => 'F5',
                'description' => 'Form 4',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('departments')->insert([
            ['id' => 1, 'name' => 'Academics', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Sciences', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('klasses')->insert([
            [
                'id' => 1,
                'name' => '4A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 1,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '2A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 2,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '4B',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 3,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('students')->insert([
            [
                'id' => 1,
                'sponsor_id' => 1,
                'first_name' => 'Primary',
                'last_name' => 'Student',
                'gender' => 'F',
                'date_of_birth' => '2016-01-01',
                'nationality' => 'Motswana',
                'id_number' => 'PRI-001',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sponsor_id' => 1,
                'first_name' => 'Junior',
                'last_name' => 'Student',
                'gender' => 'M',
                'date_of_birth' => '2013-01-01',
                'nationality' => 'Motswana',
                'id_number' => 'JUN-001',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sponsor_id' => 1,
                'first_name' => 'Senior',
                'last_name' => 'Student',
                'gender' => 'F',
                'date_of_birth' => '2011-01-01',
                'nationality' => 'Motswana',
                'id_number' => 'SEN-001',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('student_term')->insert([
            [
                'student_id' => 1,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 2,
                'term_id' => 1,
                'grade_id' => 2,
                'year' => 2026,
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 3,
                'term_id' => 1,
                'grade_id' => 3,
                'year' => 2026,
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('klass_student')->insert([
            ['student_id' => 1, 'klass_id' => 1, 'grade_id' => 1, 'term_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => 2, 'klass_id' => 2, 'grade_id' => 2, 'term_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => 3, 'klass_id' => 3, 'grade_id' => 3, 'term_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('subjects')->insert([
            [
                'id' => 1,
                'abbrev' => 'MATH',
                'name' => 'Mathematics',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'components' => false,
                'department' => 'Academics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'abbrev' => 'ENG',
                'name' => 'English',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'components' => false,
                'department' => 'Academics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'abbrev' => 'BIO',
                'name' => 'Biology',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'components' => false,
                'department' => 'Sciences',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grade_subject')->insert([
            [
                'id' => 1,
                'sequence' => 1,
                'grade_id' => 1,
                'subject_id' => 1,
                'term_id' => 1,
                'department_id' => 1,
                'year' => 2026,
                'type' => 1,
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 1,
                'grade_id' => 2,
                'subject_id' => 2,
                'term_id' => 1,
                'department_id' => 1,
                'year' => 2026,
                'type' => 1,
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 1,
                'grade_id' => 3,
                'subject_id' => 3,
                'term_id' => 1,
                'department_id' => 2,
                'year' => 2026,
                'type' => 1,
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('tests')->insert([
            [
                'id' => 1,
                'sequence' => 1,
                'name' => 'Primary Exam',
                'abbrev' => 'EX',
                'out_of' => 100,
                'grade_id' => 1,
                'grade_subject_id' => 1,
                'term_id' => 1,
                'type' => 'Exam',
                'assessment' => true,
                'year' => 2026,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 1,
                'name' => 'Junior Exam',
                'abbrev' => 'EX',
                'out_of' => 100,
                'grade_id' => 2,
                'grade_subject_id' => 2,
                'term_id' => 1,
                'type' => 'Exam',
                'assessment' => true,
                'year' => 2026,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 1,
                'name' => 'Senior Exam',
                'abbrev' => 'EX',
                'out_of' => 100,
                'grade_id' => 3,
                'grade_subject_id' => 3,
                'term_id' => 1,
                'type' => 'Exam',
                'assessment' => true,
                'year' => 2026,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('student_tests')->insert([
            [
                'id' => 1,
                'student_id' => 1,
                'test_id' => 1,
                'score' => 80,
                'percentage' => 80,
                'grade' => 'A',
                'points' => 0,
                'avg_score' => 80,
                'avg_grade' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 2,
                'test_id' => 2,
                'score' => 74,
                'percentage' => 74,
                'grade' => 'B',
                'points' => 2,
                'avg_score' => 74,
                'avg_grade' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'student_id' => 3,
                'test_id' => 3,
                'score' => 88,
                'percentage' => 88,
                'grade' => 'A',
                'points' => 1,
                'avg_score' => 88,
                'avg_grade' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('psle_grades')->insert([
            'student_id' => 2,
            'overall_grade' => 'B',
            'mathematics_grade' => 'B',
            'english_grade' => 'B',
            'science_grade' => 'C',
            'setswana_grade' => 'B',
            'agriculture_grade' => 'B',
            'social_studies_grade' => 'B',
            'religious_and_moral_education_grade' => 'B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jce_grades')->insert([
            'student_id' => 3,
            'overall' => 'A',
            'mathematics' => 'A',
            'english' => 'B',
            'science' => 'A',
            'setswana' => 'B',
            'social_studies' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail(1));
    }

    public function test_pref3_primary_student_profile_hides_external_exam_tabs_and_uses_primary_layout(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRE_F3]);

        $response = $this->get(route('students.show', 1));

        $response->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false)
            ->assertSee('Possible Marks', false);
    }

    public function test_pref3_junior_student_profile_shows_psle_tab_and_junior_layout(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRE_F3]);

        $response = $this->get(route('students.show', 2));

        $response->assertOk()
            ->assertSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false)
            ->assertSee('PSLE Grade:', false)
            ->assertSee('Total Points:', false);
    }

    public function test_junior_senior_senior_student_profile_shows_jce_tab_and_senior_layout(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);

        $response = $this->get(route('students.show', 3));

        $response->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertSee('href="#jce"', false)
            ->assertSee('JCE Grade:', false)
            ->assertSee('Best 6 Subjects Total Points:', false);
    }

    public function test_k12_profile_tabs_follow_each_students_enrollment_not_global_mode(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);

        $primaryResponse = $this->get(route('students.show', 1));
        $juniorResponse = $this->get(route('students.show', 2));
        $seniorResponse = $this->get(route('students.show', 3));

        $primaryResponse->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false);

        $juniorResponse->assertOk()
            ->assertSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false);

        $seniorResponse->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertSee('href="#jce"', false);
    }

    public function test_single_mode_profiles_keep_their_expected_exam_tabs(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRIMARY]);
        $this->get(route('students.show', 1))
            ->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false);

        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR]);
        $this->get(route('students.show', 2))
            ->assertOk()
            ->assertSee('href="#psle"', false)
            ->assertDontSee('href="#jce"', false);

        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_SENIOR]);
        $this->get(route('students.show', 3))
            ->assertOk()
            ->assertDontSee('href="#psle"', false)
            ->assertSee('href="#jce"', false);
    }

    public function test_psle_save_is_rejected_for_non_junior_students(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);

        $response = $this->post(route('students.create-psle', 1), [
            'overall_grade' => 'A',
            'mathematics_grade' => 'A',
            'english_grade' => 'A',
            'science_grade' => 'A',
            'setswana_grade' => 'A',
            'agriculture_grade' => 'A',
            'social_studies_grade' => 'A',
            'religious_and_moral_education_grade' => 'A',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('error', 'PSLE grades can only be saved for Middle School students.');

        $this->assertDatabaseMissing('psle_grades', [
            'student_id' => 1,
            'overall_grade' => 'A',
        ]);
    }

    public function test_jce_save_is_rejected_for_non_senior_students(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);

        $response = $this->post(route('students.create-jce', 2), [
            'overall' => 'A',
            'mathematics' => 'A',
            'english' => 'A',
            'science' => 'A',
            'setswana' => 'A',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('error', 'JCE grades can only be saved for High School students.');

        $this->assertDatabaseMissing('jce_grades', [
            'student_id' => 2,
            'overall' => 'A',
        ]);
    }

    public function test_progress_report_export_uses_enrollment_aware_template_selection(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);

        $primaryPdf = \Mockery::mock(DomPdf::class);
        $primaryPdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $primaryPdf->shouldReceive('download')->once()->andReturn(response('primary-pdf', 200));

        $juniorPdf = \Mockery::mock(DomPdf::class);
        $juniorPdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $juniorPdf->shouldReceive('download')->once()->andReturn(response('junior-pdf', 200));

        $seniorPdf = \Mockery::mock(DomPdf::class);
        $seniorPdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $seniorPdf->shouldReceive('download')->once()->andReturn(response('senior-pdf', 200));

        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            return $view === 'students.primary-progress-report-pdf'
                && ($data['usesPrimaryAcademicLayout'] ?? false) === true
                && ($data['showPsleTab'] ?? true) === false
                && ($data['showJceTab'] ?? true) === false;
        })->andReturn($primaryPdf);

        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            return $view === 'students.junior-progress-report-pdf'
                && ($data['usesJuniorAcademicLayout'] ?? false) === true
                && ($data['showPsleTab'] ?? false) === true
                && ($data['showJceTab'] ?? true) === false;
        })->andReturn($juniorPdf);

        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            return $view === 'students.senior-progress-report-pdf'
                && ($data['usesSeniorAcademicLayout'] ?? false) === true
                && ($data['showPsleTab'] ?? true) === false
                && ($data['showJceTab'] ?? false) === true;
        })->andReturn($seniorPdf);

        $this->get(route('students.export-progress-report', 1))->assertOk();
        $this->get(route('students.export-progress-report', 2))->assertOk();
        $this->get(route('students.export-progress-report', 3))->assertOk();
    }

    private function ensureStudentProfileSupportTables(): void
    {
        if (!Schema::hasTable('nationalities')) {
            Schema::create('nationalities', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_filters')) {
            Schema::create('student_filters', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_types')) {
            Schema::create('student_types', function (Blueprint $table): void {
                $table->id();
                $table->string('type');
                $table->boolean('exempt')->default(false);
                $table->text('description')->nullable();
                $table->string('color')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('houses')) {
            Schema::create('houses', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('color_code', 7)->default('#2563EB');
                $table->unsignedBigInteger('head')->nullable();
                $table->unsignedBigInteger('assistant')->nullable();
                $table->unsignedBigInteger('term_id')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_house')) {
            Schema::create('student_house', function (Blueprint $table): void {
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('house_id');
                $table->unsignedBigInteger('term_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_departures')) {
            Schema::create('student_departures', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->date('last_day_of_attendance')->nullable();
                $table->string('reason_for_leaving')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_medical_informations')) {
            Schema::create('student_medical_informations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->text('health_history')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('student_behaviours')) {
            Schema::create('student_behaviours', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id')->nullable();
                $table->date('date')->nullable();
                $table->string('behaviour_type')->nullable();
                $table->text('description')->nullable();
                $table->text('action_taken')->nullable();
                $table->text('remarks')->nullable();
                $table->string('reported_by')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('klass_id')->nullable();
                $table->unsignedBigInteger('term_id')->nullable();
                $table->date('date')->nullable();
                $table->string('status')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('book_allocations')) {
            Schema::create('book_allocations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('copy_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->string('accession_number')->nullable();
                $table->date('allocation_date')->nullable();
                $table->date('due_date')->nullable();
                $table->date('return_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('books')) {
            Schema::create('books', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->string('title');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('copies')) {
            Schema::create('copies', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('book_id')->nullable();
                $table->string('accession_number')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_tests')) {
            Schema::create('student_tests', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('test_id');
                $table->decimal('score', 8, 2)->nullable();
                $table->decimal('percentage', 8, 2)->nullable();
                $table->string('grade')->nullable();
                $table->integer('points')->nullable();
                $table->decimal('avg_score', 8, 2)->nullable();
                $table->string('avg_grade')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('klass_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('term_id')->nullable();
                $table->text('class_teacher_remarks')->nullable();
                $table->text('school_head_remarks')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('subject_comments')) {
            Schema::create('subject_comments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_test_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_subject_id')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('term_id')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function ensureRoleTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function resetStudentProfileTables(): void
    {
        foreach ([
            'role_users',
            'roles',
            'student_tests',
            'subject_comments',
            'comments',
            'book_allocations',
            'copies',
            'books',
            'attendances',
            'student_behaviours',
            'student_medical_informations',
            'student_departures',
            'student_house',
            'houses',
            'student_types',
            'student_filters',
            'nationalities',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
