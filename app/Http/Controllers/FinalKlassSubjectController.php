<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Models\FinalKlassSubject;
use App\Models\ExternalExamResult;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Helpers\TermHelper;
use App\Models\FinalOptionalSubject;
use DB;
use Illuminate\Http\Request;
use Log;

class FinalKlassSubjectController extends Controller{
    use InteractsWithFinalsContext;

    public function __construct(){}

    public function index(Request $request){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::findOrFail($selectedTermId);
        $selectedYear = (int) $request->query('year', $selectedTerm->year);
        $finalsDefinition = $this->finalsDefinition($request);

        $earliestYear = Term::min('year');
        $currentYear = TermHelper::getCurrentTerm()->year;
        $futureYear = $currentYear + 2;
                
        $availableYears = [];
        if ($earliestYear) {
            for ($year = $futureYear; $year >= $earliestYear; $year--) {
                $availableYears[] = $year;
            }
        }

        $badgeData = $this->calculateBadgeData($selectedYear, $finalsDefinition);

        $schoolModeResolver = $this->schoolModeResolver();
        $finalsContext = $this->finalsContext($request);
        $reportMenu = $this->finalsReportMenu($finalsDefinition, 'core', [
            'year' => $selectedYear,
        ]);

        return view('finals.core.index', compact(
            'availableYears',
            'selectedYear',
            'badgeData',
            'schoolModeResolver',
            'finalsContext',
            'finalsDefinition',
            'reportMenu'
        ));
    }

    public function getData(Request $request){
        $year = $request->get('year');
        $finalsDefinition = $this->finalsDefinition($request);
        
        $query = FinalKlassSubject::with([
            'finalKlass',
            'finalGradeSubject.subject',
            'finalGradeSubject.grade',
            'finalGradeSubject.department',
            'teacher',
            'grade',
            'graduationTerm',
            'venue'
        ]);

        $this->scopeFinalsQuery($query, 'final_klass_subjects', $finalsDefinition);

        if ($year) {
            $query->where('graduation_year', $year);
        }

        $klassSubjects = $query->orderBy('active', 'desc')
            ->get()->groupBy(function($klassSubject) {
                return $klassSubject->finalGradeSubject->subject->name;
            });

        $groupedData = $klassSubjects->map(function ($klassSubjectGroup, $subjectName) {
            $firstItem = $klassSubjectGroup->first();
            
            return [
                'subject_name' => $subjectName,
                'subject_code' => $firstItem->finalGradeSubject->subject->code,
                'subject_id' => $firstItem->finalGradeSubject->subject->id,
                'department' => $firstItem->finalGradeSubject->department->name ?? 'No Department',
                'total_classes' => $klassSubjectGroup->count(),
                'active_classes' => $klassSubjectGroup->where('active', true)->count(),
                'klass_subjects' => $klassSubjectGroup->map(function($klassSubject) {
                    $studentCount = $klassSubject->finalKlass->finalStudents()->count();
                    
                    return [
                        'id' => $klassSubject->id,
                        'klass_name' => $klassSubject->finalKlass->name,
                        'grade_name' => $klassSubject->grade->name,
                        'teacher_name' => $klassSubject->teacher->full_name ?? 'Not Assigned',
                        'teacher_id' => $klassSubject->teacher->id ?? null,
                        'venue_name' => $klassSubject->venue->name ?? 'Not Assigned',
                        'venue_id' => $klassSubject->venue->id ?? null,
                        'active' => $klassSubject->active,
                        'mandatory' => $klassSubject->finalGradeSubject->mandatory,
                        'graduation_year' => $klassSubject->graduation_year,
                        'graduation_term' => $klassSubject->graduationTerm->name,
                        'student_count' => $studentCount,
                        'final_klass_id' => $klassSubject->final_klass_id,
                        'final_grade_subject_id' => $klassSubject->final_grade_subject_id,
                    ];
                })
            ];
        });

        return view('finals.core.partial.core-partial', compact('groupedData'))->render();
    }

    public function getBadgeData(Request $request){
        $year = $request->get('year');
        $badgeData = $this->calculateBadgeData($year, $this->finalsDefinition($request));
        
        return response()->json($badgeData);
    }

    private function calculateBadgeData($year, $finalsDefinition){
        $query = FinalKlassSubject::query();
        $this->scopeFinalsQuery($query, 'final_klass_subjects', $finalsDefinition);
        
        if ($year) {
            $query->where('graduation_year', $year);
        }

        $total = $query->count();
        $active = $query->where('active', true)->count();

        $mandatory = FinalKlassSubject::whereHas('finalGradeSubject', function($q) {
            $q->where('mandatory', true);
        })->when($year, function($q) use ($year) {
            return $q->where('graduation_year', $year);
        });
        $this->scopeFinalsQuery($mandatory, 'final_klass_subjects', $finalsDefinition);
        $mandatory = $mandatory->count();

        return [
            'total' => $total,
            'active' => $active,
            'mandatory' => $mandatory,
        ];
    }

    public function show(Request $request, FinalKlassSubject $finalKlassSubject){
        $finalsDefinition = $this->finalsDefinition($request);
        abort_unless($finalsDefinition->matchesGradeName(optional($finalKlassSubject->grade)->name), 404);

        $finalKlassSubject->load([
            'finalKlass',
            'finalGradeSubject.subject',
            'finalGradeSubject.grade',
            'finalGradeSubject.department',
            'teacher',
            'grade',
            'graduationTerm',
            'venue',
            'originalKlassSubject'
        ]);

        $students = $finalKlassSubject->finalKlass->finalStudents()->with(['externalExamResults' => function($query) use ($finalsDefinition) {
                $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
            }, 'externalExamResults.subjectResults' => function($q) use ($finalKlassSubject) {
                $q->where('final_grade_subject_id', $finalKlassSubject->final_grade_subject_id);
            }])->get();

        $stats = [
            'total_students' => $students->count(),
            'students_with_results' => $students->filter(function($student) {
                return $student->externalExamResults->isNotEmpty();
            })->count(),
            'students_passed' => $students->filter(function($student) {
                return $student->externalExamResults->filter(function($result) {
                    return $result->subjectResults->where('is_pass', true)->isNotEmpty();
                })->isNotEmpty();
            })->count(),
        ];

        $stats['pass_rate'] = $stats['students_with_results'] > 0 ? 
            round(($stats['students_passed'] / $stats['students_with_results']) * 100, 1) : 0;

        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->orderBy('first_name')->get();
        
        $venues = Venue::orderBy('name')->get();

        return view('finals.core.class-view', compact(
            'finalKlassSubject', 
            'students',
            'stats', 
            'teachers',
            'venues'
        ));
    }

