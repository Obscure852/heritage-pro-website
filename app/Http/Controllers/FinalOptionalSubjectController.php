<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Models\FinalOptionalSubject;
use App\Models\FinalGradeSubject;
use App\Models\ExternalExamResult;
use App\Models\FinalStudent;
use App\Models\Term;
use App\Helpers\TermHelper;
use App\Models\FinalKlassSubject;
use DB;
use Log;

class FinalOptionalSubjectController extends Controller{
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
        $reportMenu = $this->finalsReportMenu($finalsDefinition, 'optionals', [
            'year' => $selectedYear,
        ]);

        return view('finals.optionals.index', compact(
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
        $query = FinalGradeSubject::with([
            'subject',
            'grade',
            'finalOptionalSubjects' => function($q) use ($year) {
                $q->where('active', true);
                if ($year) {
                    $q->where('final_optional_subjects.graduation_year', $year);
                }
            },
            'finalOptionalSubjects.finalKlasses.finalStudents',
            'finalOptionalSubjects.finalStudents',
            'finalOptionalSubjects.teacher',
            'finalOptionalSubjects.venue'
        ]);
        $this->scopeFinalsQuery($query, 'final_grade_subjects', $finalsDefinition);
    
        // Only get grade subjects that have optional subjects with students
        $gradeSubjects = $query->whereHas('finalOptionalSubjects', function($q) {
            $q->whereHas('finalStudents'); // This ensures only optional subjects with students are included
        })->orderBy('grade_id')->get()->groupBy(function($gradeSubject) {
            return $gradeSubject->grade->name . ' - ' . $gradeSubject->subject->name;
        });
    
        $groupedData = $gradeSubjects->map(function ($gradeSubjectGroup, $groupName) use ($finalsDefinition) {
            $gradeSubject = $gradeSubjectGroup->first();
            
            // Filter optional subjects to only include those with students
            $optionalSubjects = $gradeSubject->finalOptionalSubjects->filter(function($subject) {
                return $subject->finalStudents->count() > 0;
            });
    
            $totalSubjects = $optionalSubjects->count();
            $totalStudents = $optionalSubjects->sum(function($subject) {
                return $subject->finalStudents->count();
            });
    
            // Skip this group if there are no students
            if ($totalStudents == 0) {
                return null;
            }
    
            $allStudentIds = $optionalSubjects->flatMap->finalStudents->pluck('id');
            $examResults = ExternalExamResult::whereIn('final_student_id', $allStudentIds)
                ->whereHas('externalExam', function ($query) use ($finalsDefinition) {
                    $query->where('exam_type', $finalsDefinition->examType);
                })
                ->whereHas('subjectResults', function($q) use ($gradeSubject) {
                    $q->where('final_grade_subject_id', $gradeSubject->id);
                })
                ->with('subjectResults')
                ->get();
    
            $studentsWithResults = $examResults->count();
            $passedStudents = $examResults->filter(function($result) {
                return $result->subjectResults->where('is_pass', true)->isNotEmpty();
            })->count();
    
            $passRate = $studentsWithResults > 0 ? round(($passedStudents / $studentsWithResults) * 100, 1) : 0;
    
            return [
                'group_name' => $groupName,
                'grade_subject' => $gradeSubject,
                'total_subjects' => $totalSubjects,
                'total_students' => $totalStudents,
                'students_with_results' => $studentsWithResults,
                'students_pending' => $totalStudents - $studentsWithResults,
                'pass_rate' => $passRate,
                'optional_subjects' => $optionalSubjects->map(function($optionalSubject) {
                    return [
                        'id' => $optionalSubject->id,
                        'name' => $optionalSubject->name,
                        'teacher' => $optionalSubject->teacher->full_name ?? 'Not Assigned',
                        'venue' => $optionalSubject->venue->name ?? 'Not Assigned',
                        'grouping' => $optionalSubject->grouping,
                        'total_students' => $optionalSubject->finalStudents->count(),
                        'classes' => $optionalSubject->finalKlasses->map(function($klass) {
                            return [
                                'id' => $klass->id,
                                'name' => $klass->name,
                                'student_count' => $klass->finalStudents->count()
                            ];
                        })
                    ];
                })
            ];
        })->filter(); // Remove null values (groups with no students)
    
        return view('finals.optionals.partial.optionals-partial', compact('groupedData', 'finalsDefinition'))->render();
    }

    public function getBadgeData(Request $request){
        $year = $request->get('year');
        $badgeData = $this->calculateBadgeData($year, $this->finalsDefinition($request));

        return response()->json($badgeData);
    }

    private function calculateBadgeData(?int $year = null, $finalsDefinition = null): array{
        $finalsDefinition ??= $this->finalsDefinition();
        $base = FinalOptionalSubject::query()->where('active', true);
        $this->scopeFinalsQuery($base, 'final_optional_subjects', $finalsDefinition);

        if ($year) {
            $base->where('final_optional_subjects.graduation_year', $year);
        }

        $totalOptionalSubjects = (clone $base)->count();
        $subjectsPending = (clone $base)
            ->whereDoesntHave('finalStudents.externalExamResults', function ($query) use ($finalsDefinition) {
                $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
            })
            ->count();

        return [
            'totalOptionalSubjects' => $totalOptionalSubjects,
            'subjectsPending'       => $subjectsPending,
        ];
    }

    public function show(Request $request, FinalOptionalSubject $optionalSubject){
        $finalsDefinition = $this->finalsDefinition($request);
        abort_unless($finalsDefinition->matchesGradeName(optional($optionalSubject->grade)->name), 404);

        $optionalSubject->load([
            'teacher',
            'finalGradeSubject.subject',
            'finalGradeSubject.grade',
            'graduationTerm',
            'finalStudents.externalExamResults' => function ($query) use ($finalsDefinition) {
                $this->scopeFinalsQuery($query, 'external_exam_results', $finalsDefinition);
                $query->with('subjectResults');
            },
            'finalKlasses'
        ]);

        $students = $optionalSubject->finalStudents;
        $examResults = $students->flatMap->externalExamResults;
        $subjectResults = $examResults->flatMap->subjectResults->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id);
        
        $stats = [
            'total_students' => $students->count(),
            'students_with_results' => $subjectResults->count(),
            'students_pending' => $students->count() - $subjectResults->count(),
            'passed_students' => $subjectResults->where('is_pass', true)->count(),
            'pass_rate' => $subjectResults->count() > 0 ? round(($subjectResults->where('is_pass', true)->count() / $subjectResults->count()) * 100, 1) : 0,
            'average_points' => $subjectResults->count() > 0 ? round($subjectResults->avg('grade_points'), 1) : 0
        ];

        $gradeDistribution = $subjectResults->groupBy('grade')->map->count()->sortKeys();
        return view('finals.optional-subjects.show', compact('optionalSubject', 'stats', 'gradeDistribution'));
    }

