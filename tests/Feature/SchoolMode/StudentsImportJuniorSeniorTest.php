<?php

namespace Tests\Feature\SchoolMode;

use App\Imports\StudentsImport;
use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class StudentsImportJuniorSeniorTest extends TestCase
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
            'school_name' => 'Middle High Import School',
            'type' => SchoolSetup::TYPE_JUNIOR_SENIOR,
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
                'id' => 2,
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
                'name' => '2A',
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
                'name' => '4A',
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

    public function test_junior_senior_import_applies_psle_to_middle_school_and_jce_to_high_school(): void
    {
        $import = new StudentsImport(1);

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'Middle',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'M',
            'date_of_birth' => '03/09/2012',
            'nationality' => 'Motswana',
            'id_number' => 'MID-001',
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

        $import->model([
            'connect_id' => '345632',
            'first_name' => 'High',
            'last_name' => 'Student',
            'middle_name' => '',
            'gender' => 'F',
            'date_of_birth' => '03/09/2010',
            'nationality' => 'Motswana',
            'id_number' => 'HIGH-001',
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

        $middleStudentId = DB::table('students')->where('first_name', 'Middle')->value('id');
        $highStudentId = DB::table('students')->where('first_name', 'High')->value('id');

        $this->assertNotNull($middleStudentId);
        $this->assertNotNull($highStudentId);

        $this->assertDatabaseHas('psle_grades', [
            'student_id' => $middleStudentId,
            'overall_grade' => 'B',
            'mathematics_grade' => 'C',
        ]);
        $this->assertDatabaseMissing('jce_grades', ['student_id' => $middleStudentId]);

        $this->assertDatabaseHas('jce_grades', [
            'student_id' => $highStudentId,
            'overall' => 'A',
            'mathematics' => 'B',
            'english' => 'C',
        ]);
        $this->assertDatabaseMissing('psle_grades', ['student_id' => $highStudentId]);
    }
}
