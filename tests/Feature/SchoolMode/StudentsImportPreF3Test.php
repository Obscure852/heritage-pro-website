<?php

namespace Tests\Feature\SchoolMode;

use App\Imports\StudentsImport;
use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class StudentsImportPreF3Test extends TestCase
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
            'school_name' => 'PRE_F3 Import School',
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
            'connect_id' => 345632,
            'first_name' => 'Jane',
            'last_name' => 'Sponsor',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'Test',
            'lastname' => 'Teacher',
            'email' => 'teacher@example.com',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            [
                'id' => 3,
                'sequence' => 9,
                'name' => 'F1',
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('klasses')->insert([
            [
                'id' => 1,
                'name' => '1A',
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
        ]);
    }

    public function test_pref3_import_saves_psle_only_for_junior_rows(): void
    {
        $import = new StudentsImport(1);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Junior',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => '03/09/2012',
            'nationality' => 'Motswana',
            'id_number' => 'JUN-001',
            'status' => 'Active',
            'type' => 'Regular',
            'grade' => 'F2',
            'boarding' => '',
            'class' => '2A',
            'overall_grade' => 'B',
            'agriculture_grade' => 'A',
            'mathematics_grade' => 'C',
            'english_grade' => 'B',
            'science_grade' => 'B',
            'social_studies_grade' => 'A',
            'setswana_grade' => 'D',
            'capa_grade' => 'C',
            'religious_and_moral_education_grade' => 'B',
        ]);

        $juniorStudentId = DB::table('students')->where('first_name', 'Junior')->value('id');

        $this->assertNotNull($juniorStudentId);
        $this->assertDatabaseHas('students', [
            'id' => $juniorStudentId,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('student_term', [
            'student_id' => $juniorStudentId,
            'term_id' => 1,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('psle_grades', [
            'student_id' => $juniorStudentId,
            'overall_grade' => 'B',
            'mathematics_grade' => 'C',
        ]);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Primary',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'F',
            'date_of_birth' => '03/09/2016',
            'nationality' => 'Motswana',
            'id_number' => 'PRI-001',
            'status' => 'Active',
            'type' => 'Regular',
            'grade' => 'STD 1',
            'boarding' => '',
            'class' => '1A',
            'overall_grade' => 'A',
            'agriculture_grade' => 'A',
            'mathematics_grade' => 'A',
            'english_grade' => 'A',
            'science_grade' => 'A',
            'social_studies_grade' => 'A',
            'setswana_grade' => 'A',
            'capa_grade' => 'A',
            'religious_and_moral_education_grade' => 'A',
        ]);

        $primaryStudentId = DB::table('students')->where('first_name', 'Primary')->value('id');

        $this->assertNotNull($primaryStudentId);
        $this->assertDatabaseHas('students', [
            'id' => $primaryStudentId,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('student_term', [
            'student_id' => $primaryStudentId,
            'term_id' => 1,
            'status' => 'Current',
        ]);
        $this->assertDatabaseMissing('psle_grades', [
            'student_id' => $primaryStudentId,
        ]);
        $this->assertSame(1, DB::table('psle_grades')->count());
    }

    public function test_pref3_import_creates_separate_junior_class_when_name_matches_primary_class(): void
    {
        $import = new StudentsImport(1);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Collision',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => '03/09/2013',
            'nationality' => 'Motswana',
            'id_number' => 'COL-001',
            'status' => 'Current',
            'type' => 'Regular',
            'grade' => 'F1',
            'boarding' => '',
            'class' => '1A',
            'overall_grade' => 'B',
            'agriculture_grade' => 'A',
            'mathematics_grade' => 'C',
            'english_grade' => 'B',
            'science_grade' => 'B',
            'social_studies_grade' => 'A',
            'setswana_grade' => 'D',
            'capa_grade' => 'C',
            'religious_and_moral_education_grade' => 'B',
        ]);

        $this->assertDatabaseCount('klasses', 3);
        $this->assertDatabaseHas('klasses', [
            'name' => '1A',
            'grade_id' => 1,
        ]);
        $this->assertDatabaseHas('klasses', [
            'name' => '1A',
            'grade_id' => 3,
        ]);

        $studentId = DB::table('students')->max('id');

        $this->assertDatabaseHas('klass_student', [
            'student_id' => $studentId,
            'term_id' => 1,
            'grade_id' => 3,
        ]);
    }
}
