<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Models\FinalGradeSubject;
use App\Models\FinalKlass;
use Exception;
use App\Models\Term;
use App\Models\Department;
use App\Helpers\TermHelper;
use App\Models\FinalHouse;
use App\Models\FinalKlassSubject;
use App\Models\FinalOptionalSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;

class FinalGradeSubjectController extends Controller{
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
        $reportClasses = $this->getReportClassesByYear($selectedYear, $finalsDefinition);
        $activeReportClassId = $reportClasses->first()['id'] ?? null;

        $schoolModeResolver = $this->schoolModeResolver();
        $finalsContext = $this->finalsContext($request);
        $reportMenu = $this->finalsReportMenu($finalsDefinition, 'subjects', [
            'year' => $selectedYear,
            'class_id' => $activeReportClassId,
        ]);

        return view('finals.subjects.index', compact(
            'availableYears',
            'selectedYear',
            'badgeData',
            'reportClasses',
            'activeReportClassId',
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
            'department',
            'graduationTerm',
            'finalKlassSubjects' => function($q) use ($year) {
                $q->where('active', true);
                if ($year) {
                    $q->where('graduation_year', $year);
                }
            },
            'finalKlassSubjects.finalKlass',
            'finalKlassSubjects.teacher',
            'finalOptionalSubjects' => function($q) use ($year) {
                $q->where('active', true);
                if ($year) {
                    $q->where('graduation_year', $year);
                }
            },
            'finalOptionalSubjects.finalStudents'
        ]);

        $this->scopeFinalsQuery($query, 'final_grade_subjects', $finalsDefinition);
    
        if ($year) {
            $query->where('graduation_year', $year);
        }
    
        $gradeSubjects = $query->orderBy('grade_id')
            ->orderBy('mandatory', 'desc')
            ->get()->groupBy(function($gradeSubject) {
                return $gradeSubject->grade->name;
            });
    
        $groupedData = $gradeSubjects->map(function ($gradeSubjectGroup, $gradeName) {
            return [
                'grade_name' => $gradeName,
                'grade_subjects' => $gradeSubjectGroup->map(function($gradeSubject) {
                    $totalClasses = $gradeSubject->type == 1 
                        ? $gradeSubject->finalKlassSubjects->count()
                        : $gradeSubject->finalOptionalSubjects->count();
                    
                    $totalOptionalSubjects = $gradeSubject->finalOptionalSubjects->count();
                    
                    return [
                        'id' => $gradeSubject->id,
                        'subject_name' => $gradeSubject->subject->name,
                        'subject_code' => $gradeSubject->subject->code,
                        'department' => $gradeSubject->department->name ?? 'No Department',
                        'mandatory' => $gradeSubject->mandatory,
                        'type' => $gradeSubject->type,
                        'graduation_year' => $gradeSubject->graduation_year,
                        'graduation_term' => $gradeSubject->graduationTerm->name,
                        'total_classes' => $totalClasses,
                        'total_optional_subjects' => $totalOptionalSubjects,
                        'classes' => $gradeSubject->finalKlassSubjects->map(function($klassSubject) {
                            return [
                                'id' => $klassSubject->id,
                                'klass_name' => $klassSubject->finalKlass->name,
                                'teacher' => $klassSubject->teacher->full_name ?? 'Not Assigned',
                                'active' => $klassSubject->active
                            ];
                        }),
                        'optional_subjects' => $gradeSubject->finalOptionalSubjects->map(function($optionalSubject) {
                            return [
                                'id' => $optionalSubject->id,
                                'name' => $optionalSubject->name,
                                'student_count' => $optionalSubject->finalStudents->count()
                            ];
                        })
                    ];
                })
            ];
        });
    
