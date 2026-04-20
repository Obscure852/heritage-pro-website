<?php

namespace App\Http\Controllers;

use App\Helpers\TermHelper;
use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Http\Controllers\Controller;
use App\Models\FinalHouse;
use App\Models\Term;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FinalsHouseController extends Controller{
    use InteractsWithFinalsContext;

    public function __construct(){}

    public function index(Request $request){
        try {
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
    
            $stats = $this->calculateStatistics($selectedYear, finalsDefinition: $finalsDefinition);

            $schoolModeResolver = $this->schoolModeResolver();
            $finalsContext = $this->finalsContext($request);
            $reportMenu = $this->finalsReportMenu($finalsDefinition, 'houses', [
                'year' => $selectedYear,
            ]);

            return view('finals.houses.index', compact(
                'availableYears',
                'selectedYear',
                'schoolModeResolver',
                'finalsContext',
                'finalsDefinition',
                'reportMenu'
            ))->with($stats);
    
        } catch (Exception $e) {
            Log::error('Error loading Finals House index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to load houses. Please try again.');
        }
    }

    public function getData(Request $request){
        try {
            $request->validate([
                'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 5)
            ]);
    
            $selectedYear = $request->year ?: TermHelper::getCurrentTerm()->year;
            $finalsDefinition = $this->finalsDefinition($request);
            $graduationTerms = Term::whereIn('id', function($query) use ($selectedYear) {
                $query->select('graduation_term_id')
                      ->from('final_houses')
                      ->where('graduation_year', $selectedYear)
                      ->whereExists(function($subQuery) {
                          $subQuery->select('id')
                                  ->from('final_student_houses')
                                  ->whereColumn('final_student_houses.final_house_id', 'final_houses.id');
                      })->distinct();
            })->orderBy('start_date', 'desc')->get();
    
            $finalHouses = FinalHouse::with(['houseHead', 'houseAssistant', 'graduationTerm', 'finalStudents'])
                ->byGraduationYear($selectedYear)
                ->whereHas('finalStudents')
                ->orderBy('name');
            $this->scopeFinalsQuery($finalHouses, 'final_houses', $finalsDefinition);
            $finalHouses = $finalHouses->get();
                             
            $statistics = $this->calculateStatistics($selectedYear, $finalHouses, $graduationTerms, $finalsDefinition);
    
            return view('finals.houses.partials.houses-partial', compact(
                'finalHouses',
                'graduationTerms',
                'selectedYear',
                'statistics'
            ));
    
        } catch (Exception $e) {
            Log::error('Error loading Finals House data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
    
            return response()->json(['error' => 'Failed to load data. Please try again.'], 500);
        }
    }

    public function getBadgeData(Request $request){
        try {
            $year = $request->get('year');
            $statistics = $this->calculateStatistics($year, finalsDefinition: $this->finalsDefinition($request));
            return response()->json($statistics);

        } catch (Exception $e) {
            Log::error('Error fetching Finals House badge data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'requested_year' => $request->year ?? 'not provided'
            ]);

            return response()->json([
                'totalHouses' => 0,
                'totalStudents' => 0,
                'graduationTermsCount' => 0,
                'avgStudentsPerHouse' => 0
            ], 500);
        }
    }

    protected function calculateStatistics($year = null, $houses = null, $terms = null, $finalsDefinition = null){
        try {
            $finalsDefinition ??= $this->finalsDefinition();
            if (!$year) {
                return [
                    'totalHouses' => 0,
                    'totalStudents' => 0,
                    'totalTerms' => 0,
                    'graduationTermsCount' => 0,
                    'avgStudentsPerHouse' => 0
                ];
            }

            if (!$houses) {
                $houses = FinalHouse::withCount(['finalStudents' => function($query) use ($year) {
                        $query->where('final_students.graduation_year', $year);
                    }])->where('final_houses.graduation_year', $year)->whereHas('finalStudents', function($query) use ($year) {
                        $query->where('final_students.graduation_year', $year);
                    });
                $this->scopeFinalsQuery($houses, 'final_houses', $finalsDefinition);
                $houses = $houses->get();
            }

            if (!$terms) {
                $terms = Term::whereIn('id', function($query) use ($year) {
                    $query->select('graduation_term_id')
                        ->from('final_houses')
                        ->where('graduation_year', $year)
                        ->whereExists(function($subQuery) use ($year) {
                            $subQuery->select(DB::raw('1'))
                                    ->from('final_students')
                                    ->whereColumn('final_students.graduation_term_id', 'final_houses.graduation_term_id')
                                    ->where('final_students.graduation_year', $year);
                        })->distinct();
                })->get();
            }

            $totalHouses = $houses->count();
            $totalStudents = $houses->sum('final_students_count');

            $graduationTermsCount = $terms->count();
            $avgStudentsPerHouse = $totalHouses > 0 ? round($totalStudents / $totalHouses, 1) : 0;

            return [
                'totalHouses' => $totalHouses,
                'totalStudents' => $totalStudents,
                'totalTerms' => $graduationTermsCount,
                'graduationTermsCount' => $graduationTermsCount,
                'avgStudentsPerHouse' => $avgStudentsPerHouse
            ];

        } catch (Exception $e) {
            Log::error('Error calculating house statistics', [
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'totalHouses' => 0,
                'totalStudents' => 0,
                'totalTerms' => 0,
                'graduationTermsCount' => 0,
                'avgStudentsPerHouse' => 0
            ];
        }
    }

    public function generateOverallGradeAnalysisReport(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::findOrFail($termId);
        $graduationYear = $selectedTerm->year;

        $houses = FinalHouse::with([
            'finalStudents' => function($query) use ($graduationYear) {
                $query->where('final_students.graduation_year', $graduationYear)->with([
                        'externalExamResults' => function($subQuery) {
                            $subQuery->with('externalExam')->orderBy('created_at', 'desc');
                        },
                        'finalKlasses' => function($klassQuery) use ($graduationYear) {
                            $klassQuery->where('final_klasses.graduation_year', $graduationYear)->with(['teacher', 'grade'])->orderBy('name');
                        }
                    ]);
            }
        ])->where('final_houses.graduation_year', $graduationYear)->whereHas('finalStudents', function($query) use ($graduationYear) {
            $query->where('final_students.graduation_year', $graduationYear)->whereHas('externalExamResults');
        })->orderBy('name')->get();
        
        $reportData = [];
        $overallSummary = [
            'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0],
            'totals' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        foreach ($houses as $house) {
            $houseData = [
                'house_id' => $house->id,
                'house_name' => $house->name,
                'house_head' => $house->houseHead->full_name ?? 'Not Assigned',
                'house_assistant' => $house->houseAssistant->full_name ?? 'Not Assigned',
                'classes' => [],
                'house_summary' => [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'totals' => ['M' => 0, 'F' => 0, 'T' => 0]
                ]
            ];
            
            $studentsByClass = [];
            foreach ($house->finalStudents as $student) {
                $class = $student->finalKlasses->first();
                if ($class) {
                    $className = $class->name;
                    if (!isset($studentsByClass[$className])) {
                        $studentsByClass[$className] = [
                            'students' => [],
                            'class_info' => $class
                        ];
                    }
                    $studentsByClass[$className]['students'][] = $student;
                }
            }
            
            foreach ($studentsByClass as $className => $classData) {
                $students = $classData['students'];
                $classInfo = $classData['class_info'];
                
                $gradeAnalysis = [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'totals' => ['M' => 0, 'F' => 0, 'T' => 0]
                ];
                
                $studentsWithResults = 0;
                foreach ($students as $student) {
                    $latestResult = $student->externalExamResults->first();
                    if ($latestResult) {
                        // Use stored overall_grade or fall back to calculated_overall_grade
                        $grade = $latestResult->overall_grade ?: $latestResult->calculated_overall_grade;
                        $gender = $student->gender;
                        
                        if ($grade && isset($gradeAnalysis[$grade])) {
                            $gradeAnalysis[$grade][$gender]++;
                            $gradeAnalysis[$grade]['T']++;
                            $gradeAnalysis['totals'][$gender]++;
                            $gradeAnalysis['totals']['T']++;
                            $studentsWithResults++;
                        }
                    }
                }
                
                if ($studentsWithResults > 0) {
                    $totals = $gradeAnalysis['totals'];
                    
                    $mab = [
                        'M' => $gradeAnalysis['Merit']['M'] + $gradeAnalysis['A']['M'] + $gradeAnalysis['B']['M'],
                        'F' => $gradeAnalysis['Merit']['F'] + $gradeAnalysis['A']['F'] + $gradeAnalysis['B']['F'],
                        'T' => $gradeAnalysis['Merit']['T'] + $gradeAnalysis['A']['T'] + $gradeAnalysis['B']['T']
                    ];
                    
                    $mac = [
                        'M' => $mab['M'] + $gradeAnalysis['C']['M'],
                        'F' => $mab['F'] + $gradeAnalysis['C']['F'],
                        'T' => $mab['T'] + $gradeAnalysis['C']['T']
                    ];
                    
                    $deu = [
                        'M' => $gradeAnalysis['D']['M'] + $gradeAnalysis['E']['M'] + $gradeAnalysis['U']['M'],
                        'F' => $gradeAnalysis['D']['F'] + $gradeAnalysis['E']['F'] + $gradeAnalysis['U']['F'],
                        'T' => $gradeAnalysis['D']['T'] + $gradeAnalysis['E']['T'] + $gradeAnalysis['U']['T']
                    ];
                    
                    $percentages = [
                        'MAB' => [
                            'M' => $totals['M'] > 0 ? round(($mab['M'] / $totals['M']) * 100, 1) : 0,
                            'F' => $totals['F'] > 0 ? round(($mab['F'] / $totals['F']) * 100, 1) : 0,
                            'T' => $totals['T'] > 0 ? round(($mab['T'] / $totals['T']) * 100, 1) : 0,
                            'counts' => $mab
                        ],
                        'MAC' => [
                            'M' => $totals['M'] > 0 ? round(($mac['M'] / $totals['M']) * 100, 1) : 0,
                            'F' => $totals['F'] > 0 ? round(($mac['F'] / $totals['F']) * 100, 1) : 0,
                            'T' => $totals['T'] > 0 ? round(($mac['T'] / $totals['T']) * 100, 1) : 0,
                            'counts' => $mac
                        ],
                        'DEU' => [
                            'M' => $totals['M'] > 0 ? round(($deu['M'] / $totals['M']) * 100, 1) : 0,
                            'F' => $totals['F'] > 0 ? round(($deu['F'] / $totals['F']) * 100, 1) : 0,
                            'T' => $totals['T'] > 0 ? round(($deu['T'] / $totals['T']) * 100, 1) : 0,
                            'counts' => $deu
                        ]
                    ];
                    
                    $classAnalysis = [
                        'class_id' => $classInfo->id,
                        'class_name' => $classInfo->name,
                        'class_teacher' => $classInfo->teacher->full_name ?? 'Not Assigned',
                        'grade_name' => $classInfo->grade->name ?? 'Unknown',
                        'total_students' => count($students),
                        'total_students_with_results' => $studentsWithResults,
                        'grade_analysis' => $gradeAnalysis,
                        'percentages' => $percentages
                    ];
                    
                    $houseData['classes'][] = $classAnalysis;
                    foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                        $houseData['house_summary'][$grade]['M'] += $gradeAnalysis[$grade]['M'];
                        $houseData['house_summary'][$grade]['F'] += $gradeAnalysis[$grade]['F'];
                        $houseData['house_summary'][$grade]['T'] += $gradeAnalysis[$grade]['T'];
                    }
                    $houseData['house_summary']['totals']['M'] += $gradeAnalysis['totals']['M'];
                    $houseData['house_summary']['totals']['F'] += $gradeAnalysis['totals']['F'];
                    $houseData['house_summary']['totals']['T'] += $gradeAnalysis['totals']['T'];
                }
            }
            
            if (!empty($houseData['classes'])) {
                $houseTotals = $houseData['house_summary']['totals'];
                
                $houseMab = [
                    'M' => $houseData['house_summary']['Merit']['M'] + $houseData['house_summary']['A']['M'] + $houseData['house_summary']['B']['M'],
                    'F' => $houseData['house_summary']['Merit']['F'] + $houseData['house_summary']['A']['F'] + $houseData['house_summary']['B']['F'],
                    'T' => $houseData['house_summary']['Merit']['T'] + $houseData['house_summary']['A']['T'] + $houseData['house_summary']['B']['T']
                ];
                
                $houseMac = [
                    'M' => $houseMab['M'] + $houseData['house_summary']['C']['M'],
                    'F' => $houseMab['F'] + $houseData['house_summary']['C']['F'],
                    'T' => $houseMab['T'] + $houseData['house_summary']['C']['T']
                ];
                
                $houseDeu = [
                    'M' => $houseData['house_summary']['D']['M'] + $houseData['house_summary']['E']['M'] + $houseData['house_summary']['U']['M'],
                    'F' => $houseData['house_summary']['D']['F'] + $houseData['house_summary']['E']['F'] + $houseData['house_summary']['U']['F'],
                    'T' => $houseData['house_summary']['D']['T'] + $houseData['house_summary']['E']['T'] + $houseData['house_summary']['U']['T']
                ];
                
                $houseData['house_summary']['percentages'] = [
                    'MAB' => [
                        'M' => $houseTotals['M'] > 0 ? round(($houseMab['M'] / $houseTotals['M']) * 100, 1) : 0,
                        'F' => $houseTotals['F'] > 0 ? round(($houseMab['F'] / $houseTotals['F']) * 100, 1) : 0,
                        'T' => $houseTotals['T'] > 0 ? round(($houseMab['T'] / $houseTotals['T']) * 100, 1) : 0,
                        'counts' => $houseMab
                    ],
                    'MAC' => [
                        'M' => $houseTotals['M'] > 0 ? round(($houseMac['M'] / $houseTotals['M']) * 100, 1) : 0,
                        'F' => $houseTotals['F'] > 0 ? round(($houseMac['F'] / $houseTotals['F']) * 100, 1) : 0,
                        'T' => $houseTotals['T'] > 0 ? round(($houseMac['T'] / $houseTotals['T']) * 100, 1) : 0,
                        'counts' => $houseMac
                    ],
                    'DEU' => [
                        'M' => $houseTotals['M'] > 0 ? round(($houseDeu['M'] / $houseTotals['M']) * 100, 1) : 0,
                        'F' => $houseTotals['F'] > 0 ? round(($houseDeu['F'] / $houseTotals['F']) * 100, 1) : 0,
                        'T' => $houseTotals['T'] > 0 ? round(($houseDeu['T'] / $houseTotals['T']) * 100, 1) : 0,
                        'counts' => $houseDeu
                    ]
                ];
                
                foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                    $overallSummary[$grade]['M'] += $houseData['house_summary'][$grade]['M'];
                    $overallSummary[$grade]['F'] += $houseData['house_summary'][$grade]['F'];
                    $overallSummary[$grade]['T'] += $houseData['house_summary'][$grade]['T'];
                }
                $overallSummary['totals']['M'] += $houseData['house_summary']['totals']['M'];
                $overallSummary['totals']['F'] += $houseData['house_summary']['totals']['F'];
                $overallSummary['totals']['T'] += $houseData['house_summary']['totals']['T'];
                
                $reportData[] = $houseData;
            }
        }
        
        $overallTotals = $overallSummary['totals'];
        $overallMab = [
            'M' => $overallSummary['Merit']['M'] + $overallSummary['A']['M'] + $overallSummary['B']['M'],
            'F' => $overallSummary['Merit']['F'] + $overallSummary['A']['F'] + $overallSummary['B']['F'],
            'T' => $overallSummary['Merit']['T'] + $overallSummary['A']['T'] + $overallSummary['B']['T']
        ];
        
        $overallMac = [
            'M' => $overallMab['M'] + $overallSummary['C']['M'],
            'F' => $overallMab['F'] + $overallSummary['C']['F'],
            'T' => $overallMab['T'] + $overallSummary['C']['T']
        ];
        
        $overallDeu = [
            'M' => $overallSummary['D']['M'] + $overallSummary['E']['M'] + $overallSummary['U']['M'],
            'F' => $overallSummary['D']['F'] + $overallSummary['E']['F'] + $overallSummary['U']['F'],
            'T' => $overallSummary['D']['T'] + $overallSummary['E']['T'] + $overallSummary['U']['T']
        ];
        
        $overallSummary['percentages'] = [
            'MAB' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallMab['M'] / $overallTotals['M']) * 100, 1) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallMab['F'] / $overallTotals['F']) * 100, 1) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallMab['T'] / $overallTotals['T']) * 100, 1) : 0,
                'counts' => $overallMab
            ],
            'MAC' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallMac['M'] / $overallTotals['M']) * 100, 1) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallMac['F'] / $overallTotals['F']) * 100, 1) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallMac['T'] / $overallTotals['T']) * 100, 1) : 0,
                'counts' => $overallMac
            ],
            'DEU' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallDeu['M'] / $overallTotals['M']) * 100, 1) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallDeu['F'] / $overallTotals['F']) * 100, 1) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallDeu['T'] / $overallTotals['T']) * 100, 1) : 0,
                'counts' => $overallDeu
            ]
        ];
        
        return [
            'houses' => $reportData,
            'overall_summary' => $overallSummary,
            'graduation_year' => $graduationYear,
            'generated_at' => now(),
            'total_houses' => count($reportData),
            'total_classes' => collect($reportData)->sum(function($house) {
                return count($house['classes']);
            }),
            'total_students' => $overallSummary['totals']['T']
        ];
    }

    public function overallHouseClassAnalysis(){
        $reportData = $this->generateOverallGradeAnalysisReport();
        return view('finals.houses.house-class-analysis', compact('reportData'));
    }

    public function generateOverallExamHousePerformanceReport(){
        return $this->housePerformanceAnalysis();
    }

    public function housePerformanceAnalysis(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::findOrFail($termId);
        $graduationYear = $selectedTerm->year;

        $houses = FinalHouse::with([
            'finalStudents' => function($query) use ($graduationYear) {
                $query->where('final_students.graduation_year', $graduationYear)->with([
                    'externalExamResults' => function($subQuery) {
                        $subQuery->with('externalExam')->orderBy('created_at', 'desc');
                    }
                ]);
            }
        ])->where('final_houses.graduation_year', $graduationYear)->whereHas('finalStudents', function($query) use ($graduationYear) {
            $query->where('final_students.graduation_year', $graduationYear)->whereHas('externalExamResults');
        })->orderBy('name')->get();
        
        $reportData = [];
        $overallSummary = [
            'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0],
            'totals' => ['M' => 0, 'F' => 0, 'T' => 0]
        ];
        
        foreach ($houses as $house) {
            $houseData = [
                'house_id' => $house->id,
                'house_name' => $house->name,
                'grade_analysis' => [
                    'Merit' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                    'U' => ['M' => 0, 'F' => 0, 'T' => 0],
                ],
                'totals' => ['M' => 0, 'F' => 0, 'T' => 0]
            ];
            
            foreach ($house->finalStudents as $student) {
                $latestResult = $student->externalExamResults->first();
                if ($latestResult) {
                    // Use stored overall_grade or fall back to calculated_overall_grade
                    $grade = $latestResult->overall_grade ?: $latestResult->calculated_overall_grade;
                    $gender = $student->gender;
                    
                    if ($grade && isset($houseData['grade_analysis'][$grade])) {
                        $houseData['grade_analysis'][$grade][$gender]++;
                        $houseData['grade_analysis'][$grade]['T']++;
                        $houseData['totals'][$gender]++;
                        $houseData['totals']['T']++;
                    }
                }
            }
            
            // Calculate percentages for this house
            $totals = $houseData['totals'];
            
            $mab = [
                'M' => $houseData['grade_analysis']['Merit']['M'] + $houseData['grade_analysis']['A']['M'] + $houseData['grade_analysis']['B']['M'],
                'F' => $houseData['grade_analysis']['Merit']['F'] + $houseData['grade_analysis']['A']['F'] + $houseData['grade_analysis']['B']['F'],
                'T' => $houseData['grade_analysis']['Merit']['T'] + $houseData['grade_analysis']['A']['T'] + $houseData['grade_analysis']['B']['T']
            ];
            
            $mabc = [
                'M' => $mab['M'] + $houseData['grade_analysis']['C']['M'],
                'F' => $mab['F'] + $houseData['grade_analysis']['C']['F'],
                'T' => $mab['T'] + $houseData['grade_analysis']['C']['T']
            ];
            
            $mabcd = [
                'M' => $mabc['M'] + $houseData['grade_analysis']['D']['M'],
                'F' => $mabc['F'] + $houseData['grade_analysis']['D']['F'],
                'T' => $mabc['T'] + $houseData['grade_analysis']['D']['T']
            ];
            
            $deu = [
                'M' => $houseData['grade_analysis']['D']['M'] + $houseData['grade_analysis']['E']['M'] + $houseData['grade_analysis']['U']['M'],
                'F' => $houseData['grade_analysis']['D']['F'] + $houseData['grade_analysis']['E']['F'] + $houseData['grade_analysis']['U']['F'],
                'T' => $houseData['grade_analysis']['D']['T'] + $houseData['grade_analysis']['E']['T'] + $houseData['grade_analysis']['U']['T']
            ];
            
            $houseData['percentages'] = [
                'MAB' => [
                    'M' => $totals['M'] > 0 ? round(($mab['M'] / $totals['M']) * 100, 2) : 0,
                    'F' => $totals['F'] > 0 ? round(($mab['F'] / $totals['F']) * 100, 2) : 0,
                    'T' => $totals['T'] > 0 ? round(($mab['T'] / $totals['T']) * 100, 2) : 0,
                ],
                'MABC' => [
                    'M' => $totals['M'] > 0 ? round(($mabc['M'] / $totals['M']) * 100, 2) : 0,
                    'F' => $totals['F'] > 0 ? round(($mabc['F'] / $totals['F']) * 100, 2) : 0,
                    'T' => $totals['T'] > 0 ? round(($mabc['T'] / $totals['T']) * 100, 2) : 0,
                ],
                'MABCD' => [
                    'M' => $totals['M'] > 0 ? round(($mabcd['M'] / $totals['M']) * 100, 2) : 0,
                    'F' => $totals['F'] > 0 ? round(($mabcd['F'] / $totals['F']) * 100, 2) : 0,
                    'T' => $totals['T'] > 0 ? round(($mabcd['T'] / $totals['T']) * 100, 2) : 0,
                ],
                'DEU' => [
                    'M' => $totals['M'] > 0 ? round(($deu['M'] / $totals['M']) * 100, 2) : 0,
                    'F' => $totals['F'] > 0 ? round(($deu['F'] / $totals['F']) * 100, 2) : 0,
                    'T' => $totals['T'] > 0 ? round(($deu['T'] / $totals['T']) * 100, 2) : 0,
                ]
            ];
            
            if ($houseData['totals']['T'] > 0) {
                $reportData[] = $houseData;
                
                // Add to overall summary
                foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                    $overallSummary[$grade]['M'] += $houseData['grade_analysis'][$grade]['M'];
                    $overallSummary[$grade]['F'] += $houseData['grade_analysis'][$grade]['F'];
                    $overallSummary[$grade]['T'] += $houseData['grade_analysis'][$grade]['T'];
                }
                $overallSummary['totals']['M'] += $houseData['totals']['M'];
                $overallSummary['totals']['F'] += $houseData['totals']['F'];
                $overallSummary['totals']['T'] += $houseData['totals']['T'];
            }
        }
        
        // Calculate overall percentages
        $overallTotals = $overallSummary['totals'];
        $overallMab = [
            'M' => $overallSummary['Merit']['M'] + $overallSummary['A']['M'] + $overallSummary['B']['M'],
            'F' => $overallSummary['Merit']['F'] + $overallSummary['A']['F'] + $overallSummary['B']['F'],
            'T' => $overallSummary['Merit']['T'] + $overallSummary['A']['T'] + $overallSummary['B']['T']
        ];
        $overallMabc = [
            'M' => $overallMab['M'] + $overallSummary['C']['M'],
            'F' => $overallMab['F'] + $overallSummary['C']['F'],
            'T' => $overallMab['T'] + $overallSummary['C']['T']
        ];
        $overallMabcd = [
            'M' => $overallMabc['M'] + $overallSummary['D']['M'],
            'F' => $overallMabc['F'] + $overallSummary['D']['F'],
            'T' => $overallMabc['T'] + $overallSummary['D']['T']
        ];
        $overallDeu = [
            'M' => $overallSummary['D']['M'] + $overallSummary['E']['M'] + $overallSummary['U']['M'],
            'F' => $overallSummary['D']['F'] + $overallSummary['E']['F'] + $overallSummary['U']['F'],
            'T' => $overallSummary['D']['T'] + $overallSummary['E']['T'] + $overallSummary['U']['T']
        ];
        
        $overallSummary['percentages'] = [
            'MAB' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallMab['M'] / $overallTotals['M']) * 100, 2) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallMab['F'] / $overallTotals['F']) * 100, 2) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallMab['T'] / $overallTotals['T']) * 100, 2) : 0,
            ],
            'MABC' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallMabc['M'] / $overallTotals['M']) * 100, 2) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallMabc['F'] / $overallTotals['F']) * 100, 2) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallMabc['T'] / $overallTotals['T']) * 100, 2) : 0,
            ],
            'MABCD' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallMabcd['M'] / $overallTotals['M']) * 100, 2) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallMabcd['F'] / $overallTotals['F']) * 100, 2) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallMabcd['T'] / $overallTotals['T']) * 100, 2) : 0,
            ],
            'DEU' => [
                'M' => $overallTotals['M'] > 0 ? round(($overallDeu['M'] / $overallTotals['M']) * 100, 2) : 0,
                'F' => $overallTotals['F'] > 0 ? round(($overallDeu['F'] / $overallTotals['F']) * 100, 2) : 0,
                'T' => $overallTotals['T'] > 0 ? round(($overallDeu['T'] / $overallTotals['T']) * 100, 2) : 0,
            ]
        ];
        
        $schoolData = DB::table('school_setup')->first();
        
        return view('finals.houses.house-performance-analysis', [
            'houses' => $reportData,
            'overall_summary' => $overallSummary,
            'graduation_year' => $graduationYear,
            'graduation_term' => $selectedTerm->name,
            'generated_at' => now(),
            'total_houses' => count($reportData),
            'total_students' => $overallSummary['totals']['T'],
            'school_data' => $schoolData
        ]);
    }
}
