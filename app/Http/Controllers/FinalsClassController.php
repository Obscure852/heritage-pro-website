<?php

namespace App\Http\Controllers;

use App\Exports\JcePsleComparisonExport;
use App\Exports\TopPerformingClassesExport;
use App\Helpers\TermHelper;
use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Models\FinalKlass;
use Illuminate\Http\Request;
use App\Models\ExternalExamResult;
use App\Models\FinalStudent;
use App\Models\PerformanceTarget;
use App\Models\Term;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FinalsClassController extends Controller{
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
        $reportMenu = $this->finalsReportMenu($finalsDefinition, 'classes', [
            'year' => $selectedYear,
        ]);

        return view('finals.classes.index', compact(
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
        $query = FinalKlass::with([
            'teacher',
            'grade',
            'graduationTerm',
            'finalStudents.externalExamResults' => function($query) use ($finalsDefinition) {
                $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
                $query->with(['subjectResults', 'externalExam']); // Load externalExam for calculated_overall_grade accessor
            }
        ]);

        $this->scopeFinalsQuery($query, 'final_klasses', $finalsDefinition);

        if ($year) {
            $query->where('graduation_year', $year);
        }

        $classes = $query->orderBy('grade_id')->orderBy('name')->get();
        $classesData = $classes->map(function ($klass) use ($finalsDefinition) {
            $examResults = $klass->finalStudents
                ->flatMap->externalExamResults
                ->filter(fn ($result) => ($result->externalExam->exam_type ?? null) === $finalsDefinition->examType);

            $totalStudents = $klass->finalStudents->count();
            $studentsWithResults = $examResults->count();
            
            // Count MABC students using stored overall_grade or calculated_overall_grade fallback
            $passedStudents = $examResults->filter(function ($result) {
                $grade = $result->overall_grade ?: $result->calculated_overall_grade;
                return in_array($grade, ['Merit', 'A', 'B', 'C']);
            })->count();
            $passRate = $studentsWithResults > 0 ? round(($passedStudents / $studentsWithResults) * 100, 1) : 0;
            
            $totalPoints = $examResults->sum(function ($result) {
                return $result->subjectResults->sum('grade_points');
            });
            $averagePoints = $studentsWithResults > 0 ? round($totalPoints / $studentsWithResults, 1) : 0;

            return [
                'id' => $klass->id,
                'name' => $klass->name,
                'grade' => $klass->grade->name ?? 'Unknown',
                'teacher' => $klass->teacher->full_name ?? 'Not Assigned',
                'total_students' => $totalStudents,
                'students_with_results' => $studentsWithResults,
                'students_pending' => $totalStudents - $studentsWithResults,
                'pass_rate' => $passRate,
                'average_points' => $averagePoints,
                'graduation_year' => $klass->graduation_year,
                'graduation_term' => $klass->graduationTerm->name ?? 'Unknown'
            ];
        });

        return view('finals.classes.partial.classes-partial', compact('classesData', 'finalsDefinition'))->render();
    }

    public function getBadgeData(Request $request){
        $year = $request->get('year');
        $badgeData = $this->calculateBadgeData($year, $this->finalsDefinition($request));
        return response()->json($badgeData);
    }

    private function calculateBadgeData(?int $year = null, $finalsDefinition = null): array{
        $finalsDefinition ??= $this->finalsDefinition();
        $base = FinalKlass::query();
        $this->scopeFinalsQuery($base, 'final_klasses', $finalsDefinition);

        if ($year) {
            $base->where('graduation_year', $year);
        }

        $totalResults = (clone $base)->whereHas('finalStudents.externalExamResults', function ($query) use ($finalsDefinition) {
            $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
        })->count();
        $pendingResults = (clone $base)->count() - $totalResults;

        return [
            'totalResults'   => $totalResults,
            'pendingResults' => $pendingResults,
        ];
    }

    public function show(Request $request, FinalKlass $klass){
        $finalsDefinition = $this->finalsDefinition($request);
        abort_unless($finalsDefinition->matchesGradeName(optional($klass->grade)->name), 404);

        $klass->load([
            'teacher',
            'grade',
            'graduationTerm',
            'finalStudents.externalExamResults' => function ($query) use ($finalsDefinition) {
                $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
                $query->with('subjectResults');
            },
            'finalKlassSubjects.finalGradeSubject.subject'
        ]);

        $students = $klass->finalStudents;
        $examResults = $students->flatMap->externalExamResults
            ->filter(fn ($result) => ($result->externalExam->exam_type ?? null) === $finalsDefinition->examType);
        
        $stats = [
            'total_students' => $students->count(),
            'students_with_results' => $examResults->count(),
            'students_pending' => $students->count() - $examResults->count(),
            'passed_students' => $examResults->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count(),
            'pass_rate' => $examResults->count() > 0 ? round(($examResults->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count() / $examResults->count()) * 100, 1) : 0,
            'average_points' => $examResults->count() > 0 ? round($examResults->avg(function ($result) {
                return $result->subjectResults->sum('grade_points');
            }), 1) : 0
        ];

        $subjectPerformance = $examResults->flatMap->subjectResults
            ->groupBy('subject_code')
            ->map(function ($results, $subjectCode) {
                $total = $results->count();
                $passed = $results->where('is_pass', true)->count();
                $averagePoints = $results->avg('grade_points');
                
                return [
                    'subject_code' => $subjectCode,
                    'subject_name' => optional($results->first())->subject_name ?? 'Unknown',
                    'total_students' => $total,
                    'passed_students' => $passed,
                    'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
                    'average_points' => round($averagePoints, 1)
                ];
            })->sortBy('subject_name');

        $availableStudents = FinalStudent::where('graduation_year', $klass->graduation_year)
            ->where('graduation_grade_id', $klass->grade_id)
            ->whereDoesntHave('finalKlasses', function ($query) use ($klass) {
                $query->where('final_klass_id', $klass->id);
            });
        $this->scopeFinalsQuery($availableStudents, 'final_students', $finalsDefinition);
        $availableStudents = $availableStudents->get();

        return view('finals.classes.finals-class-list', compact('klass', 'stats', 'subjectPerformance', 'availableStudents', 'finalsDefinition'));
    }

    public function removeStudent($klassId, $studentId){
        $klass = FinalKlass::findOrFail($klassId);
        $student = FinalStudent::findOrFail($studentId);
        
        try {
            $isInClass = $klass->finalStudents()->where('final_student_id', $student->id)->exists();
            if (!$isInClass) {
                return redirect()->back()->with('error', 'Student is not assigned to this class.');
            }

            $examResults = $student->externalExamResults()->with('subjectResults')->get();
            $hasResults = $examResults->isNotEmpty();
            $resultsCount = $examResults->count();
            $subjectResultsCount = $examResults->sum(function($result) {
                return $result->subjectResults->count();
            });

            DB::beginTransaction();

            try {
                if ($hasResults) {
                    foreach ($examResults as $examResult) {
                        $examResult->subjectResults()->delete();
                        $examResult->delete();
                    }
                }

                $klass->finalStudents()->detach($student->id);
                DB::commit();
                Log::warning('Student and all exam results removed from finals class', [
                    'class_id' => $klass->id,
                    'class_name' => $klass->name,
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'had_exam_results' => $hasResults,
                    'exam_results_deleted' => $resultsCount,
                    'subject_results_deleted' => $subjectResultsCount,
                    'removed_by' => auth()->id(),
                    'removed_at' => now()
                ]);

                $message = "Student {$student->full_name} has been removed from {$klass->name}.";
                if ($hasResults) {
                    $message .= " All {$resultsCount} exam result(s) and {$subjectResultsCount} subject result(s) have been permanently deleted.";
                }

                return redirect()->back()->with('message', $message);

            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error removing student and results from finals class', [
                'class_id' => $klass->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 
                'An error occurred while removing the student and their results. Please try again.');
        }
    }

    public function generateReport(FinalKlass $klass){
        $klass->load([
            'teacher',
            'grade',
            'graduationTerm',
            'finalStudents.externalExamResults.subjectResults',
            'finalKlassSubjects.finalGradeSubject.subject'
        ]);

        $students = $klass->finalStudents;
        $examResults = $students->flatMap->externalExamResults;
        
        $reportData = [
            'class' => $klass,
            'stats' => [
                'total_students' => $students->count(),
                'students_with_results' => $examResults->count(),
                'passed_students' => $examResults->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count(),
                'failed_students' => $examResults->whereNotIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count(),
                'pass_rate' => $examResults->count() > 0 ? round(($examResults->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count() / $examResults->count()) * 100, 1) : 0,
                'average_points' => $examResults->count() > 0 ? round($examResults->avg(function ($result) {
                    return $result->subjectResults->sum('grade_points');
                }), 1) : 0
            ],
            'grade_distribution' => $examResults->groupBy('overall_grade')->map->count(),
            'subject_performance' => $examResults->flatMap->subjectResults
                ->groupBy('subject_code')
                ->map(function ($results, $subjectCode) {
                    $total = $results->count();
                    $passed = $results->where('is_pass', true)->count();
                    return [
                        'subject_code' => $subjectCode,
                        'subject_name' => optional($results->first())->subject_name ?? 'Unknown',
                        'total_students' => $total,
                        'passed_students' => $passed,
                        'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
                        'average_points' => round($results->avg('grade_points'), 1)
                    ];
                })->sortBy('subject_name')
        ];

        return view('finals.classes.report', compact('reportData'));
    }

    public function overallAnalysis(Request $request){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $query = FinalKlass::with([
                'teacher',
                'grade',
                'graduationTerm',
                'finalStudents' => function($query) {
                    $query->with(['externalExamResults' => function($subQuery) {
                        $subQuery->with('externalExam'); // Load for calculated_overall_grade accessor
                    }]);
                }
            ])->where('graduation_year', $selectedTerm->year);
    
            $classes = $query->orderBy('grade_id')->orderBy('name')->get();
            $classesAnalysis = $classes->map(function ($klass) {
                $studentsWithResults = $klass->finalStudents->filter(function ($student) {
                    return $student->externalExamResults->isNotEmpty();
                });
    
                $totalWithResults = $studentsWithResults->count();
                
                if ($totalWithResults === 0) {
                    return null;
                }
    
                $gradeAnalysis = [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];
    
                $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
                foreach ($studentsWithResults as $student) {
                    $latestResult = $student->externalExamResults->first();
                    // Use stored overall_grade, fallback to calculated_overall_grade
                    $grade = $latestResult ? ($latestResult->overall_grade ?: $latestResult->calculated_overall_grade) : null;
                    if ($latestResult && $grade) {
                        $gender = $student->gender;
    
                        if (isset($gradeAnalysis[$grade])) {
                            $gradeAnalysis[$grade][$gender]++;
                            $gradeAnalysis[$grade]['T']++;
                        }
    
                        $genderTotals[$gender]++;
                        $genderTotals['T']++;
                    }
                }
    
                $percentageCategories = [
                    'MAB' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];
    
                foreach (['Merit', 'A', 'B'] as $grade) {
                    $percentageCategories['MAB']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['MAB']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['MAB']['T'] += $gradeAnalysis[$grade]['T'];
                }
    
                foreach (['Merit', 'A', 'B', 'C'] as $grade) {
                    $percentageCategories['ABC']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['ABC']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['ABC']['T'] += $gradeAnalysis[$grade]['T'];
                }
    
                foreach (['D', 'E', 'U'] as $grade) {
                    $percentageCategories['DEU']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['DEU']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['DEU']['T'] += $gradeAnalysis[$grade]['T'];
                }
    
                $percentageAnalysis = [];
                foreach ($percentageCategories as $category => $counts) {
                    $percentageAnalysis[$category] = [
                        'M' => $genderTotals['M'] > 0 ? round(($counts['M'] / $genderTotals['M']) * 100, 1) : 0,
                        'F' => $genderTotals['F'] > 0 ? round(($counts['F'] / $genderTotals['F']) * 100, 1) : 0,
                        'T' => $genderTotals['T'] > 0 ? round(($counts['T'] / $genderTotals['T']) * 100, 1) : 0,
                        'counts' => $counts
                    ];
                }
    
                return [
                    'id' => $klass->id,
                    'name' => $klass->name,
                    'teacher' => $klass->teacher->full_name ?? 'Not Assigned',
                    'grade_name' => $klass->grade->name ?? 'Unknown',
                    'total_with_results' => $totalWithResults,
                    'gender_totals' => $genderTotals,
                    'grade_analysis' => $gradeAnalysis,
                    'percentage_analysis' => $percentageAnalysis,
                    'graduation_year' => $klass->graduation_year
                ];
                
            })->filter(function ($analysis) {
                return $analysis !== null && $analysis['total_with_results'] > 0;
            })->sortBy('name')->values();
    
            $exportData = [
                'classes' => $classesAnalysis,
                'year' => $selectedTerm->year,
                'generated_at' => now(),
                'total_classes' => $classesAnalysis->count(),
                'total_students_analyzed' => $classesAnalysis->sum('total_with_results')
            ];

            if ($request->query('export') === 'excel') {
                try {
                    Log::info('Starting Excel export', [
                        'user_id' => auth()->id(),
                        'classes_count' => $classesAnalysis->count(),
                        'students_count' => $classesAnalysis->sum('total_with_results'),
                        'year' => $selectedTerm->year
                    ]);
    
                    $filename = 'overall_analysis_report_' . $selectedTerm->year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
                    $export = new TopPerformingClassesExport($exportData);
                    return Excel::download($export, $filename);
                    
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    Log::error('PhpSpreadsheet Excel Export Error', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'user_id' => auth()->id()
                    ]);
                    
                    return redirect()->back()->with('error', 'Excel generation failed: ' . $e->getMessage());
                    
                } catch (Exception $e) {
                    Log::error('General Excel Export Error', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        'user_id' => auth()->id()
                    ]);
                    
                    return redirect()->back()->with('error', 'Export failed: Please try again or contact support if the problem persists.');
                }
            }
            
            return view('finals.analysis.top-performing-classes-analysis', $exportData);
            
        } catch (Exception $e) {
            Log::error('Overall Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate analysis. Please try again.');
        }
    }

    public function overallPerformanceAnalysis(Request $request){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $examType = $request->get('exam_type', 'JCE');
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
            
            $school_data = DB::table('school_setup')->first();
            if (!$school_data) {
                $school_data = (object) [
                    'name' => 'School Name Not Set',
                    'type' => 'Unknown'
                ];
            }

            $query = FinalKlass::with([
                'teacher',
                'grade',
                'graduationTerm',
                'finalStudents' => function($query) {
                    $query->with(['externalExamResults' => function($subQuery) {
                        $subQuery->with('externalExam'); // Load for calculated_overall_grade accessor
                    }]);
                }
            ])->where('graduation_year', $selectedTerm->year);

            $classes = $query->orderBy('grade_id')->orderBy('name')->get();
            
            $classesAnalysis = [];
            $totalStudentsAnalyzed = 0;
            $schoolWideGrades = [
                'Merit' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 
                'D' => 0, 'E' => 0, 'U' => 0
            ];
            
            foreach ($classes as $klass) {
                $studentsWithResults = $klass->finalStudents->filter(function ($student) {
                    return $student->externalExamResults->isNotEmpty();
                });

                $totalWithResults = $studentsWithResults->count();
                if ($totalWithResults === 0) {
                    continue;
                }

                $gradeAnalysis = [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];

                $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
                foreach ($studentsWithResults as $student) {
                    $latestResult = $student->externalExamResults->first();
                    // Use stored overall_grade, fallback to calculated_overall_grade
                    $grade = $latestResult ? ($latestResult->overall_grade ?: $latestResult->calculated_overall_grade) : null;
                    if ($latestResult && $grade) {
                        $gender = $student->gender;

                        if (isset($gradeAnalysis[$grade])) {
                            $gradeAnalysis[$grade][$gender]++;
                            $gradeAnalysis[$grade]['T']++;
                        }

                        $genderTotals[$gender]++;
                        $genderTotals['T']++;
                        
                        if (isset($schoolWideGrades[$grade])) {
                            $schoolWideGrades[$grade]++;
                        }
                    }
                }

                $percentageCategories = [
                    'MAB' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];

                foreach (['Merit', 'A', 'B'] as $grade) {
                    $percentageCategories['MAB']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['MAB']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['MAB']['T'] += $gradeAnalysis[$grade]['T'];
                }

                foreach (['Merit', 'A', 'B', 'C'] as $grade) {
                    $percentageCategories['ABC']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['ABC']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['ABC']['T'] += $gradeAnalysis[$grade]['T'];
                }

                foreach (['D', 'E', 'U'] as $grade) {
                    $percentageCategories['DEU']['M'] += $gradeAnalysis[$grade]['M'];
                    $percentageCategories['DEU']['F'] += $gradeAnalysis[$grade]['F'];
                    $percentageCategories['DEU']['T'] += $gradeAnalysis[$grade]['T'];
                }

                $percentageAnalysis = [];
                foreach ($percentageCategories as $category => $counts) {
                    $percentageAnalysis[$category] = [
                        'M' => $genderTotals['M'] > 0 ? round(($counts['M'] / $genderTotals['M']) * 100, 1) : 0,
                        'F' => $genderTotals['F'] > 0 ? round(($counts['F'] / $genderTotals['F']) * 100, 1) : 0,
                        'T' => $genderTotals['T'] > 0 ? round(($counts['T'] / $genderTotals['T']) * 100, 1) : 0,
                        'counts' => $counts
                    ];
                }

                $classAnalysis = [
                    'id' => $klass->id,
                    'name' => $klass->name,
                    'teacher' => $klass->teacher->full_name ?? 'Not Assigned',
                    'grade_name' => $klass->grade->name ?? 'Unknown',
                    'total_with_results' => $totalWithResults,
                    'gender_totals' => $genderTotals,
                    'grade_analysis' => $gradeAnalysis,
                    'percentage_analysis' => $percentageAnalysis,
                    'graduation_year' => $klass->graduation_year
                ];

                $classesAnalysis[] = $classAnalysis;
                $totalStudentsAnalyzed += $totalWithResults;
            }

            $schoolWideMetrics = $this->calculateSchoolWideMetrics($schoolWideGrades, $totalStudentsAnalyzed);
            $targets = PerformanceTarget::getTargetsForYear($selectedTerm->year, $examType);
            $performanceComparison = [
                'high_achievement' => [
                    'actual' => $schoolWideMetrics['high_achievement_percent'],
                    'target' => $targets['high_achievement']['target'],
                    'variance' => $schoolWideMetrics['high_achievement_percent'] - $targets['high_achievement']['target'],
                    'status' => $schoolWideMetrics['high_achievement_percent'] >= $targets['high_achievement']['target'] ? 'achieved' : 'not_achieved',
                    'label' => $targets['high_achievement']['label']
                ],
                'pass_rate' => [
                    'actual' => $schoolWideMetrics['pass_rate_percent'],
                    'target' => $targets['pass_rate']['target'],
                    'variance' => $schoolWideMetrics['pass_rate_percent'] - $targets['pass_rate']['target'],
                    'status' => $schoolWideMetrics['pass_rate_percent'] >= $targets['pass_rate']['target'] ? 'achieved' : 'not_achieved',
                    'label' => $targets['pass_rate']['label']
                ],
                'non_failure' => [
                    'actual' => $schoolWideMetrics['non_failure_percent'],
                    'target' => $targets['non_failure']['target'],
                    'variance' => $schoolWideMetrics['non_failure_percent'] - $targets['non_failure']['target'],
                    'status' => $schoolWideMetrics['non_failure_percent'] >= $targets['non_failure']['target'] ? 'achieved' : 'not_achieved',
                    'label' => $targets['non_failure']['label']
                ]
            ];

            $chartData = [
                'scatter_data' => [
                    [
                        'category' => $targets['high_achievement']['label'],
                        'target' => $targets['high_achievement']['target'],
                        'actual' => $schoolWideMetrics['high_achievement_percent'],
                        'label' => 'High Achievement'
                    ],
                    [
                        'category' => $targets['pass_rate']['label'],
                        'target' => $targets['pass_rate']['target'],
                        'actual' => $schoolWideMetrics['pass_rate_percent'],
                        'label' => 'Pass Rate'
                    ],
                    [
                        'category' => $targets['non_failure']['label'],
                        'target' => $targets['non_failure']['target'],
                        'actual' => $schoolWideMetrics['non_failure_percent'],
                        'label' => 'Non-Failure Rate'
                    ]
                ],
                'bar_data' => [
                    [
                        'category' => $targets['high_achievement']['label'],
                        'percentage' => $schoolWideMetrics['high_achievement_percent'],
                        'count' => $schoolWideMetrics['high_achievement_count']
                    ],
                    [
                        'category' => $targets['pass_rate']['label'],
                        'percentage' => $schoolWideMetrics['pass_rate_percent'],
                        'count' => $schoolWideMetrics['pass_rate_count']
                    ],
                    [
                        'category' => $targets['non_failure']['label'],
                        'percentage' => $schoolWideMetrics['non_failure_percent'],
                        'count' => $schoolWideMetrics['non_failure_count']
                    ]
                ]
            ];

            $sortedClasses = collect($classesAnalysis)->sortByDesc(function ($class) {
                return $class['percentage_analysis']['ABC']['T'] ?? 0;
            });

            $topPerformingClasses = $sortedClasses->take(5)->values();
            $classesNeedingIntervention = $sortedClasses->filter(function ($class) {
                return ($class['percentage_analysis']['ABC']['T'] ?? 0) < 40;
            })->sortBy(function ($class) {
                return $class['percentage_analysis']['ABC']['T'] ?? 0;
            })->take(5)->values();

            return view('finals.analysis.external-overall-summary-analysis', [
                'classes' => collect($classesAnalysis),
                'school_wide_metrics' => $schoolWideMetrics,
                'targets' => $targets,
                'performance_comparison' => $performanceComparison,
                'chart_data' => $chartData,
                'top_performing_classes' => $topPerformingClasses,
                'classes_needing_intervention' => $classesNeedingIntervention,
                'year' => $selectedTerm->year,
                'exam_type' => $examType,
                'total_classes' => count($classesAnalysis),
                'total_students_analyzed' => $totalStudentsAnalyzed,
                'grade_distribution' => $schoolWideGrades,
                'school_data' => $school_data,
                'generated_at' => now()
            ]);
            
        } catch (Exception $e) {
            Log::error('Comprehensive Performance Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate performance analysis. Please try again.');
        }
    }

    private function calculateSchoolWideMetrics($gradeDistribution, $totalStudents){
        if ($totalStudents === 0) {
            return [
                'high_achievement_percent' => 0,
                'pass_rate_percent' => 0,
                'non_failure_percent' => 0,
                'failure_rate_percent' => 0,
                'high_achievement_count' => 0,
                'pass_rate_count' => 0,
                'non_failure_count' => 0,
                'failure_rate_count' => 0
            ];
        }

        $highAchievement = $gradeDistribution['Merit'] + $gradeDistribution['A'] + $gradeDistribution['B'];
        $passRate = $highAchievement + $gradeDistribution['C'];
        $nonFailure = $passRate + $gradeDistribution['D'];
        $failureRate = $gradeDistribution['E'] + $gradeDistribution['U'];

        return [
            'high_achievement_percent' => round(($highAchievement / $totalStudents) * 100, 1),
            'pass_rate_percent' => round(($passRate / $totalStudents) * 100, 1),
            'non_failure_percent' => round(($nonFailure / $totalStudents) * 100, 1),
            'failure_rate_percent' => round(($failureRate / $totalStudents) * 100, 1),
            'high_achievement_count' => $highAchievement,
            'pass_rate_count' => $passRate,
            'non_failure_count' => $nonFailure,
            'failure_rate_count' => $failureRate
        ];
    }

    public function jcePsleComparison(Request $request, $classId) {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);

            $klass = FinalKlass::with([
                'teacher',
                'grade',
                'graduationTerm',
                'finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults' => function($subQuery) {
                            $subQuery->with('externalExam'); // Load for calculated_overall_grade accessor
                        },
                        'originalStudent.psle'
                    ]);
                }
            ])->where('graduation_year', $selectedTerm->year)->findOrFail($classId);
    
            $studentsWithBothResults = $klass->finalStudents->filter(function ($student) {
                return $student->externalExamResults->isNotEmpty() && 
                       $student->originalStudent && 
                       $student->originalStudent->psle;
            });
    
            $totalWithBothResults = $studentsWithBothResults->count();
            if ($totalWithBothResults === 0) {
                return redirect()->back()->with('error', 'No students found with both JCE and PSLE results for this class.');
            }
    
            $jceGradeAnalysis = [
                'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
    
            $psleGradeAnalysis = [
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
    
            $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
            foreach ($studentsWithBothResults as $student) {
                $jceResult = $student->externalExamResults->first();
                $psleResult = optional($student->originalStudent)->psle;
                $gender = $student->gender;
    
                // Use stored overall_grade, fallback to calculated_overall_grade
                $jceGrade = $jceResult ? ($jceResult->overall_grade ?: $jceResult->calculated_overall_grade) : null;
                if ($jceResult && $jceGrade) {
                    if (isset($jceGradeAnalysis[$jceGrade])) {
                        $jceGradeAnalysis[$jceGrade][$gender]++;
                        $jceGradeAnalysis[$jceGrade]['T']++;
                    }
                }
    
                if ($psleResult && $psleResult->overall_grade) {
                    $psleGrade = $psleResult->overall_grade;
                    if (isset($psleGradeAnalysis[$psleGrade])) {
                        $psleGradeAnalysis[$psleGrade][$gender]++;
                        $psleGradeAnalysis[$psleGrade]['T']++;
                    }
                }
    
                $genderTotals[$gender]++;
                $genderTotals['T']++;
            }
    
            $jceCategories = $this->calculateJcePerformanceCategories($jceGradeAnalysis, $genderTotals);
            $psleCategories = $this->calculatePslePerformanceCategories($psleGradeAnalysis, $genderTotals);
            $performanceComparison = $this->calculatePerformanceComparison($jceCategories, $psleCategories);
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

            $classInfo = [
                'id' => $klass->id,
                'name' => $klass->name,
                'teacher' => $klass->teacher->full_name ?? 'Not Assigned',
                'grade_name' => $klass->grade->name ?? 'Unknown',
                'total_with_both_results' => $totalWithBothResults,
                'gender_totals' => $genderTotals,
                'jce_grade_analysis' => $jceGradeAnalysis,
                'psle_grade_analysis' => $psleGradeAnalysis,
                'jce_categories' => $jceCategories,
                'psle_categories' => $psleCategories,
                'performance_comparison' => $performanceComparison,
                'graduation_year' => $klass->graduation_year
            ];
    
            $exportData = [
                'class_info' => $classInfo,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now(),
                'students_analyzed' => $totalWithBothResults
            ];

            return view('finals.analysis.jce-psle-comparison-analysis', $exportData);
        } catch (Exception $e) {
            Log::error('JCE-PSLE Comparison Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'class_id' => $classId
            ]);
            return redirect()->back()->with('error', 'Failed to generate comparison analysis. Please try again.');
        }
    }
    
    private function calculateJcePerformanceCategories($gradeAnalysis, $genderTotals) {
        $categories = [
            'High_Achievement' => ['M' => 0, 'F' => 0, 'T' => 0],
            'Pass_Rate' => ['M' => 0, 'F' => 0, 'T' => 0],
            'Failure_Rate' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
    
        foreach (['Merit', 'A', 'B'] as $grade) {
            $categories['High_Achievement']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['High_Achievement']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['High_Achievement']['T'] += $gradeAnalysis[$grade]['T'];
        }
    
        foreach (['Merit', 'A', 'B', 'C'] as $grade) {
            $categories['Pass_Rate']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['Pass_Rate']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['Pass_Rate']['T'] += $gradeAnalysis[$grade]['T'];
        }
    
        foreach (['D', 'E', 'U'] as $grade) {
            $categories['Failure_Rate']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['Failure_Rate']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['Failure_Rate']['T'] += $gradeAnalysis[$grade]['T'];
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
    
    private function calculatePslePerformanceCategories($gradeAnalysis, $genderTotals) {
        $categories = [
            'High_Achievement' => ['M' => 0, 'F' => 0, 'T' => 0],
            'Pass_Rate' => ['M' => 0, 'F' => 0, 'T' => 0],
            'Failure_Rate' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
    
        foreach (['A', 'B'] as $grade) {
            $categories['High_Achievement']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['High_Achievement']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['High_Achievement']['T'] += $gradeAnalysis[$grade]['T'];
        }
    
        foreach (['A', 'B', 'C'] as $grade) {
            $categories['Pass_Rate']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['Pass_Rate']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['Pass_Rate']['T'] += $gradeAnalysis[$grade]['T'];
        }
    
        foreach (['D', 'E'] as $grade) {
            $categories['Failure_Rate']['M'] += $gradeAnalysis[$grade]['M'];
            $categories['Failure_Rate']['F'] += $gradeAnalysis[$grade]['F'];
            $categories['Failure_Rate']['T'] += $gradeAnalysis[$grade]['T'];
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
    
    private function calculatePerformanceComparison($jceCategories, $psleCategories) {
        $comparison = [];
        foreach (['High_Achievement', 'Pass_Rate', 'Failure_Rate'] as $category) {
            $comparison[$category] = [
                'M' => round($jceCategories[$category]['M'] - $psleCategories[$category]['M'], 1),
                'F' => round($jceCategories[$category]['F'] - $psleCategories[$category]['F'], 1),
                'T' => round($jceCategories[$category]['T'] - $psleCategories[$category]['T'], 1),
            ];
        }
        return $comparison;
    }

    public function gradeJcePsleComparison() {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $allStudentsWithBothResults = FinalStudent::with([
                'externalExamResults' => function($subQuery) {
                    $subQuery->with('externalExam'); // Load for calculated_overall_grade accessor
                },
                'originalStudent.psle',
                'finalKlasses' => function($query) {
                    $query->with(['teacher', 'grade']);
                }
            ])->where('graduation_year', $selectedTerm->year)->get()->filter(function ($student) {
                return $student->externalExamResults->isNotEmpty() && 
                       $student->originalStudent && 
                       $student->originalStudent->psle;
            });
    
            $totalWithBothResults = $allStudentsWithBothResults->count();
            if ($totalWithBothResults === 0) {
                return redirect()->back()->with('error', 'No students found with both JCE and PSLE results for this graduation year.');
            }

            $jceGradeAnalysis = [
                'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
    
            $psleGradeAnalysis = [
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
    
            $genderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
            $classByClassAnalysis = [];
    
            $studentsByClass = $allStudentsWithBothResults->groupBy(function($student) {
                $finalKlass = $student->finalKlasses->first();
                return $finalKlass ? $finalKlass->id : 'unknown';
            });
    
            foreach ($studentsByClass as $classId => $studentsInClass) {
                if ($classId === 'unknown') continue;

                $firstStudent = $studentsInClass->first();
                $finalKlass = $firstStudent ? optional($firstStudent->finalKlasses)->first() : null;
                if (!$finalKlass) continue;
                $classJceAnalysis = [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];
    
                $classPsleAnalysis = [
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];
    
                $classGenderTotals = ['M' => 0, 'F' => 0, 'T' => 0];
    
                foreach ($studentsInClass as $student) {
                    $jceResult = $student->externalExamResults->first();
                    $psleResult = optional($student->originalStudent)->psle;
                    $gender = $student->gender;
    
                    // Use stored overall_grade, fallback to calculated_overall_grade
                    $jceGrade = $jceResult ? ($jceResult->overall_grade ?: $jceResult->calculated_overall_grade) : null;
                    if ($jceResult && $jceGrade) {
                        if (isset($jceGradeAnalysis[$jceGrade])) {
                            $jceGradeAnalysis[$jceGrade][$gender]++;
                            $jceGradeAnalysis[$jceGrade]['T']++;
                            
                            $classJceAnalysis[$jceGrade][$gender]++;
                            $classJceAnalysis[$jceGrade]['T']++;
                        }
                    }
    
                    if ($psleResult && $psleResult->overall_grade) {
                        $psleGrade = $psleResult->overall_grade;
                        if (isset($psleGradeAnalysis[$psleGrade])) {
                            $psleGradeAnalysis[$psleGrade][$gender]++;
                            $psleGradeAnalysis[$psleGrade]['T']++;
                            
                            $classPsleAnalysis[$psleGrade][$gender]++;
                            $classPsleAnalysis[$psleGrade]['T']++;
                        }
                    }
    
                    $genderTotals[$gender]++;
                    $genderTotals['T']++;
                    $classGenderTotals[$gender]++;
                    $classGenderTotals['T']++;
                }
    
                $classJceCategories = $this->calculateJcePerformanceCategories($classJceAnalysis, $classGenderTotals);
                $classPsleCategories = $this->calculatePslePerformanceCategories($classPsleAnalysis, $classGenderTotals);
                $classPerformanceComparison = $this->calculatePerformanceComparison($classJceCategories, $classPsleCategories);
    
                $classByClassAnalysis[] = [
                    'id' => $finalKlass->id,
                    'name' => $finalKlass->name,
                    'teacher' => $finalKlass->teacher->full_name ?? 'Not Assigned',
                    'grade_name' => $finalKlass->grade->name ?? 'Unknown',
                    'total_students' => $studentsInClass->count(),
                    'gender_totals' => $classGenderTotals,
                    'jce_categories' => $classJceCategories,
                    'psle_categories' => $classPsleCategories,
                    'performance_comparison' => $classPerformanceComparison
                ];
            }
    
            $schoolJceCategories = $this->calculateJcePerformanceCategories($jceGradeAnalysis, $genderTotals);
            $schoolPsleCategories = $this->calculatePslePerformanceCategories($psleGradeAnalysis, $genderTotals);
            $schoolPerformanceComparison = $this->calculatePerformanceComparison($schoolJceCategories, $schoolPsleCategories);
    
            $chartData = $this->prepareJcePsleChartData($jceGradeAnalysis, $psleGradeAnalysis, $schoolJceCategories, $schoolPsleCategories, $classByClassAnalysis);
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
    
            $schoolInfo = [
                'total_with_both_results' => $totalWithBothResults,
                'gender_totals' => $genderTotals,
                'jce_grade_analysis' => $jceGradeAnalysis,
                'psle_grade_analysis' => $psleGradeAnalysis,
                'jce_categories' => $schoolJceCategories,
                'psle_categories' => $schoolPsleCategories,
                'performance_comparison' => $schoolPerformanceComparison,
                'graduation_year' => $selectedTerm->year,
                'total_classes_analyzed' => count($classByClassAnalysis)
            ];
    
            $exportData = [
                'school_info' => $schoolInfo,
                'class_by_class_analysis' => $classByClassAnalysis,
                'chart_data' => $chartData,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now(),
                'students_analyzed' => $totalWithBothResults
            ];

            return view('finals.analysis.jce-psle-grade-comparison-analysis', $exportData);
        } catch (Exception $e) {
            Log::error('School-wide JCE-PSLE Comparison Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate school-wide comparison analysis. Please try again.');
        }
    }
    
    private function prepareJcePsleChartData($jceGradeAnalysis, $psleGradeAnalysis, $jceCategories, $psleCategories, $classByClassAnalysis) {
        $gradeDistributionData = [
            'jce' => [],
            'psle' => [],
            'labels' => []
        ];
    
        $commonGrades = ['A', 'B', 'C', 'D', 'E'];
        foreach ($commonGrades as $grade) {
            $gradeDistributionData['labels'][] = $grade;
            $gradeDistributionData['jce'][] = $jceGradeAnalysis[$grade]['T'];
            $gradeDistributionData['psle'][] = $psleGradeAnalysis[$grade]['T'];
        }
    
        $performanceCategoriesData = [
            'categories' => ['High Achievement', 'Pass Rate', 'Failure Rate'],
            'jce' => [
                $jceCategories['High_Achievement']['T'],
                $jceCategories['Pass_Rate']['T'],
                $jceCategories['Failure_Rate']['T']
            ],
            'psle' => [
                $psleCategories['High_Achievement']['T'],
                $psleCategories['Pass_Rate']['T'],
                $psleCategories['Failure_Rate']['T']
            ]
        ];
    
        $classPerformanceData = [
            'class_names' => [],
            'jce_pass_rates' => [],
            'psle_pass_rates' => [],
            'performance_changes' => []
        ];
    
        foreach ($classByClassAnalysis as $classData) {
            $classPerformanceData['class_names'][] = $classData['name'];
            $classPerformanceData['jce_pass_rates'][] = $classData['jce_categories']['Pass_Rate']['T'];
            $classPerformanceData['psle_pass_rates'][] = $classData['psle_categories']['Pass_Rate']['T'];
            $classPerformanceData['performance_changes'][] = $classData['performance_comparison']['Pass_Rate']['T'];
        }
    
        $genderPerformanceData = [
            'categories' => ['MAB%', 'MABC%', 'DEU%'],
            'jce_male' => [
                $jceCategories['High_Achievement']['M'],
                $jceCategories['Pass_Rate']['M'],
                $jceCategories['Failure_Rate']['M']
            ],
            'jce_female' => [
                $jceCategories['High_Achievement']['F'],
                $jceCategories['Pass_Rate']['F'],
                $jceCategories['Failure_Rate']['F']
            ],
            'psle_male' => [
                $psleCategories['High_Achievement']['M'],
                $psleCategories['Pass_Rate']['M'],
                $psleCategories['Failure_Rate']['M']
            ],
            'psle_female' => [
                $psleCategories['High_Achievement']['F'],
                $psleCategories['Pass_Rate']['F'],
                $psleCategories['Failure_Rate']['F']
            ]
        ];
    
        return [
            'grade_distribution' => $gradeDistributionData,
            'performance_categories' => $performanceCategoriesData,
            'class_performance' => $classPerformanceData,
            'gender_performance' => $genderPerformanceData
        ];
    }
    
}