        return view('finals.subjects.partial.subjects-partial', compact('groupedData', 'finalsDefinition'))->render();
    }

    public function getBadgeData(Request $request){
        $year = $request->get('year');
        return response()->json($this->calculateBadgeData($year, $this->finalsDefinition($request)));
    }

    public function getReportClasses(Request $request){
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:9999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $classes = $this->getReportClassesByYear((int) $validator->validated()['year'], $this->finalsDefinition($request));

        return response()->json([
            'classes' => $classes,
        ]);
    }

    private function calculateBadgeData(?int $year, $finalsDefinition): array{
        $baseQuery = FinalGradeSubject::query();
        $this->scopeFinalsQuery($baseQuery, 'final_grade_subjects', $finalsDefinition);

        if ($year) {
            $baseQuery->where('graduation_year', $year);
        }

        $total      = (clone $baseQuery)->count();
        $mandatory  = (clone $baseQuery)->where('type', 1)->count();
        $optional   = (clone $baseQuery)->where('type', 0)->count();

        return [
            'total'     => $total,
            'mandatory' => $mandatory,
            'optional'  => $optional,
        ];
    }

    private function getReportClassesByYear(int $year, $finalsDefinition){
        $query = FinalKlass::query()
            ->with('grade:id,name')
            ->where('graduation_year', $year)
            ->whereNull('deleted_at')
            ->orderBy('name');
        $this->scopeFinalsQuery($query, 'final_klasses', $finalsDefinition);

        return $query->get(['id', 'name', 'grade_id', 'graduation_year'])
            ->map(function ($klass) {
                return [
                    'id' => (int) $klass->id,
                    'name' => $klass->name,
                    'grade_name' => $klass->grade->name ?? 'Unknown',
                    'graduation_year' => (int) $klass->graduation_year,
                ];
            })
            ->values();
    }


    public function getByGrade(Request $request){
        $gradeId = $request->get('grade_id');
        $graduationTermId = $request->get('graduation_term_id');
        
        $query = FinalGradeSubject::with('subject')
            ->where('grade_id', $gradeId);
            
        if ($graduationTermId) {
            $query->where('graduation_term_id', $graduationTermId);
        }
        
        $gradeSubjects = $query->orderBy('mandatory', 'desc')
            ->get()
            ->map(function ($gradeSubject) {
                return [
                    'id' => $gradeSubject->id,
                    'name' => $gradeSubject->subject->name,
                    'code' => $gradeSubject->subject->code ?? '',
                    'mandatory' => $gradeSubject->mandatory,
                    'type' => $gradeSubject->type,
                ];
            });

        return response()->json($gradeSubjects);
    }
    
    public function edit(FinalGradeSubject $finalGradeSubject){
        $finalGradeSubject->load([
            'subject',
            'grade',
            'department',
            'graduationTerm'
        ]);
        
        $departments = Department::orderBy('name')->get();
        
        return view('finals.subjects.edit', compact(
            'finalGradeSubject',
            'departments'
        ));
    }

    public function update(Request $request, FinalGradeSubject $finalGradeSubject){
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:0,1',
            'mandatory' => 'nullable|boolean',
        ]);
                 
        $validated['grade_id'] = $finalGradeSubject->grade_id;
        $validated['subject_id'] = $finalGradeSubject->subject_id;
        $validated['graduation_term_id'] = $finalGradeSubject->graduation_term_id;
                 
        $validated['mandatory'] = $request->boolean('mandatory');
        $finalGradeSubject->update($validated);
        return redirect()->back()->with('message', 'Final grade subject updated successfully');
    }

    public function subjectGenderGradesReport(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
    
            $finalGradeSubjects = FinalGradeSubject::with([
                'subject',
                'grade',
                'department',
                'externalExamSubjectResults' => function($query) {
                    $query->with(['externalExamResult.finalStudent'])
                          ->where('is_mapped', true)
                          ->whereNotNull('grade');
                }
            ])->where('graduation_year', $selectedTerm->year)->get();
    
            $subjectPerformance = [];
            $uniqueStudentIds = []; // Track unique students across all subjects
            
            foreach ($finalGradeSubjects as $finalGradeSubject) {
                $subjectName = $finalGradeSubject->subject->name;
                $departmentName = $finalGradeSubject->department->name ?? 'No Department';
                
                if (!isset($subjectPerformance[$subjectName])) {
                    $subjectPerformance[$subjectName] = [
                        'subject_name' => $subjectName,
                        'department_name' => $departmentName,
                        'grade_analysis' => [
                            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                        ],
                        'gender_totals' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'total_students' => 0
                    ];
                }
                
                foreach ($finalGradeSubject->externalExamSubjectResults as $subjectResult) {
                    if ($subjectResult->externalExamResult && $subjectResult->externalExamResult->finalStudent) {
                        $student = $subjectResult->externalExamResult->finalStudent;
                        $grade = $subjectResult->grade;
                        $gender = $student->gender;
                        
                        // Track unique student IDs
                        $uniqueStudentIds[$student->id] = true;
                        
                        if (isset($subjectPerformance[$subjectName]['grade_analysis'][$grade])) {
                            $subjectPerformance[$subjectName]['grade_analysis'][$grade][$gender]++;
                            $subjectPerformance[$subjectName]['grade_analysis'][$grade]['T']++;
                            
                            $subjectPerformance[$subjectName]['gender_totals'][$gender]++;
                            $subjectPerformance[$subjectName]['gender_totals']['T']++;
                            $subjectPerformance[$subjectName]['total_students']++;
                        }
                    }
                }
            }
            
            $uniqueStudentsCount = count($uniqueStudentIds);
            
            foreach ($subjectPerformance as $subjectName => &$subjectData) {
                if ($subjectData['total_students'] > 0) {
                    $performanceCategories = [
                        'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                    ];
                    
                    foreach (['A', 'B'] as $grade) {
                        $performanceCategories['AB']['M'] += $subjectData['grade_analysis'][$grade]['M'];
                        $performanceCategories['AB']['F'] += $subjectData['grade_analysis'][$grade]['F'];
                        $performanceCategories['AB']['T'] += $subjectData['grade_analysis'][$grade]['T'];
                    }
                    
                    foreach (['A', 'B', 'C'] as $grade) {
                        $performanceCategories['ABC']['M'] += $subjectData['grade_analysis'][$grade]['M'];
                        $performanceCategories['ABC']['F'] += $subjectData['grade_analysis'][$grade]['F'];
                        $performanceCategories['ABC']['T'] += $subjectData['grade_analysis'][$grade]['T'];
                    }
                    
                    foreach (['D', 'E', 'U'] as $grade) {
                        $performanceCategories['DEU']['M'] += $subjectData['grade_analysis'][$grade]['M'];
                        $performanceCategories['DEU']['F'] += $subjectData['grade_analysis'][$grade]['F'];
                        $performanceCategories['DEU']['T'] += $subjectData['grade_analysis'][$grade]['T'];
                    }
                    
                    $percentageCategories = [];
                    foreach ($performanceCategories as $category => $counts) {
                        $percentageCategories[$category] = [
                            'M' => $subjectData['gender_totals']['M'] > 0 ? round(($counts['M'] / $subjectData['gender_totals']['M']) * 100, 1) : 0,
                            'F' => $subjectData['gender_totals']['F'] > 0 ? round(($counts['F'] / $subjectData['gender_totals']['F']) * 100, 1) : 0,
                            'T' => $subjectData['gender_totals']['T'] > 0 ? round(($counts['T'] / $subjectData['gender_totals']['T']) * 100, 1) : 0,
                        ];
                    }
                    
                    $subjectData['percentage_categories'] = $percentageCategories;
                } else {
                    $subjectData['percentage_categories'] = [
                        'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                    ];
                }
            }
            
            $subjectPerformance = array_filter($subjectPerformance, function($subject) {
                return $subject['total_students'] > 0;
            });
            
            uasort($subjectPerformance, function($a, $b) {
                $abComparison = $b['percentage_categories']['AB']['T'] <=> $a['percentage_categories']['AB']['T'];
                
                if ($abComparison === 0) {
                    return $b['percentage_categories']['ABC']['T'] <=> $a['percentage_categories']['ABC']['T'];
                }
                
                return $abComparison;
            });
            
            $totalSubjects = count($subjectPerformance);
            $totalStudentsAnalyzed = array_sum(array_column($subjectPerformance, 'total_students'));
            $schoolData = DB::table('school_setup')->first();
    
            $exportData = [
                'subjects' => $subjectPerformance,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now(),
                'total_subjects' => $totalSubjects,
                'total_students_analyzed' => $totalStudentsAnalyzed,
                'unique_students' => $uniqueStudentsCount
            ];    
            return view('finals.subjects.grade-subject-analysis', $exportData);
        } catch (Exception $e) {
            Log::error('Subject Gender Grades Report Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate subject gender grades report. Please try again.');
        }
    }

    public function subjectPsleJceComparisonReport(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);

            $psleSubjectMapping = [
                'Mathematics' => 'mathematics_grade',
                'English' => 'english_grade',
                'Science' => 'science_grade',
                'Social Studies' => 'social_studies_grade',
                'Agriculture' => 'agriculture_grade',
                'Setswana' => 'setswana_grade',
            ];

            $finalGradeSubjects = FinalGradeSubject::with([
                'subject',
                'grade',
                'department',
                'externalExamSubjectResults' => function($query) {
                    $query->with(['externalExamResult.finalStudent.originalStudent.psle'])
                          ->where('is_mapped', true)
                          ->whereNotNull('grade');
                }
            ])->where('graduation_year', $selectedTerm->year)->get();
    
            $subjectComparisons = [];
            foreach ($finalGradeSubjects as $finalGradeSubject) {
                $subjectName = $finalGradeSubject->subject->name;
                $departmentName = $finalGradeSubject->department->name ?? 'No Department';
                
                if (isset($subjectComparisons[$subjectName])) {
                    continue;
                }
                
                $studentsWithResults = collect();
                foreach ($finalGradeSubjects->where('subject.name', $subjectName) as $fgs) {
                    foreach ($fgs->externalExamSubjectResults as $result) {
                        if ($result->externalExamResult && $result->externalExamResult->finalStudent) {
                            $studentsWithResults->push($result->externalExamResult->finalStudent);
                        }
                    }
                }
                
                $studentsWithResults = $studentsWithResults->unique('id');
                if ($studentsWithResults->isEmpty()) {
                    continue;
                }
                
                $subjectComparisons[$subjectName] = [
                    'subject_name' => $subjectName,
                    'department_name' => $departmentName,
                    'psle' => [
                        'level' => 'PSLE',
                        'grade_analysis' => [
                            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                        ],
                        'gender_totals' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'total_students' => 0
                    ],
                    'jce' => [
                        'level' => 'JCE',
                        'grade_analysis' => [
                            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                        ],
                        'gender_totals' => ['M' => 0, 'F' => 0, 'T' => 0],
                        'total_students' => 0
                    ]
                ];
                
                foreach ($studentsWithResults as $student) {
                    if ($student->originalStudent && $student->originalStudent->psle) {
                        $psle = $student->originalStudent->psle;
                        $gender = $student->gender;
                        
                        $psleGrade = null;
                        if (isset($psleSubjectMapping[$subjectName])) {
                            $psleGrade = $psle->{$psleSubjectMapping[$subjectName]};
                        } else {
                            $psleGrade = $psle->overall_grade;
                        }
                        
                        if ($psleGrade && isset($subjectComparisons[$subjectName]['psle']['grade_analysis'][$psleGrade])) {
                            $subjectComparisons[$subjectName]['psle']['grade_analysis'][$psleGrade][$gender]++;
                            $subjectComparisons[$subjectName]['psle']['grade_analysis'][$psleGrade]['T']++;
                            $subjectComparisons[$subjectName]['psle']['gender_totals'][$gender]++;
                            $subjectComparisons[$subjectName]['psle']['gender_totals']['T']++;
                            $subjectComparisons[$subjectName]['psle']['total_students']++;
                        }
                    }
                }
                
                foreach ($finalGradeSubjects->where('subject.name', $subjectName) as $fgs) {
                    foreach ($fgs->externalExamSubjectResults as $subjectResult) {
                        if ($subjectResult->externalExamResult && $subjectResult->externalExamResult->finalStudent) {
                            $student = $subjectResult->externalExamResult->finalStudent;
                            $grade = $subjectResult->grade;
                            $gender = $student->gender;
                            
                            if (isset($subjectComparisons[$subjectName]['jce']['grade_analysis'][$grade])) {
                                $subjectComparisons[$subjectName]['jce']['grade_analysis'][$grade][$gender]++;
                                $subjectComparisons[$subjectName]['jce']['grade_analysis'][$grade]['T']++;
                                $subjectComparisons[$subjectName]['jce']['gender_totals'][$gender]++;
                                $subjectComparisons[$subjectName]['jce']['gender_totals']['T']++;
                                $subjectComparisons[$subjectName]['jce']['total_students']++;
                            }
                        }
                    }
                }
                
                foreach (['psle', 'jce'] as $level) {
                    if ($subjectComparisons[$subjectName][$level]['total_students'] > 0) {
                        $performanceCategories = [
                            'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                        ];
                        
                        foreach (['A', 'B'] as $grade) {
                            $performanceCategories['AB']['M'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['M'];
                            $performanceCategories['AB']['F'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['F'];
                            $performanceCategories['AB']['T'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['T'];
                        }
                        
                        foreach (['A', 'B', 'C'] as $grade) {
                            $performanceCategories['ABC']['M'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['M'];
                            $performanceCategories['ABC']['F'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['F'];
                            $performanceCategories['ABC']['T'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['T'];
                        }
                        
                        foreach (['D', 'E', 'U'] as $grade) {
                            $performanceCategories['DEU']['M'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['M'];
                            $performanceCategories['DEU']['F'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['F'];
                            $performanceCategories['DEU']['T'] += $subjectComparisons[$subjectName][$level]['grade_analysis'][$grade]['T'];
                        }
                        
                        $percentageCategories = [];
                        foreach ($performanceCategories as $category => $counts) {
                            $percentageCategories[$category] = [
                                'M' => $subjectComparisons[$subjectName][$level]['gender_totals']['M'] > 0 ? 
                                       round(($counts['M'] / $subjectComparisons[$subjectName][$level]['gender_totals']['M']) * 100, 1) : 0,
                                'F' => $subjectComparisons[$subjectName][$level]['gender_totals']['F'] > 0 ? 
                                       round(($counts['F'] / $subjectComparisons[$subjectName][$level]['gender_totals']['F']) * 100, 1) : 0,
                                'T' => $subjectComparisons[$subjectName][$level]['gender_totals']['T'] > 0 ? 
                                       round(($counts['T'] / $subjectComparisons[$subjectName][$level]['gender_totals']['T']) * 100, 1) : 0,
                            ];
                        }
                        
                        $subjectComparisons[$subjectName][$level]['percentage_categories'] = $percentageCategories;
                    } else {
                        $subjectComparisons[$subjectName][$level]['percentage_categories'] = [
                            'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                            'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                        ];
                    }
                }
            }
            
            $subjectComparisons = array_filter($subjectComparisons, function($subject) {
                return $subject['psle']['total_students'] > 0 || $subject['jce']['total_students'] > 0;
            });
            
            uasort($subjectComparisons, function($a, $b) {
                $abComparison = $b['jce']['percentage_categories']['AB']['T'] <=> $a['jce']['percentage_categories']['AB']['T'];
                
                if ($abComparison === 0) {
                    return $b['jce']['percentage_categories']['ABC']['T'] <=> $a['jce']['percentage_categories']['ABC']['T'];
                }
                
                return $abComparison;
            });
            
            $reportData = [];
            foreach ($subjectComparisons as $subjectName => $data) {
                $reportData[] = [
                    'subject_name' => $data['subject_name'],
                    'department_name' => $data['department_name'],
                    'level' => 'PSLE',
                    'data' => $data['psle'],
                    'is_first_row' => true,
                    'rowspan' => 2
                ];
                $reportData[] = [
                    'subject_name' => $data['subject_name'],
                    'department_name' => $data['department_name'],
                    'level' => 'JCE',
                    'data' => $data['jce'],
                    'is_first_row' => false,
                    'rowspan' => 0
                ];
            }
            
            $totalSubjects = count($subjectComparisons);
            $totalPsleStudents = array_sum(array_column(array_column($subjectComparisons, 'psle'), 'total_students'));
            $totalJceStudents = array_sum(array_column(array_column($subjectComparisons, 'jce'), 'total_students'));
            
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
                'report_data' => $reportData,
                'subject_comparisons' => $subjectComparisons,
                'school_data' => $schoolData,
                'year' => $selectedTerm->year,
                'generated_at' => now(),
                'total_subjects' => $totalSubjects,
                'total_psle_students' => $totalPsleStudents,
                'total_jce_students' => $totalJceStudents
            ];

            return view('finals.subjects.grade-psle-subject-analysis', $exportData);
        } catch (Exception $e) {
            Log::error('Subject PSLE vs JCE Comparison Report Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate subject PSLE vs JCE comparison report. Please try again.');
        }
    }

    public function overallTeacherPerformanceReport() {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
            $year = $selectedTerm->year;

            $allFinalKlassSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)
                                     ->whereNotNull('grade');
                        }
                    ]);
                },
                'finalGradeSubject.subject'
            ])->where('graduation_year', $year)->where('active', true)->get();

            $allFinalOptionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)
                                     ->whereNotNull('grade');
                        }
                    ]);
                },
                'finalGradeSubject.subject'
            ])->where('graduation_year', $year)->where('active', true)->get();

            $teachersData = [];

            foreach ($allFinalKlassSubjects as $klassSubject) {
                $teacherId = $klassSubject->user_id;
                $teacher = $klassSubject->teacher;

                if (!$teacher) continue;

                if (!isset($teachersData[$teacherId])) {
                    $teachersData[$teacherId] = [
                        'teacher' => $teacher,
                        'assignments' => []
                    ];
                }

                $studentIds = $klassSubject->finalKlass
                    ? $klassSubject->finalKlass->finalStudents->pluck('id')->toArray()
                    : [];

                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'klass',
                    'final_grade_subject_id' => $klassSubject->final_grade_subject_id,
                    'students' => $klassSubject->finalKlass ? $klassSubject->finalKlass->finalStudents : collect(),
                ];
            }

            foreach ($allFinalOptionalSubjects as $optionalSubject) {
                $teacherId = $optionalSubject->user_id;
                $teacher = $optionalSubject->teacher;

                if (!$teacher) continue;

                if (!isset($teachersData[$teacherId])) {
                    $teachersData[$teacherId] = [
                        'teacher' => $teacher,
                        'assignments' => []
                    ];
                }

                $students = $optionalSubject->finalStudents;
                if ($students->isEmpty()) continue;

                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'optional',
                    'final_grade_subject_id' => $optionalSubject->final_grade_subject_id,
                    'students' => $students,
                ];
            }

            $teacherPerformance = [];
            foreach ($teachersData as $teacherId => $data) {
                $performance = $this->calculateOverallTeacherPerformanceJCE($data['teacher'], $data['assignments']);
                if ($performance) {
                    $teacherPerformance[] = $performance;
                }
            }

            $this->sortTeacherPerformanceByPassRate($teacherPerformance);
            $totals = $this->calculateTeacherPerformanceTotals($teacherPerformance);

            $schoolData = DB::table('school_setup')->first();

            return view('finals.subjects.overall-teacher-performance', [
                'teacherPerformance' => $teacherPerformance,
                'totals' => $totals,
                'school_data' => $schoolData,
                'year' => $year,
            ]);
        } catch (Exception $e) {
            Log::error('Overall Teacher Performance Report Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate overall teacher performance report. Please try again.');
        }
    }

    public function overallTeacherPerformanceByGrade($classId, $type, $sequence) {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        try {
            $finalKlass = FinalKlass::with('grade')->findOrFail($classId);
            $year = (int) $finalKlass->graduation_year;

            $allFinalKlassSubjects = FinalKlassSubject::with([
                'teacher',
                'finalKlass.finalStudents' => function($query) {
                    $query->with([
                        'externalExamResults.subjectResults' => function($subQuery) {
                            $subQuery->where('is_mapped', true)
                                ->whereNotNull('grade');
                        }
                    ]);
                },
                'finalGradeSubject.subject'
            ])->where('graduation_year', $year)
                ->where('active', true)
                ->where('final_klass_id', $classId)
                ->get();

            $allFinalOptionalSubjects = FinalOptionalSubject::with([
                'teacher',
                'finalStudents' => function($query) use ($classId) {
                    $query->where('final_student_optional_subjects.final_klass_id', $classId)
                        ->with([
                            'externalExamResults.subjectResults' => function($subQuery) {
                                $subQuery->where('is_mapped', true)
                                    ->whereNotNull('grade');
                            }
                        ]);
                },
                'finalGradeSubject.subject'
            ])->where('graduation_year', $year)
                ->where('active', true)
                ->whereHas('finalStudents', function($query) use ($classId) {
                    $query->where('final_student_optional_subjects.final_klass_id', $classId);
                })
                ->get();

            $teachersData = [];

            foreach ($allFinalKlassSubjects as $klassSubject) {
                $teacherId = $klassSubject->user_id;
                $teacher = $klassSubject->teacher;

                if (!$teacher) continue;

                if (!isset($teachersData[$teacherId])) {
                    $teachersData[$teacherId] = [
                        'teacher' => $teacher,
                        'assignments' => []
                    ];
                }

                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'klass',
                    'final_grade_subject_id' => $klassSubject->final_grade_subject_id,
                    'students' => $klassSubject->finalKlass ? $klassSubject->finalKlass->finalStudents : collect(),
                ];
            }

            foreach ($allFinalOptionalSubjects as $optionalSubject) {
                $teacherId = $optionalSubject->user_id;
                $teacher = $optionalSubject->teacher;

                if (!$teacher) continue;

                if (!isset($teachersData[$teacherId])) {
                    $teachersData[$teacherId] = [
                        'teacher' => $teacher,
                        'assignments' => []
                    ];
                }

                $students = $optionalSubject->finalStudents;
                if ($students->isEmpty()) continue;

                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'optional',
                    'final_grade_subject_id' => $optionalSubject->final_grade_subject_id,
                    'students' => $students,
                ];
            }

            $teacherPerformance = [];
            foreach ($teachersData as $teacherId => $data) {
                $performance = $this->calculateOverallTeacherPerformanceJCE($data['teacher'], $data['assignments']);
                if ($performance) {
                    $teacherPerformance[] = $performance;
                }
            }

            $this->sortTeacherPerformanceByPassRate($teacherPerformance);
            $totals = $this->calculateTeacherPerformanceTotals($teacherPerformance);
            $schoolData = DB::table('school_setup')->first();
            $reportClasses = FinalKlass::query()
                ->with('grade:id,name')
                ->where('graduation_year', $year)
                ->orderBy('name')
                ->get(['id', 'name', 'grade_id'])
                ->map(function ($klass) {
                    return [
                        'id' => (int) $klass->id,
                        'name' => $klass->name,
                        'grade_name' => $klass->grade->name ?? 'Unknown',
                    ];
                })
                ->values();

            return view('finals.subjects.overall-teacher-performance', [
                'teacherPerformance' => $teacherPerformance,
                'totals' => $totals,
                'school_data' => $schoolData,
                'year' => $year,
                'klass' => $finalKlass,
                'reportType' => $type,
                'reportSequence' => $sequence,
                'reportClasses' => $reportClasses,
            ]);
        } catch (Exception $e) {
            Log::error('Overall Teacher Performance By Grade Report Error', [
                'class_id' => $classId,
                'type' => $type,
                'sequence' => $sequence,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to generate overall teacher performance by class report. Please try again.');
        }
    }

    private function calculateOverallTeacherPerformanceJCE($teacher, array $assignments): ?array {
        $gradeStructure = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];

        $totalMale = 0;
        $totalFemale = 0;
        $processedEntries = [];

        foreach ($assignments as $assignment) {
            $finalGradeSubjectId = $assignment['final_grade_subject_id'];
            $students = $assignment['students'];

            foreach ($students as $student) {
                foreach ($student->externalExamResults as $examResult) {
                    foreach ($examResult->subjectResults as $subjectResult) {
                        if ($subjectResult->final_grade_subject_id != $finalGradeSubjectId) continue;

                        $entryKey = $student->id . '_' . $finalGradeSubjectId;
                        if (isset($processedEntries[$entryKey])) continue;
                        $processedEntries[$entryKey] = true;

                        $grade = $subjectResult->grade;
                        if (empty($grade) || !isset($gradeStructure[$grade])) continue;

                        $gender = $student->gender;
                        $isMale = in_array(strtolower($gender), ['male', 'm']);

                        if ($isMale) {
                            $totalMale++;
                            $gradeStructure[$grade]['M']++;
                        } else {
                            $totalFemale++;
                            $gradeStructure[$grade]['F']++;
                        }
                    }
                }
            }
        }

        if ($totalMale + $totalFemale == 0) {
            return null;
        }

        $percentRanges = [
            'AB%' => ['A', 'B'],
            'ABC%' => ['A', 'B', 'C'],
            'ABCD%' => ['A', 'B', 'C', 'D'],
        ];

        $percentages = [];
        foreach ($percentRanges as $col => $grades) {
            $mSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['M'], $grades));
            $fSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['F'], $grades));

            $percentages[$col] = [
                'M' => $totalMale ? round($mSum / $totalMale * 100, 2) : 0,
                'F' => $totalFemale ? round($fSum / $totalFemale * 100, 2) : 0,
            ];
        }

        $teacherName = is_object($teacher) ? ($teacher->full_name ?? '') : '';

        return [
            'teacher_name' => $teacherName,
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'grades' => $gradeStructure,
            'AB%' => $percentages['AB%'],
            'ABC%' => $percentages['ABC%'],
            'ABCD%' => $percentages['ABCD%'],
        ];
    }

    private function sortTeacherPerformanceByPassRate(array &$teacherPerformance): void {
        usort($teacherPerformance, function($a, $b) {
            $aTotalStudents = $a['totalMale'] + $a['totalFemale'];
            $bTotalStudents = $b['totalMale'] + $b['totalFemale'];

            $aABC = $aTotalStudents > 0 ?
                (($a['grades']['A']['M'] + $a['grades']['A']['F'] +
                $a['grades']['B']['M'] + $a['grades']['B']['F'] +
                $a['grades']['C']['M'] + $a['grades']['C']['F']) /
                $aTotalStudents * 100) : 0;

            $bABC = $bTotalStudents > 0 ?
                (($b['grades']['A']['M'] + $b['grades']['A']['F'] +
                $b['grades']['B']['M'] + $b['grades']['B']['F'] +
                $b['grades']['C']['M'] + $b['grades']['C']['F']) /
                $bTotalStudents * 100) : 0;

            if (abs($aABC - $bABC) >= 0.01) {
                return $bABC <=> $aABC;
            }

            $aAB = $aTotalStudents > 0 ?
                (($a['grades']['A']['M'] + $a['grades']['A']['F'] +
                $a['grades']['B']['M'] + $a['grades']['B']['F']) /
                $aTotalStudents * 100) : 0;

            $bAB = $bTotalStudents > 0 ?
                (($b['grades']['A']['M'] + $b['grades']['A']['F'] +
                $b['grades']['B']['M'] + $b['grades']['B']['F']) /
                $bTotalStudents * 100) : 0;

            return $bAB <=> $aAB;
        });
    }

    private function calculateTeacherPerformanceTotals(array $teacherPerformance): array {
        $totals = [
            'grades' => [
                'A' => ['M' => 0, 'F' => 0], 'B' => ['M' => 0, 'F' => 0], 'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0], 'E' => ['M' => 0, 'F' => 0], 'U' => ['M' => 0, 'F' => 0],
            ],
            'totalMale' => 0,
            'totalFemale' => 0,
            'AB%' => ['M' => 0, 'F' => 0, 'T' => 0],
            'ABC%' => ['M' => 0, 'F' => 0, 'T' => 0],
            'ABCD%' => ['M' => 0, 'F' => 0, 'T' => 0],
        ];

        foreach ($teacherPerformance as $performance) {
            foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
                $totals['grades'][$g]['M'] += $performance['grades'][$g]['M'];
                $totals['grades'][$g]['F'] += $performance['grades'][$g]['F'];
            }
            $totals['totalMale'] += $performance['totalMale'];
            $totals['totalFemale'] += $performance['totalFemale'];
        }

        $mTotal = $totals['totalMale'];
        $fTotal = $totals['totalFemale'];
        $tTotal = $mTotal + $fTotal;

        $percentRanges = [
            'AB%' => ['A', 'B'],
            'ABC%' => ['A', 'B', 'C'],
            'ABCD%' => ['A', 'B', 'C', 'D'],
        ];

        foreach ($percentRanges as $col => $letters) {
            $sumM = array_sum(array_map(fn($g) => $totals['grades'][$g]['M'], $letters));
            $sumF = array_sum(array_map(fn($g) => $totals['grades'][$g]['F'], $letters));
            $sumT = $sumM + $sumF;

            $totals[$col]['M'] = $mTotal ? round($sumM / $mTotal * 100, 2) : 0;
            $totals[$col]['F'] = $fTotal ? round($sumF / $fTotal * 100, 2) : 0;
            $totals[$col]['T'] = $tTotal ? round($sumT / $tTotal * 100, 2) : 0;
        }

        return $totals;
    }
}
