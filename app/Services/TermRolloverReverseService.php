<?php

namespace App\Services;

use App\Models\SchoolSetup;
use App\Models\Term;
use App\Models\TermRolloverHistory;
use App\Models\Timetable\TimetableSetting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TermRolloverReverseService {
    private $rolloverHistory;
    private $schoolType;
    private $results = [];

    public function reverseTermRollover($historyId){
        try {
            $this->rolloverHistory = TermRolloverHistory::findOrFail($historyId);
            $this->schoolType = SchoolSetup::first();
            
            if ($this->rolloverHistory->status !== 'completed') {
                throw new \Exception('Can only reverse completed rollovers');
            }

            DB::transaction(function () {
                $this->results = [
                    'tests' => 0, 'gradingScales' => 0, 'gradingMatrices' => 0,
                    'studentHouses' => 0, 'userHouses' => 0, 'houses' => 0, 'klassSubjects' => 0,
                    'optionalSubjectAllocations' => 0, 'optionalSubjects' => 0,
                    'subjects' => 0, 'classAllocations' => 0, 'classes' => 0, 'grades' => 0,
                ];

                $this->deleteTests($this->rolloverHistory->to_term_id);
                $this->deleteGradingScales($this->rolloverHistory->to_term_id);
                $this->deleteGradingMatrices($this->rolloverHistory->to_term_id);
                
                $this->deleteHouseAllocations($this->rolloverHistory->to_term_id);
                $this->deleteHouses($this->rolloverHistory->to_term_id);
                $this->deleteKlassSubjects($this->rolloverHistory);
                

                if ($this->isSecondarySchool()) {
                  $this->reverseCouplingGroups($this->rolloverHistory);
                  $this->deleteOptionalSubjectAndAllocationsData($this->rolloverHistory);
                }
                
                $this->deleteSubjects($this->rolloverHistory);
                $this->deleteClassAllocations($this->rolloverHistory);
                $this->deleteClasses($this->rolloverHistory);
                $this->deleteGrades($this->rolloverHistory);
                $this->reopenTerm($this->rolloverHistory);

                $this->rolloverHistory->update(['status' => 'reversed','reversed_at' => now(),'reversed_by' => auth()->id()]);
            });

            return $this->results;

        } catch (\Exception $e) {
            Log::error('Term rollover reversal failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function deleteTests($termId){
       try {
           $hasStudentTests = DB::table('student_tests')
               ->join('tests', 'tests.id', '=', 'student_tests.test_id')
               ->where('tests.term_id', $termId)
               ->exists();
               
           if ($hasStudentTests) {
               DB::table('student_tests')
                   ->join('tests', 'tests.id', '=', 'student_tests.test_id')
                   ->where('tests.term_id', $termId)->delete();
               Log::info("Deleted student tests for term ID: {$termId}");
           }
           
           $this->results['tests'] = DB::table('tests')->where('term_id', $termId)->count();
           DB::table('tests')->where('term_id', $termId)->delete();
           Log::info("Successfully deleted tests for term ID: {$termId}");
       } catch (Exception $e) {
           Log::error("Failed to delete tests for term ID {$termId}: " . $e->getMessage());
           throw $e;
       }
    }
    
    
    private function deleteGradingScales($termId){
       try {
           $deletedCount = DB::table('grading_scales')
               ->where('term_id', $termId)
               ->delete();
               
           $this->results['gradingScales'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} grading scales for term ID: {$termId}");
       } catch (Exception $e) {
           Log::error("Failed to delete grading scales for term ID {$termId}: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteGradingMatrices($termId){
       try {
           $deletedCount = DB::table('overall_grading_matrices')->where('term_id', $termId)->delete();
           $this->results['gradingMatrices'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} overall grading matrices for term ID: {$termId}");
       } catch (\Exception $e) {
           Log::error("Failed to delete overall grading matrices for term ID {$termId}: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteHouseAllocations($termId){
       try {
           $deletedCount = DB::table('student_house')->where('term_id', $termId)->delete();
           $deletedUserCount = DB::table('user_house')->where('term_id', $termId)->delete();
           $this->results['studentHouses'] = $deletedCount;
           $this->results['userHouses'] = $deletedUserCount;
           Log::info("Successfully deleted {$deletedCount} student house allocations for term ID: {$termId}");
           Log::info("Successfully deleted {$deletedUserCount} user house allocations for term ID: {$termId}");
       } catch (\Exception $e) {
           Log::error("Failed to delete student house allocations for term ID {$termId}: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteHouses($termId){
       try {
           $deletedCount = DB::table('houses')->where('term_id', $termId)->delete();
           $this->results['houses'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} houses for term ID: {$termId}");
       } catch (\Exception $e) {
           Log::error("Failed to delete houses for term ID {$termId}: " . $e->getMessage());
           throw $e;
       }
    }

    private function deleteOptionalSubjectAndAllocationsData($rolloverHistory){
      try {
          $deletedAllocations = DB::table('student_optional_subjects')
              ->where('term_id', $rolloverHistory->to_term_id)
              ->delete();
              
          $deletedOptionalSubjects = DB::table('optional_subjects')
              ->where('term_id', $rolloverHistory->to_term_id)
              ->delete();
              
          $reactivatedCount = DB::table('optional_subjects')
              ->where('term_id', $rolloverHistory->from_term_id)
              ->update(['active' => 1]);
              
          $this->results['optionalSubjectAllocations'] = $deletedAllocations;
          $this->results['optionalSubjects'] = $deletedOptionalSubjects;
          Log::info("Successfully deleted {$deletedAllocations} student optional subject allocations for term ID: {$rolloverHistory->to_term_id}");
          Log::info("Successfully deleted {$deletedOptionalSubjects} optional subjects for term ID: {$rolloverHistory->to_term_id}");
          Log::info("Successfully reactivated {$reactivatedCount} optional subjects for term ID: {$rolloverHistory->from_term_id}");
      } catch (\Exception $e) {
          Log::error("Failed to delete secondary school data: " . $e->getMessage());
          throw $e;
      }
    }
    
    private function reverseCouplingGroups($rolloverHistory): void {
        $groups = TimetableSetting::get('optional_coupling_groups', []);
        if (empty($groups)) {
            return;
        }

        // Build reverse mappings from new-term → old-term using the DB records
        // New-term optional subjects were replicated from old-term ones with the same name + grade_subject subject_id
        $newTermOsRows = DB::table('optional_subjects')
            ->where('term_id', $rolloverHistory->to_term_id)
            ->get(['id', 'name', 'grade_subject_id', 'grade_id']);

        $oldTermOsRows = DB::table('optional_subjects')
            ->where('term_id', $rolloverHistory->from_term_id)
            ->get(['id', 'name', 'grade_subject_id', 'grade_id']);

        // Build grade reverse map from new grades → old grades
        $newGrades = DB::table('grades')->where('term_id', $rolloverHistory->to_term_id)->get(['id', 'name']);
        $oldGrades = DB::table('grades')->where('term_id', $rolloverHistory->from_term_id)->get(['id', 'name']);
        $reverseGradeMap = [];
        foreach ($newGrades as $newGrade) {
            $oldGrade = $oldGrades->firstWhere('name', $newGrade->name);
            if ($oldGrade) {
                $reverseGradeMap[$newGrade->id] = $oldGrade->id;
            }
        }

        // Build optional subject reverse map: new OS id → old OS id (matched by name + grade_subject subject_id + grade name)
        $newGsSubjectIds = DB::table('grade_subject')
            ->whereIn('id', $newTermOsRows->pluck('grade_subject_id')->unique())
            ->pluck('subject_id', 'id');
        $oldGsSubjectIds = DB::table('grade_subject')
            ->whereIn('id', $oldTermOsRows->pluck('grade_subject_id')->unique())
            ->pluck('subject_id', 'id');

        $reverseOsMap = [];
        foreach ($newTermOsRows as $newOs) {
            $newSubjectId = $newGsSubjectIds[$newOs->grade_subject_id] ?? null;
            $oldGradeId = $reverseGradeMap[$newOs->grade_id] ?? null;
            if (!$newSubjectId || !$oldGradeId) {
                continue;
            }
            $match = $oldTermOsRows->first(function ($oldOs) use ($newOs, $oldGsSubjectIds, $newSubjectId, $oldGradeId) {
                return $oldOs->name === $newOs->name
                    && $oldOs->grade_id === $oldGradeId
                    && ($oldGsSubjectIds[$oldOs->grade_subject_id] ?? null) === $newSubjectId;
            });
            if ($match) {
                $reverseOsMap[$newOs->id] = $match->id;
            }
        }

        $updatedGroups = [];
        foreach ($groups as $group) {
            $oldGradeId = $reverseGradeMap[(int) $group['grade_id']] ?? $group['grade_id'];

            $revertedOsIds = [];
            foreach ($group['optional_subject_ids'] as $newOsId) {
                $oldOsId = $reverseOsMap[$newOsId] ?? null;
                if ($oldOsId !== null) {
                    $revertedOsIds[] = $oldOsId;
                }
            }

            $updatedGroups[] = [
                'grade_id'             => $oldGradeId,
                'label'                => $group['label'],
                'singles'              => $group['singles'],
                'doubles'              => $group['doubles'],
                'triples'              => $group['triples'],
                'optional_subject_ids' => $revertedOsIds,
            ];
        }

        TimetableSetting::set('optional_coupling_groups', $updatedGroups, auth()->id());
        Log::info('Coupling groups reversed during term rollover reversal.');
    }

    private function deleteKlassSubjects($rolloverHistory){
       try {
           $deletedCount = DB::table('klass_subject')
               ->where('term_id', $rolloverHistory->to_term_id)->delete();
               
           $reactivatedCount = DB::table('klass_subject')
               ->where('term_id', $rolloverHistory->from_term_id)
               ->update(['active' => 1]);
               
           $this->results['klassSubjects'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} klass subjects for new term ID: {$rolloverHistory->to_term_id}");
           Log::info("Successfully reactivated {$reactivatedCount} klass subjects for old term ID: {$rolloverHistory->from_term_id}");
       } catch (\Exception $e) {
           Log::error("Failed to handle klass subjects deletion/reactivation: " . $e->getMessage());
           throw $e;
       }
    }
    
    
    private function deleteSubjects($rolloverHistory){
       try {
           if (!$this->isSecondarySchool()) {
               $deletedComponents = DB::table('components')
                   ->where('term_id', $rolloverHistory->to_term_id)
                   ->delete();
               Log::info("Deleted {$deletedComponents} components for term ID: {$rolloverHistory->to_term_id}");
           }
    
           $deletedSubjects = DB::table('grade_subject')
               ->where('term_id', $rolloverHistory->to_term_id)
               ->delete();
               
           $this->results['subjects'] = $deletedSubjects;
           Log::info("Successfully deleted {$deletedSubjects} grade subjects for term ID: {$rolloverHistory->to_term_id}");
       } catch (\Exception $e) {
           Log::error("Failed to delete subjects and components: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteClassAllocations($rolloverHistory){
       try {
           $deletedKlassStudents = DB::table('klass_student')
               ->where('term_id', $rolloverHistory->to_term_id)
               ->delete();
    
           $this->results['classAllocations'] = $deletedKlassStudents;
           Log::info("Deleted klass_student records: {$deletedKlassStudents}", [
               'term_id' => $rolloverHistory->to_term_id 
           ]);
    
           $deletedStudentTerms = DB::table('student_term')
               ->where('term_id', $rolloverHistory->to_term_id)
               ->delete();
    
           Log::info("Deleted student_term records: {$deletedStudentTerms}", [
               'term_id' => $rolloverHistory->to_term_id
           ]);
    
           $updatedStudentTerms = DB::table('student_term')
               ->where('term_id', $rolloverHistory->from_term_id)
               ->update(['status' => 'Current']);
    
           Log::info("Updated student_term statuses to 'Current': {$updatedStudentTerms}", [
               'term_id' => $rolloverHistory->from_term_id
           ]);
    
       } catch (\Exception $e) {
           Log::error("Failed to delete class allocations and update student terms: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteClasses($rolloverHistory){
       try {
           $deletedCount = DB::table('klasses')
               ->where('term_id', $rolloverHistory->to_term_id)
               ->delete();
    
           $reactivatedCount = DB::table('klasses')
               ->where('term_id', $rolloverHistory->from_term_id)
               ->update(['active' => 1]);
    
           $this->results['classes'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} classes for term ID: {$rolloverHistory->to_term_id}");
           Log::info("Successfully reactivated {$reactivatedCount} classes for term ID: {$rolloverHistory->from_term_id}");
    
       } catch (\Exception $e) {
           Log::error("Failed to delete/reactivate classes: " . $e->getMessage());
           throw $e;
       }
    }
    
    private function deleteGrades($rolloverHistory){
       try {
           $deletedCount = DB::table('grades')
               ->where('term_id', $rolloverHistory->to_term_id)
               ->delete();
    
           $reactivatedCount = DB::table('grades')
               ->where('term_id', $rolloverHistory->from_term_id)
               ->update(['active' => 1]);
    
           $this->results['grades'] = $deletedCount;
           Log::info("Successfully deleted {$deletedCount} grades for term ID: {$rolloverHistory->to_term_id}");
           Log::info("Successfully reactivated {$reactivatedCount} grades for term ID: {$rolloverHistory->from_term_id}");
    
       } catch (\Exception $e) {
           Log::error("Failed to delete/reactivate grades: " . $e->getMessage());
           throw $e;
       }
    }

    private function reOpenTerm($rolloverHistory) {
      Term::whereIn('id', [$rolloverHistory->from_term_id, $rolloverHistory->to_term_id])->update(['closed' => 0]);
      Cache::flush();
      Session::put('selected_term_id', $rolloverHistory->from_term_id);
    }
    
    private function isSecondarySchool(){
      return $this->schoolType->type !== 'Primary';
    }

}
