<?php

namespace Tests\Feature\Assessment;

use App\Http\Controllers\Assessment\PrimaryAssessmentController;
use App\Models\SchoolSetup;
use App\Services\PrimaryAnalysisReportBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class PrimaryAnalysisOptimizationTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensurePreF3SchoolModeSchema();
        $this->ensurePrimaryAnalysisSchema();
        $this->resetPrimaryAnalysisTables();
        $this->resetPreF3SchoolModeTables();
        Cache::flush();

        $this->seedPrimaryAnalysisFixture();
        session(['selected_term_id' => 1]);
    }

    public function test_primary_analysis_builder_uses_bounded_queries_for_class_and_grade_performance(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $classData = app(PrimaryAnalysisReportBuilder::class)->buildClassPerformance(1, 1, 'CA', 1);

        $classQueries = collect(DB::getQueryLog())->pluck('query');
        DB::flushQueryLog();

        $gradeData = app(PrimaryAnalysisReportBuilder::class)->buildGradePerformance(1, 1, 'CA', 1);

        $gradeQueries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(15, $classQueries->count());
        $this->assertLessThanOrEqual(12, $gradeQueries->count());
        $this->assertSame(1, $classQueries->filter(fn (string $query) => str_contains(strtolower($query), 'student_tests'))->count());
        $this->assertSame(1, $classQueries->filter(fn (string $query) => str_contains(strtolower($query), 'overall_grading_matrices'))->count());
        $this->assertSame(2, count($classData['allStudentData']));
        $this->assertSame('Alice Adams', $classData['allStudentData'][0]['studentName']);
        $this->assertSame('Cara Cole', $gradeData['allStudentData'][0]['studentName']);
        $this->assertSame('Alice Adams', $gradeData['allStudentData'][1]['studentName']);
        $this->assertSame('Bob Brown', $gradeData['allStudentData'][2]['studentName']);
    }

    public function test_primary_grade_scoped_analysis_excludes_other_term_students_and_keeps_expected_counts(): void
    {
        $gradeData = app(PrimaryAnalysisReportBuilder::class)->buildGradePerformance(1, 1, 'CA', 1);
        $overallData = app(PrimaryAnalysisReportBuilder::class)->buildOverallGradeDistribution(1, 1, 'CA', 1);
        $subjectData = app(PrimaryAnalysisReportBuilder::class)->buildSubjectGradePerformance(1, 1, 'CA', 1);
        $regionalData = app(PrimaryAnalysisReportBuilder::class)->buildRegionalExamPerformance(1, 1);

        $studentNames = array_column($gradeData['allStudentData'], 'studentName');

        $this->assertCount(3, $studentNames);
        $this->assertNotContains('Dylan Dube', $studentNames);
        $this->assertSame(['Mathematics', 'English'], $gradeData['subjects']);

        $this->assertSame(2, $overallData['gradeDistributions']['A']['F']);
        $this->assertSame(2, $overallData['gradeDistributions']['B']['F']);
        $this->assertSame(2, $overallData['gradeDistributions']['C']['M']);
        $this->assertSame(4, $overallData['gradeDistributions']['AB']['F']);
        $this->assertSame(2, $overallData['gradeDistributions']['ABC']['M']);

        $this->assertSame(1, $subjectData['subjectPerformance']['Mathematics']['A']['F']);
        $this->assertSame(1, $subjectData['subjectPerformance']['Mathematics']['B']['F']);
        $this->assertSame(1, $subjectData['subjectPerformance']['Mathematics']['C']['M']);
        $this->assertSame(1, $subjectData['subjectPerformance']['English']['A']['F']);
        $this->assertSame(1, $subjectData['subjectPerformance']['English']['B']['F']);
        $this->assertSame(1, $subjectData['subjectPerformance']['English']['C']['M']);

        $this->assertSame(3, $regionalData['subjectPerformance']['Mathematics']['Candidates']['T']);
        $this->assertSame(1, $regionalData['subjectPerformance']['Mathematics']['A']['F']);
        $this->assertSame(1, $regionalData['subjectPerformance']['Mathematics']['B']['F']);
        $this->assertSame(1, $regionalData['subjectPerformance']['Mathematics']['D']['M']);
        $this->assertEqualsWithDelta(66.67, $regionalData['subjectPerformance']['Mathematics']['ABC%']['T'], 0.01);
        $this->assertEqualsWithDelta(33.33, $regionalData['subjectPerformance']['Mathematics']['DEU%']['T'], 0.01);
    }

    public function test_primary_analysis_controller_returns_expected_views_for_all_report_types(): void
    {
        $controller = app(PrimaryAssessmentController::class);

        $classView = $controller->htmlClassPerformanceAnalysis(1, 'CA', 1);
        $gradeView = $controller->htmlGradePerformanceAnalysis(1, 'CA', 1);
        $overallView = $controller->generateGradePerformanceReport(1, 'CA', 1);
        $subjectView = $controller->generateSubjectGradePerformanceReport(new Request(), 1, 'CA', 1);
        $regionalView = $controller->generateRegionalGradePerformanceReport(new Request(), 1);

        $this->assertInstanceOf(View::class, $classView);
        $this->assertSame('assessment.primary.test-primary-class-analysis', $classView->name());
        $this->assertCount(2, $classView->getData()['allStudentData']);

        $this->assertInstanceOf(View::class, $gradeView);
        $this->assertSame('assessment.primary.test-primary-grade-analysis', $gradeView->name());
        $this->assertCount(3, $gradeView->getData()['allStudentData']);

        $this->assertInstanceOf(View::class, $overallView);
        $this->assertSame('assessment.primary.test-primary-overall-grade-analysis', $overallView->name());
        $this->assertSame(4, $overallView->getData()['gradeDistributions']['AB']['F']);

        $this->assertInstanceOf(View::class, $subjectView);
        $this->assertSame('assessment.primary.test-primary-grade-subject-analysis', $subjectView->name());
        $this->assertSame(1, $subjectView->getData()['subjectPerformance']['English']['A']['F']);

        $this->assertInstanceOf(View::class, $regionalView);
        $this->assertSame('assessment.primary.regional-test-primary-grade-subject-analysis', $regionalView->name());
        $this->assertSame(3, $regionalView->getData()['subjectPerformance']['English']['Candidates']['T']);
    }

    public function test_primary_analysis_exports_use_shared_payloads_and_return_downloads(): void
    {
        $controller = app(PrimaryAssessmentController::class);
        $request = Request::create('/', 'GET');

        $classExportResponse = $controller->htmlClassPerformanceAnalysisExport($request, 1, 'CA', 1);
        $gradeExportResponse = $controller->htmlGradePerformanceAnalysisExport($request, 1, 'CA', 1);
        $regionalExportResponse = $controller->regionalGradePerformanceReportExport($request, 1);
        $subjectExportResponse = $controller->generateSubjectGradePerformanceReportExport(1, 'CA', 1);

        $this->assertInstanceOf(BinaryFileResponse::class, $classExportResponse);
        $this->assertStringContainsString('class-performance-analysis.xlsx', (string) $classExportResponse->headers->get('content-disposition'));

        $this->assertInstanceOf(BinaryFileResponse::class, $gradeExportResponse);
        $this->assertStringContainsString('grade-performance-analysis.xlsx', (string) $gradeExportResponse->headers->get('content-disposition'));

        $this->assertInstanceOf(BinaryFileResponse::class, $regionalExportResponse);
        $this->assertStringContainsString('region-performance.xlsx', (string) $regionalExportResponse->headers->get('content-disposition'));

        $this->assertInstanceOf(StreamedResponse::class, $subjectExportResponse);
        $this->assertStringContainsString('subject-performance.xlsx', (string) $subjectExportResponse->headers->get('content-disposition'));

        $classExportHtml = (new \App\Exports\ClassPerformanceAnalysisExport(
            app(PrimaryAnalysisReportBuilder::class)->buildClassPerformance(1, 1, 'CA', 1)
        ))->view()->render();
        $gradePayload = app(PrimaryAnalysisReportBuilder::class)->buildGradePerformance(1, 1, 'CA', 1);
        $gradePayload['klass'] = \App\Models\Klass::query()->with('grade')->findOrFail(1);
        $gradeExportHtml = (new \App\Exports\GradePerformanceAnalysisExport($gradePayload))->view()->render();

        $this->assertStringContainsString('Alice Adams', $classExportHtml);
        $this->assertStringContainsString('Cara Cole', $gradeExportHtml);
    }

    public function test_primary_analysis_views_use_local_chart_assets_and_combined_modes_keep_primary_behavior(): void
    {
        $bladeFiles = [
            resource_path('views/assessment/primary/test-primary-class-analysis.blade.php') => '/assets/libs/echarts/echarts.min.js',
            resource_path('views/assessment/primary/test-primary-grade-analysis.blade.php') => '/assets/libs/echarts/echarts.min.js',
            resource_path('views/assessment/primary/test-primary-overall-grade-analysis.blade.php') => '/assets/libs/echarts/echarts.min.js',
            resource_path('views/assessment/primary/test-primary-grade-subject-analysis.blade.php') => '/assets/libs/echarts/echarts.min.js',
            resource_path('views/assessment/primary/regional-test-primary-grade-subject-analysis.blade.php') => '/assets/libs/echarts/echarts.min.js',
        ];

        foreach ($bladeFiles as $path => $expectedAsset) {
            $contents = file_get_contents($path);

            $this->assertStringContainsString($expectedAsset, $contents);
            $this->assertStringNotContainsString('cdn.jsdelivr.net', $contents);
        }

        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRE_F3]);
        $preF3View = app(PrimaryAssessmentController::class)->htmlClassPerformanceAnalysis(1, 'CA', 1);
        $this->assertInstanceOf(View::class, $preF3View);

        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        $k12View = app(PrimaryAssessmentController::class)->htmlGradePerformanceAnalysis(1, 'CA', 1);
        $this->assertInstanceOf(View::class, $k12View);
    }

    private function seedPrimaryAnalysisFixture(): void
    {
        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Heritage Primary',
            'school_id' => 'HP-01',
            'type' => SchoolSetup::TYPE_PRIMARY,
            'logo_path' => '/images/logo.png',
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
                'firstname' => 'Mia',
                'lastname' => 'Teacher',
                'email' => 'mia@example.com',
                'position' => 'Teacher',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'firstname' => 'Nia',
                'lastname' => 'Teacher',
                'email' => 'nia@example.com',
                'position' => 'Teacher',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'firstname' => 'Sara',
                'lastname' => 'Teacher',
                'email' => 'sara@example.com',
                'position' => 'Teacher',
                'status' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
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
            [
                'id' => 1,
                'name' => 'STD 4A',
                'user_id' => 10,
                'term_id' => 1,
                'grade_id' => 1,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'STD 4B',
                'user_id' => 11,
                'term_id' => 1,
                'grade_id' => 1,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'STD 4C',
                'user_id' => 12,
                'term_id' => 2,
                'grade_id' => 1,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
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
            [
                'id' => 3,
                'klass_id' => 2,
                'grade_subject_id' => 1,
                'user_id' => 11,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'klass_id' => 2,
                'grade_subject_id' => 2,
                'user_id' => 11,
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
                'last_name' => 'Adams',
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
            [
                'id' => 3,
                'connect_id' => 1003,
                'first_name' => 'Cara',
                'last_name' => 'Cole',
                'gender' => 'F',
                'date_of_birth' => '2015-05-01',
                'nationality' => 'Motswana',
                'id_number' => 'STD4-003',
                'status' => 'Current',
                'type' => 'Current',
                'year' => 2026,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'connect_id' => 1004,
                'first_name' => 'Dylan',
                'last_name' => 'Dube',
                'gender' => 'M',
                'date_of_birth' => '2015-06-01',
                'nationality' => 'Motswana',
                'id_number' => 'STD4-004',
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
            [
                'student_id' => 3,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 4,
                'term_id' => 2,
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
            [
                'klass_id' => 2,
                'student_id' => 3,
                'active' => true,
                'term_id' => 1,
                'grade_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'klass_id' => 3,
                'student_id' => 4,
                'active' => true,
                'term_id' => 2,
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
                'name' => 'Mathematics CA',
                'abbrev' => 'MCA',
                'grade_subject_id' => 1,
                'term_id' => 1,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'CA',
                'assessment' => true,
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 1,
                'name' => 'English CA',
                'abbrev' => 'ECA',
                'grade_subject_id' => 2,
                'term_id' => 1,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'CA',
                'assessment' => true,
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
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
                'id' => 4,
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
            [
                'id' => 5,
                'sequence' => 1,
                'name' => 'Mathematics CA T2',
                'abbrev' => 'MC2',
                'grade_subject_id' => 1,
                'term_id' => 2,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'CA',
                'assessment' => true,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'sequence' => 1,
                'name' => 'English CA T2',
                'abbrev' => 'EC2',
                'grade_subject_id' => 2,
                'term_id' => 2,
                'grade_id' => 1,
                'out_of' => 100,
                'year' => 2026,
                'type' => 'CA',
                'assessment' => true,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('student_tests')->insert([
            ['id' => 1, 'student_id' => 1, 'test_id' => 1, 'score' => 80, 'percentage' => 80, 'grade' => 'A', 'points' => 1, 'avg_score' => 80, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'student_id' => 1, 'test_id' => 2, 'score' => 75, 'percentage' => 75, 'grade' => 'B', 'points' => 2, 'avg_score' => 75, 'avg_grade' => 'B', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'student_id' => 2, 'test_id' => 1, 'score' => 60, 'percentage' => 60, 'grade' => 'C', 'points' => 3, 'avg_score' => 60, 'avg_grade' => 'C', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'student_id' => 2, 'test_id' => 2, 'score' => 58, 'percentage' => 58, 'grade' => 'C', 'points' => 3, 'avg_score' => 58, 'avg_grade' => 'C', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'student_id' => 3, 'test_id' => 1, 'score' => 74, 'percentage' => 74, 'grade' => 'B', 'points' => 2, 'avg_score' => 74, 'avg_grade' => 'B', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'student_id' => 3, 'test_id' => 2, 'score' => 82, 'percentage' => 82, 'grade' => 'A', 'points' => 1, 'avg_score' => 82, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'student_id' => 1, 'test_id' => 3, 'score' => 85, 'percentage' => 85, 'grade' => 'A', 'points' => 1, 'avg_score' => 85, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'student_id' => 1, 'test_id' => 4, 'score' => 88, 'percentage' => 88, 'grade' => 'A', 'points' => 1, 'avg_score' => 88, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'student_id' => 2, 'test_id' => 3, 'score' => 52, 'percentage' => 52, 'grade' => 'D', 'points' => 4, 'avg_score' => 52, 'avg_grade' => 'D', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'student_id' => 2, 'test_id' => 4, 'score' => 61, 'percentage' => 61, 'grade' => 'C', 'points' => 3, 'avg_score' => 61, 'avg_grade' => 'C', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'student_id' => 3, 'test_id' => 3, 'score' => 76, 'percentage' => 76, 'grade' => 'B', 'points' => 2, 'avg_score' => 76, 'avg_grade' => 'B', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'student_id' => 3, 'test_id' => 4, 'score' => 73, 'percentage' => 73, 'grade' => 'B', 'points' => 2, 'avg_score' => 73, 'avg_grade' => 'B', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'student_id' => 4, 'test_id' => 5, 'score' => 95, 'percentage' => 95, 'grade' => 'A', 'points' => 1, 'avg_score' => 95, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'student_id' => 4, 'test_id' => 6, 'score' => 92, 'percentage' => 92, 'grade' => 'A', 'points' => 1, 'avg_score' => 92, 'avg_grade' => 'A', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('overall_grading_matrices')->insert([
            ['id' => 1, 'term_id' => 1, 'year' => 2026, 'grade_id' => 1, 'grade' => 'C', 'min_score' => 0, 'max_score' => 69.99, 'description' => 'Average', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'term_id' => 1, 'year' => 2026, 'grade_id' => 1, 'grade' => 'B', 'min_score' => 70, 'max_score' => 79.99, 'description' => 'Good', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'term_id' => 1, 'year' => 2026, 'grade_id' => 1, 'grade' => 'A', 'min_score' => 80, 'max_score' => 100, 'description' => 'Excellent', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function ensurePrimaryAnalysisSchema(): void
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

    private function resetPrimaryAnalysisTables(): void
    {
        foreach ([
            'overall_grading_matrices',
            'student_tests',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
