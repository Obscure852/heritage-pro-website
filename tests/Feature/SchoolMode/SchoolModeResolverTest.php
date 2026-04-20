<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use App\Models\Student;
use App\Services\SchoolModeResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class SchoolModeResolverTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensurePreF3SchoolModeSchema();
        $this->resetPreF3SchoolModeTables();

        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Combined School',
            'type' => SchoolSetup::TYPE_PRE_F3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sponsors')->insert([
            'id' => 1,
            'connect_id' => 1001,
            'first_name' => 'Test',
            'last_name' => 'Sponsor',
            'password' => bcrypt('password'),
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
    }

    public function test_pref3_mode_exposes_primary_and_junior_capabilities(): void
    {
        $resolver = app(SchoolModeResolver::class);

        $this->assertSame(SchoolSetup::TYPE_PRE_F3, $resolver->mode());
        $this->assertSame(
            [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY, SchoolSetup::LEVEL_JUNIOR],
            $resolver->supportedLevels()
        );
        $this->assertTrue($resolver->supportsFinals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertFalse($resolver->supportsFinals(SchoolSetup::LEVEL_PRIMARY));
        $this->assertTrue($resolver->supportsOptionals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertFalse($resolver->supportsSeniorAdmissions());
        $this->assertSame(
            [SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY, SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR],
            $resolver->availableAssessmentContexts()
        );
        $this->assertSame(
            [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY],
            $resolver->levelsForAssessmentContext(SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY)
        );
        $this->assertSame(
            [SchoolSetup::LEVEL_JUNIOR],
            $resolver->levelsForAssessmentContext(SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR)
        );
    }

    public function test_resolver_dispatches_student_report_driver_from_grade_level(): void
    {
        DB::table('grades')->insert([
            [
                'id' => 1,
                'sequence' => 2,
                'name' => 'STD 1',
                'promotion' => 'STD 2',
                'description' => 'Standard 1',
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
                'id_number' => 'STU-1',
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
                'id_number' => 'STU-2',
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
        ]);

        $resolver = app(SchoolModeResolver::class);

        $this->assertSame('primary', $resolver->portalReportCardDriverForLevel(
            $resolver->levelForStudent(Student::findOrFail(1), 1)
        ));

        $this->assertSame('junior', $resolver->portalReportCardDriverForLevel(
            $resolver->levelForStudent(Student::findOrFail(2), 1)
        ));
    }

    public function test_k12_mode_exposes_senior_capabilities_alongside_combined_contexts(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);

        $resolver = app(SchoolModeResolver::class);

        $this->assertSame(SchoolSetup::TYPE_K12, $resolver->mode());
        $this->assertSame(
            [
                SchoolSetup::LEVEL_PRE_PRIMARY,
                SchoolSetup::LEVEL_PRIMARY,
                SchoolSetup::LEVEL_JUNIOR,
                SchoolSetup::LEVEL_SENIOR,
            ],
            $resolver->supportedLevels()
        );
        $this->assertTrue($resolver->supportsFinals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertTrue($resolver->supportsFinals(SchoolSetup::LEVEL_SENIOR));
        $this->assertTrue($resolver->supportsOptionals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertTrue($resolver->supportsOptionals(SchoolSetup::LEVEL_SENIOR));
        $this->assertTrue($resolver->supportsSeniorAdmissions());
        $this->assertSame(
            [
                SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY,
                SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR,
                SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR,
            ],
            $resolver->availableAssessmentContexts()
        );
        $this->assertSame(
            [SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_SENIOR],
            $resolver->valueAdditionSchoolTypes()
        );
    }

    public function test_junior_senior_mode_exposes_only_junior_and_senior_capabilities(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);

        $resolver = app(SchoolModeResolver::class);

        $this->assertSame(SchoolSetup::TYPE_JUNIOR_SENIOR, $resolver->mode());
        $this->assertSame(
            [SchoolSetup::LEVEL_JUNIOR, SchoolSetup::LEVEL_SENIOR],
            $resolver->supportedLevels()
        );
        $this->assertTrue($resolver->supportsFinals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertTrue($resolver->supportsFinals(SchoolSetup::LEVEL_SENIOR));
        $this->assertFalse($resolver->supportsFinals(SchoolSetup::LEVEL_PRIMARY));
        $this->assertTrue($resolver->supportsOptionals(SchoolSetup::LEVEL_JUNIOR));
        $this->assertTrue($resolver->supportsOptionals(SchoolSetup::LEVEL_SENIOR));
        $this->assertFalse($resolver->supportsOptionals(SchoolSetup::LEVEL_PRIMARY));
        $this->assertTrue($resolver->supportsSeniorAdmissions());
        $this->assertSame(
            [
                SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR,
                SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR,
            ],
            $resolver->availableAssessmentContexts()
        );
        $this->assertSame(
            [SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_SENIOR],
            $resolver->valueAdditionSchoolTypes()
        );
    }
}
