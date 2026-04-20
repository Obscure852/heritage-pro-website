<?php

namespace Tests\Feature\SchoolMode;

use App\Imports\StudentsImport;
use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class StudentsImportK12Test extends TestCase
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
            'school_name' => 'K12 Import School',
            'type' => SchoolSetup::TYPE_K12,
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
            [
                'id' => 3,
                'name' => '4A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 3,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_k12_import_applies_exam_columns_by_student_level(): void
    {
        $import = new StudentsImport(1);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Primary',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'F',
            'date_of_birth' => '03/09/2016',
            'nationality' => 'Motswana',
            'id_number' => 'K12-PRI-001',
            'status' => 'Active',
            'type' => 'Regular',
            'grade' => 'STD 1',
            'boarding' => '',
            'class' => '1A',
            'overall_grade' => 'A',
            'mathematics_grade' => 'A',
            'ov' => 'B',
            'math' => 'C',
        ]);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Junior',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => '03/09/2012',
            'nationality' => 'Motswana',
            'id_number' => 'K12-JNR-001',
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
            'ov' => 'A',
            'math' => 'A',
        ]);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Senior',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => '03/09/2010',
            'nationality' => 'Motswana',
            'id_number' => 'K12-SNR-001',
            'status' => 'Active',
            'type' => 'Regular',
            'grade' => 'F4',
            'boarding' => '',
            'class' => '4A',
            'overall_grade' => 'B',
            'mathematics_grade' => 'C',
            'ov' => 'A',
            'math' => 'B',
            'eng' => 'C',
            'sci' => 'B',
            'set' => 'A',
            'ss' => 'B',
        ]);

        $primaryStudentId = DB::table('students')->where('first_name', 'Primary')->value('id');
        $juniorStudentId = DB::table('students')->where('first_name', 'Junior')->value('id');
        $seniorStudentId = DB::table('students')->where('first_name', 'Senior')->value('id');

        $this->assertNotNull($primaryStudentId);
        $this->assertNotNull($juniorStudentId);
        $this->assertNotNull($seniorStudentId);

        $this->assertDatabaseHas('students', [
            'id' => $primaryStudentId,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $juniorStudentId,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $seniorStudentId,
            'status' => 'Current',
        ]);

        $this->assertDatabaseMissing('psle_grades', ['student_id' => $primaryStudentId]);
        $this->assertDatabaseMissing('jce_grades', ['student_id' => $primaryStudentId]);

        $this->assertDatabaseHas('psle_grades', [
            'student_id' => $juniorStudentId,
            'overall_grade' => 'B',
            'mathematics_grade' => 'C',
        ]);
        $this->assertDatabaseMissing('jce_grades', ['student_id' => $juniorStudentId]);

        $this->assertDatabaseHas('jce_grades', [
            'student_id' => $seniorStudentId,
            'overall' => 'A',
            'mathematics' => 'B',
            'english' => 'C',
        ]);
        $this->assertDatabaseMissing('psle_grades', ['student_id' => $seniorStudentId]);
    }
}
