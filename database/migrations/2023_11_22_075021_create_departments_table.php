<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SchoolSetup;
use App\Support\AcademicStructureRegistry;

class CreateDepartmentsTable extends Migration
{
    const JUNIOR_DEPARTMENTS = [
        'Administration',
        'Mathematics & Science',
        'Practicals',
        'Generals',
        'Humanities',
        'Languages',
        'CAPA'
    ];

    const PRIMARY_DEPARTMENTS = [
        'Administration',
        'Lower Primary',
        'Middle Primary',
        'Upper Primary'
    ];

    const SENIOR_DEPARTMENTS = [
        'Administration',
        'Mathematics',
        'Extended Mathematics',
        'Double Science',
        'Accounting',
        'Biology',
        'Physics',
        'Chemistry',
        'Humanities',
        'English',
        'Physical Education',
        'Religious Education',
        'Business Studies',
        'Setswana',
        'Design & Technology',
        'Geography',
        'Art',
        'Statistics',
        'Agriculture',
        'French',
        'History'
    ];

    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('department_head')->nullable();
            $table->unsignedBigInteger('assistant')->nullable();
            $table->timestamps();

            $table->foreign('department_head')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assistant')->references('id')->on('users')->onDelete('cascade');

            $table->index('name');
            $table->index('department_head');
            $table->index('assistant');
        });

        $this->seedDepartments();
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }

    private function seedDepartments()
    {
        $schoolType = SchoolSetup::normalizeType(SchoolSetup::value('type'));
        $departments = AcademicStructureRegistry::departmentNamesForMode($schoolType);

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'name' => $department,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
