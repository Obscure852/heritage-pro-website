<?php

namespace App\Services;

use App\Exceptions\RolloverException;
use App\Models\Grade;
use App\Models\GradeSubject;
use Illuminate\Support\Facades\Log;
use App\Models\Term;
use App\Models\Klass;
use Illuminate\Support\Facades\DB;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Timetable\TimetableSetting;
use App\Helpers\TermHelper;
use App\Models\KlassSubject;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class YearRolloverService{
    protected $rolloverHistoryId;
    protected $results = [];
    protected $autoCreatedGradeSubjects = 0;
    public function yearRollOver($fromTermId, $toTermId) {
        try {
            $fromTerm = Term::findOrFail($fromTermId);
            $toTerm = Term::findOrFail($toTermId);

            $this->results = [
                'grades' => 0,
                'classes' => 0,
                'subjects' => 0,
                'optionalSubjects' => 0,
                'studentAllocations' => 0,
                'houses' => 0,
                'userHouses' => 0,
                'autoCreatedGradeSubjects' => 0,
            ];
            $this->autoCreatedGradeSubjects = 0;

            $this->rolloverHistoryId = DB::table('rollover_histories')->insertGetId([
                'from_term_id' => $fromTermId,
                'to_term_id' => $toTermId,
                'status' => 'in_progress',
                'performed_by' => auth()->id(),
                'rollover_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $schoolMode = SchoolSetup::normalizeType((string) DB::table('school_setup')->value('type')) ?? SchoolSetup::TYPE_JUNIOR;
            $modeResolver = app(SchoolModeResolver::class);
            if (!in_array($schoolMode, SchoolSetup::validTypes(), true)) {
                throw new RolloverException("Invalid school type: {$schoolMode}", 'SchoolTypeError');
            }
    
            DB::beginTransaction();
            try {
                Log::info('The rollover begins');

                $finalsRolloverService = new FinalsModuleRolloverService();
                $finalsRolloverService->rolloverGraduatingStudents($fromTerm, $toTerm);

                $gradeIdMapping = $this->gradesYearRollover($fromTerm, $toTerm);
                $this->storeMappingData('Grades', $gradeIdMapping);
                $this->results['grades'] = count($gradeIdMapping);

                $classIdMapping = $this->rolloverClasses($fromTerm, $toTerm);
                $this->storeMappingData('Classes', $classIdMapping);
                $this->results['classes'] = count($classIdMapping);

                $this->rolloverClassAllocations($classIdMapping, $toTerm, $fromTerm);
                $this->results['studentAllocations'] = DB::table('klass_student')
                    ->where('term_id', $toTerm->id)->where('year', $toTerm->year)->count();

                $gradeSubjectIdMapping = $this->rolloverSubjects($gradeIdMapping, $toTerm, $fromTerm);
                $this->storeMappingData('GradeSubjects', $gradeSubjectIdMapping);
                $this->results['subjects'] = count($gradeSubjectIdMapping);

                $klassSubjectIdMapping = $this->rolloverKlassSubjects($toTerm, $fromTerm, $classIdMapping, $gradeSubjectIdMapping, $gradeIdMapping);
                $this->storeMappingData('KlassSubjects', $klassSubjectIdMapping);
                $this->results['klassSubjects'] = count($klassSubjectIdMapping);

                if ($modeResolver->supportsOptionals(null, $schoolMode)) {
                    $optionalSubjectIdMapping = $this->rolloverOptionalSubjects($gradeIdMapping, $toTerm, $fromTerm);
                    $this->storeMappingData('OptionalSubjects', $optionalSubjectIdMapping);
                    $this->rolloverStudentOptionalSubjects($toTerm, $fromTerm, $optionalSubjectIdMapping, $classIdMapping);
                    $this->results['optionalSubjects'] = count($optionalSubjectIdMapping);
                    $this->rolloverCouplingGroups($gradeIdMapping, $optionalSubjectIdMapping);
                }

                $houseIdMapping = $this->rolloverHouses($toTerm, $fromTerm);
                $this->storeMappingData('Houses', $houseIdMapping);
                $this->results['houses'] = count($houseIdMapping);

                $this->rolloverStudentHouses($toTerm, $fromTerm, $houseIdMapping);
                $this->rolloverUserHouses($toTerm, $fromTerm, $houseIdMapping);
                $this->results['userHouses'] = DB::table('user_house')
                    ->where('term_id', $toTerm->id)
                    ->count();
    
                $this->rolloverGradingScales($toTerm, $fromTerm, $gradeIdMapping, $gradeSubjectIdMapping);
                $this->rolloverOverallGradingMatrices($fromTerm, $toTerm, $gradeIdMapping);
    
                if ($modeResolver->supportsLevel(SchoolSetup::LEVEL_PRE_PRIMARY, $schoolMode)
                    || $modeResolver->supportsLevel(SchoolSetup::LEVEL_PRIMARY, $schoolMode)) {
                    $this->createTestsForSubjects('Primary', $fromTerm, $toTerm);
                }

                if ($modeResolver->supportsLevel(SchoolSetup::LEVEL_JUNIOR, $schoolMode)) {
                    $this->createTestsForSubjects('Junior', $fromTerm, $toTerm);
                }

                if ($modeResolver->supportsLevel(SchoolSetup::LEVEL_SENIOR, $schoolMode)) {
                    $this->createTestsForSubjects('Senior', $fromTerm, $toTerm);
                }
    
                $this->closeTerm($fromTerm);
                DB::table('rollover_histories')->where('id', $this->rolloverHistoryId)->update(['status' => 'completed']);

                Cache::flush();
                Session::put('selected_term_id', $toTerm->id);

                DB::commit();
                Log::info('Year rollover operation completed successfully.');

                $this->results['autoCreatedGradeSubjects'] = $this->autoCreatedGradeSubjects;
                return $this->results;
    
            } catch (\Exception $e) {
                DB::rollBack();
                DB::table('rollover_histories')->where('id', $this->rolloverHistoryId)->update(['status' => 'failed']);
                if ($e instanceof RolloverException) {
                    throw $e;
                }
                throw new RolloverException("Error during year rollover: " . $e->getMessage(), 'YearRolloverError');
            }
    
        } catch (RolloverException $e) {
            Log::error('Rollover Exception: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'context' => $e->getContextData(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during year rollover: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw new RolloverException('An unexpected error occurred during the rollover process.', 'UnexpectedError');
        }
    }
    

    public function validateTerm($termId){
        $term = Term::find($termId);
        if (!$term) {
            throw new \Exception('Term not found.');
        }
        $hasGrades = Grade::where('term_id', $term->id)->exists();
        $hasClasses = Klass::where('term_id', $term->id)->exists();
        $hasSubjects = GradeSubject::where('term_id', $term->id)->exists();
        if (!$hasGrades || !$hasClasses || !$hasSubjects) {
            return false;
        }
        return true;
    }

    protected function storeMappingData($tableName, $mappingArray) {
        $mappingRecords = array_map(function($oldId, $newId) use ($tableName) {
            return [
                'rollover_history_id' => $this->rolloverHistoryId,
                'table_name' => $tableName,
                'old_id' => $oldId,
                'new_id' => $newId,
                'created_at' => now()
            ];
        }, array_keys($mappingArray), array_values($mappingArray));
        DB::table('rollover_mapping_data')->insert($mappingRecords);
    }

    public function closeTerm($currentTerm) {
        $term = Term::where('id', $currentTerm->id)
                    ->where('year', $currentTerm->year)
                    ->first();
        $term->closed = true;
        $term->save();
    }

    protected function gradesYearRollover($fromTerm, $toTerm){
        try {
            $oldToNewGradeIds = [];
            $existingNewGrades = Grade::where('term_id', $toTerm->id)->where('year', $toTerm->year)->get();
            if ($existingNewGrades->isEmpty()) {
                $oldGrades = Grade::where('term_id', $fromTerm->id)
                                    ->where('year', $fromTerm->year)
                                    ->get();
        
                if ($oldGrades->isEmpty()) {
                    throw new RollOverException(
                        "No grades found in the current term. Cannot proceed with grade rollover.",
                        "GradeRollover",
                        ['currentTermId' => $fromTerm->id, 'nextTermId' => $toTerm->id]
                    );
                }
                
                foreach ($oldGrades as $oldGrade) {
                    $oldGrade->active = 0;
                    $oldGrade->save();
        
                    $newGrade = $oldGrade->replicate(['id', 'created_at', 'updated_at']);
                    $newGrade->term_id = $toTerm->id;
                    $newGrade->year = $toTerm->year;
                    $newGrade->active = 1;
                    $newGrade->save();
        
                    $oldToNewGradeIds[$oldGrade->id] = $newGrade->id;
                    Log::info("Grade '{$oldGrade->name}' rolled over successfully. Old ID: {$oldGrade->id}, New ID: {$newGrade->id}", [
                        'oldGradeId' => $oldGrade->id,
                        'newGradeId' => $newGrade->id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                }
                Log::info("Total grades rolled over: " . count($oldToNewGradeIds), [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'rolledOverCount' => count($oldToNewGradeIds)
                ]);
            } else {
                $oldGrades = Grade::where('term_id', $fromTerm->id)
                                    ->where('year', $fromTerm->year)
                                    ->get();
        
                foreach ($existingNewGrades as $newGrade) {
                    $oldGrade = $oldGrades->firstWhere('name', $newGrade->name);
                    if ($oldGrade) {
                        $oldToNewGradeIds[$oldGrade->id] = $newGrade->id;
                        Log::info("Existing grade '{$newGrade->name}' mapped. Old ID: {$oldGrade->id}, New ID: {$newGrade->id}", [
                            'oldGradeId' => $oldGrade->id,
                            'newGradeId' => $newGrade->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                    } else {
                        Log::warning("No corresponding old grade found for existing new grade: {$newGrade->name}", [
                            'newGradeId' => $newGrade->id,
                            'gradeName' => $newGrade->name,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                    }
                }
                Log::info("Total grades mapped: " . count($oldToNewGradeIds), [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'mappedCount' => count($oldToNewGradeIds)
                ]);
            }
        
            $unmappedOldGrades = $oldGrades->pluck('id')->diff(array_keys($oldToNewGradeIds));
            if ($unmappedOldGrades->isNotEmpty()) {
                $unmappedGradeNames = $oldGrades->whereIn('id', $unmappedOldGrades)->pluck('name');
                throw new RolloverException(
                    "Some grades were not rolled over or mapped: " . $unmappedGradeNames->implode(', '),
                    "GradeRollover",
                    [
                        'currentTermId' => $fromTerm->id,
                        'nextTermId' => $toTerm->id,
                        'unmappedGrades' => $unmappedGradeNames->toArray()
                    ]
                );
            }
        
            if (empty($oldToNewGradeIds)) {
                throw new RollOverException(
                    "No grades were rolled over or mapped. This is a critical error.",
                    "GradeRollover",
                    ['currentTermId' => $fromTerm->id, 'nextTermId' => $toTerm->id]
                );
            }
            return $oldToNewGradeIds;
        
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in gradesYearRollover: ' . $e->getMessage(), [
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            throw new RolloverException(
                "An unexpected error occurred during gradesYearRollover: " . $e->getMessage(),
                "GradeRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }

    protected function rolloverClasses($fromTerm, $toTerm){
        try {
            $softDeletedClasses = Klass::onlyTrashed()->where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->get();
            if ($softDeletedClasses->isNotEmpty()) {
                Log::info('Found soft-deleted classes to permanently remove before rollover:', [
                    'count' => $softDeletedClasses->count(),
                    'classIds' => $softDeletedClasses->pluck('id')->toArray(),
                    'classNames' => $softDeletedClasses->pluck('name')->toArray(),
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
                
                $forceDeletedCount = Klass::onlyTrashed()->where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->forceDelete();
                Log::info('Successfully permanently deleted soft-deleted classes from fromTerm', [
                    'deletedCount' => $forceDeletedCount,
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
            } else {
                Log::info('No soft-deleted classes found to clean up in fromTerm', [
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
            }
    
            $classIdMapping = [];
            $newYearGrades = Grade::where('term_id', $toTerm->id)->where('active', 1)->get()->pluck('id', 'name');
    
            Log::info('New year grades:', [
                'grades' => $newYearGrades->toArray(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            if ($newYearGrades->isEmpty()) {
                throw new RollOverException(
                    "No active grades found for the next year. Cannot proceed with class rollover.",
                    "ClassRollover",
                    ['nextTermId' => $toTerm->id, 'nextTermYear' => $toTerm->year]
                );
            }
    
            $klasses = Klass::where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->get();
            Log::info('Classes found for rollover (after cleanup):', [
                'count' => $klasses->count(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            foreach ($klasses as $klass) {
                $currentGrade = Grade::find($klass->grade_id);
                if (!$currentGrade) {
                    throw new RollOverException(
                        "Current grade not found for class: {$klass->name}",
                        "ClassRollover",
                        ['klassId' => $klass->id, 'klassName' => $klass->name, 'gradeId' => $klass->grade_id]
                    );
                }
    
                $klass->active = 0;
                $klass->save();
    
                $currentGradePromotionName = $currentGrade->promotion ?? null;
                Log::info("Processing class:", [
                    'className' => $klass->name,
                    'currentGrade' => $currentGrade->name,
                    'promotionGrade' => $currentGradePromotionName,
                    'klassId' => $klass->id,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
    
                $isPromoting = $currentGradePromotionName && $currentGradePromotionName !== 'Alumni' && isset($newYearGrades[$currentGradePromotionName]);
    
                try {
                    if ($isPromoting) {
                        $promotedKlass = $klass->replicate(['id', 'created_at', 'updated_at']);
                        $promotedKlass->term_id = $toTerm->id;
                        $promotedKlass->name = $this->promoteClassName($klass->name);
                        $promotedKlass->year = $toTerm->year;
                        $promotedKlass->active = 1;
                        $promotedKlass->grade_id = $newYearGrades[$currentGradePromotionName];
    
                        $promotedKlass->save();
                        $classIdMapping[$klass->id] = $promotedKlass->id;
    
                        Log::info("Class promoted successfully", [
                            'oldClass' => $klass->name,
                            'newClass' => $promotedKlass->name,
                            'oldId' => $klass->id,
                            'newId' => $promotedKlass->id,
                            'newGradeName' => $currentGradePromotionName,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
    
                        if (isset($newYearGrades[$currentGrade->name])) {
                            $existingClass = Klass::where('term_id', $toTerm->id)
                                                  ->where('name', $klass->name)
                                                  ->where('grade_id', $newYearGrades[$currentGrade->name])
                                                  ->where('year', $toTerm->year)
                                                  ->first();
                        
                            if (!$existingClass) {
                                $duplicateCheck = DB::table('klasses')
                                    ->where('term_id', $toTerm->id)
                                    ->where('year', $toTerm->year)
                                    ->where('name', $klass->name)
                                    ->where('grade_id', $newYearGrades[$currentGrade->name])
                                    ->lockForUpdate()
                                    ->exists();
                        
                                if (!$duplicateCheck) {
                                    $shellKlass = $klass->replicate(['id', 'created_at', 'updated_at']);
                                    $shellKlass->term_id = $toTerm->id;
                                    $shellKlass->year = $toTerm->year;
                                    $shellKlass->active = 1;
                                    $shellKlass->grade_id = $newYearGrades[$currentGrade->name];
                                    $shellKlass->save();
                        
                                    Log::info("Shell class created for same grade", [
                                        'className' => $shellKlass->name,
                                        'id' => $shellKlass->id,
                                        'gradeName' => $currentGrade->name,
                                        'fromTermId' => $fromTerm->id,
                                        'toTermId' => $toTerm->id
                                    ]);
                                } else {
                                    Log::info("Shell class already exists (duplicate check), skipping creation", [
                                        'className' => $klass->name,
                                        'gradeName' => $currentGrade->name,
                                        'fromTermId' => $fromTerm->id,
                                        'toTermId' => $toTerm->id
                                    ]);
                                }
                            } else {
                                Log::info("Shell class already exists, skipping creation", [
                                    'className' => $existingClass->name,
                                    'id' => $existingClass->id,
                                    'gradeName' => $currentGrade->name,
                                    'fromTermId' => $fromTerm->id,
                                    'toTermId' => $toTerm->id
                                ]);
                            }
                        } else {
                            Log::warning("Grade '{$currentGrade->name}' is not active in the new term. Cannot create shell class.", [
                                'gradeName' => $currentGrade->name,
                                'fromTermId' => $fromTerm->id,
                                'toTermId' => $toTerm->id
                            ]);
                        }
    
                    } else {
                        Log::info("Class '{$klass->name}' will not be promoted (graduating class). No new classes created.", [
                            'className' => $klass->name,
                            'klassId' => $klass->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to rollover class '{$klass->name}': " . $e->getMessage(), [
                        'klassId' => $klass->id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'exception' => get_class($e),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
    
                    throw new RollOverException(
                        "Failed to rollover class '{$klass->name}': " . $e->getMessage(),
                        "ClassRollover",
                        [
                            'klassName' => $klass->name,
                            'klassId' => $klass->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id,
                            'originalException' => get_class($e),
                            'errorMessage' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    );
                }
            }
    
            $rolledOverCount = count($classIdMapping);
            $totalClassCount = $klasses->count();
    
            Log::info("Class rollover completed", [
                'totalClasses' => $totalClassCount,
                'rolledOverClasses' => $rolledOverCount,
                'skippedClasses' => $totalClassCount - $rolledOverCount,
                'classIdMapping' => $classIdMapping,
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            return $classIdMapping;
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in rolloverClasses: ' . $e->getMessage(), [
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverClasses: " . $e->getMessage(),
                "ClassRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }

    protected function promoteClassName(string $className): string{
        return preg_replace_callback('/(\d+)/',
            function (array $matches) {
                $oldLevel = (int)$matches[1];
                $newLevel = $oldLevel + 1;
                return (string) $newLevel; 
            },$className,1 );
    }

    protected function rolloverClassAllocations($oldToNewClassIdMapping, $toTerm, $fromTerm) {
        try {
            if (empty($oldToNewClassIdMapping)) {
                throw new RollOverException(
                    'No class ID mappings found. Cannot proceed with class allocations rollover.',
                    'ClassAllocationRollover',
                    ['currentTermId' => $fromTerm->id, 'nextTermId' => $toTerm->id]
                );
            }
    
            $currentAllocations = DB::table('klass_student')
                                    ->where('term_id', $fromTerm->id)
                                    ->where('year', $fromTerm->year)
                                    ->get();
    
            Log::info('Current allocations found:', [
                'count' => $currentAllocations->count(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            $newAllocations = [];
            $newStudentTerms = [];
            $skippedAllocations = [];
            $graduatingAllocations = [];
    
            foreach ($currentAllocations as $allocation) {
                $newClassId = $oldToNewClassIdMapping[$allocation->klass_id] ?? null;
                if (!$newClassId) {
                    $graduatingAllocations[] = $allocation->student_id;
                    Log::info("Student ID: {$allocation->student_id} from class ID: {$allocation->klass_id} is likely graduating. Skipping allocation.", [
                        'studentId' => $allocation->student_id,
                        'klassId' => $allocation->klass_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $student = Student::find($allocation->student_id);
                    $sponsor = $student->sponsor;
                    if ($sponsor) {
                        $sponsor->update(['status' => 'Past']);
                    }
                    
                    continue;
                }
    
                $newClass = DB::table('klasses')->where('id', $newClassId)->first();
                if (!$newClass) {
                    $skippedAllocations[] = $allocation->student_id;
                    Log::error("New class not found for ID: {$newClassId}. Skipping allocation for student ID: {$allocation->student_id}", [
                        'newClassId' => $newClassId,
                        'studentId' => $allocation->student_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    continue;
                }
    
                $student = DB::table('students')->where('id', $allocation->student_id)->first();
                if (!$student) {
                    $skippedAllocations[] = $allocation->student_id;
                    Log::error("Student not found for ID: {$allocation->student_id}. Skipping allocation.", [
                        'studentId' => $allocation->student_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    continue;
                }
    
                $grade = DB::table('grades')->where('id', $newClass->grade_id)->first();
                if (!$grade) {
                    $skippedAllocations[] = $allocation->student_id;
                    Log::error("Grade not found for ID: {$newClass->grade_id}. Skipping allocation for student ID: {$allocation->student_id}", [
                        'gradeId' => $newClass->grade_id,
                        'studentId' => $allocation->student_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    continue;
                }
    
                $newAllocations[] = [
                    'klass_id' => $newClassId,
                    'student_id' => $allocation->student_id,
                    'active' => 1,
                    'term_id' => $toTerm->id,
                    'year' => $toTerm->year,
                    'grade_id' => $newClass->grade_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $newStudentTerms[] = [
                    'student_id' => $allocation->student_id,
                    'term_id' => $toTerm->id,
                    'year' => $toTerm->year,
                    'grade_id' => $newClass->grade_id,
                    'status' => 'Current',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
    
            DB::transaction(function () use ($newAllocations, $newStudentTerms,$fromTerm,$toTerm) {
                DB::table('klass_student')->insert($newAllocations);
                Log::info('New class allocations inserted', [
                    'count' => count($newAllocations),
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
    
                foreach ($newStudentTerms as $studentTerm) {
                    DB::table('student_term')->updateOrInsert(
                        [
                            'student_id' => $studentTerm['student_id'],
                            'term_id' => $studentTerm['term_id'],
                            'year' => $studentTerm['year'],
                        ],
                        $studentTerm
                    );
                }
                Log::info('Student terms updated/inserted', [
                    'count' => count($newStudentTerms),
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            });
    
            Log::info('Class and student_term allocations rolled over successfully.', [
                'totalAllocations' => count($newAllocations),
                'skippedAllocations' => count($skippedAllocations),
                'graduatingAllocations' => count($graduatingAllocations),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            if (!empty($skippedAllocations)) {
                Log::warning("Skipped allocations for the following student IDs: " . implode(', ', $skippedAllocations), [
                    'skippedStudentIds' => $skippedAllocations,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            }
    
            if (!empty($graduatingAllocations)) {
                Log::info("The following student IDs are graduating: " . implode(', ', $graduatingAllocations), [
                    'graduatingStudentIds' => $graduatingAllocations,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            }
    
            return true;
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in rolloverClassAllocations: ' . $e->getMessage(), [
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverClassAllocations: " . $e->getMessage(),
                "ClassAllocationRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }
    
    protected function rolloverSubjects($oldToNewGradeIds, $toTerm, $fromTerm) {
        try {
            $gradeSubjectIdMapping = [];
            $schoolMode = SchoolSetup::normalizeType((string) DB::table('school_setup')->value('type')) ?? SchoolSetup::TYPE_JUNIOR;
            $oldSubjectAllocations = DB::table('grade_subject')
                                        ->where('term_id', $fromTerm->id)
                                        ->where('year', $fromTerm->year)
                                        ->get();
    
            Log::info('Starting rolloverSubjects', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'schoolType' => $schoolMode,
                'oldSubjectAllocationsCount' => $oldSubjectAllocations->count()
            ]);
    
            foreach ($oldSubjectAllocations as $allocation) {
                try {
                    if (!isset($oldToNewGradeIds[$allocation->grade_id])) {
                        Log::warning("Skipping grade_subject ID {$allocation->id} due to missing new grade ID.", [
                            'oldGradeId' => $allocation->grade_id,
                            'subjectId' => $allocation->subject_id,
                            'allocationId' => $allocation->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                        continue;
                    }
    
                    $newGradeId = $oldToNewGradeIds[$allocation->grade_id];
                    
                    $existingGradeSubject = DB::table('grade_subject')
                        ->where('grade_id', $newGradeId)
                        ->where('subject_id', $allocation->subject_id)
                        ->where('term_id', $toTerm->id)
                        ->where('year', $toTerm->year)
                        ->first();
    
                    if ($existingGradeSubject) {
                        $newGradeSubjectId = $existingGradeSubject->id;
                        Log::info("Existing grade_subject found", [
                            'oldId' => $allocation->id,
                            'newId' => $newGradeSubjectId,
                            'subjectId' => $allocation->subject_id,
                            'oldGradeId' => $allocation->grade_id,
                            'newGradeId' => $newGradeId,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                    } else {
                        $newGradeSubjectId = DB::table('grade_subject')->insertGetId([
                            'grade_id' => $newGradeId,
                            'subject_id' => $allocation->subject_id,
                            'department_id' => $allocation->department_id,
                            'term_id' => $toTerm->id,
                            'year' => $toTerm->year,
                            'type' => $allocation->type,
                            'mandatory' => $allocation->mandatory,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
    
                        $gradeSubjectIdMapping[$allocation->id] = $newGradeSubjectId;
    
                        if (in_array($schoolMode, [SchoolSetup::TYPE_PRIMARY, SchoolSetup::TYPE_PRE_F3, SchoolSetup::TYPE_K12], true)) {
                            $this->rolloverSubjectComponents($allocation, $newGradeSubjectId, $newGradeId, $toTerm);
                        }
    
                        Log::info("New grade_subject created", [
                            'oldId' => $allocation->id,
                            'newId' => $newGradeSubjectId,
                            'subjectId' => $allocation->subject_id,
                            'oldGradeId' => $allocation->grade_id,
                            'newGradeId' => $newGradeId,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                    }
    
                    $gradeSubjectIdMapping[$allocation->id] = $newGradeSubjectId;
                } catch (RollOverException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    Log::error("Unexpected error processing grade_subject ID {$allocation->id}: " . $e->getMessage(), [
                        'allocationId' => $allocation->id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'gradeId' => $allocation->grade_id,
                        'subjectId' => $allocation->subject_id,
                        'exception' => get_class($e),
                        'errorMessage' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
    
                    throw new RollOverException(
                        "An unexpected error occurred while processing grade_subject ID {$allocation->id}: " . $e->getMessage(),
                        "SubjectRollover",
                        [
                            'allocationId' => $allocation->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id,
                            'gradeId' => $allocation->grade_id,
                            'subjectId' => $allocation->subject_id,
                            'originalException' => get_class($e),
                            'errorMessage' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    );
                }
            }
    
            Log::info("Complete grade subject mapping", [
                'mapping' => $gradeSubjectIdMapping,
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            return $gradeSubjectIdMapping;
        } catch (\Exception $e) {
            Log::error("Error in rolloverSubjects: " . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    private function rolloverSubjectComponents($allocation, $newGradeSubjectId, $newGradeId, $toTerm){
        try {
            $gradeSubject = GradeSubject::find($allocation->id);
            
            if ($gradeSubject && $gradeSubject->subject->components) {
                foreach ($gradeSubject->components as $component) {
                    try {
                        $newComponent = $component->replicate(['id', 'created_at', 'updated_at']);
                        $newComponent->term_id = $toTerm->id;
                        $newComponent->grade_subject_id = $newGradeSubjectId;
                        $newComponent->grade_id = $newGradeId;
                        $newComponent->save();
    
                        Log::info("Subject component rolled over successfully", [
                            'oldComponentId' => $component->id,
                            'newComponentId' => $newComponent->id,
                            'gradeSubjectId' => $newGradeSubjectId,
                            'toTermId' => $toTerm->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to rollover subject component ID {$component->id}: " . $e->getMessage(), [
                            'componentId' => $component->id,
                            'gradeSubjectId' => $newGradeSubjectId,
                            'toTermId' => $toTerm->id,
                            'errorMessage' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
    
                        throw new RollOverException(
                            "Failed to rollover subject component ID {$component->id}: " . $e->getMessage(),
                            "SubjectComponentRollover",
                            [
                                'componentId' => $component->id,
                                'gradeSubjectId' => $newGradeSubjectId,
                                'toTermId' => $toTerm->id,
                                'originalException' => get_class($e),
                                'errorMessage' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]
                        );
                    }
                }
            } else {
                Log::warning("GradeSubject ID {$allocation->id} or its components are missing.", [
                    'allocationId' => $allocation->id,
                    'toTermId' => $toTerm->id
                ]);
            }
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverSubjectComponents: " . $e->getMessage(), [
                'allocationId' => $allocation->id,
                'newGradeSubjectId' => $newGradeSubjectId,
                'newGradeId' => $newGradeId,
                'toTermId' => $toTerm->id,
                'exception' => get_class($e),
                'errorMessage' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverSubjectComponents: " . $e->getMessage(),
                "SubjectComponentRolloverUnexpectedError",
                [
                    'allocationId' => $allocation->id,
                    'newGradeSubjectId' => $newGradeSubjectId,
                    'newGradeId' => $newGradeId,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
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

    protected function rolloverOptionalSubjects($oldToNewGradeIds, $toTerm, $fromTerm) {
        try {
            // First, clean up any soft-deleted optional subjects from the fromTerm before rollover
            $softDeletedOptionalSubjects = OptionalSubject::onlyTrashed()
                                                          ->where('term_id', $fromTerm->id)
                                                          ->get();
            
            if ($softDeletedOptionalSubjects->isNotEmpty()) {
                Log::info('Found soft-deleted optional subjects to permanently remove before rollover:', [
                    'count' => $softDeletedOptionalSubjects->count(),
                    'optionalSubjectIds' => $softDeletedOptionalSubjects->pluck('id')->toArray(),
                    'optionalSubjectNames' => $softDeletedOptionalSubjects->pluck('name')->toArray(),
                    'fromTermId' => $fromTerm->id
                ]);
                
                // Force delete (permanently remove) the soft-deleted optional subjects
                $forceDeletedCount = OptionalSubject::onlyTrashed()
                                                   ->where('term_id', $fromTerm->id)
                                                   ->forceDelete();
                     
                Log::info('Successfully permanently deleted soft-deleted optional subjects from fromTerm', [
                    'deletedCount' => $forceDeletedCount,
                    'fromTermId' => $fromTerm->id
                ]);
            } else {
                Log::info('No soft-deleted optional subjects found to clean up in fromTerm', [
                    'fromTermId' => $fromTerm->id
                ]);
            }
    
            // Now proceed with the regular rollover logic
            $optionalSubjectIdMapping = [];
            $oldOptionalSubjects = OptionalSubject::with(['gradeSubject.grade', 'gradeSubject.subject'])
                                                 ->where('term_id', $fromTerm->id)
                                                 ->get();
    
            $entryGrades = ['F1', 'F4'];
            Log::info('Starting rolloverOptionalSubjects (Smart Shell Creation - after cleanup)', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldOptionalSubjectsCount' => $oldOptionalSubjects->count(),
                'entryGrades' => $entryGrades
            ]);
    
            foreach ($oldOptionalSubjects as $oldOptionalSubject) {
                try {
                    $currentGrade = $oldOptionalSubject->gradeSubject->grade;
    
                    if ($currentGrade->promotion === 'Alumni') {
                        Log::info("Skipping optional subject for graduating grade", [
                            'optionalSubjectId' => $oldOptionalSubject->id,
                            'name' => $oldOptionalSubject->name,
                            'grade' => $currentGrade->name,
                            'promotion' => $currentGrade->promotion,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                        continue;
                    }
    
                    $this->createPromotedOptionalSubject(
                        $oldOptionalSubject, 
                        $currentGrade, 
                        $toTerm, 
                        $optionalSubjectIdMapping
                    );
    
                    $isEntryGrade = in_array($currentGrade->name, $entryGrades);
                    if ($isEntryGrade) {
                        $this->createShellOptionalSubject(
                            $oldOptionalSubject, 
                            $currentGrade, 
                            $toTerm, 
                            $oldToNewGradeIds
                        );
                    }
    
                } catch (\Exception $e) {
                    Log::error("Error processing optional subject ID {$oldOptionalSubject->id}: " . $e->getMessage(), [
                        'optionalSubjectId' => $oldOptionalSubject->id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'errorMessage' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
    
                    throw new RollOverException(
                        "An unexpected error occurred while processing optional subject ID {$oldOptionalSubject->id}: " . $e->getMessage(),
                        "OptionalSubjectRollover",
                        [
                            'optionalSubjectId' => $oldOptionalSubject->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id,
                            'originalException' => get_class($e),
                            'errorMessage' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    );
                }
            }
    
            Log::info("Complete optional subject mapping (Fixed Shell Creation)", [
                'mapping' => $optionalSubjectIdMapping,
                'totalMapped' => count($optionalSubjectIdMapping),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            return $optionalSubjectIdMapping;
        } catch (\Exception $e) {
            Log::error("Error in rolloverOptionalSubjects: " . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function createPromotedOptionalSubject($oldOptionalSubject, $currentGrade, $toTerm, &$optionalSubjectIdMapping) {
        $promotedGrade = Grade::where('name', $currentGrade->promotion)
            ->where('term_id', $toTerm->id)
            ->first();
    
        if (!$promotedGrade) {
            Log::warning("No promoted grade found", [
                'currentGrade' => $currentGrade->name,
                'promotionTarget' => $currentGrade->promotion,
                'optionalSubjectId' => $oldOptionalSubject->id,
                'toTermId' => $toTerm->id
            ]);
            return;
        }

        $promotedGradeSubjectId = GradeSubject::where('grade_id', $promotedGrade->id)
            ->where('subject_id', $oldOptionalSubject->gradeSubject->subject_id)
            ->where('term_id', $toTerm->id)
            ->value('id');

        if (!$promotedGradeSubjectId) {
            $oldGs = $oldOptionalSubject->gradeSubject;
            $promotedGradeSubjectId = DB::table('grade_subject')->insertGetId([
                'grade_id' => $promotedGrade->id,
                'subject_id' => $oldGs->subject_id,
                'department_id' => $oldGs->department_id,
                'term_id' => $toTerm->id,
                'year' => $toTerm->year,
                'type' => $oldGs->type,
                'mandatory' => $oldGs->mandatory,
                'sequence' => $oldGs->sequence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->autoCreatedGradeSubjects++;

            Log::info("Auto-created missing grade_subject for promoted optional subject", [
                'newGradeSubjectId' => $promotedGradeSubjectId,
                'optionalSubjectId' => $oldOptionalSubject->id,
                'promotedGradeId' => $promotedGrade->id,
                'subjectId' => $oldGs->subject_id,
                'toTermId' => $toTerm->id
            ]);
        }
    
        $promotedOptionalSubjectName = $this->promoteClassName($oldOptionalSubject->name);
        $existingPromotedOptionalSubject = OptionalSubject::where('grade_subject_id', $promotedGradeSubjectId)
            ->where('name', $promotedOptionalSubjectName)
            ->where('term_id', $toTerm->id)
            ->first();
    
        if (!$existingPromotedOptionalSubject) {
            $promotedOptionalSubject = new OptionalSubject();
            $promotedOptionalSubject->grade_subject_id = $promotedGradeSubjectId;
            $promotedOptionalSubject->name = $promotedOptionalSubjectName;
            $promotedOptionalSubject->term_id = $toTerm->id;
            $promotedOptionalSubject->user_id = $oldOptionalSubject->user_id;
            $promotedOptionalSubject->venue_id = $oldOptionalSubject->venue_id;
            $promotedOptionalSubject->grouping = $oldOptionalSubject->grouping;
            $promotedOptionalSubject->grade_id = $promotedGrade->id;
            $promotedOptionalSubject->active = 1;
            $promotedOptionalSubject->save();
    
            $optionalSubjectIdMapping[$oldOptionalSubject->id] = $promotedOptionalSubject->id;
    
            Log::info("Promoted optional subject created", [
                'oldId' => $oldOptionalSubject->id,
                'newId' => $promotedOptionalSubject->id,
                'oldName' => $oldOptionalSubject->name,
                'newName' => $promotedOptionalSubject->name,
                'oldGrade' => $currentGrade->name,
                'newGrade' => $promotedGrade->name,
                'toTermId' => $toTerm->id
            ]);
        } else {
            $optionalSubjectIdMapping[$oldOptionalSubject->id] = $existingPromotedOptionalSubject->id;
            Log::info("Promoted optional subject already exists, mapping to existing", [
                'oldId' => $oldOptionalSubject->id,
                'existingId' => $existingPromotedOptionalSubject->id,
                'name' => $existingPromotedOptionalSubject->name,
                'grade' => $promotedGrade->name,
                'toTermId' => $toTerm->id
            ]);
        }
    }


    private function createShellOptionalSubject($oldOptionalSubject, $currentGrade, $toTerm, $oldToNewGradeIds) {
        $newGradeId = $oldToNewGradeIds[$currentGrade->id] ?? null;
        $shellGradeSubjectId = GradeSubject::where('grade_id', $newGradeId)
            ->where('subject_id', $oldOptionalSubject->gradeSubject->subject_id)
            ->where('term_id', $toTerm->id)
            ->value('id');
    
        if (!$shellGradeSubjectId) {
            $oldGs = $oldOptionalSubject->gradeSubject;
            $shellGradeSubjectId = DB::table('grade_subject')->insertGetId([
                'grade_id' => $newGradeId,
                'subject_id' => $oldGs->subject_id,
                'department_id' => $oldGs->department_id,
                'term_id' => $toTerm->id,
                'year' => $toTerm->year,
                'type' => $oldGs->type,
                'mandatory' => $oldGs->mandatory,
                'sequence' => $oldGs->sequence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->autoCreatedGradeSubjects++;

            Log::info("Auto-created missing grade_subject for shell optional subject", [
                'newGradeSubjectId' => $shellGradeSubjectId,
                'optionalSubjectId' => $oldOptionalSubject->id,
                'newGradeId' => $newGradeId,
                'subjectId' => $oldGs->subject_id,
                'toTermId' => $toTerm->id
            ]);
        }
    
        $existingShellOptionalSubject = OptionalSubject::where('grade_subject_id', $shellGradeSubjectId)
            ->where('name', $oldOptionalSubject->name)
            ->where('term_id', $toTerm->id)
            ->where('grade_id', $newGradeId)
            ->first();
    
        if (!$existingShellOptionalSubject) {
            $duplicateCheck = DB::table('optional_subjects')
                ->where('grade_subject_id', $shellGradeSubjectId)
                ->where('name', $oldOptionalSubject->name)
                ->where('term_id', $toTerm->id)
                ->where('grade_id', $newGradeId)
                ->lockForUpdate()
                ->exists();
    
            if (!$duplicateCheck) {
                $shellOptionalSubject = new OptionalSubject();
                $shellOptionalSubject->grade_subject_id = $shellGradeSubjectId;
                $shellOptionalSubject->name = $oldOptionalSubject->name;
                $shellOptionalSubject->term_id = $toTerm->id;
                $shellOptionalSubject->user_id = $oldOptionalSubject->user_id;
                $shellOptionalSubject->venue_id = $oldOptionalSubject->venue_id;
                $shellOptionalSubject->grouping = $oldOptionalSubject->grouping;
                $shellOptionalSubject->grade_id = $newGradeId;
                $shellOptionalSubject->active = 1;
                $shellOptionalSubject->save();
    
                Log::info("Shell optional subject created for entry grade", [
                    'oldId' => $oldOptionalSubject->id,
                    'newId' => $shellOptionalSubject->id,
                    'name' => $shellOptionalSubject->name,
                    'gradeId' => $newGradeId,
                    'gradeName' => $currentGrade->name,
                    'promotesTo' => $currentGrade->promotion,
                    'toTermId' => $toTerm->id
                ]);
            }
        } else {
            Log::info("Shell optional subject already exists for entry grade", [
                'existingOptionalSubjectId' => $existingShellOptionalSubject->id,
                'name' => $existingShellOptionalSubject->name,
                'gradeId' => $newGradeId,
                'toTermId' => $toTerm->id
            ]);
        }
    }

    protected function rolloverStudentOptionalSubjects($toTerm, $fromTerm, $optionalSubjectIdMapping, $klassIdMapping){
        try {
            $newAllocations = [];
            $skippedAllocations = [];
    
            Log::info('Starting rolloverStudentOptionalSubjects', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'optionalSubjectMappingsCount' => count($optionalSubjectIdMapping),
                'klassIdMappingsCount' => count($klassIdMapping)
            ]);
    
            DB::table('student_optional_subjects')
                ->where('term_id', $fromTerm->id)
                ->orderBy('id')
                ->chunk(1000, function ($studentOptionalSubjects) use (&$newAllocations, &$skippedAllocations, $optionalSubjectIdMapping, $klassIdMapping, $toTerm, $fromTerm) {
                    foreach ($studentOptionalSubjects as $allocation) {
                        $newOptionalSubjectId = $optionalSubjectIdMapping[$allocation->optional_subject_id] ?? null;
                        $newKlassId = $klassIdMapping[$allocation->klass_id] ?? null;
    
                        if (!$newOptionalSubjectId || !$newKlassId) {
                            $skippedAllocations[] = [
                                'student_id' => $allocation->student_id,
                                'old_optional_subject_id' => $allocation->optional_subject_id,
                                'old_klass_id' => $allocation->klass_id,
                                'reason' => !$newOptionalSubjectId ? 'Missing new optional subject mapping' : 'Missing new class mapping'
                            ];
                            Log::warning("Skipping student_optional_subject ID {$allocation->id} due to missing mappings.", [
                                'student_id' => $allocation->student_id,
                                'old_optional_subject_id' => $allocation->optional_subject_id,
                                'old_klass_id' => $allocation->klass_id,
                                'reason' => !$newOptionalSubjectId ? 'Missing new optional subject mapping' : 'Missing new class mapping',
                                'fromTermId' => $fromTerm->id,
                                'toTermId' => $toTerm->id
                            ]);
                            continue;
                        }
    
                        $studentEnrolledInNewClass = DB::table('klass_student')
                            ->where('student_id', $allocation->student_id)
                            ->where('klass_id', $newKlassId)
                            ->where('term_id', $toTerm->id)
                            ->exists();
    
                        if (!$studentEnrolledInNewClass) {
                            $skippedAllocations[] = [
                                'student_id' => $allocation->student_id,
                                'old_optional_subject_id' => $allocation->optional_subject_id,
                                'old_klass_id' => $allocation->klass_id,
                                'reason' => 'Student not enrolled in new class in new term'
                            ];
                            Log::warning("Skipping student_optional_subject ID {$allocation->id} as the student is not enrolled in the new class.", [
                                'student_id' => $allocation->student_id,
                                'optional_subject_id' => $allocation->optional_subject_id,
                                'klass_id' => $allocation->klass_id,
                                'reason' => 'Student not enrolled in new class in new term',
                                'fromTermId' => $fromTerm->id,
                                'toTermId' => $toTerm->id
                            ]);
                            continue;
                        }
    
                        $allocationExists = DB::table('student_optional_subjects')
                            ->where('student_id', $allocation->student_id)
                            ->where('optional_subject_id', $newOptionalSubjectId)
                            ->where('term_id', $toTerm->id)
                            ->exists();
    
                        if ($allocationExists) {
                            $skippedAllocations[] = [
                                'student_id' => $allocation->student_id,
                                'optional_subject_id' => $newOptionalSubjectId,
                                'reason' => 'Allocation already exists in new term'
                            ];
                            Log::warning("Skipping student_optional_subject ID {$allocation->id} as the allocation already exists in the new term.", [
                                'student_id' => $allocation->student_id,
                                'optional_subject_id' => $newOptionalSubjectId,
                                'reason' => 'Allocation already exists in new term',
                                'fromTermId' => $fromTerm->id,
                                'toTermId' => $toTerm->id
                            ]);
                            continue;
                        }

                        $newAllocations[] = [
                            'student_id' => (int) $allocation->student_id,
                            'optional_subject_id' => (int) $newOptionalSubjectId,
                            'term_id' => (int) $toTerm->id,
                            'klass_id' => (int) $newKlassId,
                            'created_at' => Carbon::now('UTC'),
                            'updated_at' => Carbon::now('UTC'),
                        ];
                    }
    
                    if (!empty($newAllocations)) {
                        try {
                            DB::table('student_optional_subjects')->insert($newAllocations);
                            Log::info('Inserted new student_optional_subject allocations.', [
                                'totalAllocations' => count($newAllocations),
                                'toTermId' => $toTerm->id,
                                'insertedAt' => Carbon::now('UTC')
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to insert new student_optional_subject allocations.', [
                                'errorMessage' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'allocationsCount' => count($newAllocations),
                                'toTermId' => $toTerm->id,
                                'fromTermId' => $fromTerm->id
                            ]);

                            throw new RollOverException(
                                'Failed to insert new student_optional_subject allocations.',
                                'StudentOptionalSubjectRollover',
                                [
                                    'error' => $e->getMessage(),
                                    'allocationsCount' => count($newAllocations),
                                    'fromTermId' => $fromTerm->id,
                                    'toTermId' => $toTerm->id
                                ]
                            );
                        }
                        $newAllocations = [];
                    }
                });
    
            if (!empty($skippedAllocations)) {
                Log::warning('Some student_optional_subject allocations were skipped during rollover.', [
                    'skippedCount' => count($skippedAllocations),
                    'skippedAllocations' => $skippedAllocations,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            }
    
            Log::info("Completed rolloverStudentOptionalSubjects.", [
                'optionalSubjectIdMappingCount' => count($optionalSubjectIdMapping),
                'klassIdMappingCount' => count($klassIdMapping),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            return $optionalSubjectIdMapping;
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverStudentOptionalSubjects: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverStudentOptionalSubjects: " . $e->getMessage(),
                "StudentOptionalSubjectRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }

    protected function rolloverKlassSubjects($toTerm, $fromTerm, $klassIdMapping, $gradeSubjectIdMapping, $gradeIdMapping){
        try {
            $softDeletedKlassSubjects = KlassSubject::onlyTrashed()
                                                  ->where('term_id', $fromTerm->id)
                                                  ->where('year', $fromTerm->year)
                                                  ->get();
            
            if ($softDeletedKlassSubjects->isNotEmpty()) {
                Log::info('Found soft-deleted klass subjects to permanently remove before rollover:', [
                    'count' => $softDeletedKlassSubjects->count(),
                    'klassSubjectIds' => $softDeletedKlassSubjects->pluck('id')->toArray(),
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
                
                $forceDeletedCount = KlassSubject::onlyTrashed()
                                               ->where('term_id', $fromTerm->id)
                                               ->where('year', $fromTerm->year)
                                               ->forceDelete();
                     
                Log::info('Successfully permanently deleted soft-deleted klass subjects from fromTerm', [
                    'deletedCount' => $forceDeletedCount,
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
            } else {
                Log::info('No soft-deleted klass subjects found to clean up in fromTerm', [
                    'fromTermId' => $fromTerm->id,
                    'fromTermYear' => $fromTerm->year
                ]);
            }
    
            $newKlassSubjectMappings = [];
            $skippedKlassSubjects = [];
            $oldKlassSubjects = DB::table('klass_subject')
                                  ->where('term_id', $fromTerm->id)
                                  ->where('year', $fromTerm->year)
                                  ->get();
    
            Log::info('Starting rolloverKlassSubjects (after cleanup)', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldKlassSubjectsCount' => $oldKlassSubjects->count(),
                'klassIdMappingCount' => count($klassIdMapping),
                'gradeSubjectIdMappingCount' => count($gradeSubjectIdMapping),
                'gradeIdMappingCount' => count($gradeIdMapping)
            ]);
    
            Log::info('Review gradeSubjectIdMapping', ['gradeSubjectIdMapping' => $gradeSubjectIdMapping]);
    
            $oldGrades = Grade::where('term_id', $fromTerm->id)->get()->keyBy('id');
            $newGrades = Grade::where('term_id', $toTerm->id)->get()->keyBy('name');
    
            foreach ($oldKlassSubjects as $klassSubject) {
                $newKlassId = $klassIdMapping[$klassSubject->klass_id] ?? null;
                $oldGradeSubjectId = $klassSubject->grade_subject_id;
                $oldGrade = $oldGrades[$klassSubject->grade_id] ?? null;
    
                if (!$oldGrade) {
                    Log::warning("Old grade not found for klass_subject", [
                        'klass_subject_id' => $klassSubject->id,
                        'grade_id' => $klassSubject->grade_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    $skippedKlassSubjects[] = $klassSubject->id;
                    continue;
                }
    
                $isPromoting = ($oldGrade->promotion !== 'Alumni');
    
                if ($isPromoting) {
                    $newGrade = $newGrades[$oldGrade->promotion] ?? null;
                    if (!$newGrade) {
                        Log::warning("New grade not found for promotion", [
                            'old_grade_id' => $oldGrade->id,
                            'promotion' => $oldGrade->promotion,
                            'klass_subject_id' => $klassSubject->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                        $skippedKlassSubjects[] = $klassSubject->id;
                        continue;
                    }
                } else {
                    $newGrade = null;
                }
    
                $oldGradeSubject = DB::table('grade_subject')->find($oldGradeSubjectId);
                if (!$oldGradeSubject) {
                    Log::warning("Old grade_subject not found", [
                        'grade_subject_id' => $oldGradeSubjectId,
                        'klass_subject_id' => $klassSubject->id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    $skippedKlassSubjects[] = $klassSubject->id;
                    continue;
                }
    
                if ($isPromoting) {
                    $newGradeSubject = DB::table('grade_subject')
                        ->where('subject_id', $oldGradeSubject->subject_id)
                        ->where('grade_id', $newGrade->id)
                        ->where('term_id', $toTerm->id)
                        ->first();
    
                    if (!$newGradeSubject) {
                        Log::warning("New grade_subject not found during promotion", [
                            'subject_id' => $oldGradeSubject->subject_id,
                            'grade_id' => $newGrade->id,
                            'term_id' => $toTerm->id,
                            'klass_subject_id' => $klassSubject->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                        $skippedKlassSubjects[] = $klassSubject->id;
                        continue;
                    }
    
                    $newGradeSubjectId = $newGradeSubject->id;
                    $newGradeId = $newGrade->id;
                } else {
                    $newGradeSubjectId = $gradeSubjectIdMapping[$oldGradeSubjectId] ?? null;
                    $newGradeId = $gradeIdMapping[$klassSubject->grade_id] ?? null;
    
                    if (!$newGradeSubjectId || !$newGradeId) {
                        Log::warning("Missing mapping for grade_subject or grade", [
                            'oldGradeSubjectId' => $oldGradeSubjectId,
                            'newGradeSubjectId' => $newGradeSubjectId,
                            'oldGradeId' => $klassSubject->grade_id,
                            'newGradeId' => $newGradeId,
                            'klass_subject_id' => $klassSubject->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id
                        ]);
                        $skippedKlassSubjects[] = $klassSubject->id;
                        continue;
                    }
                }
    
                Log::info("Processing klass_subject rollover", [
                    'oldKlassSubjectId' => $klassSubject->id,
                    'oldKlassId' => $klassSubject->klass_id,
                    'newKlassId' => $newKlassId,
                    'oldGradeSubjectId' => $oldGradeSubjectId,
                    'newGradeSubjectId' => $newGradeSubjectId,
                    'oldGradeId' => $klassSubject->grade_id,
                    'newGradeId' => $newGradeId,
                    'isPromoting' => $isPromoting,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
    
                if (!$newKlassId || !$newGradeSubjectId || !$newGradeId) {
                    Log::warning("Missing mapping for klass_subject rollover", [
                        'oldKlassSubjectId' => $klassSubject->id,
                        'oldKlassId' => $klassSubject->klass_id,
                        'oldGradeSubjectId' => $oldGradeSubjectId,
                        'oldGradeId' => $klassSubject->grade_id,
                        'newKlassId' => $newKlassId,
                        'newGradeSubjectId' => $newGradeSubjectId,
                        'newGradeId' => $newGradeId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                    $skippedKlassSubjects[] = $klassSubject->id;
                    continue;
                }
    
                try {
                    $newKlassSubjectId = DB::table('klass_subject')->insertGetId([
                        'klass_id' => $newKlassId,
                        'grade_subject_id' => $newGradeSubjectId,
                        'user_id' => $klassSubject->user_id,
                        'term_id' => $toTerm->id,
                        'grade_id' => $newGradeId,
                        'venue_id' => $klassSubject->venue_id,
                        'year' => $toTerm->year,
                        'active' => 1,
                        'created_at' => Carbon::now('UTC'),
                        'updated_at' => Carbon::now('UTC'),
                    ]);
    
                    $newKlassSubjectMappings[$klassSubject->id] = $newKlassSubjectId;
    
                    Log::info("New klass_subject created", [
                        'oldId' => $klassSubject->id,
                        'newId' => $newKlassSubjectId,
                        'klassId' => $newKlassId,
                        'gradeSubjectId' => $newGradeSubjectId,
                        'gradeId' => $newGradeId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    if ($isPromoting) {
                        $lowestGrade = $newGrades->sortBy('sequence')->first();
    
                        if ($lowestGrade) {
                            $lowestKlass = Klass::where('term_id', $toTerm->id)
                                                ->where('grade_id', $lowestGrade->id)
                                                ->first();
    
                            if ($lowestKlass) {
                                $newLowestKlassId = $lowestKlass->id;
                                $newLowestGradeSubject = DB::table('grade_subject')
                                    ->where('subject_id', $oldGradeSubject->subject_id)
                                    ->where('grade_id', $lowestGrade->id)
                                    ->where('term_id', $toTerm->id)
                                    ->first();
    
                                if ($newLowestGradeSubject) {
                                    $newLowestKlassSubjectId = DB::table('klass_subject')->insertGetId([
                                        'klass_id' => $newLowestKlassId,
                                        'grade_subject_id' => $newLowestGradeSubject->id,
                                        'user_id' => $klassSubject->user_id,
                                        'term_id' => $toTerm->id,
                                        'grade_id' => $lowestGrade->id,
                                        'venue_id' => $klassSubject->venue_id,
                                        'year' => $toTerm->year,
                                        'active' => 1,
                                        'created_at' => Carbon::now('UTC'),
                                        'updated_at' => Carbon::now('UTC'),
                                    ]);
    
                                    Log::info("New lowest grade klass_subject created", [
                                        'oldId' => $klassSubject->id,
                                        'newId' => $newLowestKlassSubjectId,
                                        'klassId' => $newLowestKlassId,
                                        'gradeSubjectId' => $newLowestGradeSubject->id,
                                        'gradeId' => $lowestGrade->id,
                                        'fromTermId' => $fromTerm->id,
                                        'toTermId' => $toTerm->id
                                    ]);
                                } else {
                                    Log::warning("No grade_subject found for lowest grade during promotion", [
                                        'subject_id' => $oldGradeSubject->subject_id,
                                        'grade_id' => $lowestGrade->id,
                                        'term_id' => $toTerm->id,
                                        'klass_subject_id' => $klassSubject->id,
                                        'fromTermId' => $fromTerm->id,
                                        'toTermId' => $toTerm->id
                                    ]);
                                }
                            } else {
                                Log::warning("No klass found for lowest grade during promotion", [
                                    'lowestGradeId' => $lowestGrade->id,
                                    'term_id' => $toTerm->id,
                                    'klass_subject_id' => $klassSubject->id,
                                    'fromTermId' => $fromTerm->id,
                                    'toTermId' => $toTerm->id
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to create new klass_subject", [
                        'oldId' => $klassSubject->id,
                        'errorMessage' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    throw new RollOverException(
                        "Failed to create new klass_subject ID {$klassSubject->id}: " . $e->getMessage(),
                        "KlassSubjectRollover",
                        [
                            'klassSubjectId' => $klassSubject->id,
                            'fromTermId' => $fromTerm->id,
                            'toTermId' => $toTerm->id,
                            'errorMessage' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    );
                }
            }
    
            if (!empty($skippedKlassSubjects)) {
                Log::warning('Some klass_subject allocations were skipped during rollover.', [
                    'skippedCount' => count($skippedKlassSubjects),
                    'skippedKlassSubjectIds' => $skippedKlassSubjects,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            }
    
            $this->logRolloverResults($newKlassSubjectMappings, $skippedKlassSubjects);
            Log::info("Completed rolloverKlassSubjects.", [
                'newKlassSubjectMappingsCount' => count($newKlassSubjectMappings),
                'skippedKlassSubjectsCount' => count($skippedKlassSubjects),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            return $newKlassSubjectMappings;
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverKlassSubjects: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverKlassSubjects: " . $e->getMessage(),
                "KlassSubjectRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }
    
    private function logRolloverResults($newKlassSubjectMappings, $skippedKlassSubjects) {
        Log::info("Klass subject rollover completed", [
            'totalRolledOver' => count($newKlassSubjectMappings),
            'totalSkipped' => count($skippedKlassSubjects)
        ]);

        if (!empty($skippedKlassSubjects)) {
            Log::warning("Some klass subjects were skipped during rollover", [
                'skippedIds' => $skippedKlassSubjects
            ]);
        }
    }

    protected function rolloverHouses($toTerm, $fromTerm) {
        try {
            $newHouseMappings = [];
            $skippedHouses = [];
    
            $oldHouses = DB::table('houses')
                          ->where('term_id', $fromTerm->id)
                          ->get();
    
            Log::info('Starting rolloverHouses', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldHousesCount' => $oldHouses->count()
            ]);
    
            if ($oldHouses->isEmpty()) {
                Log::info('No houses found for the current term. Continuing with the rollover process.');
                return $newHouseMappings;
            }

            foreach ($oldHouses as $house) {
                try {
                    $newHouseId = DB::table('houses')->insertGetId([
                        'name' => $house->name,
                        'color_code' => $house->color_code,
                        'head' => $house->head,
                        'assistant' => $house->assistant,
                        'term_id' => $toTerm->id,
                        'year' => $toTerm->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $newHouseMappings[$house->id] = $newHouseId;
    
                    Log::info("House '{$house->name}' successfully rolled over.", [
                        'oldHouseId' => $house->id,
                        'newHouseId' => $newHouseId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to rollover house '{$house->name}' (ID: {$house->id}).", [
                        'errorMessage' => $e->getMessage(),
                        'stackTrace' => $e->getTraceAsString(),
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedHouses[] = [
                        'house_id' => $house->id,
                        'house_name' => $house->name,
                        'reason' => 'Database insertion failed',
                        'error' => $e->getMessage()
                    ];
                }
            }
    
            $this->logHouseRolloverResults($newHouseMappings, $skippedHouses);
            return $newHouseMappings;
        } catch (RollOverException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverHouses: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverHouses: " . $e->getMessage(),
                "HouseRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ]
            );
        }
    }
    
    private function logHouseRolloverResults($newHouseMappings, $skippedHouses){
        $totalRolledOver = count($newHouseMappings);
        $totalSkipped = count($skippedHouses);
    
        if ($totalRolledOver > 0) {
            Log::info("Houses successfully rolled over to the new term.", [
                'totalRolledOver' => $totalRolledOver,
                'newHouseMappings' => $newHouseMappings
            ]);
        }
    
        if ($totalSkipped > 0) {
            Log::warning("Some houses were skipped during rollover.", [
                'totalSkipped' => $totalSkipped,
                'skippedHouses' => $skippedHouses
            ]);
        }
    }

    protected function rolloverStudentHouses($toTerm, $fromTerm, $houseIdMapping){
        try {
            $rolledOverCount = 0;
            $skippedAllocations = [];
    
            $oldStudentHouses = DB::table('student_house')->where('term_id', $fromTerm->id)->get();
    
            Log::info('Starting rolloverStudentHouses', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldStudentHousesCount' => $oldStudentHouses->count(),
                'houseIdMappingCount' => count($houseIdMapping)
            ]);
    
            if ($oldStudentHouses->isEmpty()) {
                Log::info('No student house allocations found for the current term. Continuing with the rollover process.');
                return [
                    'rolledOverCount' => $rolledOverCount,
                    'skippedAllocations' => $skippedAllocations
                ];
            }
    
            $students = Student::with(['currentGrade', 'studentTerms' => function ($query) use ($fromTerm) {
                    $query->where('term_id', $fromTerm->id)->where('status', 'Current');
                }])->whereIn('id', $oldStudentHouses->pluck('student_id'))->get()->keyBy('id');
    
            foreach ($oldStudentHouses as $studentHouse) {
                $student = $students->get($studentHouse->student_id);
    
                if (!$student || !$student->currentGrade || $student->currentGrade->promotion === 'Alumni') {
                    Log::info("Skipping student house allocation for graduating or non-existent student.", [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'reason' => $student ? 'Graduating (Alumni grade)' : 'Student not found'
                    ]);
                    $skippedAllocations[] = [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'reason' => $student ? 'Student graduating (Alumni grade)' : 'Student not found'
                    ];
                    continue;
                }
    
                $newHouseId = $houseIdMapping[$studentHouse->house_id] ?? null;
                if (!$newHouseId) {
                    Log::warning("Missing new house mapping for student house allocation.", [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedAllocations[] = [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'reason' => 'Missing new house mapping'
                    ];
                    continue;
                }

                try {
                    DB::table('student_house')->insert([
                        'student_id' => $studentHouse->student_id,
                        'house_id' => $newHouseId,
                        'term_id' => $toTerm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $rolledOverCount++;
                    Log::info("Student house allocation successfully rolled over.", [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'new_house_id' => $newHouseId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to rollover student house allocation for student ID {$studentHouse->student_id}.", [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'new_house_id' => $newHouseId,
                        'errorMessage' => $e->getMessage(),
                        'stackTrace' => $e->getTraceAsString(),
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedAllocations[] = [
                        'student_id' => $studentHouse->student_id,
                        'old_house_id' => $studentHouse->house_id,
                        'reason' => 'Database insertion failed',
                        'error' => $e->getMessage()
                    ];
                }
            }
    
            Log::info('Student house rollover completed', [
                'rolledOverCount' => $rolledOverCount,
                'skippedAllocationsCount' => count($skippedAllocations),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
    
            if (!empty($skippedAllocations)) {
                Log::warning("Skipped allocations for some students", [
                    'skippedAllocationsCount' => count($skippedAllocations),
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
            }
    
            return [
                'rolledOverCount' => $rolledOverCount,
                'skippedAllocations' => $skippedAllocations
            ];
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverStudentHouses: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
            ]);
    
            throw new RollOverException(
                "An unexpected error occurred during rolloverStudentHouses: " . $e->getMessage(),
                "StudentHouseRolloverUnexpectedError",
                [
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id,
                    'originalException' => get_class($e),
                    'errorMessage' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ]
            );
        }
    }

    protected function rolloverUserHouses($toTerm, $fromTerm, $houseIdMapping)
    {
        try {
            $rolledOverCount = 0;
            $skippedAllocations = [];

            $oldUserHouses = DB::table('user_house')
                ->where('term_id', $fromTerm->id)
                ->get();

            Log::info('Starting rolloverUserHouses', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldUserHousesCount' => $oldUserHouses->count(),
                'houseIdMappingCount' => count($houseIdMapping),
            ]);

            if ($oldUserHouses->isEmpty()) {
                Log::info('No user house allocations found for the current term. Continuing with the rollover process.');

                return [
                    'rolledOverCount' => $rolledOverCount,
                    'skippedAllocations' => $skippedAllocations,
                ];
            }

            $users = DB::table('users')
                ->whereIn('id', $oldUserHouses->pluck('user_id'))
                ->where('status', 'Current')
                ->whereNull('deleted_at')
                ->pluck('id')
                ->flip();

            $existingAllocations = DB::table('user_house')
                ->where('term_id', $toTerm->id)
                ->pluck('house_id', 'user_id')
                ->all();

            foreach ($oldUserHouses as $userHouse) {
                $userId = (int) $userHouse->user_id;

                if (!$users->has($userId)) {
                    Log::info('Skipping user house allocation for inactive or missing user.', [
                        'user_id' => $userId,
                        'old_house_id' => $userHouse->house_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                    ]);

                    $skippedAllocations[] = [
                        'user_id' => $userId,
                        'old_house_id' => $userHouse->house_id,
                        'reason' => 'User not current or not found',
                    ];
                    continue;
                }

                if (array_key_exists($userId, $existingAllocations)) {
                    $skippedAllocations[] = [
                        'user_id' => $userId,
                        'old_house_id' => $userHouse->house_id,
                        'reason' => 'User already allocated in destination term',
                    ];
                    continue;
                }

                $newHouseId = $houseIdMapping[$userHouse->house_id] ?? null;
                if (!$newHouseId) {
                    Log::warning('Missing new house mapping for user house allocation.', [
                        'user_id' => $userId,
                        'old_house_id' => $userHouse->house_id,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                    ]);

                    $skippedAllocations[] = [
                        'user_id' => $userId,
                        'old_house_id' => $userHouse->house_id,
                        'reason' => 'Missing new house mapping',
                    ];
                    continue;
                }

                DB::table('user_house')->insert([
                    'user_id' => $userId,
                    'house_id' => $newHouseId,
                    'term_id' => $toTerm->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingAllocations[$userId] = $newHouseId;
                $rolledOverCount++;
            }

            return [
                'rolledOverCount' => $rolledOverCount,
                'skippedAllocations' => $skippedAllocations,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to rollover user house allocations.', [
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id,
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
    

    protected function rolloverGradingScales($toTerm, $fromTerm, $gradeIdMapping, $gradeSubjectIdMapping) {
        try {
            $newGradingScaleMappings = [];
            $skippedScales = [];
    
            $oldGradingScales = DB::table('grading_scales')
                                  ->where('term_id', $fromTerm->id)
                                  ->get();
    
            Log::info('Starting rolloverGradingScales', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'oldGradingScalesCount' => $oldGradingScales->count(),
                'gradeIdMappingCount' => count($gradeIdMapping),
                'gradeSubjectIdMappingCount' => count($gradeSubjectIdMapping)
            ]);
    
            if ($oldGradingScales->isEmpty()) {
                Log::info('No grading scales found for the current term. Continuing with the rollover process.');
                return $newGradingScaleMappings;
            }
    
            foreach ($oldGradingScales as $scale) {
                $newGradeId = $gradeIdMapping[$scale->grade_id] ?? null;
                $newGradeSubjectId = $gradeSubjectIdMapping[$scale->grade_subject_id] ?? null;
    
                if (!$newGradeId || !$newGradeSubjectId) {
                    Log::warning("Missing mapping for grading scale.", [
                        'scale_id' => $scale->id,
                        'new_grade_id' => $newGradeId,
                        'new_grade_subject_id' => $newGradeSubjectId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedScales[] = [
                        'id' => $scale->id,
                        'reason' => !$newGradeId ? 'Missing new grade mapping' : 'Missing new grade subject mapping'
                    ];
                    continue;
                }
    
                $gradeSubject = GradeSubject::find($scale->grade_subject_id);
                if (!$gradeSubject || ($gradeSubject->subject && $gradeSubject->components->count() > 0)) {
                    Log::warning("Invalid grade subject for grading scale.", [
                        'scale_id' => $scale->id,
                        'grade_subject_id' => $scale->grade_subject_id,
                        'reason' => !$gradeSubject ? 'Grade subject not found' : 'Subject has components',
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedScales[] = [
                        'id' => $scale->id,
                        'reason' => !$gradeSubject ? 'Grade subject not found' : 'Subject has components'
                    ];
                    continue;
                }
    
                try {
                    $newScaleId = DB::table('grading_scales')->insertGetId([
                        'grade_subject_id' => $newGradeSubjectId,
                        'term_id' => $toTerm->id,
                        'grade_id' => $newGradeId,
                        'grade' => $scale->grade,
                        'year' => $toTerm->year,
                        'min_score' => $scale->min_score,
                        'max_score' => $scale->max_score,
                        'points' => $scale->points,
                        'description' => $scale->description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $newGradingScaleMappings[$scale->id] = $newScaleId;
    
                    Log::info("Grading scale successfully rolled over.", [
                        'oldScaleId' => $scale->id,
                        'newScaleId' => $newScaleId,
                        'newGradeSubjectId' => $newGradeSubjectId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to rollover grading scale (ID: {$scale->id}).", [
                        'errorMessage' => $e->getMessage(),
                        'stackTrace' => $e->getTraceAsString(),
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'gradeId' => $newGradeId,
                        'gradeSubjectId' => $newGradeSubjectId
                    ]);
    
                    $skippedScales[] = [
                        'id' => $scale->id,
                        'reason' => 'Database insertion failed',
                        'error' => $e->getMessage()
                    ];
                }
            }
    
            $this->logGradingScaleRolloverResults($newGradingScaleMappings, $skippedScales);
            return $newGradingScaleMappings;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverGradingScales: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
            throw $e;
        }
    }
    
    private function logGradingScaleRolloverResults($newGradingScaleMappings, $skippedScales) {
        $rolledOverCount = count($newGradingScaleMappings);
        $skippedCount = count($skippedScales);
    
        if ($rolledOverCount > 0) {
            Log::info("Grading scales successfully rolled over to the new term.", [
                'totalRolledOver' => $rolledOverCount,
                'newGradingScaleMappings' => $newGradingScaleMappings
            ]);
        } else {
            Log::warning("No grading scales were rolled over. This might indicate an issue with the rollover process.");
        }
    
        if ($skippedCount > 0) {
            Log::warning("Some grading scales were skipped during rollover.", [
                'skippedCount' => $skippedCount,
                'skippedScales' => $skippedScales
            ]);
        }
    }

    protected function rolloverOverallGradingMatrices($fromTerm, $toTerm, $oldToNewGradeIds) {
        try {
            $newGradingMatrices = [];
            $skippedMatrices = [];
    
            $currentGradingMatrices = DB::table('overall_grading_matrices')
                                        ->where('term_id', $fromTerm->id)
                                        ->get();
    
            Log::info('Starting rolloverOverallGradingMatrices', [
                'fromTermId' => $fromTerm->id,
                'fromTermYear' => $fromTerm->year,
                'toTermId' => $toTerm->id,
                'toTermYear' => $toTerm->year,
                'currentGradingMatricesCount' => $currentGradingMatrices->count(),
                'oldToNewGradeIdsCount' => count($oldToNewGradeIds)
            ]);
    
            if ($currentGradingMatrices->isEmpty()) {
                Log::info('No overall grading matrices found for the current term. Continuing with the rollover process.');
                return $newGradingMatrices;
            }
    
            foreach ($currentGradingMatrices as $matrix) {
                $newGradeId = $oldToNewGradeIds[$matrix->grade_id] ?? null;
    
                if ($newGradeId === null) {
                    Log::warning("Missing new grade ID for grading matrix.", [
                        'oldGradeId' => $matrix->grade_id,
                        'reason' => "No new grade ID found for grade {$matrix->grade_id}",
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
    
                    $skippedMatrices[] = [
                        'grade_id' => $matrix->grade_id,
                        'reason' => "No new grade ID found for grade {$matrix->grade_id}"
                    ];
                    continue;
                }
    
                try {
                    $newMatrixId = DB::table('overall_grading_matrices')->insertGetId([
                        'term_id' => $toTerm->id,
                        'grade_id' => $newGradeId,
                        'year' => $toTerm->year,
                        'min_score' => $matrix->min_score,
                        'max_score' => $matrix->max_score,
                        'grade' => $matrix->grade,
                        'description' => $matrix->description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $newGradingMatrices[$matrix->id] = $newMatrixId;
    
                    Log::info("Grading matrix successfully rolled over.", [
                        'oldMatrixId' => $matrix->id,
                        'newMatrixId' => $newMatrixId,
                        'gradeId' => $newGradeId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to rollover grading matrix (ID: {$matrix->id}).", [
                        'errorMessage' => $e->getMessage(),
                        'stackTrace' => $e->getTraceAsString(),
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id,
                        'gradeId' => $newGradeId
                    ]);
    
                    $skippedMatrices[] = [
                        'id' => $matrix->id,
                        'reason' => 'Database insertion failed',
                        'error' => $e->getMessage()
                    ];
                }
            }
    
            $this->logOverallGradingMatricesRolloverResults($newGradingMatrices, $skippedMatrices);
            return $newGradingMatrices;
        } catch (\Exception $e) {
            Log::error("Unexpected error in rolloverOverallGradingMatrices: " . $e->getMessage(), [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
            throw $e;
        }
    }
    
    private function logOverallGradingMatricesRolloverResults($newGradingMatrices, $skippedMatrices) {
        $rolledOverCount = count($newGradingMatrices);
        $skippedCount = count($skippedMatrices);
    
        if ($rolledOverCount > 0) {
            Log::info("Overall grading matrices successfully rolled over to the new year.", [
                'totalRolledOver' => $rolledOverCount
            ]);
        } else {
            Log::warning("No overall grading matrices were rolled over. This might indicate an issue with the rollover process.");
        }
    
        if ($skippedCount > 0) {
            Log::warning("Some overall grading matrices were skipped during rollover.", [
                'skippedCount' => $skippedCount,
                'skippedMatrices' => $skippedMatrices
            ]);
        }
    }
    
    
    public function createTestsForSubjects($level, $fromTerm, $toTerm) {
        $createdTests = 0;
        $failedTests = [];
    
        try {
            $subjects = GradeSubject::whereHas('subject', function ($query) use ($level) {
                $query->where('level', $level)
                      ->where('components', 0);
            })->where('term_id', $toTerm->id)->get();
    
            Log::info("Creating tests for subjects at level '{$level}'", [
                'termId' => $toTerm->id,
                'subjectsCount' => $subjects->count()
            ]);
    
            if ($subjects->isEmpty()) {
                Log::warning("No subjects found for level '{$level}' and term ID {$fromTerm->id}. No tests will be created.");
                return;
            }
    
            $startDate = $toTerm->start_date;
            $endDate = $toTerm->end_date;
    
            $numberOfMonths = $startDate->diffInMonths($endDate) + 1;
            $caTestsCount = min(3, $numberOfMonths);
    
            for ($i = 1; $i <= $caTestsCount; $i++) {
                $testDate = $startDate->copy()->addMonths($i - 1)->endOfMonth();
                if ($testDate->greaterThan($endDate)) {
                    $testDate = $endDate->copy();
                }
    
                $monthName = $testDate->format('F');
                $abbrev = $testDate->format('M');
    
                foreach ($subjects as $subject) {
                    try {
                        TermHelper::createTest($toTerm, $subject, "{$monthName}", $abbrev, 'CA', $i, 100, $testDate);
                        $createdTests++;
                        Log::info("Test created successfully", [
                            'subjectId' => $subject->id,
                            'month' => $monthName,
                            'type' => 'CA',
                            'date' => $testDate->toDateString(),
                            'sequence' => $i
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create CA test for subject", [
                            'subjectId' => $subject->id,
                            'month' => $monthName,
                            'error' => $e->getMessage()
                        ]);
    
                        $failedTests[] = [
                            'subject_id' => $subject->id,
                            'month' => $monthName,
                            'type' => 'CA',
                            'sequence' => $i,
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
    
            foreach ($subjects as $subject) {
                try {
                    TermHelper::createTest($toTerm, $subject, "{$endDate->format('F')}", 'Exam', 'Exam', 1, 100, $endDate);
                    $createdTests++;
                    Log::info("Final Exam test created successfully", [
                        'subjectId' => $subject->id,
                        'type' => 'Exam',
                        'date' => $endDate->toDateString()
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create final exam test for subject", [
                        'subjectId' => $subject->id,
                        'type' => 'Final Exam',
                        'error' => $e->getMessage()
                    ]);
                    $failedTests[] = [
                        'subject_id' => $subject->id,
                        'type' => 'Final Exam',
                        'error' => $e->getMessage()
                    ];
                }
            }
    
        } catch (\Exception $e) {
            Log::error('Error occurred while creating tests for subjects', [
                'error' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'level' => $level,
                'termId' => $toTerm->id
            ]);
            throw new RollOverException(
                "Error occurred while creating tests for subjects",
                'TestCreation',
                [
                    'error' => $e->getMessage(),
                    'level' => $level,
                    'termId' => $toTerm->id
                ]
            );
        }
    
        $this->logTestCreationResults($createdTests, $failedTests, $level, $toTerm->id);
        if (!empty($failedTests)) {
            throw new RollOverException(
                "Some tests failed to be created during the rollover process",
                'TestCreation',
                [
                    'failedTests' => $failedTests,
                    'level' => $level,
                    'termId' => $toTerm->id
                ]
            );
        }
    }
    
    private function logTestCreationResults($createdTests, $failedTests, $level, $termId) {
        Log::info("Test creation process completed for level '{$level}' and term ID {$termId}", [
            'createdTests' => $createdTests,
            'failedTests' => count($failedTests)
        ]);

        if (!empty($failedTests)) {
            Log::warning("Some tests failed to be created", [
                'failedTests' => $failedTests
            ]);
        }
    }

    public function previewYearRollover($fromTermId, $toTermId): array {
        $fromTerm = Term::findOrFail($fromTermId);
        $toTerm = Term::findOrFail($toTermId);
        $schoolMode = SchoolSetup::normalizeType((string) DB::table('school_setup')->value('type')) ?? SchoolSetup::TYPE_JUNIOR;
        $modeResolver = app(SchoolModeResolver::class);

        $grades = Grade::where('term_id', $fromTerm->id)
            ->where('year', $fromTerm->year)
            ->orderBy('sequence')
            ->get();

        $gradeData = [];
        foreach ($grades as $grade) {
            $isGraduating = $grade->promotion === 'Alumni';
            $gradeData[] = [
                'name' => $grade->name,
                'promotion' => $grade->promotion,
                'action' => $isGraduating ? 'graduating' : 'promote',
            ];
        }

        $klasses = Klass::where('term_id', $fromTerm->id)
            ->where('year', $fromTerm->year)
            ->with('grade')
            ->get();

        $classData = [];
        foreach ($klasses as $klass) {
            $currentGrade = $klass->grade;
            $isGraduating = $currentGrade && $currentGrade->promotion === 'Alumni';
            $isPromoting = $currentGrade && $currentGrade->promotion && !$isGraduating;

            $studentCount = DB::table('klass_student')
                ->where('klass_id', $klass->id)
                ->where('term_id', $fromTerm->id)
                ->count();

            $action = $isGraduating ? 'graduating' : ($isPromoting ? 'promote' : 'shell');
            $promotedName = $isPromoting ? $this->promoteClassName($klass->name) : null;

            $classData[] = [
                'name' => $klass->name,
                'grade' => $currentGrade ? $currentGrade->name : 'N/A',
                'studentCount' => $studentCount,
                'promotedName' => $promotedName,
                'action' => $action,
            ];
        }

        $optionalSubjectData = [];
        if ($modeResolver->supportsOptionals(null, $schoolMode)) {
            $optionalSubjects = OptionalSubject::with(['gradeSubject.grade', 'gradeSubject.subject'])
                ->where('term_id', $fromTerm->id)
                ->get();

            $newTermGradeSubjects = DB::table('grade_subject')
                ->where('term_id', $toTerm->id)
                ->select('grade_id', 'subject_id')
                ->get();

            $newGradesByName = Grade::where('term_id', $toTerm->id)->pluck('id', 'name');

            foreach ($optionalSubjects as $os) {
                $currentGrade = $os->gradeSubject->grade ?? null;
                if (!$currentGrade) continue;

                $isGraduating = $currentGrade->promotion === 'Alumni';
                if ($isGraduating) continue;

                $promotedGradeName = $currentGrade->promotion;
                $promotedGradeId = $newGradesByName[$promotedGradeName] ?? null;

                $gradeSubjectExists = false;
                if ($promotedGradeId) {
                    $gradeSubjectExists = $newTermGradeSubjects->contains(function ($gs) use ($promotedGradeId, $os) {
                        return $gs->grade_id == $promotedGradeId && $gs->subject_id == $os->gradeSubject->subject_id;
                    });
                }

                $studentCount = DB::table('student_optional_subjects')
                    ->where('optional_subject_id', $os->id)
                    ->where('term_id', $fromTerm->id)
                    ->count();

                $optionalSubjectData[] = [
                    'name' => $os->name,
                    'subject' => $os->gradeSubject->subject->name ?? 'N/A',
                    'grade' => $currentGrade->name,
                    'studentCount' => $studentCount,
                    'promotedName' => $this->promoteClassName($os->name),
                    'gradeSubjectExists' => $gradeSubjectExists,
                ];
            }
        }

        $subjectCount = DB::table('grade_subject')
            ->where('term_id', $fromTerm->id)
            ->where('year', $fromTerm->year)
            ->count();

        $houseCount = DB::table('houses')
            ->where('term_id', $fromTerm->id)
            ->count();

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
            ],
            'schoolType' => $schoolType,
        ];
    }

}