    public function coreSubjectsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $allFinalKlassSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.grade',
                'finalGradeSubject.subject',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryOptionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'grade',
                'finalGradeSubject.subject',
                'finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->whereHas('finalGradeSubject', function($query) {
                $query->where('mandatory', 1)->where('type', 1);
            })->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryFinalKlassSubjects = $allFinalKlassSubjects->filter(function($klassSubject) {
                return $klassSubject->finalGradeSubject && $klassSubject->finalGradeSubject->mandatory;
            });
    
            if ($allFinalKlassSubjects->isEmpty() && $mandatoryOptionalSubjects->isEmpty()) {
                return redirect()->back()->with('error', 'No klass subjects found for the selected graduation year.');
            }
    
            $allSubjectsAnalysis = $this->processSubjectGroupsWithPSLE($allFinalKlassSubjects, $mandatoryOptionalSubjects, 'All Subjects');
            $mandatorySubjectsAnalysis = $this->processSubjectGroupsWithPSLE($mandatoryFinalKlassSubjects, $mandatoryOptionalSubjects, 'Mandatory Subjects');
    
            $schoolData = DB::table('school_setup')->first();
            if (!$schoolData) {
                $schoolData = (object) [
                    'school_name' => 'School Name Not Set',
                    'physical_address' => 'Physical Address Not Set',
                    'postal_address' => 'Postal Address Not Set',
                    'telephone' => 'Tel Not Set',
                    'fax' => 'Fax Not Set',
                    'logo_path' => null
                ];
            }
    
            $exportData = [
                'all_subjects_analysis' => $allSubjectsAnalysis['subject_analysis'],
                'mandatory_subjects_analysis' => $mandatorySubjectsAnalysis['subject_analysis'],
                'all_subjects_summary' => $allSubjectsAnalysis['summary'],
                'mandatory_subjects_summary' => $mandatorySubjectsAnalysis['summary'],
                'all_subjects_chart_data' => $this->prepareChartDataWithPSLE($allSubjectsAnalysis['subject_analysis'], 'all'),
                'mandatory_subjects_chart_data' => $this->prepareChartDataWithPSLE($mandatorySubjectsAnalysis['subject_analysis'], 'mandatory'),
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.core.core-subjects-analysis', $exportData);
        } catch (\Exception $e) {
            Log::error('Subject Analysis with PSLE Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate subject analysis: ' . $e->getMessage());
        }
    }

    private function processKlassSubjectGrades($klassSubject){  
        try {
            $studentsWithResults = $klassSubject->finalKlass->finalStudents->filter(function ($student) use ($klassSubject) {
                return $student->externalExamResults->isNotEmpty() && 
                    $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });

            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }

            $gradeAnalysis = [
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];

            $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
            foreach ($studentsWithResults as $student) {
                $gender = $student->gender;
                $subjectResult = $student->externalExamResults->first()->subjectResults
                                    ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                                    ->where('is_mapped', true)
                                    ->whereNotNull('grade')
                                    ->first();

                if ($subjectResult) {
                    $grade = $subjectResult->grade;
                    if (isset($gradeAnalysis[$grade])) {
                        $gradeAnalysis[$grade][$gender]++;
                        $gradeAnalysis[$grade]['T']++;
                    }

                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }

            $performanceCategories = $this->calculateSubjectPerformanceCategories($gradeAnalysis, $genderTotals);
            return [
                'klass_subject_id' => $klassSubject->id,
                'teacher_name' => $klassSubject->teacher->full_name ?? 'Not Assigned',
                'class_name' => $klassSubject->finalKlass->name ?? 'Unknown Class',
                'subject_name' => $klassSubject->finalGradeSubject->subject->name ?? 'Unknown Subject',
                'grade_name' => $klassSubject->finalKlass->grade->name ?? 'Unknown Grade',
                'total_students' => $totalStudents,
                'gender_totals' => $genderTotals,
                'grade_analysis' => $gradeAnalysis,
                'performance_categories' => $performanceCategories,
                'graduation_year' => $klassSubject->graduation_year,
                'is_mandatory' => $klassSubject->finalGradeSubject->mandatory ?? false
            ];
            
        } catch (\Exception $e) {
            Log::error('Error processing klass subject grades', [
                'klass_subject_id' => $klassSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    private function processSubjectGroupsWithPSLE($klassSubjects, $optionalSubjects, $type){
        $klassSubjectGroups = $klassSubjects->groupBy(function($klassSubject) {
            return $klassSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
        });
        
        $optionalSubjectGroups = $optionalSubjects->groupBy(function($optionalSubject) {
            return $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
        });
        
        $allSubjectNames = collect($klassSubjectGroups->keys())->merge($optionalSubjectGroups->keys())->unique();
        
        $subjectAnalysis = [];
        $totalClasses = 0;
        $totalStudents = 0;
        
        foreach ($allSubjectNames as $subjectName) {
            $combinedAnalysis = [];
            
            if ($klassSubjectGroups->has($subjectName)) {
                foreach ($klassSubjectGroups[$subjectName] as $klassSubject) {
                    $analysis = $this->processKlassSubjectGradesWithPSLESubjectGroups($klassSubject);
                    if ($analysis) {
                        foreach ($analysis as $row) {
                            $combinedAnalysis[] = $row;
                            if ($row['row_type'] === 'OUTPUT') {
                                $totalStudents += $row['total_students'];
                            }
                        }
                    }
                }
            }
            
            if ($optionalSubjectGroups->has($subjectName)) {
                foreach ($optionalSubjectGroups[$subjectName] as $optionalSubject) {
                    $analysis = $this->processOptionalSubjectGradesWithPSLESubjectGroups($optionalSubject);
                    if ($analysis) {
                        foreach ($analysis as $row) {
                            $combinedAnalysis[] = $row;
                            if ($row['row_type'] === 'OUTPUT') {
                                $totalStudents += $row['total_students'];
                            }
                        }
                    }
                }
            }
            
            if (!empty($combinedAnalysis)) {
                usort($combinedAnalysis, function($a, $b) {
                    $classComparison = strcmp($a['class_name'], $b['class_name']);
                    if ($classComparison === 0) {
                        return $a['row_type'] === 'PSLE' ? -1 : 1;
                    }
                    return $classComparison;
                });
                
                $outputRows = array_filter($combinedAnalysis, function($row) {
                    return $row['row_type'] === 'OUTPUT';
                });
                
                $subjectAnalysis[$subjectName] = [
                    'subject_name' => $subjectName,
                    'klass_subjects' => $combinedAnalysis,
                    'total_classes' => count($outputRows),
                    'total_students' => array_sum(array_column($outputRows, 'total_students')),
                    'subject_type' => $type
                ];
                $totalClasses += count($outputRows);
            }
        }
        
        ksort($subjectAnalysis);
        return [
            'subject_analysis' => $subjectAnalysis,
            'summary' => [
                'total_subjects' => count($subjectAnalysis),
                'total_classes' => $totalClasses,
                'total_students' => $totalStudents,
                'type' => $type
            ]
        ];
    }

    private function processKlassSubjectGradesWithPSLESubjectGroups($klassSubject){
        try {
            $subjectName = $klassSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
            $psleSubjectMapping = $this->getPSLESubjectMappingSubjectGroups();
            
            $studentsWithResults = $klassSubject->finalKlass->finalStudents->filter(function ($student) use ($klassSubject) {
                return $student->externalExamResults->isNotEmpty() &&
                     $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });
            
            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }
            
            $rows = [];
            $psleAnalysis = null;
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubjectSubjectGroups($studentsWithResults, $psleSubjectMapping[$subjectName]);
            }
            if (!$psleAnalysis) {
                $psleAnalysis = $this->processPSLEOverallGradesSubjectGroups($studentsWithResults);
            }

            if ($psleAnalysis) {
                $psleAnalysis['row_type'] = 'PSLE';
                $psleAnalysis['klass_subject_id'] = $klassSubject->id . '_psle';
                $psleAnalysis['teacher_name'] = 'PSLE Results';
                $psleAnalysis['class_name'] = $klassSubject->finalKlass->name ?? 'Unknown Class';
                $psleAnalysis['subject_name'] = $subjectName;
                $psleAnalysis['grade_name'] = $klassSubject->finalKlass->grade->name ?? 'Unknown Grade';
                $psleAnalysis['graduation_year'] = $klassSubject->graduation_year;
                $psleAnalysis['is_mandatory'] = $klassSubject->finalGradeSubject->mandatory ?? false;
                $rows[] = $psleAnalysis;
            }
            
            $jceAnalysis = $this->processJCEGradesForSubjectSubjectGroups($studentsWithResults, $klassSubject);
            if ($jceAnalysis) {
                $jceAnalysis['row_type'] = 'OUTPUT';
                $jceAnalysis['klass_subject_id'] = $klassSubject->id;
                $jceAnalysis['teacher_name'] = $klassSubject->teacher->full_name ?? 'Not Assigned';
                $jceAnalysis['class_name'] = $klassSubject->finalKlass->name ?? 'Unknown Class';
                $jceAnalysis['subject_name'] = $subjectName;
                $jceAnalysis['grade_name'] = $klassSubject->finalKlass->grade->name ?? 'Unknown Grade';
                $jceAnalysis['graduation_year'] = $klassSubject->graduation_year;
                $jceAnalysis['is_mandatory'] = $klassSubject->finalGradeSubject->mandatory ?? false;
                $rows[] = $jceAnalysis;
            }
            
            return $rows;
            
        } catch (\Exception $e) {
            Log::error('Error processing klass subject grades with PSLE for subject groups', [
                'klass_subject_id' => $klassSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function processOptionalSubjectGradesWithPSLESubjectGroups($optionalSubject){
        try {
            $subjectName = $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
            $psleSubjectMapping = $this->getPSLESubjectMappingSubjectGroups();
            
            $studentsWithResults = $optionalSubject->finalStudents->filter(function ($student) use ($optionalSubject) {
                return $student->externalExamResults->isNotEmpty() &&
                     $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });
            
            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }
            
            $rows = [];
            $psleAnalysis = null;
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubjectSubjectGroups($studentsWithResults, $psleSubjectMapping[$subjectName]);
            }
            if (!$psleAnalysis) {
                $psleAnalysis = $this->processPSLEOverallGradesSubjectGroups($studentsWithResults);
            }

            if ($psleAnalysis) {
                $psleAnalysis['row_type'] = 'PSLE';
                $psleAnalysis['klass_subject_id'] = 'optional_' . $optionalSubject->id . '_psle';
                $psleAnalysis['teacher_name'] = 'PSLE Results';
                $psleAnalysis['class_name'] = $optionalSubject->name;
                $psleAnalysis['subject_name'] = $subjectName;
                $psleAnalysis['grade_name'] = $optionalSubject->grade->name ?? 'Unknown Grade';
                $psleAnalysis['graduation_year'] = $optionalSubject->graduation_year;
                $psleAnalysis['is_mandatory'] = true;
                $psleAnalysis['is_optional_subject'] = true;
                $rows[] = $psleAnalysis;
            }
            
            $jceAnalysis = $this->processJCEGradesForOptionalSubjectSubjectGroups($studentsWithResults, $optionalSubject);
            if ($jceAnalysis) {
                $jceAnalysis['row_type'] = 'OUTPUT';
                $jceAnalysis['klass_subject_id'] = 'optional_' . $optionalSubject->id;
                $jceAnalysis['teacher_name'] = $optionalSubject->teacher->full_name ?? 'Not Assigned';
                $jceAnalysis['class_name'] = $optionalSubject->name;
                $jceAnalysis['subject_name'] = $subjectName;
                $jceAnalysis['grade_name'] = $optionalSubject->grade->name ?? 'Unknown Grade';
                $jceAnalysis['graduation_year'] = $optionalSubject->graduation_year;
                $jceAnalysis['is_mandatory'] = true;
                $jceAnalysis['is_optional_subject'] = true;
                $rows[] = $jceAnalysis;
            }
            
            return $rows;
            
        } catch (\Exception $e) {
            Log::error('Error processing optional subject grades with PSLE for subject groups', [
                'optional_subject_id' => $optionalSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    private function processPSLEGradesForSubjectSubjectGroups($students, $psleGradeField){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            if ($student->originalStudent && $student->originalStudent->psle) {
                $psleGrade = $student->originalStudent->psle->{$psleGradeField};
                if ($psleGrade && isset($gradeAnalysis[$psleGrade])) {
                    $gender = $student->gender;
                    $gradeAnalysis[$psleGrade][$gender]++;
                    $gradeAnalysis[$psleGrade]['T']++;
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLESubjectGroups($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function processPSLEOverallGradesSubjectGroups($students){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            if ($student->originalStudent && $student->originalStudent->psle) {
                $psleOverallGrade = $student->originalStudent->psle->overall_grade;
                if ($psleOverallGrade && isset($gradeAnalysis[$psleOverallGrade])) {
                    $gender = $student->gender;
                    $gradeAnalysis[$psleOverallGrade][$gender]++;
                    $gradeAnalysis[$psleOverallGrade]['T']++;
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLESubjectGroups($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function processJCEGradesForSubjectSubjectGroups($students, $klassSubject){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            $gender = $student->gender;
            $subjectResult = $student->externalExamResults->first()->subjectResults
                                ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                                ->where('is_mapped', true)
                                ->whereNotNull('grade')
                                ->first();
            
            if ($subjectResult && $subjectResult->grade) {
                $grade = $subjectResult->grade;
                if (isset($gradeAnalysis[$grade])) {
                    $gradeAnalysis[$grade][$gender]++;
                    $gradeAnalysis[$grade]['T']++;
                }
                
                $genderTotals[$gender]++;
                $genderTotals['T']++;
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLESubjectGroups($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function processJCEGradesForOptionalSubjectSubjectGroups($students, $optionalSubject){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            $gender = $student->gender;
            $subjectResult = $student->externalExamResults->first()->subjectResults
                                ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                                ->where('is_mapped', true)
                                ->whereNotNull('grade')
                                ->first();
            
            if ($subjectResult && $subjectResult->grade) {
                $grade = $subjectResult->grade;
                if (isset($gradeAnalysis[$grade])) {
                    $gradeAnalysis[$grade][$gender]++;
                    $gradeAnalysis[$grade]['T']++;
                }
                
                $genderTotals[$gender]++;
                $genderTotals['T']++;
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLESubjectGroups($gradeAnalysis, $genderTotals);
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function calculateSubjectPerformanceCategoriesWithPSLESubjectGroups($gradeAnalysis, $genderTotals){
        $categories = [
            'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
            'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
            'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        foreach (['A', 'B'] as $grade) {
            $categories['AB']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['AB']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['AB']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        foreach (['A', 'B', 'C'] as $grade) {
            $categories['ABC']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['ABC']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['ABC']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        foreach (['D', 'E', 'U'] as $grade) {
            $categories['DEU']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['DEU']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['DEU']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        $result = [];
        foreach ($categories as $category => $counts) {
            $result[$category] = [
                'M' => $genderTotals['M'] > 0 ? round(($counts['M'] / $genderTotals['M']) * 100, 1) : 0,
                'F' => $genderTotals['F'] > 0 ? round(($counts['F'] / $genderTotals['F']) * 100, 1) : 0,
                'T' => $genderTotals['T'] > 0 ? round(($counts['T'] / $genderTotals['T']) * 100, 1) : 0,
                'counts' => $counts
            ];
        }
        
        return $result;
    }
    
    private function prepareChartDataWithPSLE($subjectAnalysis, $type){
        $chartData = [
            'subjects' => [],
            'ab_percentages' => [],
            'abc_percentages' => [],
            'deu_percentages' => [],
            'grade_distributions' => [
                'A' => [],
                'B' => [],
                'C' => [],
                'D' => [],
                'E' => [],
                'U' => []
            ]
        ];
        
        foreach ($subjectAnalysis as $subjectName => $subjectData) {
            $chartData['subjects'][] = $subjectName;
            
            $outputRows = array_filter($subjectData['klass_subjects'], function($row) {
                return $row['row_type'] === 'OUTPUT';
            });
            
            if (!empty($outputRows)) {
                $totalStudents = array_sum(array_column($outputRows, 'total_students'));
                $aggregateGrades = [
                    'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0
                ];
                
                foreach ($outputRows as $row) {
                    foreach ($aggregateGrades as $grade => $count) {
                        $aggregateGrades[$grade] += $row['grade_analysis'][$grade]['T'];
                    }
                }
                
                $abCount = $aggregateGrades['A'] + $aggregateGrades['B'];
                $abcCount = $abCount + $aggregateGrades['C'];
                $deuCount = $aggregateGrades['D'] + $aggregateGrades['E'] + $aggregateGrades['U'];
                
                $chartData['ab_percentages'][] = $totalStudents > 0 ? round(($abCount / $totalStudents) * 100, 1) : 0;
                $chartData['abc_percentages'][] = $totalStudents > 0 ? round(($abcCount / $totalStudents) * 100, 1) : 0;
                $chartData['deu_percentages'][] = $totalStudents > 0 ? round(($deuCount / $totalStudents) * 100, 1) : 0;
                
                foreach ($aggregateGrades as $grade => $count) {
                    $chartData['grade_distributions'][$grade][] = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0;
                }
            } else {
                $chartData['ab_percentages'][] = 0;
                $chartData['abc_percentages'][] = 0;
                $chartData['deu_percentages'][] = 0;
                
                foreach ($chartData['grade_distributions'] as $grade => $percentages) {
                    $chartData['grade_distributions'][$grade][] = 0;
                }
            }
        }
        
        return $chartData;
    }
    

    private function getPSLESubjectMappingSubjectGroups(){
        return [
            'Agriculture' => 'agriculture_grade',
            'Mathematics' => 'mathematics_grade', 
            'English' => 'english_grade',
            'Science' => 'science_grade',
            'Social Studies' => 'social_studies_grade',
            'Setswana' => 'setswana_grade',
        ];
    }

    private function processOptionalSubjectGrades($optionalSubject){  
        try {
            $studentsWithResults = $optionalSubject->finalStudents->filter(function ($student) use ($optionalSubject) {
                return $student->externalExamResults->isNotEmpty() && 
                    $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });
    
            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }
    
            $gradeAnalysis = [
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
    
            $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
            foreach ($studentsWithResults as $student) {
                $gender = $student->gender;
                $subjectResult = $student->externalExamResults->first()->subjectResults
                                    ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                                    ->where('is_mapped', true)
                                    ->whereNotNull('grade')
                                    ->first();
    
                if ($subjectResult) {
                    $grade = $subjectResult->grade;
                    if (isset($gradeAnalysis[$grade])) {
                        $gradeAnalysis[$grade][$gender]++;
                        $gradeAnalysis[$grade]['T']++;
                    }
    
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }
    
            $performanceCategories = $this->calculateSubjectPerformanceCategories($gradeAnalysis, $genderTotals);
            return [
                'klass_subject_id' => 'optional_' . $optionalSubject->id,
                'teacher_name' => $optionalSubject->teacher->full_name ?? 'Not Assigned',
                'class_name' => $optionalSubject->name,
                'subject_name' => $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject',
                'grade_name' => $optionalSubject->grade->name ?? 'Unknown Grade',
                'total_students' => $totalStudents,
                'gender_totals' => $genderTotals,
                'grade_analysis' => $gradeAnalysis,
                'performance_categories' => $performanceCategories,
                'graduation_year' => $optionalSubject->graduation_year,
                'is_mandatory' => true,
                'is_optional_subject' => true
            ];
            
        } catch (\Exception $e) {
            Log::error('Error processing optional subject grades', [
                'optional_subject_id' => $optionalSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    private function calculateSubjectPerformanceCategories($gradeAnalysis, $genderTotals){
        $categories = [
            'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
            'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
            'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];

        foreach (['A', 'B'] as $grade) {
            $categories['AB']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['AB']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['AB']['T'] += $gradeAnalysis[$grade]['T'];
        }

        foreach (['A', 'B', 'C'] as $grade) {
            $categories['ABC']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['ABC']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['ABC']['T'] += $gradeAnalysis[$grade]['T'];
        }

        foreach (['D', 'E', 'U'] as $grade) {
            $categories['DEU']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['DEU']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['DEU']['T'] += $gradeAnalysis[$grade]['T'];
        }

        $result = [];
        foreach ($categories as $category => $counts) {
            $result[$category] = [
                'M' => $genderTotals['M'] > 0 ? round(($counts['M'] / $genderTotals['M']) * 100, 1) : 0,
                'F' => $genderTotals['F'] > 0 ? round(($counts['F'] / $genderTotals['F']) * 100, 1) : 0,
                'T' => $genderTotals['T'] > 0 ? round(($counts['T'] / $genderTotals['T']) * 100, 1) : 0,
                'counts' => $counts
            ];
        }

        return $result;
    }

    public function teacherSubjectsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $allFinalKlassSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)
                                    ->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryOptionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)
                                    ->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->whereHas('finalGradeSubject', function($query) {
                $query->where('mandatory', true);
            })->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryFinalKlassSubjects = $allFinalKlassSubjects->filter(function($klassSubject) {
                return $klassSubject->finalGradeSubject && $klassSubject->finalGradeSubject->mandatory;
            });
    
            if ($allFinalKlassSubjects->isEmpty() && $mandatoryOptionalSubjects->isEmpty()) {
                return redirect()->back()->with('error', 'No klass subjects found for the selected graduation year.');
            }
    
            $allSubjectsByTeacher = $this->processByTeacher($allFinalKlassSubjects, $mandatoryOptionalSubjects, 'All Subjects');
            $mandatorySubjectsByTeacher = $this->processByTeacher($mandatoryFinalKlassSubjects, $mandatoryOptionalSubjects, 'Mandatory Subjects');
            $schoolData = DB::table('school_setup')->first();    
            $exportData = [
                'all_teachers_analysis' => $allSubjectsByTeacher['teacher_analysis'],
                'mandatory_teachers_analysis' => $mandatorySubjectsByTeacher['teacher_analysis'],
                'all_teachers_summary' => $allSubjectsByTeacher['summary'],
                'mandatory_teachers_summary' => $mandatorySubjectsByTeacher['summary'],
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.core.teacher-subjects-analysis', $exportData);
            
        } catch (\Exception $e) {
            Log::error('Teacher Subject Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate teacher subject analysis: ' . $e->getMessage());
        }
    }

    private function processByTeacher($klassSubjects, $optionalSubjects, $type){
        $allSubjects = collect();
        
        // Process class subjects with PSLE data
        foreach ($klassSubjects as $klassSubject) {
            $analysis = $this->processKlassSubjectGradesWithPSLE($klassSubject); // ← CHANGE THIS LINE
            if ($analysis) {
                foreach ($analysis as $row) { // ← NOW HANDLE MULTIPLE ROWS
                    $row['teacher_full_name'] = $klassSubject->teacher->full_name ?? 'Not Assigned';
                    $row['teacher_id'] = $klassSubject->teacher->id ?? 0;
                    $row['department_name'] = $klassSubject->finalGradeSubject->department->name ?? 'No Department';
                    $allSubjects->push($row);
                }
            }
        }
    
        // Process optional subjects with PSLE data
        foreach ($optionalSubjects as $optionalSubject) {
            $analysis = $this->processOptionalSubjectGradesWithPSLE($optionalSubject); // ← CHANGE THIS LINE
            if ($analysis) {
                foreach ($analysis as $row) { // ← NOW HANDLE MULTIPLE ROWS
                    $row['teacher_full_name'] = $optionalSubject->teacher->full_name ?? 'Not Assigned';
                    $row['teacher_id'] = $optionalSubject->teacher->id ?? 0;
                    $row['department_name'] = $optionalSubject->finalGradeSubject->department->name ?? 'No Department';
                    $allSubjects->push($row);
                }
            }
        }
    
        $teacherGroups = $allSubjects->groupBy('teacher_full_name');
        $teacherAnalysis = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $totalTeachers = 0;
    
        foreach ($teacherGroups as $teacherName => $teacherSubjects) {
            // Sort by class name, subject name, and row type (PSLE first, then OUTPUT)
            $sortedSubjects = $teacherSubjects->sortBy(['class_name', 'subject_name', 'row_type'])->values();
            $departments = $sortedSubjects->pluck('department_name')->unique()->filter()->values();
            
            // Count only OUTPUT rows for class statistics
            $outputRows = $sortedSubjects->where('row_type', 'OUTPUT');
            
            $teacherAnalysis[$teacherName] = [
                'teacher_name' => $teacherName,
                'teacher_subjects' => $sortedSubjects,
                'departments' => $departments,
                'total_classes' => $outputRows->count(),
                'total_students' => $outputRows->sum('total_students'),
                'teacher_type' => $type
            ];
            
            $totalClasses += $outputRows->count();
            $totalStudents += $outputRows->sum('total_students');
            $totalTeachers++;
        }
    
        ksort($teacherAnalysis);
        return [
            'teacher_analysis' => $teacherAnalysis,
            'summary' => [
                'total_teachers' => $totalTeachers,
                'total_classes' => $totalClasses,
                'total_students' => $totalStudents,
                'type' => $type
            ]
        ];
    }

    public function departmentSubjectsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $allFinalKlassSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryOptionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)->whereNotNull('grade');
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->whereHas('finalGradeSubject', function($query) {
                $query->where('mandatory', true);
            })->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $mandatoryFinalKlassSubjects = $allFinalKlassSubjects->filter(function($klassSubject) {
                return $klassSubject->finalGradeSubject && $klassSubject->finalGradeSubject->mandatory;
            });
    
            if ($allFinalKlassSubjects->isEmpty() && $mandatoryOptionalSubjects->isEmpty()) {
                return redirect()->back()->with('error', 'No klass subjects found for the selected graduation year.');
            }
    
            $allSubjectsByDepartment = $this->processByDepartmentWithPSLE($allFinalKlassSubjects, $mandatoryOptionalSubjects, 'All Subjects');
            $mandatorySubjectsByDepartment = $this->processByDepartmentWithPSLE($mandatoryFinalKlassSubjects, $mandatoryOptionalSubjects, 'Mandatory Subjects');
    
            $schoolData = DB::table('school_setup')->first();
            if (!$schoolData) {
                $schoolData = (object) [
                    'school_name' => 'School Name Not Set',
                    'physical_address' => 'Physical Address Not Set',
                    'postal_address' => 'Postal Address Not Set',
                    'telephone' => 'Tel Not Set',
                    'fax' => 'Fax Not Set',
                    'logo_path' => null
                ];
            }
    
            $exportData = [
                'all_departments_analysis' => $allSubjectsByDepartment['department_analysis'],
                'mandatory_departments_analysis' => $mandatorySubjectsByDepartment['department_analysis'],
                'all_departments_summary' => $allSubjectsByDepartment['summary'],
                'mandatory_departments_summary' => $mandatorySubjectsByDepartment['summary'],
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.core.core-subjects-department-analysis', $exportData);
        } catch (\Exception $e) {
            Log::error('Department Subject Analysis with PSLE Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate department subject analysis: ' . $e->getMessage());
        }
    }

    private function processByDepartmentWithPSLE($klassSubjects, $optionalSubjects, $type){
        $allSubjects = collect();
        
        foreach ($klassSubjects as $klassSubject) {
            $analysis = $this->processKlassSubjectGradesWithPSLE($klassSubject);
            if ($analysis) {
                foreach ($analysis as $row) {
                    $row['department_name'] = $klassSubject->finalGradeSubject->department->name ?? 'No Department';
                    $row['department_id'] = $klassSubject->finalGradeSubject->department->id ?? 0;
                    $allSubjects->push($row);
                }
            }
        }
        
        foreach ($optionalSubjects as $optionalSubject) {
            $analysis = $this->processOptionalSubjectGradesWithPSLE($optionalSubject);
            if ($analysis) {
                foreach ($analysis as $row) {
                    $row['department_name'] = $optionalSubject->finalGradeSubject->department->name ?? 'No Department';
                    $row['department_id'] = $optionalSubject->finalGradeSubject->department->id ?? 0;
                    $allSubjects->push($row);
                }
            }
        }
        
        $departmentGroups = $allSubjects->groupBy('department_name');
        
        $departmentAnalysis = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $totalDepartments = 0;
        
        foreach ($departmentGroups as $departmentName => $departmentSubjects) {
            $sortedSubjects = $departmentSubjects->sortBy(['class_name', 'subject_name', 'row_type'])->values();
            $departmentAnalysis[$departmentName] = [
                'department_name' => $departmentName,
                'department_subjects' => $sortedSubjects,
                'total_classes' => $sortedSubjects->where('row_type', 'OUTPUT')->count(),
                'total_students' => $sortedSubjects->where('row_type', 'OUTPUT')->sum('total_students'),
                'department_type' => $type
            ];
            
            $totalClasses += $sortedSubjects->where('row_type', 'OUTPUT')->count();
            $totalStudents += $sortedSubjects->where('row_type', 'OUTPUT')->sum('total_students');
            $totalDepartments++;
        }
        
        ksort($departmentAnalysis);
        
        return [
            'department_analysis' => $departmentAnalysis,
            'summary' => [
                'total_departments' => $totalDepartments,
                'total_classes' => $totalClasses,
                'total_students' => $totalStudents,
                'type' => $type
            ]
        ];
    }

    private function processKlassSubjectGradesWithPSLE($klassSubject){
        try {
            $subjectName = $klassSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
            $psleSubjectMapping = $this->getPSLESubjectMapping();
            
            $studentsWithResults = $klassSubject->finalKlass->finalStudents->filter(function ($student) use ($klassSubject) {
                return $student->externalExamResults->isNotEmpty() &&
                     $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });
            
            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }
            
            $rows = [];

            $psleAnalysis = null;
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubject($studentsWithResults, $psleSubjectMapping[$subjectName]);
            }
            if (!$psleAnalysis) {
                $psleAnalysis = $this->processPSLEOverallGrades($studentsWithResults);
            }

            if ($psleAnalysis) {
                $psleAnalysis['row_type'] = 'PSLE';
                $psleAnalysis['klass_subject_id'] = $klassSubject->id . '_psle';
                $psleAnalysis['teacher_name'] = 'PSLE Results';
                $psleAnalysis['class_name'] = $klassSubject->finalKlass->name ?? 'Unknown Class';
                $psleAnalysis['subject_name'] = $subjectName;
                $psleAnalysis['grade_name'] = $klassSubject->finalKlass->grade->name ?? 'Unknown Grade';
                $psleAnalysis['graduation_year'] = $klassSubject->graduation_year;
                $psleAnalysis['is_mandatory'] = $klassSubject->finalGradeSubject->mandatory ?? false;
                $rows[] = $psleAnalysis;
            }
            
            $jceAnalysis = $this->processJCEGradesForSubject($studentsWithResults, $klassSubject);
            if ($jceAnalysis) {
                $jceAnalysis['row_type'] = 'OUTPUT';
                $jceAnalysis['klass_subject_id'] = $klassSubject->id;
                $jceAnalysis['teacher_name'] = $klassSubject->teacher->full_name ?? 'Not Assigned';
                $jceAnalysis['class_name'] = $klassSubject->finalKlass->name ?? 'Unknown Class';
                $jceAnalysis['subject_name'] = $subjectName;
                $jceAnalysis['grade_name'] = $klassSubject->finalKlass->grade->name ?? 'Unknown Grade';
                $jceAnalysis['graduation_year'] = $klassSubject->graduation_year;
                $jceAnalysis['is_mandatory'] = $klassSubject->finalGradeSubject->mandatory ?? false;
                $rows[] = $jceAnalysis;
            }
            
            return $rows;
            
        } catch (\Exception $e) {
            Log::error('Error processing klass subject grades with PSLE', [
                'klass_subject_id' => $klassSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    private function processJCEGradesForSubject($students, $klassSubject){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            $gender = $student->gender;
            $subjectResult = $student->externalExamResults->first()->subjectResults
                                ->where('final_grade_subject_id', $klassSubject->final_grade_subject_id)
                                ->where('is_mapped', true)
                                ->whereNotNull('grade')
                                ->first();
            
            if ($subjectResult && $subjectResult->grade) {
                $grade = $subjectResult->grade;
                if (isset($gradeAnalysis[$grade])) {
                    $gradeAnalysis[$grade][$gender]++;
                    $gradeAnalysis[$grade]['T']++;
                }
                
                $genderTotals[$gender]++;
                $genderTotals['T']++;
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLE($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function processOptionalSubjectGradesWithPSLE($optionalSubject){
        try {
            $subjectName = $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
            $psleSubjectMapping = $this->getPSLESubjectMapping();
            
            $studentsWithResults = $optionalSubject->finalStudents->filter(function ($student) use ($optionalSubject) {
                return $student->externalExamResults->isNotEmpty() &&
                     $student->externalExamResults->first()->subjectResults
                            ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                            ->where('is_mapped', true)
                            ->whereNotNull('grade')
                            ->isNotEmpty();
            });
            
            $totalStudents = $studentsWithResults->count();
            
            if ($totalStudents === 0) {
                return null;
            }
            
            $rows = [];

            $psleAnalysis = null;
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubject($studentsWithResults, $psleSubjectMapping[$subjectName]);
            }
            if (!$psleAnalysis) {
                $psleAnalysis = $this->processPSLEOverallGrades($studentsWithResults);
            }

            if ($psleAnalysis) {
                $psleAnalysis['row_type'] = 'PSLE';
                $psleAnalysis['klass_subject_id'] = 'optional_' . $optionalSubject->id . '_psle';
                $psleAnalysis['teacher_name'] = 'PSLE Results';
                $psleAnalysis['class_name'] = $optionalSubject->name;
                $psleAnalysis['subject_name'] = $subjectName;
                $psleAnalysis['grade_name'] = $optionalSubject->grade->name ?? 'Unknown Grade';
                $psleAnalysis['graduation_year'] = $optionalSubject->graduation_year;
                $psleAnalysis['is_mandatory'] = true;
                $psleAnalysis['is_optional_subject'] = true;
                $rows[] = $psleAnalysis;
            }
            
            $jceAnalysis = $this->processJCEGradesForOptionalSubject($studentsWithResults, $optionalSubject);
            if ($jceAnalysis) {
                $jceAnalysis['row_type'] = 'OUTPUT';
                $jceAnalysis['klass_subject_id'] = 'optional_' . $optionalSubject->id;
                $jceAnalysis['teacher_name'] = $optionalSubject->teacher->full_name ?? 'Not Assigned';
                $jceAnalysis['class_name'] = $optionalSubject->name;
                $jceAnalysis['subject_name'] = $subjectName;
                $jceAnalysis['grade_name'] = $optionalSubject->grade->name ?? 'Unknown Grade';
                $jceAnalysis['graduation_year'] = $optionalSubject->graduation_year;
                $jceAnalysis['is_mandatory'] = true;
                $jceAnalysis['is_optional_subject'] = true;
                $rows[] = $jceAnalysis;
            }
            
            return $rows;
            
        } catch (\Exception $e) {
            Log::error('Error processing optional subject grades with PSLE', [
                'optional_subject_id' => $optionalSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    private function getPSLESubjectMapping(){
        return [
            'Agriculture' => 'agriculture_grade',
            'Mathematics' => 'mathematics_grade', 
            'English' => 'english_grade',
            'Science' => 'science_grade',
            'Social Studies' => 'social_studies_grade',
            'Setswana' => 'setswana_grade',
        ];
    }

    private function processPSLEGradesForSubject($students, $psleGradeField){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            if ($student->originalStudent && $student->originalStudent->psle) {
                $psleGrade = $student->originalStudent->psle->{$psleGradeField};
                if ($psleGrade && isset($gradeAnalysis[$psleGrade])) {
                    $gender = $student->gender;
                    $gradeAnalysis[$psleGrade][$gender]++;
                    $gradeAnalysis[$psleGrade]['T']++;
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLE($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }
    
    private function processJCEGradesForOptionalSubject($students, $optionalSubject){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        foreach ($students as $student) {
            $gender = $student->gender;
            $subjectResult = $student->externalExamResults->first()->subjectResults
                                ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                                ->where('is_mapped', true)
                                ->whereNotNull('grade')
                                ->first();
            
            if ($subjectResult && $subjectResult->grade) {
                $grade = $subjectResult->grade;
                if (isset($gradeAnalysis[$grade])) {
                    $gradeAnalysis[$grade][$gender]++;
                    $gradeAnalysis[$grade]['T']++;
                }
                
                $genderTotals[$gender]++;
                $genderTotals['T']++;
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLE($gradeAnalysis, $genderTotals);
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    private function calculateSubjectPerformanceCategoriesWithPSLE($gradeAnalysis, $genderTotals){
        $categories = [
            'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
            'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
            'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        foreach (['A', 'B'] as $grade) {
            $categories['AB']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['AB']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['AB']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        foreach (['A', 'B', 'C'] as $grade) {
            $categories['ABC']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['ABC']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['ABC']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        foreach (['D', 'E', 'U'] as $grade) {
            $categories['DEU']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['DEU']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['DEU']['T'] += $gradeAnalysis[$grade]['T'];
        }
        
        $result = [];
        foreach ($categories as $category => $counts) {
            $result[$category] = [
                'M' => $genderTotals['M'] > 0 ? round(($counts['M'] / $genderTotals['M']) * 100, 1) : 0,
                'F' => $genderTotals['F'] > 0 ? round(($counts['F'] / $genderTotals['F']) * 100, 1) : 0,
                'T' => $genderTotals['T'] > 0 ? round(($counts['T'] / $genderTotals['T']) * 100, 1) : 0,
                'counts' => $counts
            ];
        }
        return $result;
    }

    private function processPSLEOverallGrades($students){
        $gradeAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
        
        foreach ($students as $student) {
            if ($student->originalStudent && $student->originalStudent->psle) {
                $psleOverallGrade = $student->originalStudent->psle->overall_grade;
                if ($psleOverallGrade && isset($gradeAnalysis[$psleOverallGrade])) {
                    $gender = $student->gender;
                    $gradeAnalysis[$psleOverallGrade][$gender]++;
                    $gradeAnalysis[$psleOverallGrade]['T']++;
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                }
            }
        }
        
        if ($genderTotals['T'] === 0) {
            return null;
        }
        
        $performanceCategories = $this->calculateSubjectPerformanceCategoriesWithPSLE($gradeAnalysis, $genderTotals);
        
        return [
            'total_students' => $genderTotals['T'],
            'gender_totals' => $genderTotals,
            'grade_analysis' => $gradeAnalysis,
            'performance_categories' => $performanceCategories,
        ];
    }

    public function coreSubjectsClassListsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $coreSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'originalStudent.psle',
                        'finalKlasses' => function($subQuery) {
                            $subQuery->where('active', true);
                        }
                    ])->orderBy('first_name')->orderBy('last_name');
                }
            ])->where('graduation_year', $selectedTerm->year)->where('active', true)->orderBy('final_klass_id')->get();
    
            $coreSubjects = $coreSubjects->filter(function($coreSubject) {
                return $coreSubject->finalKlass && $coreSubject->finalKlass->finalStudents->isNotEmpty();
            });
    
            if ($coreSubjects->isEmpty()) {
                return redirect()->back()->with('info', 'No core subjects with students found for the selected graduation year.');
            }
    
            $classListsData = $this->processCoreSubjectsClassListsWithPSLE($coreSubjects);
            $schoolData = DB::table('school_setup')->first();
            $exportData = [
                'class_lists' => $classListsData['class_lists'],
                'summary' => $classListsData['summary'],
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.core.core-class-lists-analysis', $exportData);
        } catch (\Exception $e) {
            Log::error('Core Subjects Class Lists Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate core subjects class lists analysis: ' . $e->getMessage());
        }
    }
    
    private function processCoreSubjectsClassListsWithPSLE($coreSubjects) {
        $classLists = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $studentsWithPSLE = 0;
        $studentsWithoutPSLE = 0;
    
        foreach ($coreSubjects as $coreSubject) {
            $students = $coreSubject->finalKlass->finalStudents;
            $studentsList = [];
    
            foreach ($students as $student) {
                $className = $coreSubject->finalKlass->name ?? 'No Class Assigned';
                $psleData = [
                    'overall_grade' => null,
                    'mathematics_grade' => null,
                    'english_grade' => null,
                    'science_grade' => null,
                    'setswana_grade' => null,
                    'agriculture_grade' => null,
                    'social_studies_grade' => null
                ];
                
                $hasPSLE = false;
                
                if ($student->originalStudent && $student->originalStudent->psle) {
                    $psle = $student->originalStudent->psle;
                    $psleData = [
                        'overall_grade' => $psle->overall_grade,
                        'mathematics_grade' => $psle->mathematics_grade,
                        'english_grade' => $psle->english_grade,
                        'science_grade' => $psle->science_grade,
                        'setswana_grade' => $psle->setswana_grade,
                        'agriculture_grade' => $psle->agriculture_grade,
                        'social_studies_grade' => $psle->social_studies_grade
                    ];
                    $hasPSLE = !is_null($psle->overall_grade);
                }
    
                $studentsList[] = [
                    'exam_number' => $student->exam_number ?? 'N/A',
                    'full_name' => $student->full_name,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'gender' => $student->gender,
                    'class_name' => $className,
                    'psle_overall_grade' => $psleData['overall_grade'],
                    'psle_mathematics_grade' => $psleData['mathematics_grade'],
                    'psle_english_grade' => $psleData['english_grade'],
                    'psle_science_grade' => $psleData['science_grade'],
                    'psle_setswana_grade' => $psleData['setswana_grade'],
                    'psle_agriculture_grade' => $psleData['agriculture_grade'],
                    'psle_social_studies_grade' => $psleData['social_studies_grade'],
                    'has_psle' => $hasPSLE
                ];
    
                $totalStudents++;
                if ($hasPSLE) {
                    $studentsWithPSLE++;
                } else {
                    $studentsWithoutPSLE++;
                }
            }
    
            usort($studentsList, function($a, $b) {
                if ($a['exam_number'] === 'N/A' && $b['exam_number'] !== 'N/A') return 1;
                if ($a['exam_number'] !== 'N/A' && $b['exam_number'] === 'N/A') return -1;
                if ($a['exam_number'] !== 'N/A' && $b['exam_number'] !== 'N/A') {
                    return strcmp($a['exam_number'], $b['exam_number']);
                }
                return strcmp($a['first_name'] . ' ' . $a['last_name'], $b['first_name'] . ' ' . $b['last_name']);
            });
    
            $classLists[] = [
                'klass_subject_id' => $coreSubject->id,
                'class_name' => $coreSubject->finalKlass->name ?? 'Unknown Class',
                'subject_name' => $coreSubject->finalGradeSubject->subject->name ?? 'Unknown Subject',
                'teacher_name' => $coreSubject->teacher->full_name ?? 'Not Assigned',
                'teacher_id' => $coreSubject->teacher->id ?? null,
                'department_name' => $coreSubject->finalGradeSubject->department->name ?? 'No Department',
                'grade_name' => $coreSubject->finalKlass->grade->name ?? 'Unknown Grade',
                'is_mandatory' => $coreSubject->finalGradeSubject->mandatory ?? true,
                'total_students' => $students->count(),
                'male_students' => $students->where('gender', 'M')->count(),
                'female_students' => $students->where('gender', 'F')->count(),
                'students_with_psle' => $studentsList ? count(array_filter($studentsList, function($s) { return $s['has_psle']; })) : 0,
                'students_without_psle' => $studentsList ? count(array_filter($studentsList, function($s) { return !$s['has_psle']; })) : 0,
                'students_list' => $studentsList,
                'graduation_year' => $coreSubject->graduation_year
            ];
    
            $totalClasses++;
        }
    
        return [
            'class_lists' => $classLists,
            'summary' => [
                'total_core_classes' => $totalClasses,
                'total_students' => $totalStudents,
                'students_with_psle' => $studentsWithPSLE,
                'students_without_psle' => $studentsWithoutPSLE,
                'psle_coverage_percentage' => $totalStudents > 0 ? round(($studentsWithPSLE / $totalStudents) * 100, 1) : 0
            ]
        ];
    }

}
