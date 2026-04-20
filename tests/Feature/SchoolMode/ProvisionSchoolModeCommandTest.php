<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class ProvisionSchoolModeCommandTest extends TestCase
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
            'school_name' => 'Provision Test School',
            'type' => SchoolSetup::TYPE_PRIMARY,
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

        session(['selected_term_id' => 1]);
    }

    public function test_pref3_provision_command_creates_canonical_grades_subjects_and_tests(): void
    {
        $exitCode = Artisan::call('school:provision-mode', ['--mode' => SchoolSetup::TYPE_PRE_F3]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(SchoolSetup::TYPE_PRE_F3, DB::table('school_setup')->value('type'));
        $this->assertDatabaseHas('grades', ['name' => 'REC', 'level' => SchoolSetup::LEVEL_PRE_PRIMARY]);
        $this->assertDatabaseHas('grades', ['name' => 'F3', 'level' => SchoolSetup::LEVEL_JUNIOR]);
        $this->assertDatabaseHas('subjects', ['level' => 'Preschool', 'canonical_key' => 'gross_motor_development']);
        $this->assertDatabaseHas('subjects', ['level' => SchoolSetup::LEVEL_JUNIOR, 'canonical_key' => 'mathematics']);
        $this->assertGreaterThan(0, DB::table('grade_subject')->count());
        $this->assertGreaterThan(0, DB::table('components')->count());
        $this->assertGreaterThan(0, DB::table('tests')->count());

        foreach (['F1', 'F2', 'F3'] as $gradeName) {
            $gradeId = DB::table('grades')
                ->where('name', $gradeName)
                ->where('level', SchoolSetup::LEVEL_JUNIOR)
                ->value('id');

            foreach (['social_studies', 'agriculture', 'moral_education'] as $canonicalKey) {
                $subjectId = DB::table('subjects')
                    ->where('level', SchoolSetup::LEVEL_JUNIOR)
                    ->where('canonical_key', $canonicalKey)
                    ->value('id');

                $this->assertDatabaseHas('grade_subject', [
                    'grade_id' => $gradeId,
                    'subject_id' => $subjectId,
                    'type' => 1,
                    'mandatory' => false,
                ]);
            }

            $setswanaSubjectId = DB::table('subjects')
                ->where('level', SchoolSetup::LEVEL_JUNIOR)
                ->where('canonical_key', 'setswana')
                ->value('id');

            $this->assertDatabaseHas('grade_subject', [
                'grade_id' => $gradeId,
                'subject_id' => $setswanaSubjectId,
                'type' => 0,
                'mandatory' => true,
            ]);
        }
    }

    public function test_k12_provision_command_creates_senior_layers_without_breaking_combined_structure(): void
    {
        $exitCode = Artisan::call('school:provision-mode', ['--mode' => SchoolSetup::TYPE_K12]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(SchoolSetup::TYPE_K12, DB::table('school_setup')->value('type'));
        $this->assertDatabaseHas('grades', ['name' => 'REC', 'level' => SchoolSetup::LEVEL_PRE_PRIMARY]);
        $this->assertDatabaseHas('grades', ['name' => 'F3', 'level' => SchoolSetup::LEVEL_JUNIOR]);
        $this->assertDatabaseHas('grades', ['name' => 'F5', 'level' => SchoolSetup::LEVEL_SENIOR]);
        $this->assertDatabaseHas('subjects', ['level' => SchoolSetup::LEVEL_SENIOR, 'canonical_key' => 'english']);
        $this->assertDatabaseHas('subjects', ['level' => SchoolSetup::LEVEL_SENIOR, 'canonical_key' => 'accounting']);

        $f4GradeId = DB::table('grades')->where('name', 'F4')->where('level', SchoolSetup::LEVEL_SENIOR)->value('id');
        $englishSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where('canonical_key', 'english')
            ->value('id');
        $accountingSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where('canonical_key', 'accounting')
            ->value('id');
        $mathematicsSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where('canonical_key', 'mathematics')
            ->value('id');

        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f4GradeId,
            'subject_id' => $englishSubjectId,
            'type' => 1,
            'mandatory' => true,
        ]);
        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f4GradeId,
            'subject_id' => $accountingSubjectId,
            'type' => 0,
            'mandatory' => false,
        ]);
        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f4GradeId,
            'subject_id' => $mathematicsSubjectId,
            'type' => 0,
            'mandatory' => false,
        ]);

        $f1GradeId = DB::table('grades')->where('name', 'F1')->where('level', SchoolSetup::LEVEL_JUNIOR)->value('id');
        $juniorSetswanaSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_JUNIOR)
            ->where('canonical_key', 'setswana')
            ->value('id');

        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f1GradeId,
            'subject_id' => $juniorSetswanaSubjectId,
            'type' => 0,
            'mandatory' => true,
        ]);
    }

    public function test_junior_senior_provision_command_creates_f1_to_f5_without_elementary_layers(): void
    {
        $exitCode = Artisan::call('school:provision-mode', ['--mode' => SchoolSetup::TYPE_JUNIOR_SENIOR]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(SchoolSetup::TYPE_JUNIOR_SENIOR, DB::table('school_setup')->value('type'));
        $this->assertDatabaseHas('grades', ['name' => 'F1', 'level' => SchoolSetup::LEVEL_JUNIOR]);
        $this->assertDatabaseHas('grades', ['name' => 'F5', 'level' => SchoolSetup::LEVEL_SENIOR]);
        $this->assertDatabaseMissing('grades', ['name' => 'REC']);
        $this->assertDatabaseMissing('grades', ['name' => 'STD 1']);
        $this->assertDatabaseHas('subjects', ['level' => SchoolSetup::LEVEL_JUNIOR, 'canonical_key' => 'mathematics']);
        $this->assertDatabaseHas('subjects', ['level' => SchoolSetup::LEVEL_SENIOR, 'canonical_key' => 'english']);
        $this->assertDatabaseMissing('subjects', ['level' => 'Preschool']);
        $this->assertDatabaseMissing('subjects', ['level' => SchoolSetup::LEVEL_PRIMARY]);

        $f4GradeId = DB::table('grades')->where('name', 'F4')->where('level', SchoolSetup::LEVEL_SENIOR)->value('id');
        $englishSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where('canonical_key', 'english')
            ->value('id');
        $accountingSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_SENIOR)
            ->where('canonical_key', 'accounting')
            ->value('id');

        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f4GradeId,
            'subject_id' => $englishSubjectId,
            'type' => 1,
            'mandatory' => true,
        ]);
        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f4GradeId,
            'subject_id' => $accountingSubjectId,
            'type' => 0,
            'mandatory' => false,
        ]);

        $f1GradeId = DB::table('grades')->where('name', 'F1')->where('level', SchoolSetup::LEVEL_JUNIOR)->value('id');
        $juniorSetswanaSubjectId = DB::table('subjects')
            ->where('level', SchoolSetup::LEVEL_JUNIOR)
            ->where('canonical_key', 'setswana')
            ->value('id');

        $this->assertDatabaseHas('grade_subject', [
            'grade_id' => $f1GradeId,
            'subject_id' => $juniorSetswanaSubjectId,
            'type' => 0,
            'mandatory' => true,
        ]);
    }
}
