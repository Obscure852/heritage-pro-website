<?php

namespace Tests\Feature\Assessment;

use App\Http\Controllers\Assessment\PrimaryAssessmentController;
use App\Http\Controllers\AssessmentController;
use App\Models\SchoolSetup;
use App\Services\PrimaryReportCardBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class PrimaryReportCardOptimizationTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensurePreF3SchoolModeSchema();
        $this->ensurePrimaryReportCardSchema();
        $this->resetPrimaryReportCardTables();
        $this->resetPreF3SchoolModeTables();
        Cache::flush();

        $this->seedPrimaryReportCardFixture();
        session(['selected_term_id' => 1]);
        Cache::put('attendance_absent_codes', ['A'], 3600);
    }

    public function test_primary_report_card_builder_prepares_student_data_with_bounded_queries(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $data = app(PrimaryReportCardBuilder::class)->buildStudentReport(1, 1, 1);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(20, count($queries));
        $this->assertSame('Alice', $data['student']->first_name);
        $this->assertSame('STD 4A', $data['currentClass']->name);
        $this->assertSame(2, $data['classSize']);
        $this->assertSame(3, $data['absentDays']);
        $this->assertSame('Ms Class Teacher', $data['teacherName']);
        $this->assertSame('Mr School Head', $data['schoolHeadName']);
        $this->assertCount(2, $data['scores']);
        $this->assertSame(170.0, $data['totalScore']);
        $this->assertSame(200.0, $data['totalOutOf']);
        $this->assertEqualsWithDelta(85.0, $data['averagePercentage'], 0.001);
        $this->assertSame(1, $data['rank']);
        $this->assertSame('A', $data['overallGrade']?->grade);
        $this->assertSame('Excellent effort.', $data['classTeacherRemarks']);
        $this->assertSame('Ready for the next grade.', $data['headTeachersRemarks']);
        $this->assertSame('Healthy and active', $data['otherInfo']);
        $this->assertEquals(250.75, $data['schoolFees']);
        $this->assertSame('Strong Math performance', $data['scores'][0]['comments']);
        $this->assertSame('Confident reader', $data['scores'][1]['comments']);
    }

    public function test_primary_html_controller_returns_prepared_view_data(): void
    {
        $view = app(PrimaryAssessmentController::class)->primaryHTMLReportCard(1);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('assessment.primary.primary-report-card-html', $view->name());

        $data = $view->getData();

        $this->assertSame('Alice', $data['student']->first_name);
        $this->assertSame('STD 4A', $data['currentClass']->name);
        $this->assertCount(2, $data['scores']);
        $this->assertSame(2, $data['classSize']);
        $this->assertSame('2026-01-10', $data['termStart']);
        $this->assertSame('2026-04-10', $data['termEnd']);
    }

    public function test_primary_pdf_views_render_without_hitting_report_card_tables(): void
    {
        $studentData = app(PrimaryReportCardBuilder::class)->buildStudentReport(1, 1, 1);
        Cache::forget('school_type');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $studentHtml = view('assessment.primary.primary-report-card-pdf', $studentData)->render();
        $studentQueries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertStringContainsString('Alice', $studentHtml);
        $this->assertStringContainsString('Mathematics', $studentHtml);
        $this->assertNoReportCardDataQueries($studentQueries->all());

        $classData = app(PrimaryReportCardBuilder::class)->buildClassReport(1, 1, 1);
        Cache::forget('school_type');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $classHtml = view('assessment.primary.report-card-primary-list-pdf', $classData)->render();
        $classQueries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertStringContainsString('Alice', $classHtml);
        $this->assertStringContainsString('Bob', $classHtml);
        $this->assertNoReportCardDataQueries($classQueries->all());
    }

    public function test_primary_pdf_controllers_still_generate_student_and_bulk_pdfs(): void
    {
        $studentResponse = app(PrimaryAssessmentController::class)->primaryPDFReportCard(1);

        $this->assertSame('application/pdf', $studentResponse->headers->get('content-type'));
        $this->assertStringContainsString('student-report-card.pdf', (string) $studentResponse->headers->get('content-disposition'));

        $bulkResponse = app(PrimaryAssessmentController::class)->pdfReportCardsForClassPrimary(1);

        $this->assertSame('application/pdf', $bulkResponse->headers->get('content-type'));
        $this->assertStringContainsString('class-report-cards.pdf', (string) $bulkResponse->headers->get('content-disposition'));
    }

    public function test_combined_mode_email_pdf_generation_uses_same_primary_builder(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRE_F3]);
        Cache::flush();

        $pdf = app(AssessmentController::class)->generatePrimaryReportCardPDF(1);

        $this->assertNotEmpty($pdf->output());
    }

    private function assertNoReportCardDataQueries(array $queries): void
    {
        $forbiddenTables = [
            'student_tests',
            'subject_comments',
            'manual_attendance_entries',
            'attendances',
            'klass_student',
            'comments',
            'overall_grading_matrices',
        ];

        foreach ($queries as $query) {
            $normalizedQuery = strtolower($query);

            foreach ($forbiddenTables as $table) {
                $this->assertStringNotContainsString($table, $normalizedQuery);
            }
        }
    }

    private function seedPrimaryReportCardFixture(): void
    {
        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Heritage Primary',
            'school_id' => 'HP-01',
            'type' => SchoolSetup::TYPE_PRIMARY,
            'physical_address' => 'Plot 100, Gaborone',
            'postal_address' => 'P O Box 123',
            'telephone' => '3900000',
            'fax' => '3900001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('terms')->insert([
            [
                'id' => 1,
                'term' => 1,
                'year' => 2026,
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
                'closed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'term' => 2,
                'year' => 2026,
                'start_date' => '2026-05-15',
                'end_date' => '2026-08-15',
                'closed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('departments')->insert([
            'id' => 1,
            'name' => 'Academics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            [
                'id' => 10,
                'firstname' => 'Ms',
                'lastname' => 'Class Teacher',
                'email' => 'teacher@example.com',
                'position' => 'Teacher',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'firstname' => 'Mr',
                'lastname' => 'School Head',
                'email' => 'head@example.com',
                'position' => 'School Head',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grades')->insert([
            'id' => 1,
            'sequence' => 5,
            'name' => 'STD 4',
            'promotion' => 'STD 5',
            'description' => 'Standard 4',
            'level' => SchoolSetup::LEVEL_PRIMARY,
            'active' => true,
            'term_id' => 1,
            'year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('klasses')->insert([
            'id' => 1,
            'name' => 'STD 4A',
            'user_id' => 10,
            'term_id' => 1,
            'grade_id' => 1,
            'active' => true,
            'year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('subjects')->insert([
            [
                'id' => 1,
                'abbrev' => 'MATH',
                'name' => 'Mathematics',
                'canonical_key' => 'mathematics',
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
                'canonical_key' => 'english',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'components' => false,
                'department' => 'Academics',
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
                'department_id' => 1,
                'term_id' => 1,
                'year' => 2026,
                'type' => '1',
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 2,
                'grade_id' => 1,
                'subject_id' => 2,
                'department_id' => 1,
                'term_id' => 1,
                'year' => 2026,
                'type' => '1',
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('klass_subject')->insert([
            [
                'id' => 1,
                'klass_id' => 1,
                'grade_subject_id' => 1,
                'user_id' => 10,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'klass_id' => 1,
                'grade_subject_id' => 2,
                'user_id' => 10,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('students')->insert([
            [
                'id' => 1,
                'connect_id' => 1001,
                'first_name' => 'Alice',
                'last_name' => 'Anderson',
                'gender' => 'F',
                'date_of_birth' => '2015-03-01',
                'nationality' => 'Motswana',
                'id_number' => 'STD4-001',
                'status' => 'Current',
                'type' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'connect_id' => 1002,
                'first_name' => 'Bob',
                'last_name' => 'Brown',
                'gender' => 'M',
                'date_of_birth' => '2015-04-01',
                'nationality' => 'Motswana',
                'id_number' => 'STD4-002',
                'status' => 'Current',
                'type' => 'Current',
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
                'grade_id' => 1,
                'year' => 2026,
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('klass_student')->insert([
            [
                'klass_id' => 1,
                'student_id' => 1,
                'active' => true,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'klass_id' => 1,
                'student_id' => 2,
                'active' => true,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('tests')->insert([
            [
                'id' => 1,
                'sequence' => 1,
                'name' => 'Mathematics Exam',
                'abbrev' => 'MEX',
                'grade_subject_id' => 1,
                'term_id' => 1,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'Exam',
                'assessment' => true,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 1,
                'name' => 'English Exam',
                'abbrev' => 'EEX',
                'grade_subject_id' => 2,
                'term_id' => 1,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'Exam',
                'assessment' => true,
                'start_date' => '2026-03-02',
                'end_date' => '2026-03-02',
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
                'points' => 1,
                'avg_score' => 80,
                'avg_grade' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 1,
                'test_id' => 2,
                'score' => 90,
                'percentage' => 90,
                'grade' => 'A',
                'points' => 1,
                'avg_score' => 90,
                'avg_grade' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'student_id' => 2,
                'test_id' => 1,
                'score' => 60,
                'percentage' => 60,
                'grade' => 'C',
                'points' => 3,
                'avg_score' => 60,
                'avg_grade' => 'C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'student_id' => 2,
                'test_id' => 2,
                'score' => 70,
                'percentage' => 70,
                'grade' => 'B',
                'points' => 2,
                'avg_score' => 70,
                'avg_grade' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('comments')->insert([
            [
                'id' => 1,
                'student_id' => 1,
                'klass_id' => 1,
                'user_id' => 10,
                'term_id' => 1,
                'class_teacher_remarks' => 'Excellent effort.',
                'school_head_remarks' => 'Ready for the next grade.',
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 2,
                'klass_id' => 1,
                'user_id' => 10,
                'term_id' => 1,
                'class_teacher_remarks' => 'Keep working steadily.',
                'school_head_remarks' => 'Shows good potential.',
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('subject_comments')->insert([
            [
                'id' => 1,
                'student_test_id' => 1,
                'student_id' => 1,
                'grade_subject_id' => 1,
                'remarks' => 'Strong Math performance',
                'user_id' => 10,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'student_test_id' => 2,
                'student_id' => 1,
                'grade_subject_id' => 2,
                'remarks' => 'Confident reader',
                'user_id' => 10,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'student_test_id' => 3,
                'student_id' => 2,
                'grade_subject_id' => 1,
                'remarks' => 'Needs revision',
                'user_id' => 10,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'student_test_id' => 4,
                'student_id' => 2,
                'grade_subject_id' => 2,
                'remarks' => 'Improving steadily',
                'user_id' => 10,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('manual_attendance_entries')->insert([
            'id' => 1,
            'student_id' => 1,
            'term_id' => 1,
            'days_absent' => 3,
            'school_fees_owing' => 250.75,
            'other_info' => 'Healthy and active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('attendances')->insert([
            [
                'id' => 1,
                'student_id' => 2,
                'klass_id' => 1,
                'term_id' => 1,
                'date' => '2026-02-01',
                'status' => 'A',
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 2,
                'klass_id' => 1,
                'term_id' => 1,
                'date' => '2026-02-02',
                'status' => 'A',
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('overall_grading_matrices')->insert([
            [
                'id' => 1,
                'term_id' => 1,
                'year' => 2026,
                'grade_id' => 1,
                'grade' => 'C',
                'min_score' => 0,
                'max_score' => 69.99,
                'description' => 'Average',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'term_id' => 1,
                'year' => 2026,
                'grade_id' => 1,
                'grade' => 'B',
                'min_score' => 70,
                'max_score' => 79.99,
                'description' => 'Good',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'term_id' => 1,
                'year' => 2026,
                'grade_id' => 1,
                'grade' => 'A',
                'min_score' => 80,
                'max_score' => 100,
                'description' => 'Excellent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function ensurePrimaryReportCardSchema(): void
    {
        if (!Schema::hasTable('student_tests')) {
            Schema::create('student_tests', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('test_id');
                $table->decimal('score', 8, 2)->nullable();
                $table->decimal('percentage', 8, 2)->nullable();
                $table->string('grade')->nullable();
                $table->decimal('points', 8, 2)->nullable();
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
                $table->unsignedBigInteger('term_id');
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
                $table->unsignedBigInteger('grade_subject_id');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('term_id');
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('manual_attendance_entries')) {
            Schema::create('manual_attendance_entries', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->integer('days_absent')->nullable();
                $table->decimal('school_fees_owing', 12, 2)->nullable();
                $table->text('other_info')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('klass_id')->nullable();
                $table->unsignedBigInteger('term_id');
                $table->date('date');
                $table->string('status');
                $table->unsignedInteger('year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('overall_grading_matrices')) {
            Schema::create('overall_grading_matrices', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('term_id');
                $table->unsignedInteger('year')->nullable();
                $table->unsignedBigInteger('grade_id');
                $table->string('grade');
                $table->decimal('min_score', 8, 2);
                $table->decimal('max_score', 8, 2);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function resetPrimaryReportCardTables(): void
    {
        foreach ([
            'overall_grading_matrices',
            'attendances',
            'manual_attendance_entries',
            'subject_comments',
            'comments',
            'student_tests',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
