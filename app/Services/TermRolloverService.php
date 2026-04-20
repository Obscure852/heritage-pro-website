<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Term;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\GradingScale;
use App\Models\House;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Timetable\TimetableSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TermRolloverService {

    private const SCHOOL_TYPE_PRIMARY = 'Primary';
    private const SCHOOL_TYPE_JUNIOR = 'Junior';
    private const SCHOOL_TYPE_SENIOR = 'Senior';

    protected $termRolloverHistoryId;
    protected $results = [];
    protected $autoCreatedGradeSubjects = 0;

    public function rollover(Term $fromTerm, Term $toTerm): array {
        Log::info("Starting term rollover from term ID: {$fromTerm->id} to term ID: {$toTerm->id}");
        $this->gradeAndClassesRollover($fromTerm->id, $toTerm->id);
        Log::info('Term rollover completed successfully.');
        return $this->results;
    }
    
    private function gradeAndClassesRollover($fromTermId, $toTermId) {
        try {
            DB::transaction(function () use ($fromTermId, $toTermId) {
                $fromTerm = Term::findOrFail($fromTermId);
                $toTerm = Term::findOrFail($toTermId);

                $this->results = [
                    'grades' => 0,
                    'classes' => 0,
                    'subjects' => 0,
                    'optionalSubjects' => 0,
                    'studentAllocations' => 0,
                    'klassSubjects' => 0,
                    'houses' => 0,
                    'studentHouses' => 0,
                    'userHouses' => 0,
                    'gradingScales' => 0,
                    'gradingMatrices' => 0,
                    'tests' => 0,
                    'autoCreatedGradeSubjects' => 0,
                ];
                $this->autoCreatedGradeSubjects = 0;

                $this->termRolloverHistoryId = DB::table('term_rollover_histories')->insertGetId([
                    'from_term_id' => $fromTermId,
                    'to_term_id' => $toTermId,
                    'performed_by' => auth()->user()->id,
                    'mappings' => null,
                    'status' => 'in-progress',
                    'reversed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $schoolSetup = SchoolSetup::first();
                if (!$schoolSetup || !$schoolSetup->type) {
                    throw new \Exception('School type configuration is missing.');
                }
                $schoolType = $schoolSetup->type;
                Log::info("School type identified as '{$schoolType}'.");

                $oldToNewGradeIds = $this->rolloverGrades($fromTerm, $toTerm);
                if (empty($oldToNewGradeIds)) {
                    throw new \Exception('No grades were rolled over. Aborting term rollover.');
                }
                $this->results['grades'] = count($oldToNewGradeIds);
                Log::info("Rollover Grades: " . json_encode($oldToNewGradeIds));

                $this->rolloverClasses($oldToNewGradeIds, $fromTermId, $toTermId);
                $this->results['classes'] = Klass::where('term_id', $toTermId)->where('active', 1)->count();
                Log::info("Rollover Classes completed.");

                $this->rolloverClassAllocations($oldToNewGradeIds, $fromTermId, $toTermId);
                $this->results['studentAllocations'] = DB::table('klass_student')->where('term_id', $toTermId)->count();
                Log::info("Rollover Class Allocations completed.");

                $this->rolloverSubjects($oldToNewGradeIds, $fromTermId, $toTermId);
                $this->results['subjects'] = GradeSubject::where('term_id', $toTermId)->count();
                Log::info("Rollover Subjects completed.");

                $oldToNewKlassIds = $this->mapOldClassesToNewClasses($fromTermId, $toTermId, $oldToNewGradeIds);
                Log::info("Mapped Old to New Klass IDs: " . json_encode($oldToNewKlassIds));

                $oldToNewSubjectIds = $this->mapOldSubjectsToNewSubjects($fromTermId, $toTermId, $oldToNewGradeIds);
                Log::info("Mapped Old to New Subject IDs: " . json_encode($oldToNewSubjectIds));

                if ($this->isSecondarySchool($schoolType)) {
                    Log::info('School is identified as secondary. Initiating secondary school data rollover.');
                    $this->rolloverSecondarySchoolData($oldToNewSubjectIds, $oldToNewGradeIds, $oldToNewKlassIds, $fromTermId, $toTermId);
                    $this->results['optionalSubjects'] = OptionalSubject::where('term_id', $toTermId)->whereNull('deleted_at')->count();
                    Log::info('Secondary school data rollover completed.');
                } else {
                    Log::info('School is not identified as secondary. Skipping secondary school data rollover.');
                }

                $this->rolloverKlassSubjects($oldToNewKlassIds, $oldToNewSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId);
                $this->results['klassSubjects'] = KlassSubject::where('term_id', $toTermId)->where('active', 1)->count();
                Log::info("Rollover Klass Subjects completed.");


                $oldToNewHouseIds = $this->rolloverHouses($fromTermId, $toTermId);
                $this->results['houses'] = count($oldToNewHouseIds);
                Log::info("Rollover Houses: " . json_encode($oldToNewHouseIds));

                $this->allocateStudentsToNewHouses($fromTermId, $toTermId, $oldToNewHouseIds);
                $this->results['studentHouses'] = DB::table('student_house')->where('term_id', $toTermId)->count();
                Log::info("Allocation of students to new houses completed.");

                $this->allocateUsersToNewHouses($fromTermId, $toTermId, $oldToNewHouseIds);
                $this->results['userHouses'] = DB::table('user_house')->where('term_id', $toTermId)->count();
                Log::info("Allocation of users to new houses completed.");

                $this->rolloverOverallGradingMatrices($fromTermId, $toTermId, $oldToNewGradeIds);
                $this->results['gradingMatrices'] = DB::table('overall_grading_matrices')->where('term_id', $toTermId)->count();
                Log::info("Rollover Overall Grading Matrices completed.");

                $this->rolloverGradingScales($oldToNewSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId);
                $this->results['gradingScales'] = GradingScale::where('term_id', $toTermId)->count();
                Log::info("Rollover Grading Scales completed.");

                Log::info("Closing term ID: {$fromTerm->id}.");
                self::closeTerm($fromTerm);
                Log::info("Term ID: {$fromTerm->id} has been closed.");

                $newTerm = TermHelper::getCurrentTerm();
                if (!$newTerm) {
                    throw new \Exception('Failed to retrieve the current term for test creation.');
                }
                Log::info("Creating tests for school type '{$schoolType}' for term ID: {$newTerm->id}.");
                $this->createTestsForSchoolType($schoolType, $newTerm);
                $this->results['tests'] = DB::table('tests')->where('term_id', $newTerm->id)->count();
                Log::info("Test creation for term ID: {$newTerm->id} completed.");

                $this->results['autoCreatedGradeSubjects'] = $this->autoCreatedGradeSubjects;

                DB::table('term_rollover_histories')->where('id', $this->termRolloverHistoryId)->update(['status' => 'completed']);

                Cache::flush();
                Session::put('selected_term_id', $toTerm->id);
            });
            Log::info("Grade and classes rollover completed successfully for term ID: {$fromTermId} to term ID: {$toTermId}.");
            return true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Term not found during grade and classes rollover: ' . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('General error during grade and classes rollover: ' . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    static function closeTerm($fromTerm) {
        $term = Term::where('id', $fromTerm->id )->first();
        $term->closed = 1;
        $term->extension_days = 0;
        $term->save();
    }

    private function isSecondarySchool($schoolType){
        return in_array($schoolType, [self::SCHOOL_TYPE_JUNIOR, self::SCHOOL_TYPE_SENIOR]);
    }

    private function rolloverSecondarySchoolData(&$oldToNewSubjectIds, $oldToNewGradeIds, $oldToNewKlassIds, $fromTermId, $toTermId){
        $oldToNewOptionalSubjectIds = $this->rolloverOptionalSubjects($oldToNewSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId);
        if (empty($oldToNewOptionalSubjectIds)) {
            Log::warning("No optional subject mappings available. Skipping student allocation to optional subjects.");
            return;
        }
        $this->allocateStudentsToOptionalSubjects($oldToNewOptionalSubjectIds, $oldToNewKlassIds, $fromTermId, $toTermId);
        $this->rolloverCouplingGroups($oldToNewGradeIds, $oldToNewOptionalSubjectIds);
    }

    protected function rolloverCouplingGroups(array $oldToNewGradeIds, array $oldToNewOptionalSubjectIds): void {
        $groups = TimetableSetting::get('optional_coupling_groups', []);

        if (empty($groups)) {
            Log::info('No coupling groups to rollover.');
            return;
        }

        $updatedGroups = [];
        foreach ($groups as $group) {
            $oldGradeId = (int) $group['grade_id'];
            $newGradeId = $oldToNewGradeIds[$oldGradeId] ?? null;

            $newOptionalSubjectIds = [];
            foreach ($group['optional_subject_ids'] as $oldOsId) {
                $newOsId = $oldToNewOptionalSubjectIds[$oldOsId] ?? null;
                if ($newOsId !== null) {
                    $newOptionalSubjectIds[] = $newOsId;
                }
            }

            $updatedGroups[] = [
                'grade_id'             => $newGradeId ?? $oldGradeId,
                'label'                => $group['label'],
                'singles'              => $group['singles'],
                'doubles'              => $group['doubles'],
                'triples'              => $group['triples'],
                'optional_subject_ids' => $newOptionalSubjectIds,
            ];

            Log::info("Coupling group '{$group['label']}' rolled over.", [
                'old_grade_id' => $oldGradeId,
                'new_grade_id' => $newGradeId ?? $oldGradeId,
                'old_os_count' => count($group['optional_subject_ids']),
                'new_os_count' => count($newOptionalSubjectIds),
            ]);
        }

        TimetableSetting::set('optional_coupling_groups', $updatedGroups, auth()->id());
        Log::info('Coupling groups rollover completed.', ['group_count' => count($updatedGroups)]);
    }

    private function createTestsForSchoolType($schoolType, $term){
        switch ($schoolType) {
            case self::SCHOOL_TYPE_PRIMARY:
                $this->createTestsForSubjects(self::SCHOOL_TYPE_PRIMARY, $term);
                break;
            case self::SCHOOL_TYPE_JUNIOR:
                $this->createTestsForSubjects(self::SCHOOL_TYPE_JUNIOR, $term);
                break;
            case self::SCHOOL_TYPE_SENIOR:
                $this->createTestsForSubjects(self::SCHOOL_TYPE_SENIOR, $term);
                break;
        }
    }



    protected function rolloverGrades($fromTerm, $toTerm) {
        try {
            $existingNewGrades = Grade::where('term_id', $toTerm->id)->get();
            $oldGrades = Grade::where('term_id', $fromTerm->id)->get();

            if ($oldGrades->isEmpty()) {
                Log::warning("No grades found to rollover for term_id: {$fromTerm->id}.");
                throw new \Exception("No grades available for rollover from term ID {$fromTerm->id}.");
            }

            $oldGradesByName = $oldGrades->keyBy('name');
            $oldToNewGradeIds = [];

            if ($existingNewGrades->isNotEmpty()) {
                foreach ($existingNewGrades as $newGrade) {
                    if (isset($oldGradesByName[$newGrade->name])) {
                        $oldGrade = $oldGradesByName[$newGrade->name];
                        $oldToNewGradeIds[$oldGrade->id] = $newGrade->id;
                    } else {
                        Log::warning("No matching old grade found for new grade '{$newGrade->name}' (ID: {$newGrade->id}) in term ID {$toTerm->id}.");
                        throw new \Exception("No matching old grade found for new grade '{$newGrade->name}' (ID: {$newGrade->id}).");
                    }
                }
            } else {
                foreach ($oldGrades as $oldGrade) {
                    $oldGrade->active = 0;
                    if (!$oldGrade->save()) {
                        Log::error("Failed to deactivate old grade ID {$oldGrade->id}.");
                        throw new \Exception("Failed to deactivate old grade ID {$oldGrade->id}.");
                    }

                    $newGrade = $oldGrade->replicate(['id', 'created_at', 'updated_at']);
                    $newGrade->term_id = $toTerm->id;
                    $newGrade->active = 1;

                    if (!$newGrade->save()) {
                        Log::error("Failed to save new grade for term ID {$toTerm->id}.");
                        throw new \Exception("Failed to save new grade for term ID {$toTerm->id}.");
                    }

                    $oldToNewGradeIds[$oldGrade->id] = $newGrade->id;
                    Log::info("Rolled over grade '{$oldGrade->name}' (Old ID: {$oldGrade->id}) to new grade ID {$newGrade->id}.");
                }
            }

            Log::info('Grades rollover successfully!', [
                'old_to_new_grade_ids' => $oldToNewGradeIds,
                'total_grades_rolled_over' => count($oldToNewGradeIds)
            ]);

            return $oldToNewGradeIds;
        } catch (\Exception $e) {
            Log::error('Error occurred rolling over grades: ' . $e->getMessage(), [
                'from_term_id' => $fromTerm->id,
                'to_term_id' => $toTerm->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function rolloverClasses($oldToNewGradeIds, $fromTermId, $toTermId) {
        try {
            $this->cleanupDeletedClasses($fromTermId);
            $klasses = Klass::whereIn('grade_id', array_keys($oldToNewGradeIds))->where('term_id', $fromTermId)->whereNull('deleted_at')->get();

            if ($klasses->isEmpty()) {
                Log::warning("No active classes found to rollover for term_id: {$fromTermId}.");
                throw new \Exception("No active classes available for rollover from term ID {$fromTermId}.");
            }
    
            foreach ($klasses as $klass) {
                if (!isset($oldToNewGradeIds[$klass->grade_id])) {
                    Log::error("Failed to find corresponding new grade ID for class (ID: {$klass->id}, Grade ID: {$klass->grade_id}) during rollover.");
                    throw new \Exception("Missing new grade ID for class ID {$klass->id}.");
                }
    
                $klass->active = 0;
                if (!$klass->save()) {
                    Log::error("Failed to deactivate class ID {$klass->id}.");
                    throw new \Exception("Failed to deactivate class ID {$klass->id}.");
                }
                Log::info("Deactivated class ID {$klass->id}.");
                
                $existingClass = Klass::where('name', $klass->name)
                                      ->where('grade_id', $oldToNewGradeIds[$klass->grade_id])
                                      ->where('term_id', $toTermId)
                                      ->first();
    
                if ($existingClass) {
                    Log::info("Class '{$klass->name}' (Grade ID: {$oldToNewGradeIds[$klass->grade_id]}) already exists in term ID {$toTermId}, skipping replication.");
                    continue;
                }
    
                $newKlass = $klass->replicate(['id', 'created_at', 'updated_at']);
                $newKlass->term_id = $toTermId;
                $newKlass->grade_id = $oldToNewGradeIds[$klass->grade_id];
                $newKlass->active = 1;
    
                if (!$newKlass->save()) {
                    Log::error("Failed to save new class for '{$klass->name}' in term ID {$toTermId}.");
                    throw new \Exception("Failed to save new class '{$klass->name}' for term ID {$toTermId}.");
                }
                Log::info("Replicated class '{$klass->name}' (Old ID: {$klass->id}) to new class ID {$newKlass->id} for term ID {$toTermId}.");
            }
    
            Log::info('Classes rollover successfully!', [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'total_classes_rolled_over' => $klasses->count(),
                'cleanup_performed' => true
            ]);
    
        } catch (\Exception $e) {
            Log::error('Unexpected error occurred rolling over classes: ' . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    

    protected function cleanupDeletedClasses($fromTermId) {
        try {
            $deletedClasses = Klass::where('term_id', $fromTermId)->whereNotNull('deleted_at')->get();
            if ($deletedClasses->isEmpty()) {
                Log::info("No soft-deleted classes found for term_id: {$fromTermId}.");
                return;
            }
    
            Log::info("Found " . $deletedClasses->count() . " soft-deleted classes in term_id: {$fromTermId}. Cleaning up related data.");
    
            $deletedClassIds = $deletedClasses->pluck('id')->toArray();
            $deletedStudentAllocations = DB::table('klass_student')
                ->whereIn('klass_id', $deletedClassIds)
                ->where('term_id', $fromTermId)
                ->delete();
    
            if ($deletedStudentAllocations > 0) {
                Log::info("Removed {$deletedStudentAllocations} student allocations for soft-deleted classes.");
            }
    
            $deletedKlassSubjects = DB::table('klass_subjects')->whereIn('klass_id', $deletedClassIds)->where('term_id', $fromTermId)->delete();
            if ($deletedKlassSubjects > 0) {
                Log::info("Removed {$deletedKlassSubjects} klass-subject relationships for soft-deleted classes.");
            }
    
            $deletedOptionalAllocations = DB::table('student_optional_subjects')
                ->whereIn('klass_id', $deletedClassIds)
                ->where('term_id', $fromTermId)
                ->delete();
    
            if ($deletedOptionalAllocations > 0) {
                Log::info("Removed {$deletedOptionalAllocations} optional subject allocations for soft-deleted classes.");
            }

            $permanentlyDeleted = Klass::where('term_id', $fromTermId)->whereNotNull('deleted_at')->forceDelete();
            Log::info("Permanently deleted {$permanentlyDeleted} soft-deleted classes from term_id: {$fromTermId}.");
        } catch (\Exception $e) {
            Log::error("Error occurred while cleaning up deleted classes: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            Log::warning("Continuing with rollover despite cleanup error.");
        }
    }
    
    protected function rolloverClassAllocations($oldToNewGradeIds, $fromTermId, $toTermId) {
        try {
            $toTerm = Term::findOrFail($toTermId);
            $toYear = $toTerm->year;
    
            $oldToNewClassId = $this->mapOldClassesToNewClasses($fromTermId, $toTermId, $oldToNewGradeIds);
            $currentAllocations = DB::table('klass_student')
                ->where('term_id', $fromTermId)
                ->get();
    
            if ($currentAllocations->isEmpty()) {
                Log::warning("No class allocations found to rollover for term_id: {$fromTermId}.");
                throw new \Exception("No class allocations available for rollover from term ID {$fromTermId}.");
            }
    
            $validStudentIds = DB::table('students')
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
    
            Log::info("Found " . count($validStudentIds) . " valid students for class allocation.");
    
            $existingClassAllocations = DB::table('klass_student')
                ->where('term_id', $toTermId)
                ->pluck('student_id')
                ->toArray();
    
            $existingStudentTerms = DB::table('student_term')
                ->where('term_id', $toTermId)
                ->pluck('student_id')
                ->toArray();
    
            $newClassAllocations = [];
            $newStudentTerms = [];
            $skippedCount = 0;
            $unmappedCount = 0;
            $invalidStudentCount = 0;
    
            foreach ($currentAllocations as $allocation) {
                // Check if student still exists
                if (!in_array($allocation->student_id, $validStudentIds)) {
                    Log::warning("Student ID {$allocation->student_id} no longer exists in students table, skipping class allocation.");
                    $invalidStudentCount++;
                    continue;
                }
    
                $newClassId = $oldToNewClassId[$allocation->klass_id] ?? null;
                $newGradeId = $oldToNewGradeIds[$allocation->grade_id] ?? null;
    
                if (!$newClassId || !$newGradeId) {
                    Log::warning("Missing new class ID or new grade ID for allocation: Klass ID {$allocation->klass_id}, Grade ID {$allocation->grade_id}, Student ID {$allocation->student_id}. Skipping this allocation.");
                    $unmappedCount++;
                    continue;
                }
    
                if (in_array($allocation->student_id, $existingClassAllocations)) {
                    Log::info("Student ID {$allocation->student_id} is already allocated to a class in term ID {$toTermId}, skipping.");
                    $skippedCount++;
                    continue;
                }
    
                $newClassAllocations[] = [
                    'klass_id' => $newClassId,
                    'student_id' => $allocation->student_id,
                    'grade_id' => $newGradeId,
                    'term_id' => $toTermId,
                    'year' => $toYear,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                if (!in_array($allocation->student_id, $existingStudentTerms)) {
                    $newStudentTerms[] = [
                        'student_id' => $allocation->student_id,
                        'term_id' => $toTermId,
                        'grade_id' => $newGradeId,
                        'year' => $toYear,
                        'status' => 'Current',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
    
            if (!empty($newClassAllocations)) {
                try {
                    $batchStudentIds = array_column($newClassAllocations, 'student_id');
                    $invalidBatchStudents = array_diff($batchStudentIds, $validStudentIds);
                    
                    if (!empty($invalidBatchStudents)) {
                        Log::warning("Found invalid student IDs in batch allocation: " . implode(', ', $invalidBatchStudents));
                        $newClassAllocations = array_filter($newClassAllocations, function($allocation) use ($validStudentIds) {
                            return in_array($allocation['student_id'], $validStudentIds);
                        });
                        $newStudentTerms = array_filter($newStudentTerms, function($term) use ($validStudentIds) {
                            return in_array($term['student_id'], $validStudentIds);
                        });
                    }
    
                    if (!empty($newClassAllocations)) {
                        $insertedClasses = DB::table('klass_student')->insert($newClassAllocations);
                        if (!$insertedClasses) {
                            Log::error("Failed to insert new class allocations for term ID {$toTermId}.");
                            throw new \Exception("Failed to insert new class allocations for term ID {$toTermId}.");
                        }
                        Log::info("Inserted " . count($newClassAllocations) . " new class allocations for term ID {$toTermId}.");
                    }
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new class allocations for term ID {$toTermId}: " . $insertException->getMessage());
                    throw new \Exception("Failed to insert new class allocations for term ID {$toTermId}.");
                }
            }
    
            if (!empty($newStudentTerms)) {
                try {
                    $insertedStudentTerms = DB::table('student_term')->insert($newStudentTerms);
                    if (!$insertedStudentTerms) {
                        Log::error("Failed to insert new student term allocations for term ID {$toTermId}.");
                        throw new \Exception("Failed to insert new student term allocations for term ID {$toTermId}.");
                    }
                    Log::info("Inserted " . count($newStudentTerms) . " new student term allocations for term ID {$toTermId}.");
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new student term allocations for term ID {$toTermId}: " . $insertException->getMessage());
                    throw new \Exception("Failed to insert new student term allocations for term ID {$toTermId}.");
                }
            }
    
            Log::info('Class and student term allocations complete!', [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'new_class_allocations' => count($newClassAllocations),
                'new_student_terms' => count($newStudentTerms),
                'skipped_existing' => $skippedCount,
                'skipped_unmapped' => $unmappedCount,
                'skipped_invalid_students' => $invalidStudentCount
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred rolling over class and student term allocations: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; 
        }
    }
    
    protected function mapOldClassesToNewClasses($fromTermId, $toTermId, $oldToNewGradeIds) {
        $oldClasses = Klass::where('term_id', $fromTermId)
                           ->where('active', 0)
                           ->whereNull('deleted_at')
                           ->get();
    
        $newClasses = Klass::where('term_id', $toTermId)
                           ->where('active', 1)
                           ->get();
    
        $mapping = [];
        foreach ($oldClasses as $oldClass) {
            $newGradeId = $oldToNewGradeIds[$oldClass->grade_id] ?? null;
            if (!$newGradeId) {
                Log::warning("No new grade ID found for old grade ID {$oldClass->grade_id}");
                continue;
            }
    
            $newClass = $newClasses->first(function ($newClass) use ($oldClass, $newGradeId) {
                return $newClass->name === $oldClass->name && $newClass->grade_id == $newGradeId;
            });
    
            if ($newClass) {
                $mapping[$oldClass->id] = $newClass->id;
            } else {
                Log::warning("No matching new class found for old class ID {$oldClass->id}");
            }
        }
        return $mapping;
    }
  
    protected function rolloverSubjects($oldToNewGradeIds, $fromTermId, $toTermId) {
        try {
            $schoolSetup = SchoolSetup::first();
            if (!$schoolSetup || !$schoolSetup->type) {
                Log::error("School type configuration is missing.");
                throw new \Exception("School type configuration is missing.");
            }
            $schoolType = $schoolSetup->type;
            Log::info("School type identified as '{$schoolType}'.");
    
            $subjects = GradeSubject::with('components')
                        ->whereIn('grade_id', array_keys($oldToNewGradeIds))
                        ->where('term_id', $fromTermId)
                        ->get();
    
            if ($subjects->isEmpty()) {
                Log::warning("No subjects found to rollover for term_id: {$fromTermId}.");
                throw new \Exception("No subjects available for rollover from term ID {$fromTermId}.");
            }
    
            $rolledOverSubjects = 0;
            $skippedSubjects = 0;
            $rolledOverComponents = 0;
            $skippedComponents = 0;
    
            foreach ($subjects as $subject) {
                $newGradeId = $oldToNewGradeIds[$subject->grade_id] ?? null;
                if ($newGradeId === null) {
                    Log::error("No new grade ID found for old grade ID {$subject->grade_id}, skipping subject ID {$subject->id}.");
                    $skippedSubjects++;
                    throw new \Exception("Missing new grade ID for subject ID {$subject->id}.");
                }
    
                $existingSubject = GradeSubject::where('grade_id', $newGradeId)
                                               ->where('term_id', $toTermId)
                                               ->where('subject_id', $subject->subject_id)
                                               ->first();
    
                if ($existingSubject) {
                    Log::info("Subject ID {$subject->subject_id} for grade ID {$newGradeId} already exists for the new term, skipping.");
                    $skippedSubjects++;
                    continue;
                }
    
                $newSubject = $subject->replicate(['id', 'created_at', 'updated_at']);
                $newSubject->grade_id = $newGradeId;
                $newSubject->term_id = $toTermId;
    
                if (!$newSubject->save()) {
                    Log::error("Failed to save replicated subject ID {$newSubject->id}.");
                    throw new \Exception("Failed to save replicated subject ID {$newSubject->id}.");
                }
    
                Log::info("Replicated subject ID {$subject->id} to new subject ID {$newSubject->id} for term ID {$toTermId}.");
                $rolledOverSubjects++;
    
                if ($schoolType === 'Primary' && $subject->components->count() > 0) {
                    foreach ($subject->components as $component) {
                        $existingComponent = Component::where('grade_subject_id', $newSubject->id)
                                                      ->where('term_id', $toTermId)
                                                      ->where('name', $component->name)
                                                      ->first();
    
                        if ($existingComponent) {
                            Log::info("Component '{$component->name}' already exists for new subject ID {$newSubject->id} in term ID {$toTermId}, skipping.");
                            $skippedComponents++;
                            continue; 
                        }
    
                        $newComponent = $component->replicate(['id', 'created_at', 'updated_at']);
                        $newComponent->grade_subject_id = $newSubject->id;
                        $newComponent->grade_id = $newGradeId;
                        $newComponent->term_id = $toTermId;
    
                        if (!$newComponent->save()) {
                            Log::error("Failed to save replicated component ID {$newComponent->id}.");
                            throw new \Exception("Failed to save replicated component ID {$newComponent->id}.");
                        }
    
                        Log::info("Replicated component ID {$component->id} to new component ID {$newComponent->id} for subject ID {$newSubject->id} in term ID {$toTermId}.");
                        $rolledOverComponents++;
                    }
                }
            }
    
            Log::info('Subjects and components rollover completed successfully.', [
                'rolled_over_subjects' => $rolledOverSubjects,
                'skipped_subjects' => $skippedSubjects,
                'rolled_over_components' => $rolledOverComponents,
                'skipped_components' => $skippedComponents,
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred while rolling over subjects and components: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    
    protected function mapOldSubjectsToNewSubjects($fromTermId, $toTermId, $oldToNewGradeIds) {
        $oldSubjects = GradeSubject::where('term_id', $fromTermId)
                                   ->get();
  
        $newSubjects = GradeSubject::where('term_id', $toTermId)
                                   ->get();
  
        $newSubjectsMap = [];
        foreach ($newSubjects as $newSubject) {
            $newSubjectsMap[$newSubject->subject_id][$newSubject->grade_id] = $newSubject->id;
        }
  
        $mapping = [];
        foreach ($oldSubjects as $oldSubject) {
            $newGradeId = $oldToNewGradeIds[$oldSubject->grade_id] ?? null;
            if (!$newGradeId) {
                Log::warning("No new grade ID found for old grade ID {$oldSubject->grade_id}, skipping subject ID {$oldSubject->id}.");
                continue;
            }
  
            $newSubjectId = $newSubjectsMap[$oldSubject->subject_id][$newGradeId] ?? null;
  
            if ($newSubjectId) {
                $mapping[$oldSubject->id] = $newSubjectId;
            } else {
                Log::warning("No matching new subject found for old subject ID {$oldSubject->id} with subject_id {$oldSubject->subject_id} and new grade ID {$newGradeId}.");
            }
        }
        return $mapping;
    }
  
    protected function rolloverKlassSubjects($oldToNewKlassIds, $oldToNewSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId) {
        DB::beginTransaction();
        try {
            $klassSubjects = KlassSubject::where('term_id', $fromTermId)->get();
    
            if ($klassSubjects->isEmpty()) {
                Log::warning("No klass_subjects found to rollover for term_id: {$fromTermId}.");
                throw new \Exception("No klass_subjects available for rollover from term ID {$fromTermId}.");
            }
    
            $rolledOverCount = 0;
            $skippedCount = 0;
    
            foreach ($klassSubjects as $klassSubject) {
                $klassSubject->active = 0;
                if (!$klassSubject->save()) {
                    Log::error("Failed to deactivate klass_subject ID {$klassSubject->id}.");
                    throw new \Exception("Failed to deactivate klass_subject ID {$klassSubject->id}.");
                }
                Log::info("Deactivated klass_subject ID {$klassSubject->id}.");
    
                $newKlassId = $oldToNewKlassIds[$klassSubject->klass_id] ?? null;
                $newSubjectId = $oldToNewSubjectIds[$klassSubject->grade_subject_id] ?? null;
                $newGradeId = $oldToNewGradeIds[$klassSubject->grade_id] ?? null;
    
                if (!$newKlassId || !$newSubjectId || !$newGradeId) {
                    Log::error("Missing new IDs for klass_subject ID {$klassSubject->id}: New Klass ID = {$newKlassId}, New Subject ID = {$newSubjectId}, New Grade ID = {$newGradeId}.");
                    throw new \Exception("Missing new IDs for klass_subject ID {$klassSubject->id}.");
                }
    
                $existingKlassSubject = KlassSubject::where('klass_id', $newKlassId)
                    ->where('grade_subject_id', $newSubjectId)
                    ->where('term_id', $toTermId)
                    ->first();
    
                if ($existingKlassSubject) {
                    Log::info("KlassSubject already exists for new klass ID {$newKlassId}, subject ID {$newSubjectId} in term ID {$toTermId}, skipping.");
                    $skippedCount++;
                    continue;
                }
    
                $newKlassSubject = $klassSubject->replicate(['id', 'created_at', 'updated_at']);
                $newKlassSubject->klass_id = $newKlassId;
                $newKlassSubject->grade_subject_id = $newSubjectId;
                $newKlassSubject->grade_id = $newGradeId;
                $newKlassSubject->term_id = $toTermId;
                $newKlassSubject->active = 1;
    
                if (!$newKlassSubject->save()) {
                    Log::error("Failed to save new klass_subject for klass_subject ID {$klassSubject->id}.");
                    throw new \Exception("Failed to save new klass_subject for klass_subject ID {$klassSubject->id}.");
                }
    
                Log::info("Replicated klass_subject ID {$klassSubject->id} to new klass_subject ID {$newKlassSubject->id} for term ID {$toTermId}.");
                $rolledOverCount++;
            }
    
            DB::commit();
            Log::info('Klass subjects rolled over successfully!', [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'rolled_over' => $rolledOverCount,
                'skipped' => $skippedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error occurred while rolling over klass_subjects: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function rolloverOptionalSubjects(&$oldToNewGradeSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId) {
        try {
            $this->cleanupDeletedOptionalSubjects($fromTermId);
            
            $optionalSubjects = OptionalSubject::where('term_id', $fromTermId)
                ->whereNull('deleted_at')
                ->get();
    
            if ($optionalSubjects->isEmpty()) {
                Log::warning("No active optional subjects found to rollover for term_id: {$fromTermId}.");
                return [];
            }
    
            $existingOptionalSubjects = OptionalSubject::where('term_id', $toTermId)
                ->get()
                ->map(function ($item) {
                    return $item->name . '-' . $item->grade_subject_id . '-' . $item->grade_id;
                })
                ->toArray();
    
            $oldToNewOptionalSubjectIds = [];
            $rolledOverCount = 0;
            $skippedCount = 0;
            $toTermYear = Term::findOrFail($toTermId)->year;

            foreach ($optionalSubjects as $optionalSubject) {
                $newGradeSubjectId = $oldToNewGradeSubjectIds[$optionalSubject->grade_subject_id] ?? null;
                $newGradeId = $oldToNewGradeIds[$optionalSubject->grade_id] ?? null;

                if (!$newGradeId) {
                    Log::warning("Missing new grade ID for optional subject ID {$optionalSubject->id}. Skipping.");
                    $skippedCount++;
                    continue;
                }

                if (!$newGradeSubjectId) {
                    $oldGs = GradeSubject::find($optionalSubject->grade_subject_id);
                    if ($oldGs) {
                        $newGradeSubjectId = DB::table('grade_subject')->insertGetId([
                            'grade_id' => $newGradeId,
                            'subject_id' => $oldGs->subject_id,
                            'department_id' => $oldGs->department_id,
                            'term_id' => $toTermId,
                            'year' => $toTermYear,
                            'type' => $oldGs->type,
                            'mandatory' => $oldGs->mandatory,
                            'sequence' => $oldGs->sequence,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $this->autoCreatedGradeSubjects++;
                        $oldToNewGradeSubjectIds[$optionalSubject->grade_subject_id] = $newGradeSubjectId;

                        Log::info("Auto-created missing grade_subject for term rollover optional subject", [
                            'newGradeSubjectId' => $newGradeSubjectId,
                            'optionalSubjectId' => $optionalSubject->id,
                            'newGradeId' => $newGradeId,
                            'subjectId' => $oldGs->subject_id,
                            'toTermId' => $toTermId,
                        ]);
                    } else {
                        Log::warning("Could not find old grade_subject ID {$optionalSubject->grade_subject_id} for optional subject ID {$optionalSubject->id}. Skipping.");
                        $skippedCount++;
                        continue;
                    }
                }
    
                $optionalSubjectKey = $optionalSubject->name . '-' . $newGradeSubjectId . '-' . $newGradeId;
                if (in_array($optionalSubjectKey, $existingOptionalSubjects)) {
                    Log::info("Optional subject '{$optionalSubject->name}' (Grade Subject ID: {$newGradeSubjectId}, Grade ID: {$newGradeId}) already exists in the new term, skipping.");
                    $skippedCount++;
                    continue;
                }

                $optionalSubject->active = 0;
                if (!$optionalSubject->save()) {
                    Log::error("Failed to deactivate optional subject ID {$optionalSubject->id}.");
                    throw new \Exception("Failed to deactivate optional subject ID {$optionalSubject->id}.");
                }
                Log::info("Deactivated optional subject ID {$optionalSubject->id}.");

                $newOptionalSubject = $optionalSubject->replicate(['id', 'created_at', 'updated_at']);
                $newOptionalSubject->grade_subject_id = $newGradeSubjectId;
                $newOptionalSubject->grade_id = $newGradeId;
                $newOptionalSubject->term_id = $toTermId;
                $newOptionalSubject->user_id = $optionalSubject->user_id;
                $newOptionalSubject->venue_id = $optionalSubject->venue_id;
                $newOptionalSubject->active = 1;
                $newOptionalSubject->created_at = now();
                $newOptionalSubject->updated_at = now();
    
                if (!$newOptionalSubject->save()) {
                    Log::error("Failed to save new optional subject for '{$optionalSubject->name}' in term ID {$toTermId}.");
                    throw new \Exception("Failed to save new optional subject for '{$optionalSubject->name}' in term ID {$toTermId}.");
                }
    
                $oldToNewOptionalSubjectIds[$optionalSubject->id] = $newOptionalSubject->id;
                Log::info("Replicated optional subject '{$optionalSubject->name}' (Old ID: {$optionalSubject->id}) to new optional subject ID {$newOptionalSubject->id} for term ID {$toTermId}.");
                $rolledOverCount++;
                $existingOptionalSubjects[] = $optionalSubjectKey;
            }
    
            Log::info('Optional subjects rollover completed successfully.', [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'rolled_over' => $rolledOverCount,
                'skipped' => $skippedCount,
                'mappings_created' => count($oldToNewOptionalSubjectIds),
                'cleanup_performed' => true
            ]);
            
            return $oldToNewOptionalSubjectIds;
        } catch (\Exception $e) {
            Log::error("Error occurred while rolling over optional subjects: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function cleanupDeletedOptionalSubjects($fromTermId) {
        try {
            $deletedOptionalSubjects = OptionalSubject::where('term_id', $fromTermId)
                ->whereNotNull('deleted_at')
                ->get();
    
            if ($deletedOptionalSubjects->isEmpty()) {
                Log::info("No soft-deleted optional subjects found for term_id: {$fromTermId}.");
                return;
            }
    
            Log::info("Found " . $deletedOptionalSubjects->count() . " soft-deleted optional subjects in term_id: {$fromTermId}. Cleaning up related data.");
            $deletedOptionalSubjectIds = $deletedOptionalSubjects->pluck('id')->toArray();
            $deletedAllocations = DB::table('student_optional_subjects')
                ->whereIn('optional_subject_id', $deletedOptionalSubjectIds)
                ->where('term_id', $fromTermId)
                ->delete();
    
            if ($deletedAllocations > 0) {
                Log::info("Removed {$deletedAllocations} student allocations for soft-deleted optional subjects.");
            }

            $permanentlyDeleted = OptionalSubject::where('term_id', $fromTermId)
                ->whereNotNull('deleted_at')
                ->forceDelete();
    
            Log::info("Permanently deleted {$permanentlyDeleted} soft-deleted optional subjects from term_id: {$fromTermId}.");
    
        } catch (\Exception $e) {
            Log::error("Error occurred while cleaning up deleted optional subjects: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            Log::warning("Continuing with rollover despite cleanup error.");
        }
    }
    
    protected function generateOldToNewOptionalSubjectIds($fromTermId, $toTermId, $oldToNewGradeIds, $oldToNewGradeSubjectIds) {
        $mapping = [];
        $currentOptionalSubjects = OptionalSubject::where('term_id', $fromTermId)->get();
        $nextOptionalSubjects = OptionalSubject::where('term_id', $toTermId)->get();
    
        $nextOptionalSubjectsMap = [];
        foreach ($nextOptionalSubjects as $subject) {
            $key = $subject->name . '-' . $subject->grade_id . '-' . $subject->grade_subject_id . '-' . $subject->grouping;
            $nextOptionalSubjectsMap[$key] = $subject->id;
        }
    
        foreach ($currentOptionalSubjects as $currentSubject) {
            $newGradeId = $oldToNewGradeIds[$currentSubject->grade_id] ?? null;
            $newGradeSubjectId = $oldToNewGradeSubjectIds[$currentSubject->grade_subject_id] ?? null;
    
            if ($newGradeId === null || $newGradeSubjectId === null) {
                Log::warning("No new grade or grade_subject ID found for old IDs (grade_id: {$currentSubject->grade_id}, grade_subject_id: {$currentSubject->grade_subject_id}), skipping optional subject ID {$currentSubject->id}.");
                continue;
            }
    
            $key = $currentSubject->name . '-' . $newGradeId . '-' . $newGradeSubjectId . '-' . $currentSubject->grouping;
            if (isset($nextOptionalSubjectsMap[$key])) {
                $mapping[$currentSubject->id] = $nextOptionalSubjectsMap[$key];
            } else {
                Log::warning("No matching optional subject found in new term for old optional subject ID {$currentSubject->id}.");
            }
        }
    
        return $mapping;
    }

    protected function allocateStudentsToOptionalSubjects($oldToNewOptionalSubjectIds, $oldToNewKlassIds, $fromTermId, $toTermId) {
        try {
            $studentAllocations = DB::table('student_optional_subjects')->where('term_id', $fromTermId)->get();
    
            if ($studentAllocations->isEmpty()) {
                Log::info("No student allocations found for optional subjects in term_id: {$fromTermId}.");
                return;
            }

            $validStudentIds = DB::table('students')
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
    
            Log::info("Found " . count($validStudentIds) . " valid students for optional subject allocation.");
    
            $existingAllocations = DB::table('student_optional_subjects')
                ->where('term_id', $toTermId)
                ->select('student_id', 'optional_subject_id')
                ->get()
                ->map(function ($item) {
                    return $item->student_id . '-' . $item->optional_subject_id;
                })->toArray();
    
            $newAllocations = [];
            $rolledOverCount = 0;
            $skippedCount = 0;
            $unmappedCount = 0;
            $invalidStudentCount = 0;
    
            foreach ($studentAllocations as $allocation) {
                if (!in_array($allocation->student_id, $validStudentIds)) {
                    Log::warning("Student ID {$allocation->student_id} no longer exists in students table, skipping optional subject allocation.");
                    $invalidStudentCount++;
                    continue;
                }
    
                $newOptionalSubjectId = $oldToNewOptionalSubjectIds[$allocation->optional_subject_id] ?? null;
                $newKlassId = $oldToNewKlassIds[$allocation->klass_id] ?? null;
    
                if (!$newOptionalSubjectId) {
                    Log::warning("Missing new optional_subject_id for old optional_subject_id {$allocation->optional_subject_id} (Student ID: {$allocation->student_id}). Skipping this allocation.");
                    $unmappedCount++;
                    continue;
                }
    
                if (!$newKlassId) {
                    Log::warning("Missing new klass_id for old klass_id {$allocation->klass_id} (Student ID: {$allocation->student_id}). Assigning null.");
                    $newKlassId = null; 
                }
    
                $allocationKey = $allocation->student_id . '-' . $newOptionalSubjectId;
                if (in_array($allocationKey, $existingAllocations)) {
                    Log::info("Student ID {$allocation->student_id} is already allocated to optional_subject_id {$newOptionalSubjectId} in term ID {$toTermId}, skipping.");
                    $skippedCount++;
                    continue;
                }
    
                $newAllocations[] = [
                    'student_id' => $allocation->student_id,
                    'optional_subject_id' => $newOptionalSubjectId,
                    'term_id' => $toTermId,
                    'klass_id' => $newKlassId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $existingAllocations[] = $allocationKey;
                $rolledOverCount++;
            }
    
            if (!empty($newAllocations)) {
                try {
                    DB::table('student_optional_subjects')->insert($newAllocations);
                    Log::info("Inserted " . count($newAllocations) . " new student allocations to optional subjects for term ID {$toTermId}.");
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new student allocations for term ID {$toTermId}: " . $insertException->getMessage());
                    throw new \Exception("Failed to insert new student allocations for term ID {$toTermId}.");
                }
            } else {
                Log::info("No new student allocations to insert for term ID {$toTermId}.");
            }
    
            Log::info('Student allocation to optional subjects completed.', [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'total_found' => $studentAllocations->count(),
                'allocated' => $rolledOverCount,
                'skipped_existing' => $skippedCount,
                'skipped_unmapped' => $unmappedCount,
                'skipped_invalid_students' => $invalidStudentCount
            ]);
    
        } catch (\Exception $e) {
            Log::error("Error occurred while allocating students to optional subjects for the next term: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; 
        }
    }
    
    protected function rolloverOverallGradingMatrices($fromTermId, $toTermId, $oldToNewGradeIds) {
        try {
            $existingMatrices = DB::table('overall_grading_matrices')
                ->where('term_id', $toTermId)
                ->select('grade_id', 'grade')
                ->get()
                ->map(function ($item) {
                    return $item->grade_id . '_' . $item->grade;
                })
                ->toArray();
    
            $currentGradingMatrices = DB::table('overall_grading_matrices')
                ->where('term_id', $fromTermId)
                ->get();
    
            if ($currentGradingMatrices->isEmpty()) {
                Log::warning("No grading matrices found to rollover for term ID: {$fromTermId}.");
                throw new \Exception("No grading matrices available for rollover from term ID {$fromTermId}.");
            }
    
            $newGradingMatrices = [];
            $rolledOverCount = 0;
            $skippedCount = 0;
            $failedCount = 0;
    
            foreach ($currentGradingMatrices as $matrix) {
                $newGradeId = $oldToNewGradeIds[$matrix->grade_id] ?? null;

                if ($newGradeId === null) {
                    Log::error("Missing new grade ID for old grade ID {$matrix->grade_id}, skipping matrix.");
                    throw new \Exception("Missing new grade ID for old grade ID {$matrix->grade_id}.");
                }
    
                $matrixIdentifier = $newGradeId . '_' . $matrix->grade;
                if (in_array($matrixIdentifier, $existingMatrices)) {
                    Log::info("Grading matrix for grade ID {$newGradeId} and grade '{$matrix->grade}' already exists in term ID {$toTermId}, skipping.");
                    $skippedCount++;
                    continue;
                }
    
                $newGradingMatrices[] = [
                    'term_id'      => $toTermId,
                    'grade_id'     => $newGradeId,
                    'year'         => $matrix->year,
                    'min_score'    => $matrix->min_score,
                    'max_score'    => $matrix->max_score,
                    'grade'        => $matrix->grade,
                    'description'  => $matrix->description,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
    
                $existingMatrices[] = $matrixIdentifier;
                $rolledOverCount++;
            }
    
            if (!empty($newGradingMatrices)) {
                try {
                    DB::table('overall_grading_matrices')->insert($newGradingMatrices);
                    Log::info("Inserted {$rolledOverCount} new grading matrices for term ID {$toTermId}.");
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new grading matrices for term ID {$toTermId}: " . $insertException->getMessage());
                    throw new \Exception("Failed to insert new grading matrices for term ID {$toTermId}.");
                }
            } else {
                Log::info("No new grading matrices to insert for term ID: {$toTermId}.");
            }
    
            Log::info('Overall grading matrices rollover completed successfully.', [
                'from_term_id'  => $fromTermId,
                'to_term_id'    => $toTermId,
                'rolled_over'   => $rolledOverCount,
                'skipped'       => $skippedCount,
                'failed'        => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred while rolling over grading matrices: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId,
                'exception'    => get_class($e),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'trace'        => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    protected function rolloverGradingScales($oldToNewGradeSubjectIds, $oldToNewGradeIds, $fromTermId, $toTermId) {
        try {
            $existingGradingScales = GradingScale::where('term_id', $toTermId)
                ->get()
                ->map(function ($item) {
                    return $item->grade_subject_id . '_' . $item->grade;
                })
                ->toArray();
    
            $gradingScales = GradingScale::where('term_id', $fromTermId)
                ->with(['gradeSubject.components'])
                ->get();
    
            if ($gradingScales->isEmpty()) {
                Log::warning("No grading scales found to rollover for term ID: {$fromTermId}.");
                throw new \Exception("No grading scales available for rollover from term ID {$fromTermId}.");
            }
    
            $newGradingScales = [];
            $rolledOverCount = 0;
            $skippedCount = 0;
    
            foreach ($gradingScales as $scale) {
                $newGradeSubjectId = $oldToNewGradeSubjectIds[$scale->grade_subject_id] ?? null;
                $newGradeId = $oldToNewGradeIds[$scale->grade_id] ?? null;
    
                if (!$newGradeSubjectId || !$newGradeId) {
                    Log::error("Missing mappings for grade_subject_id {$scale->grade_subject_id} or grade_id {$scale->grade_id}, skipping grading scale ID {$scale->id}.");
                    throw new \Exception("Missing mappings for grade_subject_id {$scale->grade_subject_id} or grade_id {$scale->grade_id}.");
                }
    
                $gradingScaleIdentifier = $newGradeSubjectId . '_' . $scale->grade;
                if (in_array($gradingScaleIdentifier, $existingGradingScales)) {
                    Log::info("Grading scale for grade_subject_id {$newGradeSubjectId} and grade '{$scale->grade}' already exists in term ID {$toTermId}, skipping.");
                    $skippedCount++;
                    continue;
                }
    
                if ($scale->gradeSubject && $scale->gradeSubject->components->count() > 0) {
                    Log::info("Skipping grading scale for grade_subject_id '{$scale->grade_subject_id}' because the grade subject has components.");
                    $skippedCount++;
                    continue;
                }
    
                $newGradingScales[] = [
                    'grade_subject_id' => $newGradeSubjectId,
                    'term_id'          => $toTermId,
                    'grade_id'         => $newGradeId,
                    'grade'            => $scale->grade,
                    'year'             => $scale->year, 
                    'min_score'        => $scale->min_score,
                    'max_score'        => $scale->max_score,
                    'points'           => $scale->points,
                    'description'      => $scale->description,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
    
                $existingGradingScales[] = $gradingScaleIdentifier;
                $rolledOverCount++;
            }
    
            if (!empty($newGradingScales)) {
                try {
                    GradingScale::insert($newGradingScales);
                    Log::info("Inserted {$rolledOverCount} new grading scales for term ID {$toTermId}.");
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new grading scales for term ID {$toTermId}: " . $insertException->getMessage());
                    throw new \Exception("Failed to insert new grading scales for term ID {$toTermId}.");
                }
            } else {
                Log::info("No new grading scales to insert for term ID: {$toTermId}.");
            }
    
            Log::info('Grading scales rollover completed successfully.', [
                'from_term_id'  => $fromTermId,
                'to_term_id'    => $toTermId,
                'rolled_over'   => $rolledOverCount,
                'skipped'       => $skippedCount,
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred while rolling over grading scales: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId,
                'exception'    => get_class($e),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'trace'        => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function rolloverHouses($fromTermId, $toTermId) {
        try {
            $currentHouses = House::where('term_id', $fromTermId)->get();
            $oldToNewHouseIds = [];
    
            if ($currentHouses->isEmpty()) {
                Log::warning("No houses found for term ID: {$fromTermId}. Proceeding without rolling over houses.", [
                    'from_term_id' => $fromTermId,
                    'to_term_id' => $toTermId
                ]);
                return $oldToNewHouseIds;
            }
    
            $existingHouses = House::where('term_id', $toTermId)
                ->pluck('id', 'name')
                ->toArray();
    
            $rolledOverCount = 0;
            $skippedCount = 0;
    
            foreach ($currentHouses as $house) {
                if (array_key_exists($house->name, $existingHouses)) {
                    Log::info("House '{$house->name}' already exists in term ID {$toTermId}, skipping.");
                    $oldToNewHouseIds[$house->id] = $existingHouses[$house->name];
                    $skippedCount++;
                    continue;
                }
    
                $newHouse = $house->replicate(['id', 'created_at', 'updated_at']);
                $newHouse->term_id = $toTermId;
                $newHouse->year = $house->year;
                $newHouse->head = $house->head;
                $newHouse->assistant = $house->assistant;
    
                if (!$newHouse->save()) {
                    Log::error("Failed to save new house '{$newHouse->name}' for term ID {$toTermId}.");
                    throw new \Exception("Failed to save new house '{$newHouse->name}' for term ID {$toTermId}.");
                }
    
                Log::info("House '{$newHouse->name}' successfully rolled over to term ID {$toTermId} with new ID {$newHouse->id}.");
                $oldToNewHouseIds[$house->id] = $newHouse->id;
                $rolledOverCount++;
            }
    
            Log::info("Houses rollover completed: {$rolledOverCount} rolled over, {$skippedCount} skipped.", [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId
            ]);
    
            return $oldToNewHouseIds;
        } catch (\Exception $e) {
            Log::error("Error occurred while rolling over houses: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId,
                'exception'    => get_class($e),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'trace'        => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function allocateStudentsToNewHouses($fromTermId, $toTermId, $oldToNewHouseIds) {
        try {
            $studentHouses = DB::table('student_house')
                ->where('term_id', $fromTermId)
                ->get();
    
            $allocationSummary = [
                'total_found' => $studentHouses->count(),
                'allocated'   => 0,
                'skipped'     => 0,
                'failed'      => 0,
                'invalid_students' => 0,
            ];
    
            if ($studentHouses->isEmpty()) {
                Log::warning("No student house allocations found for term ID: {$fromTermId}. Proceeding without allocating students to new houses.", [
                    'from_term_id' => $fromTermId,
                    'to_term_id'   => $toTermId,
                ]);
                return $allocationSummary;
            }
    
            if (empty($oldToNewHouseIds)) {
                Log::warning("No house mappings available to allocate students to new houses. Proceeding without allocations.", [
                    'from_term_id' => $fromTermId,
                    'to_term_id'   => $toTermId,
                ]);
                return $allocationSummary;
            }
    
            $validStudentIds = DB::table('students')
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
    
    
            $existingAllocations = DB::table('student_house')
                ->where('term_id', $toTermId)
                ->select('student_id', 'house_id')
                ->get()
                ->map(function ($item) {
                    return $item->student_id . '-' . $item->house_id;
                })
                ->toArray();
    
            $newAllocations = [];
            $rolledOverCount = 0;
            $skippedCount = 0;
            $invalidStudentCount = 0;
    
            foreach ($studentHouses as $studentHouse) {
                $oldHouseId = $studentHouse->house_id;
                $studentId = $studentHouse->student_id;

                if (!in_array($studentId, $validStudentIds)) {
                    Log::warning("Student ID {$studentId} no longer exists in students table, skipping house allocation.");
                    $allocationSummary['invalid_students']++;
                    $invalidStudentCount++;
                    continue;
                }
    
                if (isset($oldToNewHouseIds[$oldHouseId])) {
                    $newHouseId = $oldToNewHouseIds[$oldHouseId];
    
                    $allocationKey = $studentId . '-' . $newHouseId;
                    if (in_array($allocationKey, $existingAllocations)) {
                        Log::info("Student ID {$studentId} is already allocated to house ID {$newHouseId} in term ID {$toTermId}, skipping.");
                        $allocationSummary['skipped']++;
                        $skippedCount++;
                        continue;
                    }
    
                    $newAllocations[] = [
                        'student_id'    => $studentId,
                        'house_id'      => $newHouseId,
                        'term_id'       => $toTermId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
    
                    $existingAllocations[] = $allocationKey;
                    $allocationSummary['allocated']++;
                    $rolledOverCount++;
                } else {
                    Log::warning("No new house ID found for old house ID {$oldHouseId}, skipping allocation for student ID {$studentId}.");
                    $allocationSummary['failed']++;
                }
            }
    
            if (!empty($newAllocations)) {
                try {
                    $batchStudentIds = array_column($newAllocations, 'student_id');
                    $invalidBatchStudents = array_diff($batchStudentIds, $validStudentIds);
                    
                    if (!empty($invalidBatchStudents)) {
                        Log::warning("Found invalid student IDs in batch allocation: " . implode(', ', $invalidBatchStudents));
                        $newAllocations = array_filter($newAllocations, function($allocation) use ($validStudentIds) {
                            return in_array($allocation['student_id'], $validStudentIds);
                        });
                    }
    
                    if (!empty($newAllocations)) {
                        DB::table('student_house')->insert($newAllocations);
                        Log::info("Inserted " . count($newAllocations) . " new student house allocations for term ID {$toTermId}.");
                    } else {
                        Log::info("No valid student house allocations to insert after filtering for term ID {$toTermId}.");
                    }
                } catch (\Exception $insertException) {
                    Log::error("Failed to insert new student house allocations for term ID {$toTermId}: " . $insertException->getMessage());
                    Log::error("Failed allocation details:", [
                        'student_ids' => array_column($newAllocations, 'student_id'),
                        'house_ids' => array_column($newAllocations, 'house_id'),
                        'term_id' => $toTermId
                    ]);
                    
                    throw new \Exception("Failed to insert new student house allocations for term ID {$toTermId}.");
                }
            } else {
                Log::info("No new student house allocations to insert for term ID: {$toTermId}.");
            }
    
            Log::info('Student house allocations rollover completed successfully.', [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId,
                'total_found'  => $allocationSummary['total_found'],
                'allocated'    => $allocationSummary['allocated'],
                'skipped'      => $allocationSummary['skipped'],
                'failed'       => $allocationSummary['failed'],
                'invalid_students' => $allocationSummary['invalid_students'],
            ]);
    
            return $allocationSummary;
        } catch (\Exception $e) {
            Log::error("Error occurred while allocating students to new houses: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id'   => $toTermId,
                'exception'    => get_class($e),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'trace'        => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function allocateUsersToNewHouses($fromTermId, $toTermId, $oldToNewHouseIds) {
        try {
            $userHouses = DB::table('user_house')
                ->where('term_id', $fromTermId)
                ->get();

            $allocationSummary = [
                'total_found' => $userHouses->count(),
                'allocated' => 0,
                'skipped' => 0,
                'failed' => 0,
                'invalid_users' => 0,
            ];

            if ($userHouses->isEmpty()) {
                Log::info("No user house allocations found for term ID: {$fromTermId}. Proceeding without user allocations.", [
                    'from_term_id' => $fromTermId,
                    'to_term_id' => $toTermId,
                ]);

                return $allocationSummary;
            }

            if (empty($oldToNewHouseIds)) {
                Log::warning("No house mappings available to allocate users to new houses. Proceeding without user allocations.", [
                    'from_term_id' => $fromTermId,
                    'to_term_id' => $toTermId,
                ]);

                return $allocationSummary;
            }

            $validUserIds = DB::table('users')
                ->whereNull('deleted_at')
                ->where('status', 'Current')
                ->pluck('id')
                ->all();

            $existingAllocations = DB::table('user_house')
                ->where('term_id', $toTermId)
                ->pluck('house_id', 'user_id')
                ->all();

            $newAllocations = [];

            foreach ($userHouses as $userHouse) {
                $userId = (int) $userHouse->user_id;
                $oldHouseId = (int) $userHouse->house_id;

                if (!in_array($userId, $validUserIds, true)) {
                    $allocationSummary['invalid_users']++;
                    Log::warning("User ID {$userId} is no longer eligible for house rollover, skipping.");
                    continue;
                }

                if (array_key_exists($userId, $existingAllocations)) {
                    $allocationSummary['skipped']++;
                    Log::info("User ID {$userId} is already allocated to a house in term ID {$toTermId}, skipping.");
                    continue;
                }

                $newHouseId = $oldToNewHouseIds[$oldHouseId] ?? null;
                if (!$newHouseId) {
                    $allocationSummary['failed']++;
                    Log::warning("No new house mapping found for old house ID {$oldHouseId}, skipping user ID {$userId}.");
                    continue;
                }

                $newAllocations[] = [
                    'user_id' => $userId,
                    'house_id' => $newHouseId,
                    'term_id' => $toTermId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $existingAllocations[$userId] = $newHouseId;
                $allocationSummary['allocated']++;
            }

            if ($newAllocations !== []) {
                DB::table('user_house')->insert($newAllocations);
                Log::info('Inserted rolled over user house allocations.', [
                    'count' => count($newAllocations),
                    'to_term_id' => $toTermId,
                ]);
            }

            return $allocationSummary;
        } catch (\Exception $e) {
            Log::error("Error occurred while allocating users to new houses: " . $e->getMessage(), [
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
    
    
    public function createTestsForSubjects($level, $term) {
        $subjects = GradeSubject::whereHas('subject', function($query) use ($level) {
            $query->where('level', $level)
                  ->where('components', 0);
        })->where('term_id', $term->id)->get();
    
        $startDate = $term->start_date;
        $endDate = $term->end_date;
        $caCount = 0;
    
        while ($startDate->lessThanOrEqualTo($endDate) && $caCount < 3) {
            $testDate = $startDate->copy()->endOfMonth();
            if ($testDate->greaterThan($endDate)) {
                break;
            }
    
            $monthName = $testDate->format('F');
            $abbrev = $testDate->format('M');
            $caCount++;
    
            foreach ($subjects as $subject) {
                TermHelper::createTest(
                    $term,
                    $subject,
                    "{$monthName}",
                    $abbrev,
                    'CA',
                    $caCount,
                    100,
                    $testDate
                );
            }
            $startDate->addMonth();
        }
    
        $examDate = $endDate->copy();
        $examMonthName = $examDate->format('F');
    
        foreach ($subjects as $subject) {
            TermHelper::createTest(
                $term,
                $subject,
                "{$examMonthName}",
                'Exam',
                'Exam',
                1,
                100,
                $examDate
            );
        }
    }

    public function previewTermRollover($fromTermId, $toTermId): array {
        $fromTerm = Term::findOrFail($fromTermId);
        $schoolType = SchoolSetup::first()?->type;

        $grades = Grade::where('term_id', $fromTerm->id)->orderBy('sequence')->get();
        $gradeData = [];
        foreach ($grades as $grade) {
            $gradeData[] = [
                'name' => $grade->name,
                'action' => 'copy',
            ];
        }

        $klasses = Klass::where('term_id', $fromTerm->id)->whereNull('deleted_at')->with('grade')->get();
        $classData = [];
        foreach ($klasses as $klass) {
            $studentCount = DB::table('klass_student')
                ->where('klass_id', $klass->id)
                ->where('term_id', $fromTerm->id)
                ->count();

            $classData[] = [
                'name' => $klass->name,
                'grade' => $klass->grade ? $klass->grade->name : 'N/A',
                'studentCount' => $studentCount,
            ];
        }

        $optionalSubjectData = [];
        if ($this->isSecondarySchool($schoolType)) {
            $optionalSubjects = OptionalSubject::with(['gradeSubject.subject', 'gradeSubject.grade'])
                ->where('term_id', $fromTerm->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($optionalSubjects as $os) {
                $studentCount = DB::table('student_optional_subjects')
                    ->where('optional_subject_id', $os->id)
                    ->where('term_id', $fromTerm->id)
                    ->count();

                $optionalSubjectData[] = [
                    'name' => $os->name,
                    'subject' => $os->gradeSubject->subject->name ?? 'N/A',
                    'grade' => $os->gradeSubject->grade->name ?? 'N/A',
                    'studentCount' => $studentCount,
                ];
            }
        }

        $subjectCount = GradeSubject::where('term_id', $fromTerm->id)->count();
        $houseCount = House::where('term_id', $fromTerm->id)->count();
        $gradingScaleCount = GradingScale::where('term_id', $fromTerm->id)->count();
        $gradingMatrixCount = DB::table('overall_grading_matrices')->where('term_id', $fromTerm->id)->count();

        return [
            'grades' => $gradeData,
            'classes' => $classData,
            'optionalSubjects' => $optionalSubjectData,
            'summary' => [
                'grades' => count($gradeData),
                'classes' => count($classData),
                'subjects' => $subjectCount,
                'optionalSubjects' => count($optionalSubjectData),
                'houses' => $houseCount,
                'gradingScales' => $gradingScaleCount,
                'gradingMatrices' => $gradingMatrixCount,
            ],
            'schoolType' => $schoolType,
        ];
    }
}