    public function showStudents($id){
        $optionalSubject = FinalOptionalSubject::with([
            'finalGradeSubject.subject',
            'finalGradeSubject.grade',
            'teacher',
            'finalStudents.finalKlasses' => function($query) {
                $query->where('active', true);
            }
        ])->findOrFail($id);

        $students = $optionalSubject->finalStudents()->with([
                'finalKlasses' => function($query) {
                    $query->where('active', true);
                },
                'graduationGrade'
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'Alumni')->count();
        $maleStudents = $students->where('gender', 'M')->count();
        $femaleStudents = $students->where('gender', 'F')->count();

        return view('finals.optionals.optional-class-view', compact(
            'optionalSubject',
            'students',
            'totalStudents',
            'activeStudents',
            'maleStudents',
            'femaleStudents'
        ));
    }

    public function manageStudents(FinalOptionalSubject $optionalSubject){
        $optionalSubject->load(['finalStudents', 'finalGradeSubject.grade', 'graduationTerm']);
        
        $availableStudents = FinalStudent::where('graduation_year', $optionalSubject->graduation_year)
            ->where('graduation_grade_id', $optionalSubject->grade_id)
            ->whereDoesntHave('finalOptionalSubjects', function ($query) use ($optionalSubject) {
                $query->where('final_optional_subject_id', $optionalSubject->id);
            })->get();

        return view('finals.optional-subjects.manage-students', compact('optionalSubject', 'availableStudents'));
    }

    public function addStudents(Request $request, FinalOptionalSubject $optionalSubject){
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:final_students,id',
            'klass_id' => 'required|exists:final_klasses,id'
        ]);

