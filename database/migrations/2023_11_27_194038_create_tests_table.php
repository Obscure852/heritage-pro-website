<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\TermHelper;
use Carbon\Carbon;
use App\Models\SchoolSetup;
use App\Support\AcademicStructureRegistry;

return new class extends Migration {

    public function up(){
        Schema::create('tests', function (Blueprint $table){
            $table->id();
            $table->integer('sequence');
            $table->string('name');
            $table->string('abbrev');
            $table->unsignedBigInteger('grade_subject_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->integer('out_of')->nullable();
            $table->year('year')->default(date('Y'));
            $table->string('type')->nullable();
            $table->boolean('assessment')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');

            $table->index('term_id');
            $table->index('grade_id');
            $table->index('grade_subject_id');

            $table->index('year');
            $table->index('sequence');
            $table->index('name');
            $table->index('abbrev');
            $table->index('assessment');
            $table->index('start_date');
            $table->index('end_date');
        });

        $this->createTestsForCurrentTerm();
    }

    public function down(){
        Schema::dropIfExists('tests');
    }

    private function createTestsForCurrentTerm() {
        $term = TermHelper::getCurrentTerm();
        if ($term) {
            $school_type = SchoolSetup::normalizeType(SchoolSetup::value('type'));

            foreach (AcademicStructureRegistry::supportedTestLevelsForMode($school_type) as $level) {
                $this->createTestsForLevel($level, $term);
            }
        }
    }

    private function createTestsForLevel($level, $term) {
        $subjects = DB::table('grade_subject')
            ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->where('subjects.level', $level)
            ->where('grade_subject.term_id', $term->id)
            ->select('grade_subject.*')
            ->get();
    
        $startDate = $term->start_date instanceof Carbon 
            ? clone $term->start_date 
            : Carbon::parse($term->start_date);
            
        $endDate = $term->end_date instanceof Carbon 
            ? clone $term->end_date 
            : Carbon::parse($term->end_date);
    
        while ($startDate->lessThanOrEqualTo($endDate)) {
            $testDate = $startDate->copy()->endOfMonth();
            if ($testDate->greaterThan($endDate)) {
                break;
            }
    
            $monthName = $testDate->format('F');
            $abbrev = $testDate->format('M');
    
            foreach ($subjects as $subject) {
                $caTestCount = DB::table('tests')
                    ->where('grade_subject_id', $subject->id)
                    ->where('type', 'CA')
                    ->count();
    
                if ($caTestCount < 3) {
                    $sequence = DB::table('tests')
                        ->where('grade_subject_id', $subject->id)
                        ->max('sequence') + 1;
    
                    TermHelper::createTest($term, $subject, "{$monthName}", $abbrev, 'CA', $sequence, 100, $testDate);
                }
            }
            $startDate->addMonth();
        }
        
        foreach ($subjects as $subject) {
            TermHelper::createTest($term, $subject, "{$endDate->format('F')} Exam", 'Exam', 'Exam', 1, 100, $endDate);
        }
    }
};
