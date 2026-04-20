<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class RemarkGenerationService{
    public function generateRemarksForStudent($studentId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($studentId);

        if ($student->tests->count() < 7) {
            return;
        }

        $totalPoints = $this->calculateTotalPoints($studentId);
        $possibleComments = DB::table('comment_banks')
            ->where('min_points', '<=', $totalPoints)
            ->where('max_points', '>=', $totalPoints)
            ->pluck('body');

        if ($possibleComments->isEmpty()) {
            $classTeacherComment = 'No remarks available.';
            $schoolHeadComment   = 'No remarks available.';
        } else {
            $classTeacherComment = $possibleComments->random();
            if ($possibleComments->count() === 1) {
                $schoolHeadComment = $classTeacherComment;
            } else {
                $maxRetries        = 5;
                $retryCount        = 0;
                $schoolHeadComment = $possibleComments->random();

                while (($schoolHeadComment === $classTeacherComment) && ($retryCount < $maxRetries)) {
                    $schoolHeadComment = $possibleComments->random();
                    $retryCount++;
                }
            }
        }

        $overallComment = $student->overallComments->where('term_id', $selectedTermId)->first();
        $classTeacherFinal = $classTeacherComment ?? 'No remarks provided.';
        $schoolHeadFinal   = $schoolHeadComment ?? 'No remarks provided.';

        if ($overallComment) {
            $overallComment->update([
                'class_teacher_remarks' => $classTeacherFinal,
                'school_head_remarks'   => $schoolHeadFinal,
            ]);
        } else {
            $student->overallComments()->create([
                'term_id'               => $selectedTermId,
                'class_teacher_remarks' => $classTeacherFinal,
                'school_head_remarks'   => $schoolHeadFinal,
                'klass_id'              => $student->currentClass()->id,
                'user_id'               => auth()->id(),
                'year'                  => date('Y'),
            ]);
        }
    }

    public function calculateTotalPoints($studentId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            }
        ])->findOrFail($studentId);

        $subjects = $student->tests->pluck('subject')->unique();
        $isForeigner = $student->nationality !== 'Motswana';
        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
            $student,
            $subjects,
            $selectedTermId,
            $isForeigner,
            'Exam' 
        );

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        return $totalPoints;
    }

    private function calculatePoints($student, $subjects, $selectedTermId, $isForeigner, $type, $sequence = 1){
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];
    
        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId, $type, $sequence);
            
            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                    continue;
                }

                if (!$subject->type) {
                    $optionalPoints[] = $points;
                    continue;
                }

                $corePoints[] = $points;
                continue;
            }
    
            if ($subject->mandatory) {
                $mandatoryPoints += $points;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPoints[] = $points;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePoints[] = $points;
            }
        }
    
        rsort($optionalPoints);
        rsort($corePoints);
    
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
            $remainingOptionals = array_slice($optionalPoints, 2); 
        } else {
            $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
            $remainingOptionals = array_slice($optionalPoints, 1);
        }
    
        $combinedRemaining = array_merge($remainingOptionals, $corePoints);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        return [$mandatoryPoints, $bestOptionalPoints, $bestFromCombined];
    }

    private function getSubjectPoints($student, $subject, $selectedTermId, $type = 'Exam', $sequence = 1){
        $examTest = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('sequence', $sequence)
            ->first();

        if (!empty($examTest)) {
            return $examTest->pivot->points;
        }
        return 0;
    }

}
