<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\GradeSubject;
use App\Helpers\TermHelper;

return new class extends Migration{

    public function up(){
        Schema::create('grading_scales', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');

            $table->string('grade')->nullable();
            $table->year('year')->nullable();
            $table->integer('min_score')->nullable();
            $table->integer('max_score')->nullable();
            $table->integer('points')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');

            $table->index('grade_subject_id');
            $table->index('points');
            $table->index('term_id');
            $table->index('grade_id');
            $table->index('year');
        });

        $this->allocateGradingScales();
    }

    public function down(){
        Schema::dropIfExists('grading_scales');
    }

    private function allocateGradingScales() {
        $currentTerm = TermHelper::getCurrentTerm();
        if (!$currentTerm) {
            return;
        }
    
        $gradeSubjects = GradeSubject::where('term_id', $currentTerm->id)->get();
    
        foreach ($gradeSubjects as $gradeSubject) {
            $subject = DB::table('subjects')->where('id', $gradeSubject->subject_id)->first();
            if ($subject && $subject->components) {
                continue;
            }
    
            $gradingScales = $this->getGradingScalesForLevel($gradeSubject->grade_id);
            foreach ($gradingScales as $scale) {
                DB::table('grading_scales')->insert([
                    'grade_subject_id' => $gradeSubject->id,
                    'term_id' => $currentTerm->id,
                    'grade_id' => $gradeSubject->grade_id,
                    'grade' => $scale['grade'],
                    'year' => now()->year,
                    'min_score' => $scale['min_score'],
                    'max_score' => $scale['max_score'],
                    'points' => $scale['points'],
                    'description' => $scale['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function getGradingScalesForLevel($gradeId) {
        $primaryGradingScales = [
            ['grade' => 'A', 'min_score' => 80, 'max_score' => 100, 'points' => null, 'description' => null],
            ['grade' => 'B', 'min_score' => 70, 'max_score' => 79, 'points' => null, 'description' => null],
            ['grade' => 'C', 'min_score' => 60, 'max_score' => 69, 'points' => null, 'description' => null],
            ['grade' => 'D', 'min_score' => 50, 'max_score' => 59, 'points' => null, 'description' => null],
            ['grade' => 'E', 'min_score' => 40, 'max_score' => 49, 'points' => null, 'description' => null],
            ['grade' => 'U', 'min_score' => 0, 'max_score' => 39, 'points' => null, 'description' => null],
        ];
    
        $juniorGradingScales = [
            ['grade' => 'A', 'min_score' => 80, 'max_score' => 100, 'points' => 9, 'description' => null],
            ['grade' => 'B', 'min_score' => 70, 'max_score' => 79, 'points' => 7, 'description' => null],
            ['grade' => 'C', 'min_score' => 60, 'max_score' => 69, 'points' => 5, 'description' => null],
            ['grade' => 'D', 'min_score' => 40, 'max_score' => 59, 'points' => 3, 'description' => null],
            ['grade' => 'E', 'min_score' => 13, 'max_score' => 39, 'points' => 1, 'description' => null],
            ['grade' => 'U', 'min_score' => 0, 'max_score' => 12, 'points' => 0, 'description' => null],
        ];
    
        $seniorGradingScales = [
            ['grade' => 'A*', 'min_score' => 86, 'max_score' => 100, 'points' => 8, 'description' => null],
            ['grade' => 'A', 'min_score' => 77, 'max_score' => 85, 'points' => 8, 'description' => null],
            ['grade' => 'B', 'min_score' => 66, 'max_score' => 76, 'points' => 7, 'description' => null],
            ['grade' => 'C', 'min_score' => 56, 'max_score' => 65, 'points' => 6, 'description' => null],
            ['grade' => 'D', 'min_score' => 46, 'max_score' => 55, 'points' => 5, 'description' => null],
            ['grade' => 'E', 'min_score' => 40, 'max_score' => 45, 'points' => 4, 'description' => null],
            ['grade' => 'F', 'min_score' => 30, 'max_score' => 39, 'points' => 1, 'description' => null],
            ['grade' => 'G', 'min_score' => 20, 'max_score' => 29, 'points' => 1, 'description' => null],
            ['grade' => 'U', 'min_score' => 0, 'max_score' => 19, 'points' => 0, 'description' => null],
        ];
    
        $gradeLevel = DB::table('grades')->where('id', $gradeId)->value('level');
    
        switch ($gradeLevel) {
            case 'Primary':
                return $primaryGradingScales;
            case 'Junior':
                return $juniorGradingScales;
            case 'Senior':
                return $seniorGradingScales;
            default:
                throw new \Exception("Invalid grade level: {$gradeLevel}");
        }
    }
    
};
