<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentTermRemovalService{
    protected $defaultTermId;
    protected $schoolType;
    protected $removalSummary = [];

    public function __construct(){
        $this->defaultTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $this->schoolType = SchoolSetup::latest()->first()->type;
    }

    public function removeFromCurrentTerm(Student $student){
        return $this->removeFromTerm($student, $this->defaultTermId);
    }

    public function removeFromTerm(Student $student, int $termId){
        try {
            DB::beginTransaction();

            $this->processRemoval('attendance', 'Attendance records', $student, $termId);
            $this->processRemoval('optional_subjects', 'Optional subject allocations', $student, $termId);
            $this->processRemoval('tests', 'Test records', $student, $termId);
            
            if ($this->schoolType === 'Primary') {
                $this->processRemoval('criteria_based_tests', 'Criteria based assessments', $student, $termId);
            }

            $this->processRemoval('manual_attendance', 'Manual attendance entries', $student, $termId);
            $this->processRemoval('comments', 'Term comments', $student, $termId);
            $this->processRemoval('subject_comments', 'Subject comments', $student, $termId);
            $this->processRemoval('house', 'House allocation', $student, $termId);
            $this->processRemoval('class', 'class allocation', $student, $termId);

            DB::commit();
            
            return [
                'success' => true,
                'removed' => $this->getSuccessfulRemovals(),
                'not_allocated' => $this->getUnallocatedItems(),
                'term_id' => $termId
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove student from term', [
                'student_id' => $student->id,
                'term_id' => $termId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function processRemoval(string $key, string $label, Student $student, int $termId){
        $count = match($key) {
            'attendance' => $this->removeAttendance($student, $termId),
            'optional_subjects' => $this->removeOptionalSubjects($student, $termId),
            'tests' => $this->removeTestRecords($student, $termId),
            'criteria_based_tests' => $this->removeCriteriaBasedTests($student, $termId),
            'manual_attendance' => $this->removeManualAttendanceEntries($student, $termId),
            'comments' => $this->removeComments($student, $termId),
            'subject_comments' => $this->removeSubjectComments($student, $termId),
            'house' => $this->removeHouseAllocation($student, $termId),
            'class' => $this->removeClassAllocation($student, $termId),
            
            default => 0
        };

        $this->removalSummary[$key] = [
            'label' => $label,
            'count' => $count,
            'had_records' => $count > 0
        ];
    }

    public function removeStudentFromOptionalSubject(Student $student, int $optionalSubjectId, ?int $termId = null){
        $termId = $termId ?? $this->defaultTermId;
        $this->removalSummary = [];
    
        try {
            DB::beginTransaction();
        
            $optionalSubject = DB::table('optional_subjects')
                ->where('id', $optionalSubjectId)
                ->first();
                
            if (!$optionalSubject) {
                throw new Exception("Optional subject not found");
            }
            
            $gradeSubjectId = $optionalSubject->grade_subject_id;
            $removedRelation = DB::table('student_optional_subjects')
                ->where('student_id', $student->id)
                ->where('optional_subject_id', $optionalSubjectId)
                ->where('term_id', $termId)
                ->delete();
                
            $this->removalSummary['optional_subject'] = [
                'label' => 'Optional subject allocation',
                'count' => $removedRelation,
                'had_records' => $removedRelation > 0
            ];
            
            $testIds = DB::table('tests')
                ->where('grade_subject_id', $gradeSubjectId)
                ->where('term_id', $termId)
                ->pluck('id');
            
            $removedScores = 0;
            if ($testIds->isNotEmpty()) {
                $studentTests = DB::table('student_tests')
                    ->where('student_id', $student->id)
                    ->whereIn('test_id', $testIds)
                    ->get();
                    
                $studentTestIds = $studentTests->pluck('id')->toArray();
                Log::info('Student test ids', ['student_test_ids' => $studentTestIds]);
                $removedScores = DB::table('student_tests')
                    ->where('student_id', $student->id)
                    ->whereIn('test_id', $testIds)
                    ->delete();
            }
            
            $this->removalSummary['test_scores'] = [
                'label' => 'Test scores',
                'count' => $removedScores,
                'had_records' => $removedScores > 0
            ];
            
            $removedCommentsByTest = 0;
            if (!empty($studentTestIds ?? [])) {
                $removedCommentsByTest = DB::table('subject_comments')
                    ->whereIn('student_test_id', $studentTestIds)
                    ->delete();
            }
            
            $removedCommentsBySubject = DB::table('subject_comments')
                ->where('student_id', $student->id)
                ->where('grade_subject_id', $gradeSubjectId)
                ->where('term_id', $termId)
                ->delete();
                
            $totalRemovedComments = $removedCommentsByTest + $removedCommentsBySubject;
            
            $this->removalSummary['subject_comments'] = [
                'label' => 'Subject comments',
                'count' => $totalRemovedComments,
                'had_records' => $totalRemovedComments > 0
            ];
            
            $removedCriteriaScores = 0;
            if ($this->schoolType === 'Primary') {
                $criteriaTestIds = DB::table('criteria_based_tests')
                    ->where('grade_subject_id', $gradeSubjectId)
                    ->where('term_id', $termId)
                    ->pluck('id');
                    
                if ($criteriaTestIds->isNotEmpty()) {
                    $removedCriteriaScores = DB::table('criteria_based_student_tests')
                        ->where('student_id', $student->id)
                        ->whereIn('criteria_based_test_id', $criteriaTestIds)
                        ->delete();
                }
                
                $this->removalSummary['criteria_scores'] = [
                    'label' => 'Criteria-based assessments',
                    'count' => $removedCriteriaScores,
                    'had_records' => $removedCriteriaScores > 0
                ];
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'removed' => $this->getSuccessfulRemovals(),
                'not_allocated' => $this->getUnallocatedItems(),
                'term_id' => $termId
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove student from optional subject', [
                'student_id' => $student->id,
                'optional_subject_id' => $optionalSubjectId,
                'term_id' => $termId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Updated removal methods to accept termId parameter
    protected function removeAttendance(Student $student, int $termId){
        return DB::table('attendances')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeOptionalSubjects(Student $student, int $termId){
        return DB::table('student_optional_subjects')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeTestRecords(Student $student, int $termId){
        $testIds = DB::table('tests')
            ->where('term_id', $termId)
            ->pluck('id');

        if ($testIds->isEmpty()) {
            return 0;
        }

        return DB::table('student_tests')
            ->where('student_id', $student->id)
            ->whereIn('test_id', $testIds)
            ->delete();
    }

    protected function removeCriteriaBasedTests(Student $student, int $termId){
        return DB::table('criteria_based_student_tests')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeManualAttendanceEntries(Student $student, int $termId){
        return DB::table('manual_attendance_entries')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeComments(Student $student, int $termId){
        return DB::table('comments')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeSubjectComments(Student $student, int $termId){
        return DB::table('subject_comments')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeHouseAllocation(Student $student, int $termId){
        return DB::table('student_house')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function removeClassAllocation(Student $student, int $termId){
        return DB::table('klass_student')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->delete();
    }

    protected function getSuccessfulRemovals(){
        return array_values(array_filter($this->removalSummary, function($item) {
            return $item['had_records'];
        }));
    }

    protected function getUnallocatedItems(){
        return array_values(array_filter($this->removalSummary, function($item) {
            return !$item['had_records'];
        }));
    }
}