        foreach ($request->student_ids as $studentId) {
            $optionalSubject->finalStudents()->attach($studentId, [
                'graduation_term_id' => $optionalSubject->graduation_term_id,
                'final_klass_id' => $request->klass_id,
                'graduation_year' => $optionalSubject->graduation_year,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->route('finals.optional-subjects.show', $optionalSubject)->with('message', 'Students added to optional subject successfully.');
    }

    public function removeStudent(FinalOptionalSubject $optionalSubject, FinalStudent $student){
        $optionalSubject->finalStudents()->detach($student->id);
        return redirect()->route('finals.optional-subjects.show', $optionalSubject)->with('message', 'Student removed from optional subject successfully.');
    }

    public function generateReport(FinalOptionalSubject $optionalSubject){
        $optionalSubject->load([
            'teacher',
            'finalGradeSubject.subject',
            'finalGradeSubject.grade',
            'graduationTerm',
            'finalStudents.externalExamResults.subjectResults',
            'finalKlasses'
        ]);

        $students = $optionalSubject->finalStudents;
        $subjectResults = $students->flatMap->externalExamResults
            ->flatMap->subjectResults
            ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id);
        
        $reportData = [
            'optional_subject' => $optionalSubject,
            'stats' => [
                'total_students' => $students->count(),
                'students_with_results' => $subjectResults->count(),
                'passed_students' => $subjectResults->where('is_pass', true)->count(),
                'failed_students' => $subjectResults->where('is_pass', false)->count(),
                'pass_rate' => $subjectResults->count() > 0 ? round(($subjectResults->where('is_pass', true)->count() / $subjectResults->count()) * 100, 1) : 0,
                'average_points' => $subjectResults->count() > 0 ? round($subjectResults->avg('grade_points'), 1) : 0
            ],
            'grade_distribution' => $subjectResults->groupBy('grade')->map->count()->sortKeys(),
            'class_breakdown' => $optionalSubject->finalKlasses->map(function($klass) {
                return [
                    'name' => $klass->name,
                    'student_count' => $klass->finalStudents->count()
                ];
            })
        ];

        return view('finals.optional-subjects.report', compact('reportData'));
    }

    public function export(Request $request, FinalOptionalSubject $optionalSubject){
        $format = $request->get('format', 'excel');
        $optionalSubject->load([
            'teacher',
            'finalGradeSubject.subject',
            'finalStudents.externalExamResults.subjectResults'
        ]);

        $students = $optionalSubject->finalStudents->map(function ($student) use ($optionalSubject) {
            $subjectResult = $student->externalExamResults
                ->flatMap->subjectResults
                ->where('final_grade_subject_id', $optionalSubject->final_grade_subject_id)
                ->first();
                
            return [
                'name' => $student->full_name,
                'exam_number' => $student->exam_number,
                'gender' => $student->gender,
                'subject_grade' => $subjectResult->grade ?? 'No Result',
                'subject_points' => $subjectResult->grade_points ?? 0,
                'pass_status' => $subjectResult && $subjectResult->is_pass ? 'Pass' : 'Fail'
            ];
        });

        switch ($format) {
            case 'csv':
                return $this->exportToCSV($optionalSubject, $students);
            case 'pdf':
                return $this->exportToPDF($optionalSubject, $students);
            default:
                return $this->exportToExcel($optionalSubject, $students);
        }
    }


    private function exportToCSV($optionalSubject, $students){
        $filename = "optional_subject_{$optionalSubject->name}_students_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Exam Number', 'Gender', 'Subject Grade', 'Subject Points', 'Pass Status']);
            
            foreach ($students as $student) {
                fputcsv($file, [
                    $student['name'],
                    $student['exam_number'],
                    $student['gender'],
                    $student['subject_grade'],
                    $student['subject_points'],
                    $student['pass_status']
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($optionalSubject, $students){
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }

    private function exportToPDF($optionalSubject, $students){
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }

    public function optionalSubjectsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $optionalFinalKlassSubjects = FinalKlassSubject::with([
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
            ])->whereHas('finalGradeSubject', function($query) {
                $query->where('mandatory', false)
                      ->where('type', 0);
            })->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $optionalSubjects = FinalOptionalSubject::with([
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
                $query->where('mandatory', false)
                      ->where('type', 0);
            })->where('graduation_year', $selectedTerm->year)->where('active', true)->get();
    
            $optionalFinalKlassSubjects = $optionalFinalKlassSubjects->filter(function($klassSubject) {
                return $klassSubject->finalKlass->finalStudents->some(function($student) {
                    return $student->externalExamResults->isNotEmpty() && 
                           $student->externalExamResults->some(function($examResult) {
                               return $examResult->subjectResults->isNotEmpty();
                           });
                });
            });
    
            $optionalSubjects = $optionalSubjects->filter(function($optionalSubject) {
                return $optionalSubject->finalStudents->some(function($student) {
                    return $student->externalExamResults->isNotEmpty() && 
                           $student->externalExamResults->some(function($examResult) {
                               return $examResult->subjectResults->isNotEmpty();
                           });
                });
            });
    
            if ($optionalFinalKlassSubjects->isEmpty() && $optionalSubjects->isEmpty()) {
                return redirect()->back()->with('info', 'No optional subjects with external exam results found for the selected graduation year.');
            }
    
            $optionalSubjectsAnalysis = $this->processSubjectGroupsWithPSLE($optionalFinalKlassSubjects, $optionalSubjects, 'Optional Subjects');
    
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
                'optional_subjects_analysis' => $optionalSubjectsAnalysis['subject_analysis'],
                'optional_subjects_summary' => $optionalSubjectsAnalysis['summary'],
                'optional_subjects_chart_data' => $this->prepareChartDataWithPSLE($optionalSubjectsAnalysis['subject_analysis'], 'optional'), // ← CHANGE: Use PSLE-enabled chart data
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.optionals.optional-subjects-analysis', $exportData);
            
        } catch (\Exception $e) {
            Log::error('Optional Subject Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate optional subject analysis: ' . $e->getMessage());
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
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubjectSubjectGroups($studentsWithResults, $psleSubjectMapping[$subjectName]);
            } else {
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

    public function optionalSubjectsDepartmentAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $optionalSubjects = FinalOptionalSubject::with([
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
                $query->where('mandatory', false)
                      ->where('type', 0);
            })->where('graduation_year', $selectedTerm->year)
              ->where('active', true)
              ->get();
    
            $optionalSubjects = $optionalSubjects->filter(function($optionalSubject) {
                return $optionalSubject->finalStudents->some(function($student) {
                    return $student->externalExamResults->isNotEmpty() && 
                           $student->externalExamResults->some(function($examResult) {
                               return $examResult->subjectResults->isNotEmpty();
                           });
                });
            });
    
            if ($optionalSubjects->isEmpty()) {
                return redirect()->back()->with('info', 'No optional subjects with external exam results found for the selected graduation year.');
            }
    
            $optionalSubjectsByDepartment = $this->processOptionalSubjectsByDepartmentWithPSLE($optionalSubjects);
            $chartData = $this->prepareDepartmentChartData($optionalSubjectsByDepartment['department_analysis']);
            
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
                'departments_analysis' => $optionalSubjectsByDepartment['department_analysis'],
                'departments_summary' => $optionalSubjectsByDepartment['summary'],
                'chart_data' => $chartData,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.optionals.optional-subjects-department-analysis', $exportData);
            
        } catch (\Exception $e) {
            Log::error('Optional Subjects Department Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate optional subjects department analysis: ' . $e->getMessage());
        }
    }
    
    
    private function processOptionalSubjectsByDepartmentWithPSLE($optionalSubjects) {
        $allSubjects = collect();
        
        foreach ($optionalSubjects as $optionalSubject) {
            $analysis = $this->processOptionalSubjectGradesWithPSLEForDepartment($optionalSubject);
            if ($analysis) {
                foreach ($analysis as $row) {
                    $row['department_name'] = $optionalSubject->finalGradeSubject->department->name ?? 'No Department';
                    $row['department_id'] = $optionalSubject->finalGradeSubject->department->id ?? 0;
                    $row['subject_name'] = $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject';
                    $allSubjects->push($row);
                }
            }
        }
        
        $departmentGroups = $allSubjects->groupBy('department_name');
        $departmentAnalysis = [];
        $totalClasses = 0;
        $totalSubjects = 0;
        $totalDepartments = 0;
        
        foreach ($departmentGroups as $departmentName => $departmentSubjects) {
            $subjectGroups = $departmentSubjects->groupBy('subject_name');
            
            $processedSubjects = [];
            $departmentTotalClasses = 0;
            
            foreach ($subjectGroups as $subjectName => $subjectClasses) {
                $sortedClasses = $subjectClasses->sortBy(['class_name', 'row_type'])->values();
                
                $processedSubjects[$subjectName] = [
                    'subject_name' => $subjectName,
                    'klass_subjects' => $sortedClasses->toArray(),
                    'total_classes' => $sortedClasses->where('row_type', 'OUTPUT')->count()
                ];
                $departmentTotalClasses += $sortedClasses->where('row_type', 'OUTPUT')->count();
            }
            
            $departmentAnalysis[$departmentName] = [
                'department_name' => $departmentName,
                'subjects' => $processedSubjects,
                'total_subjects' => count($processedSubjects),
                'total_classes' => $departmentTotalClasses
            ];
            
            $totalClasses += $departmentTotalClasses;
            $totalSubjects += count($processedSubjects);
            $totalDepartments++;
        }
        
        ksort($departmentAnalysis);
        
        return [
            'department_analysis' => $departmentAnalysis,
            'summary' => [
                'total_departments' => $totalDepartments,
                'total_subjects' => $totalSubjects,
                'total_classes' => $totalClasses
            ]
        ];
    }
    
    private function processOptionalSubjectGradesWithPSLEForDepartment($optionalSubject){
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
            
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubjectSubjectGroups($studentsWithResults, $psleSubjectMapping[$subjectName]);
            } else {
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
            Log::error('Error processing optional subject grades with PSLE for department analysis', [
                'optional_subject_id' => $optionalSubject->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    private function prepareDepartmentChartData($departmentsAnalysis) {
        $chartData = [
            'departments' => [],
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
    
        foreach ($departmentsAnalysis as $departmentName => $departmentData) {
            $chartData['departments'][] = $departmentName;
            
            $deptABTotal = 0;
            $deptABCTotal = 0; 
            $deptDEUTotal = 0;
            $deptGradeTotals = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0];
            $totalOutputClasses = 0;
    
            foreach ($departmentData['subjects'] ?? [] as $subjectName => $subjectData) {
                foreach ($subjectData['klass_subjects'] ?? [] as $klassSubject) {
                    if (($klassSubject['row_type'] ?? 'OUTPUT') === 'OUTPUT') {
                        $deptABTotal += $klassSubject['performance_categories']['AB']['T'] ?? 0;
                        $deptABCTotal += $klassSubject['performance_categories']['ABC']['T'] ?? 0;
                        $deptDEUTotal += $klassSubject['performance_categories']['DEU']['T'] ?? 0;
                        
                        foreach ($deptGradeTotals as $grade => $count) {
                            $deptGradeTotals[$grade] += $klassSubject['grade_analysis'][$grade]['T'] ?? 0;
                        }
                        
                        $totalOutputClasses++;
                    }
                }
            }
    
            if ($totalOutputClasses > 0) {
                $chartData['ab_percentages'][] = round($deptABTotal / $totalOutputClasses, 1);
                $chartData['abc_percentages'][] = round($deptABCTotal / $totalOutputClasses, 1);
                $chartData['deu_percentages'][] = round($deptDEUTotal / $totalOutputClasses, 1);
                
                foreach ($deptGradeTotals as $grade => $total) {
                    $chartData['grade_distributions'][$grade][] = round($total / $totalOutputClasses, 1);
                }
            } else {
                $chartData['ab_percentages'][] = 0;
                $chartData['abc_percentages'][] = 0;
                $chartData['deu_percentages'][] = 0;
                
                foreach (array_keys($deptGradeTotals) as $grade) {
                    $chartData['grade_distributions'][$grade][] = 0;
                }
            }
        }
    
        return $chartData;
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

    public function optionalSubjectsTeacherAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $optionalSubjects = FinalOptionalSubject::with([
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
                $query->where('mandatory', false)
                      ->where('type', 0);
            })->where('graduation_year', $selectedTerm->year)
              ->where('active', true)
              ->get();
    
            $optionalSubjects = $optionalSubjects->filter(function($optionalSubject) {
                return $optionalSubject->finalStudents->some(function($student) {
                    return $student->externalExamResults->isNotEmpty() && 
                           $student->externalExamResults->some(function($examResult) {
                               return $examResult->subjectResults->isNotEmpty();
                           });
                });
            });
    
            if ($optionalSubjects->isEmpty()) {
                return redirect()->back()->with('info', 'No optional subjects with external exam results found for the selected graduation year.');
            }
    
            $optionalSubjectsByTeacher = $this->processOptionalSubjectsByTeacherWithPSLE($optionalSubjects);
            $chartData = $this->prepareTeacherChartDataWithPSLE($optionalSubjectsByTeacher['teacher_analysis']);
            $schoolData = DB::table('school_setup')->first();
    
            $exportData = [
                'teachers_analysis' => $optionalSubjectsByTeacher['teacher_analysis'],
                'teachers_summary' => $optionalSubjectsByTeacher['summary'],
                'chart_data' => $chartData,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.optionals.optional-subjects-teachers-analysis', $exportData);
        } catch (\Exception $e) {
            Log::error('Optional Subjects Teacher Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate optional subjects teacher analysis: ' . $e->getMessage());
        }
    }

    private function prepareTeacherChartDataWithPSLE($teachersAnalysis) {
        $chartData = [
            'teachers' => [],
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
            ],
            'teacher_details' => []
        ];
    
        foreach ($teachersAnalysis as $teacherName => $teacherData) {
            $chartData['teachers'][] = $teacherName;
            $outputSubjects = array_filter($teacherData['teacher_subjects'] ?? [], function($subject) {
                return ($subject['row_type'] ?? 'OUTPUT') === 'OUTPUT';
            });
            
            $totalSubjects = count($outputSubjects);
            $totalAB = 0;
            $totalABC = 0;
            $totalDEU = 0;
            $gradeTotal = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0];
            
            foreach ($outputSubjects as $teacherSubject) {
                $totalAB += $teacherSubject['performance_categories']['AB']['T'] ?? 0;
                $totalABC += $teacherSubject['performance_categories']['ABC']['T'] ?? 0;
                $totalDEU += $teacherSubject['performance_categories']['DEU']['T'] ?? 0;
                
                foreach ($gradeTotal as $grade => $count) {
                    $gradeTotal[$grade] += $teacherSubject['grade_analysis'][$grade]['T'] ?? 0;
                }
            }
            
            $chartData['ab_percentages'][] = $totalSubjects > 0 ? round($totalAB / $totalSubjects, 1) : 0;
            $chartData['abc_percentages'][] = $totalSubjects > 0 ? round($totalABC / $totalSubjects, 1) : 0;
            $chartData['deu_percentages'][] = $totalSubjects > 0 ? round($totalDEU / $totalSubjects, 1) : 0;
            
            foreach ($gradeTotal as $grade => $count) {
                $chartData['grade_distributions'][$grade][] = $totalSubjects > 0 ? round($count / $totalSubjects, 1) : 0;
            }
            
            $chartData['teacher_details'][$teacherName] = [
                'total_classes' => $teacherData['total_classes'] ?? 0,
                'total_students' => $teacherData['total_students'] ?? 0,
                'departments' => $teacherData['departments'] ?? [],
                'subjects' => $teacherData['teacher_subjects'] ?? []
            ];
        }
        
        return $chartData;
    }

    private function processOptionalSubjectsByTeacherWithPSLE($optionalSubjects) {
        $allSubjects = collect();
        
        foreach ($optionalSubjects as $optionalSubject) {
            $analysis = $this->processOptionalSubjectGradesWithPSLE($optionalSubject);
            if ($analysis) {
                foreach ($analysis as $row) {
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
            $sortedSubjects = $teacherSubjects->sortBy([
                'class_name', 
                'subject_name', 
                function($item) {
                    return ($item['row_type'] ?? 'OUTPUT') === 'OUTPUT' ? 0 : 1;
                }
            ])->values();
            
            $departments = $sortedSubjects->pluck('department_name')->unique()->filter()->values();
            $outputRows = $sortedSubjects->where('row_type', 'OUTPUT');
            
            $teacherAnalysis[$teacherName] = [
                'teacher_name' => $teacherName,
                'teacher_subjects' => $sortedSubjects->toArray(),
                'departments' => $departments->toArray(),
                'total_classes' => $outputRows->count(),
                'total_students' => $outputRows->sum('total_students'),
                'teacher_type' => 'Optional Subjects'
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
                'type' => 'Optional Subjects'
            ]
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
            if (isset($psleSubjectMapping[$subjectName])) {
                $psleAnalysis = $this->processPSLEGradesForSubject($studentsWithResults, $psleSubjectMapping[$subjectName]);
            } else {
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

    public function optionalSubjectsClassListsAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $optionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'grade',
                'finalGradeSubject.subject',
                'finalGradeSubject.department',
                'finalStudents' => function($query) {
                    $query->with([
                        'originalStudent.psle',
                        'finalKlasses' => function($subQuery) {
                            $subQuery->where('active', true);
                        }
                    ])->orderBy('first_name')->orderBy('last_name');
                }
            ])->whereHas('finalGradeSubject', function($query) {
                $query->where('mandatory', false)
                      ->where('type', 0);
            })->where('graduation_year', $selectedTerm->year)
              ->where('active', true)
              ->orderBy('name')
              ->get();
    
            $optionalSubjects = $optionalSubjects->filter(function($optionalSubject) {
                return $optionalSubject->finalStudents->isNotEmpty();
            });
    
            if ($optionalSubjects->isEmpty()) {
                return redirect()->back()->with('info', 'No optional subjects with students found for the selected graduation year.');
            }
    
            $classListsData = $this->processOptionalSubjectsClassListsWithPSLE($optionalSubjects);
            $schoolData = DB::table('school_setup')->first();
    
            $exportData = [
                'class_lists' => $classListsData['class_lists'],
                'summary' => $classListsData['summary'],
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now()
            ];
    
            return view('finals.optionals.optional-class-lists-analysis', $exportData);
        } catch (\Exception $e) {
            Log::error('Optional Subjects Class Lists Analysis Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate optional subjects class lists analysis: ' . $e->getMessage());
        }
    }
    
    private function processOptionalSubjectsClassListsWithPSLE($optionalSubjects) {
        $classLists = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $studentsWithPSLE = 0;
        $studentsWithoutPSLE = 0;
    
        foreach ($optionalSubjects as $optionalSubject) {
            $students = $optionalSubject->finalStudents;
            $studentsList = [];
    
            foreach ($students as $student) {
                $className = optional($student->finalKlasses->first())->name ?? 'No Class Assigned';
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
                'optional_subject_id' => $optionalSubject->id,
                'optional_subject_name' => $optionalSubject->name,
                'subject_name' => $optionalSubject->finalGradeSubject->subject->name ?? 'Unknown Subject',
                'teacher_name' => $optionalSubject->teacher->full_name ?? 'Not Assigned',
                'teacher_id' => $optionalSubject->teacher->id ?? null,
                'department_name' => $optionalSubject->finalGradeSubject->department->name ?? 'No Department',
                'grade_name' => $optionalSubject->grade->name ?? 'Unknown Grade',
                'total_students' => $students->count(),
                'male_students' => $students->where('gender', 'M')->count(),
                'female_students' => $students->where('gender', 'F')->count(),
                'students_with_psle' => $studentsList ? count(array_filter($studentsList, function($s) { return $s['has_psle']; })) : 0,
                'students_without_psle' => $studentsList ? count(array_filter($studentsList, function($s) { return !$s['has_psle']; })) : 0,
                'students_list' => $studentsList,
                'graduation_year' => $optionalSubject->graduation_year
            ];
    
            $totalClasses++;
        }
    
        return [
            'class_lists' => $classLists,
            'summary' => [
                'total_optional_classes' => $totalClasses,
                'total_students' => $totalStudents,
                'students_with_psle' => $studentsWithPSLE,
                'students_without_psle' => $studentsWithoutPSLE,
                'psle_coverage_percentage' => $totalStudents > 0 ? round(($studentsWithPSLE / $totalStudents) * 100, 1) : 0
            ]
        ];
    }

}
