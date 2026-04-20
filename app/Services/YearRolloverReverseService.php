<?php
namespace App\Services;

use App\Models\Term;
use App\Models\Timetable\TimetableSetting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class YearRolloverReverseService {
    protected $rolloverHistoryId;
    protected $mappingData;
    protected $schoolType;
    protected $results = [];

    public function __construct() {
        $this->schoolType = DB::table('school_setup')->value('type');
    }

    protected function getRolloverHistory(): object
    {
        $rolloverHistory = DB::table('rollover_histories')
            ->where('id', $this->rolloverHistoryId)
            ->first();

        if (!$rolloverHistory) {
            throw new Exception('Rollover history not found.');
        }

        return $rolloverHistory;
    }

    public function reverseRollover($rolloverHistoryId) {
        $this->rolloverHistoryId = $rolloverHistoryId;
        $rolloverHistory = $this->getRolloverHistory();
        
        if (!$rolloverHistory || $rolloverHistory->status !== 'completed') {
            throw new Exception('Invalid rollover history or rollover not completed');
        }

        DB::beginTransaction();
        try {
            $this->results = [
                'tests' => 0, 'gradingMatrices' => 0, 'gradingScales' => 0,
                'studentHouses' => 0, 'userHouses' => 0, 'houses' => 0, 'studentOptionalSubjects' => 0,
                'optionalSubjects' => 0, 'klassSubjects' => 0, 'subjects' => 0,
                'classAllocations' => 0, 'classes' => 0, 'grades' => 0,
            ];

            $this->mappingData = DB::table('rollover_mapping_data')->where('rollover_history_id', $rolloverHistoryId)->get()->groupBy('table_name');

            $this->reverseFinalsModule($rolloverHistory);

            $this->reverseTests();
            $this->reverseGradingMatrices();
            $this->reverseGradingScales();
            $this->reverseStudentHouses();
            $this->reverseHouses();
            $this->reverseStudentOptionalSubjects();
            $this->reverseCouplingGroups();
            $this->reverseOptionalSubjects();
            $this->reverseKlassSubjects();

            if($this->schoolType === 'Primary'){
                $this->reverseSubjectComponents();
                $this->reverseCriteriaBasedTests();
            }

            $this->reverseSubjects();
            $this->reverseBookAllocations();
            $this->reverseBooks();
            $this->reverseCharges();
            $this->reverseFees();
            $this->reverseClassAllocations();
            $this->reverseClasses();
            $this->reverseGrades();

            DB::table('rollover_histories')->where('id', $rolloverHistoryId)->update([
                'status' => 'reversed',
                'reversed_timestamp' => now()
            ]);

            DB::commit();

            return $this->results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function reverseFinalsModule($rolloverHistory) {
        try {
            $finalsDataExists = DB::table('final_students')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $rolloverHistory->to_term_id ? DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year') : null)
                ->exists();

            if (!$finalsDataExists) {
                Log::info('No finals module data found for this rollover', [
                    'rolloverId' => $this->rolloverHistoryId
                ]);
                return;
            }

            Log::info('Starting finals module reversal', [
                'graduationTermId' => $rolloverHistory->from_term_id,
                'rolloverId' => $this->rolloverHistoryId
            ]);

            $this->reverseFinalStudentOptionalSubjects($rolloverHistory);
            $this->reverseFinalStudentKlass($rolloverHistory);
            $this->reverseFinalStudentHouses($rolloverHistory);
            $this->reverseFinalStudents($rolloverHistory);
            $this->reverseFinalOptionalSubjects($rolloverHistory);
            $this->reverseFinalHouses($rolloverHistory);
            $this->reverseFinalKlassSubjects($rolloverHistory);
            $this->reverseFinalKlasses($rolloverHistory);
            $this->reverseFinalGradeSubjects($rolloverHistory);

            Log::info('Completed finals module reversal', [
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse finals module', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalStudentOptionalSubjects($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_student_optional_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_student_optional_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final student optional subjects', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final student optional subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalStudentKlass($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_student_klass')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_student_klass')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final student klass relationships', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final student klass relationships', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalStudents($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_students')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_students')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final students', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final students', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalStudentHouses($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');

            $count = DB::table('final_student_houses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();

            $deletedCount = DB::table('final_student_houses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final student houses', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reverse final student houses', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function reverseFinalOptionalSubjects($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_optional_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_optional_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final optional subjects', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final optional subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalHouses($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');

            $count = DB::table('final_houses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();

            $deletedCount = DB::table('final_houses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final houses', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reverse final houses', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function reverseFinalKlassSubjects($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_klass_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_klass_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final klass subjects', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final klass subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalKlasses($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_klasses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_klasses')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final klasses', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final klasses', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseFinalGradeSubjects($rolloverHistory) {
        try {
            $graduationYear = DB::table('terms')->where('id', $rolloverHistory->to_term_id)->value('year');
            
            $count = DB::table('final_grade_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->count();
                
            $deletedCount = DB::table('final_grade_subjects')
                ->where('graduation_term_id', $rolloverHistory->from_term_id)
                ->where('graduation_year', $graduationYear)
                ->delete();

            Log::info('Reversed final grade subjects', [
                'found' => $count,
                'deleted' => $deletedCount,
                'graduationTermId' => $rolloverHistory->from_term_id,
                'graduationYear' => $graduationYear,
                'rolloverId' => $this->rolloverHistoryId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reverse final grade subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function reverseTests() {
        $rolloverHistory = $this->getRolloverHistory();
        try {
            $testCount = DB::table('tests')->where('term_id', $rolloverHistory->to_term_id)->count();
            DB::table('tests')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['tests'] = $testCount;

            Log::info("Successfully reversed tests", [
                'termId' => $rolloverHistory->to_term_id,
                'testsDeleted' => $testCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
        } catch (Exception $e) {
            Log::error('Failed to reverse tests', [
                'termId' => $rolloverHistory->to_term_id,
                'rolloverId' => $rolloverHistory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseGradingMatrices() {
        $rolloverHistory = $this->getRolloverHistory();
        try {
            $deletedCount = DB::table('overall_grading_matrices')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['gradingMatrices'] = $deletedCount;
            Log::info('Reversed overall grading matrices', [
                'termId' => $rolloverHistory->to_term_id,
                'deletedCount' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reverse overall grading scales', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseGradingScales() {
        $rolloverHistory = $this->getRolloverHistory();
        try {
            $deletedCount = DB::table('grading_scales')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['gradingScales'] = $deletedCount;
            Log::info('Reversed grading scales', [
                'termId' => $rolloverHistory->to_term_id,
                'deletedCount' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
        } catch (Exception $e) {
            Log::error('Failed to reverse grading scales', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseStudentHouses() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $allocationsCount = DB::table('student_house')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('student_house')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $userAllocationsCount = DB::table('user_house')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedUserCount = DB::table('user_house')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['studentHouses'] = $deletedCount;
            $this->results['userHouses'] = $deletedUserCount;

            Log::info('Successfully reversed student house allocations', [
                'termId' => $rolloverHistory->to_term_id,
                'allocationsFound' => $allocationsCount,
                'allocationsDeleted' => $deletedCount,
                'userAllocationsFound' => $userAllocationsCount,
                'userAllocationsDeleted' => $deletedUserCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
        } catch (Exception $e) {
            Log::error('Failed to reverse student house allocations', [
                'termId' => $rolloverHistory->to_term_id,
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseHouses() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
     
            $housesCount = DB::table('houses')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('houses')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['houses'] = $deletedCount;

            Log::info('Successfully reversed houses', [
                'termId' => $rolloverHistory->to_term_id,
                'housesFound' => $housesCount,
                'housesDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
     
            if ($housesCount !== $deletedCount) {
                Log::warning('Mismatch in houses deletion count', [
                    'housesFound' => $housesCount,
                    'housesDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse houses', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }

     protected function reverseKlassSubjects() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $subjectsCount = DB::table('klass_subject')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('klass_subject')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['klassSubjects'] = $deletedCount;

            $reactivatedCount = DB::table('klass_subject')->where('term_id', $rolloverHistory->from_term_id)->where('active', 0)->update(['active' => 1]);
            Log::info('Successfully reversed klass subjects', [
                'termId' => $rolloverHistory->to_term_id,
                'subjectsFound' => $subjectsCount,
                'subjectsDeleted' => $deletedCount,
                'oldSubjectsReactivated' => $reactivatedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
     
            if ($subjectsCount !== $deletedCount) {
                Log::warning('Mismatch in klass subjects deletion count', [
                    'subjectsFound' => $subjectsCount,
                    'subjectsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse klass subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }

    protected function reverseStudentOptionalSubjects() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $allocationsCount = DB::table('student_optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('student_optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['studentOptionalSubjects'] = $deletedCount;

            Log::info('Successfully reversed student optional subject allocations', [
                'termId' => $rolloverHistory->to_term_id,
                'allocationsFound' => $allocationsCount,
                'allocationsDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
            if ($allocationsCount !== $deletedCount) {
                Log::warning('Mismatch in student optional subject allocations deletion count', [
                    'allocationsFound' => $allocationsCount,
                    'allocationsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse student optional subject allocations', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseCouplingGroups(): void {
        $groups = TimetableSetting::get('optional_coupling_groups', []);
        if (empty($groups)) {
            return;
        }

        // Use stored mapping data for precise reversal
        $gradeMapping = $this->mappingData['Grades'] ?? collect();
        $osMapping = $this->mappingData['OptionalSubjects'] ?? collect();

        // Build reverse maps: new_id → old_id
        $reverseGradeMap = $gradeMapping->pluck('old_id', 'new_id')->toArray();
        $reverseOsMap = $osMapping->pluck('old_id', 'new_id')->toArray();

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
        Log::info('Coupling groups reversed during year rollover reversal.');
    }

    protected function reverseOptionalSubjects() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $usedMappings = false;
    
            if (isset($this->mappingData['OptionalSubjects']) && !empty($this->mappingData['OptionalSubjects'])) {
                $newIds = $this->mappingData['OptionalSubjects']->pluck('new_id')->toArray();
                $subjectsCount = DB::table('optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->whereIn('id', $newIds)->count();
                $deletedCount = DB::table('optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->whereIn('id', $newIds)->delete();

                $usedMappings = true;
            } else {
                $subjectsCount = DB::table('optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->count();
                $deletedCount = DB::table('optional_subjects')->where('term_id', $rolloverHistory->to_term_id)->delete();
            }
            $this->results['optionalSubjects'] = $deletedCount;

            $reactivatedCount = DB::table('optional_subjects')
                                ->where('term_id', $rolloverHistory->from_term_id)
                                ->where('active', 0)
                                ->update(['active' => 1]);

            Log::info('Successfully reversed optional subjects', [
                'termId' => $rolloverHistory->to_term_id,
                'subjectsFound' => $subjectsCount,
                'subjectsDeleted' => $deletedCount,
                'oldSubjectsReactivated' => $reactivatedCount,
                'usedMappings' => $usedMappings,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
            if ($subjectsCount !== $deletedCount) {
                Log::warning('Mismatch in optional subjects deletion count', [
                    'subjectsFound' => $subjectsCount,
                    'subjectsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId,
                    'usedMappings' => $usedMappings
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse optional subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseSubjectComponents() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $componentsCount = DB::table('subject_components')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('subject_components')->where('term_id', $rolloverHistory->to_term_id)->delete();
    
            Log::info('Successfully reversed subject components', [
                'termId' => $rolloverHistory->to_term_id,
                'componentsFound' => $componentsCount,
                'componentsDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
            if ($componentsCount !== $deletedCount) {
                Log::warning('Mismatch in subject components deletion count', [
                    'componentsFound' => $componentsCount,
                    'componentsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse subject components', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseSubjects() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $subjectsCount = DB::table('grade_subject')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('grade_subject')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['subjects'] = $deletedCount;

            Log::info('Successfully reversed subjects', [
                'termId' => $rolloverHistory->to_term_id,
                'subjectsFound' => $subjectsCount,
                'subjectsDeleted' => $deletedCount,
                'schoolType' => $this->schoolType,
                'rolloverId' => $this->rolloverHistoryId
            ]);
     
            if ($subjectsCount !== $deletedCount) {
                Log::warning('Mismatch in subjects deletion count', [
                    'subjectsFound' => $subjectsCount,
                    'subjectsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse subjects', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }

    protected function reverseClassAllocations() {
        try {
            $rolloverHistory = $this->getRolloverHistory();

            $klassStudentCount = DB::table('klass_student')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedKlassStudentCount = DB::table('klass_student')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['classAllocations'] = $deletedKlassStudentCount;

            $studentTermCount = DB::table('student_term')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedStudentTermCount = DB::table('student_term')->where('term_id', $rolloverHistory->to_term_id)->delete();

            $reactivatedCount = DB::table('klass_student')->where('term_id', $rolloverHistory->from_term_id)->where('active', 0)->update(['active' => 1]);

            $reactivatedSponsorsCount = DB::table('sponsors')->where('status', 'Past')->where('updated_at', '>=', $rolloverHistory->rollover_timestamp)
                                        ->update(['status' => 'Current']);

            Log::info('Successfully reversed class allocations', [
                'termId' => $rolloverHistory->to_term_id,
                'klassStudentFound' => $klassStudentCount,
                'klassStudentDeleted' => $deletedKlassStudentCount,
                'studentTermsFound' => $studentTermCount,
                'studentTermsDeleted' => $deletedStudentTermCount,
                'oldAllocationsReactivated' => $reactivatedCount,
                'sponsorsReactivated' => $reactivatedSponsorsCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);

            if ($klassStudentCount !== $deletedKlassStudentCount) {
                Log::warning('Mismatch in class allocation deletion count', [
                    'found' => $klassStudentCount,
                    'deleted' => $deletedKlassStudentCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }

            if ($studentTermCount !== $deletedStudentTermCount) {
                Log::warning('Mismatch in student term deletion count', [
                    'found' => $studentTermCount,
                    'deleted' => $deletedStudentTermCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }

        } catch (Exception $e) {
            Log::error('Failed to reverse class allocations', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseClasses() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $classesCount = DB::table('klasses')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('klasses')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['classes'] = $deletedCount;

            $reactivatedCount = DB::table('klasses')->where('term_id', $rolloverHistory->from_term_id)->where('active', 0)->update(['active' => 1]);

            Log::info('Successfully reversed classes', [
                'termId' => $rolloverHistory->to_term_id,
                'classesFound' => $classesCount,
                'classesDeleted' => $deletedCount,
                'oldClassesReactivated' => $reactivatedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
     
            if ($classesCount !== $deletedCount) {
                Log::warning('Mismatch in classes deletion count', [
                    'classesFound' => $classesCount,
                    'classesDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse classes', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseBookAllocations() {
        try {
            if (isset($this->mappingData['Grades']) && !empty($this->mappingData['Grades'])) {
                $newGradeIds = $this->mappingData['Grades']->pluck('new_id')->toArray();
                
                $allocationsCount = DB::table('book_allocations')->whereIn('grade_id', $newGradeIds)->count();
                $deletedCount = DB::table('book_allocations')->whereIn('grade_id', $newGradeIds)->delete();
    
                Log::info('Successfully reversed book allocations', [
                    'gradeCount' => count($newGradeIds),
                    'allocationsFound' => $allocationsCount,
                    'allocationsDeleted' => $deletedCount,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
    
                if ($allocationsCount !== $deletedCount) {
                    Log::warning('Mismatch in book allocations deletion count', [
                        'allocationsFound' => $allocationsCount,
                        'allocationsDeleted' => $deletedCount,
                        'gradeIds' => $newGradeIds,
                        'rolloverId' => $this->rolloverHistoryId
                    ]);
                }
            } else {
                Log::info('No grade mappings found for book allocations reversal', [
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse book allocations', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseBooks() {
        try {
            if (isset($this->mappingData['Grades']) && !empty($this->mappingData['Grades'])) {
                $newGradeIds = $this->mappingData['Grades']->pluck('new_id')->toArray();
                
                $booksCount = DB::table('books')->whereIn('grade_id', $newGradeIds)->count();
                $deletedCount = DB::table('books')->whereIn('grade_id', $newGradeIds)->delete();
     
                Log::info('Successfully reversed books', [
                    'gradeCount' => count($newGradeIds),
                    'booksFound' => $booksCount,
                    'booksDeleted' => $deletedCount,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
     
                if ($booksCount !== $deletedCount) {
                    Log::warning('Mismatch in books deletion count', [
                        'booksFound' => $booksCount,
                        'booksDeleted' => $deletedCount,
                        'gradeIds' => $newGradeIds,
                        'rolloverId' => $this->rolloverHistoryId
                    ]);
                }
            } else {
                Log::info('No grade mappings found for books reversal', [
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse books', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }

     protected function reverseCharges() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $chargesCount = DB::table('charges')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('charges')->where('term_id', $rolloverHistory->to_term_id)->delete();
    
            Log::info('Successfully reversed charges', [
                'termId' => $rolloverHistory->to_term_id,
                'chargesFound' => $chargesCount,
                'chargesDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
            if ($chargesCount !== $deletedCount) {
                Log::warning('Mismatch in charges deletion count', [
                    'chargesFound' => $chargesCount,
                    'chargesDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse charges', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseCriteriaBasedTests() {
        try {
            $rolloverHistory = $this->getRolloverHistory();
            $testsCount = DB::table('criteria_based_tests')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('criteria_based_tests')->where('term_id', $rolloverHistory->to_term_id)->delete();
    
            Log::info('Successfully reversed criteria based tests', [
                'termId' => $rolloverHistory->to_term_id,
                'testsFound' => $testsCount,
                'testsDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
    
            if ($testsCount !== $deletedCount) {
                Log::warning('Mismatch in criteria based tests deletion count', [
                    'testsFound' => $testsCount,
                    'testsDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
    
        } catch (Exception $e) {
            Log::error('Failed to reverse criteria based tests', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function reverseFees() {
        try {
            $rolloverHistory = DB::table('rollover_histories')->findOrFail($this->rolloverHistoryId);
            $feesCount = DB::table('fees')->where('term_id', $rolloverHistory->to_term_id)->count();
            $deletedCount = DB::table('fees')->where('term_id', $rolloverHistory->to_term_id)->delete();
     
            Log::info('Successfully reversed fees', [
                'termId' => $rolloverHistory->to_term_id,
                'feesFound' => $feesCount,
                'feesDeleted' => $deletedCount,
                'rolloverId' => $this->rolloverHistoryId
            ]);
     
            if ($feesCount !== $deletedCount) {
                Log::warning('Mismatch in fees deletion count', [
                    'feesFound' => $feesCount,
                    'feesDeleted' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse fees', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }

     protected function reverseGrades() {
        try {
            $rolloverHistory = DB::table('rollover_histories')->findOrFail($this->rolloverHistoryId);
            $gradesCount = DB::table('grades')->where('term_id', $rolloverHistory->to_term_id)->count();

            $deletedCount = DB::table('grades')->where('term_id', $rolloverHistory->to_term_id)->delete();
            $this->results['grades'] = $deletedCount;
            $reactivatedCount = DB::table('grades')->where('term_id', $rolloverHistory->from_term_id)->where('active', 0)->update(['active' => 1]);
    
            $sourceTerm = Term::find($rolloverHistory->from_term_id);
            $destinationTerm = Term::find($rolloverHistory->to_term_id);
     
            if ($sourceTerm) {
                $sourceTerm->closed = false;
                $sourceTerm->save();
            }
     
            if ($destinationTerm) {
                $destinationTerm->closed = false;
                $destinationTerm->save();
            }
     
            Cache::flush();
            Session::put('selected_term_id', $rolloverHistory->from_term_id);
     
            Log::info('Successfully reversed grades', [
                'termId' => $rolloverHistory->to_term_id,
                'gradesFound' => $gradesCount,
                'gradesDeleted' => $deletedCount,
                'oldGradesReactivated' => $reactivatedCount,
                'sourceTermReopened' => $sourceTerm ? true : false,
                'destinationTermClosed' => $destinationTerm ? true : false,
                'rolloverId' => $this->rolloverHistoryId,
                'sessionUpdated' => true,
                'cacheCleared' => true
            ]);
     
            if ($gradesCount !== $deletedCount) {
                Log::warning('Mismatch in grades deletion count', [
                    'gradesFound' => $gradesCount,
                    'deletedCount' => $deletedCount,
                    'termId' => $rolloverHistory->to_term_id,
                    'rolloverId' => $this->rolloverHistoryId
                ]);
            }
     
        } catch (Exception $e) {
            Log::error('Failed to reverse grades', [
                'rolloverId' => $this->rolloverHistoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
     }
}
