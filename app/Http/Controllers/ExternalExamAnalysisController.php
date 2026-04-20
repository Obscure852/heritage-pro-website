<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinalStudent;
use App\Helpers\TermHelper;
use App\Models\FinalKlass;
use App\Models\SchoolSetup;
use App\Models\Term;
use Excel;
use App\Exports\FinalsClassSubjectSummaryReport;
use App\Exports\FinalsStudentOverallExport;
use App\Exports\GraduateYearOverallExport;
use Illuminate\Support\Facades\Log;

class ExternalExamAnalysisController extends Controller{
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->authorize('manage-assessment');
            return $next($request);
        });
    }

    public function getClassExamResults($classId){
        try {
            $finalKlass = FinalKlass::with(['grade', 'graduationTerm'])->findOrFail($classId);
            $graduationYear = $finalKlass->graduation_year;
            $examYear = $graduationYear - 1; 
    
            $students = FinalStudent::whereHas('finalKlasses', function($query) use ($classId) {
                $query->where('final_klasses.id', $classId);
            })->with([
                'externalExamResults' => function($query) use ($examYear) {
                    $query->whereHas('externalExam', function($q) use ($examYear) {
                        $q->where('exam_year', $examYear);
                    })->orderByDesc('created_at')->orderByDesc('id');
                },
                'externalExamResults.subjectResults' => function($query) {
                    $query->orderBy('subject_name');
                },
                'externalExamResults.externalExam',
                'originalStudent.psle',
                'graduationGrade'
            ])->get();
    
            $allSubjects = collect();
            $studentsData = [];
    
            foreach ($students as $student) {
                $studentData = [
                    'student_name' => $student->full_name,
                    'class_name' => $finalKlass->name,
                    'psle_grade' => null,
                    'exam_type' => null,
                    'subjects' => [],
                    'total_points' => null,
                    'overall_grade' => null,
                    'has_results' => false,
                ];

                if ($student->originalStudent && $student->originalStudent->psle) {
                    $studentData['psle_grade'] = $student->originalStudent->psle->overall_grade;
                }
    
                if ($student->externalExamResults->isNotEmpty()) {
                    $examResult = $student->externalExamResults->first();
                    
                    $studentData['exam_type'] = $examResult->externalExam->exam_type ?? 'N/A';
                    $studentData['total_points'] = $examResult->overall_points;
                    // Use calculated_overall_grade as fallback when overall_grade is null/empty
                    $studentData['overall_grade'] = $examResult->overall_grade ?? $examResult->calculated_overall_grade;
                    $studentData['has_results'] = true;
    
                    foreach ($examResult->subjectResults as $subjectResult) {
                        $subjectName = $subjectResult->subject_name;
                        $studentData['subjects'][$subjectName] = $subjectResult->grade;
                        $allSubjects->push($subjectName);
                    }
                }
                $studentsData[] = $studentData;
            }
    
            $uniqueSubjects = $allSubjects->unique()->sort()->values()->toArray();
            usort($studentsData, function($a, $b) {
                $aHasResults = $a['has_results'] ?? false;
                $bHasResults = $b['has_results'] ?? false;
                if ($aHasResults !== $bHasResults) {
                    return $bHasResults <=> $aHasResults;
                }

                $aPoints = $a['total_points'] ?? -1;
                $bPoints = $b['total_points'] ?? -1;
                return $bPoints <=> $aPoints;
            });
    
            foreach ($studentsData as &$studentData) {
                foreach ($uniqueSubjects as $subject) {
                    if (!isset($studentData['subjects'][$subject])) {
                        $studentData['subjects'][$subject] = '';
                    }
                }
            }
    
            $reportData = [
                'class_info' => [
                    'id' => $finalKlass->id,
                    'name' => $finalKlass->name,
                    'grade' => $finalKlass->grade->name ?? 'Unknown',
                    'graduation_year' => $finalKlass->graduation_year
                ],
                'exam_year' => $examYear,
                'subjects' => $uniqueSubjects,
                'students' => $studentsData,
                'total_students' => count($studentsData)
            ];
    
            Log::info('Class exam results generated', [
                'class_id' => $classId,
                'class_name' => $finalKlass->name,
                'exam_year' => $examYear,
                'total_students' => count($studentsData),
                'subjects_count' => count($uniqueSubjects)
            ]);

            $school_data = SchoolSetup::first();
            if (request()->has('export') && request()->export === 'excel') {
                return Excel::download(
                    new FinalsStudentOverallExport($reportData, $school_data), 
                    'student_performance_' . $finalKlass->name . '_' . date('Y-m-d') . '.xlsx'
                );
            }
    
            return view('finals.analysis.classes-overall-analysis', compact('reportData', 'school_data'));
    
        } catch (\Exception $e) {
            Log::error('Error generating class exam results', [
                'class_id' => $classId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getSubjectPerformanceReport($classId){
        try {
            $finalKlass = FinalKlass::with(['grade', 'graduationTerm'])->findOrFail($classId);
            $graduationYear = $finalKlass->graduation_year;
            $examYear = $graduationYear - 1;

            $students = FinalStudent::whereHas('finalKlasses', function($query) use ($classId) {
                $query->where('final_klasses.id', $classId);
            })->with([
                'externalExamResults' => function($query) use ($examYear) {
                    $query->whereHas('externalExam', function($q) use ($examYear) {
                        $q->where('exam_year', $examYear);
                    });
                },
                'externalExamResults.subjectResults' => function($query) {
                    $query->orderBy('subject_name');
                },
                'externalExamResults.externalExam'
            ])->get();

            $subjectData = [];
            $allSubjects = collect();

            foreach ($students as $student) {
                if ($student->externalExamResults->isNotEmpty()) {
                    $examResult = $student->externalExamResults->first();
                    $studentGender = $student->gender;

                    foreach ($examResult->subjectResults as $subjectResult) {
                        $subjectName = $subjectResult->subject_name;
                        $grade = $subjectResult->grade;

                        $allSubjects->push($subjectName);

                        if (!isset($subjectData[$subjectName])) {
                            $subjectData[$subjectName] = [
                                'total_students' => ['M' => 0, 'F' => 0, 'T' => 0],
                                'grades' => [
                                    'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'F' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'U' => ['M' => 0, 'F' => 0, 'T' => 0]
                                ],
                                'percentages' => [
                                    'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                                    'DEU' => ['M' => 0, 'F' => 0, 'T' => 0]
                                ]
                            ];
                        }

                        $subjectData[$subjectName]['total_students'][$studentGender]++;
                        $subjectData[$subjectName]['total_students']['T']++;

                        if (isset($subjectData[$subjectName]['grades'][$grade])) {
                            $subjectData[$subjectName]['grades'][$grade][$studentGender]++;
                            $subjectData[$subjectName]['grades'][$grade]['T']++;
                        }
                    }
                }
            }

            foreach ($subjectData as $subject => &$data) {
                foreach (['M', 'F', 'T'] as $gender) {
                    $total = $data['total_students'][$gender];
                    
                    if ($total > 0) {
                        $abCount = $data['grades']['A'][$gender] + $data['grades']['B'][$gender];
                        $data['percentages']['AB'][$gender] = round(($abCount / $total) * 100, 1);

                        $abcCount = $data['grades']['A'][$gender] + $data['grades']['B'][$gender] + $data['grades']['C'][$gender];
                        $data['percentages']['ABC'][$gender] = round(($abcCount / $total) * 100, 1);

                        $deuCount = $data['grades']['D'][$gender] + $data['grades']['E'][$gender] + $data['grades']['U'][$gender];
                        $data['percentages']['DEU'][$gender] = round(($deuCount / $total) * 100, 1);
                    }
                }
            }

            $uniqueSubjects = $allSubjects->unique()->sort()->values()->toArray();
            $reportData = [
                'class_info' => [
                    'id' => $finalKlass->id,
                    'name' => $finalKlass->name,
                    'grade' => $finalKlass->grade->name ?? 'Unknown',
                    'graduation_year' => $finalKlass->graduation_year
                ],
                'exam_year' => $examYear,
                'subjects' => $uniqueSubjects,
                'subject_data' => $subjectData,
                'total_students' => $students->count()
            ];

            Log::info('Subject performance report generated', [
                'class_id' => $classId,
                'class_name' => $finalKlass->name,
                'exam_year' => $examYear,
                'subjects_count' => count($uniqueSubjects),
                'total_students' => $students->count()
            ]);

            if (request()->has('export')) {
                return Excel::download(
                    new FinalsClassSubjectSummaryReport($reportData),
                    "Class_{$finalKlass->name}_" . date('Y-m-d') . ".xlsx"
                );
            }

            $school_data = SchoolSetup::first();
            $reportClasses = FinalKlass::query()
                ->with('grade:id,name')
                ->where('graduation_year', $graduationYear)
                ->orderBy('name')
                ->get(['id', 'name', 'grade_id', 'graduation_year'])
                ->map(function ($klass) {
                    return [
                        'id' => (int) $klass->id,
                        'name' => $klass->name,
                        'grade_name' => $klass->grade->name ?? 'Unknown',
                    ];
                })
                ->values();

            return view('finals.analysis.class-subjects-summary-analysis', compact('reportData', 'school_data', 'reportClasses'));

        } catch (\Exception $e) {
            Log::error('Error generating subject performance report', [
                'class_id' => $classId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }


    public function getGraduateYearExamResults(){
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
            $graduationYear = $selectedTerm->year;

            $examYear = $graduationYear - 1;
            $students = FinalStudent::where('graduation_year', $graduationYear)->with([
                    'externalExamResults' => function($query) use ($examYear) {
                        $query->whereHas('externalExam', function($q) use ($examYear) {
                            $q->where('exam_year', $examYear);
                        })->orderByDesc('created_at')->orderByDesc('id');
                    },
                    'externalExamResults.subjectResults' => function($query) {
                        $query->orderBy('subject_name');
                    },
                    'externalExamResults.externalExam',
                    'originalStudent.psle',
                    'graduationGrade',
                    'finalKlasses' => function($query) {
                        $query->with('grade');
                    }
                ])->get();
    
            $allSubjects = collect();
            $studentsData = [];
            $classesSummary = [];
    
            foreach ($students as $student) {
                $studentClass = $student->finalKlasses->first();
                
                $studentData = [
                    'student_name' => $student->full_name,
                    'class_id' => $studentClass ? $studentClass->id : null,
                    'class_name' => $studentClass ? $studentClass->name : 'No Class',
                    'grade_name' => $studentClass ? ($studentClass->grade->name ?? 'Unknown') : 'Unknown',
                    'psle_grade' => null,
                    'exam_type' => null,
                    'subjects' => [],
                    'total_points' => null,
                    'overall_grade' => null,
                    'has_results' => false,
                ];
    
                if ($student->originalStudent && $student->originalStudent->psle) {
                    $studentData['psle_grade'] = $student->originalStudent->psle->overall_grade;
                }
    
                if ($student->externalExamResults->isNotEmpty()) {
                    $examResult = $student->externalExamResults->first();
                    
                    $studentData['exam_type'] = $examResult->externalExam->exam_type ?? 'N/A';
                    $studentData['total_points'] = $examResult->overall_points;
                    // Use calculated_overall_grade as fallback when overall_grade is null/empty
                    $studentData['overall_grade'] = $examResult->overall_grade ?? $examResult->calculated_overall_grade;
                    $studentData['has_results'] = true;
    
                    foreach ($examResult->subjectResults as $subjectResult) {
                        $subjectName = $subjectResult->subject_name;
                        $studentData['subjects'][$subjectName] = $subjectResult->grade;
                        $allSubjects->push($subjectName);
                    }
                }
                
                $studentsData[] = $studentData;
                $className = $studentData['class_name'];
                if (!isset($classesSummary[$className])) {
                    $classesSummary[$className] = [
                        'name' => $className,
                        'grade' => $studentData['grade_name'],
                        'total_students' => 0,
                        'students_with_results' => 0,
                        'total_points' => 0,
                        'grades' => []
                    ];
                }
                
                $classesSummary[$className]['total_students']++;
                
                if ($studentData['has_results']) {
                    $classesSummary[$className]['students_with_results']++;
                    $classesSummary[$className]['total_points'] += (float) ($studentData['total_points'] ?? 0);
                    
                    if (!empty($studentData['overall_grade'])) {
                        $grade = $studentData['overall_grade'];
                        if (!isset($classesSummary[$className]['grades'][$grade])) {
                            $classesSummary[$className]['grades'][$grade] = 0;
                        }
                        $classesSummary[$className]['grades'][$grade]++;
                    }
                }
            }
    
            foreach ($classesSummary as &$classData) {
                if ($classData['students_with_results'] > 0) {
                    $classData['average_points'] = round($classData['total_points'] / $classData['students_with_results'], 1);
                    
                    $passGrades = ['A', 'B', 'C', 'Merit'];
                    $passCount = 0;
                    foreach ($passGrades as $grade) {
                        $passCount += $classData['grades'][$grade] ?? 0;
                    }
                    $classData['pass_rate'] = round(($passCount / $classData['students_with_results']) * 100, 1);
                } else {
                    $classData['average_points'] = 0;
                    $classData['pass_rate'] = 0;
                }
            }
    
            $uniqueSubjects = $allSubjects->unique()->sort()->values()->toArray();
            
            // Sort students by results first, then by highest points.
            usort($studentsData, function($a, $b) {
                $aHasResults = $a['has_results'] ?? false;
                $bHasResults = $b['has_results'] ?? false;
                if ($aHasResults !== $bHasResults) {
                    return $bHasResults <=> $aHasResults;
                }

                $aPoints = $a['total_points'] ?? -1;
                $bPoints = $b['total_points'] ?? -1;
                return $bPoints <=> $aPoints;
            });
    
            foreach ($studentsData as &$studentData) {
                foreach ($uniqueSubjects as $subject) {
                    if (!isset($studentData['subjects'][$subject])) {
                        $studentData['subjects'][$subject] = '';
                    }
                }
            }
    
            $reportData = [
                'graduation_year' => $graduationYear,
                'exam_year' => $examYear,
                'subjects' => $uniqueSubjects,
                'students' => $studentsData,
                'classes_summary' => array_values($classesSummary),
                'total_students' => count($studentsData),
                'total_classes' => count($classesSummary)
            ];
    
            Log::info('Graduate year exam results generated', [
                'graduation_year' => $graduationYear,
                'exam_year' => $examYear,
                'total_students' => count($studentsData),
                'total_classes' => count($classesSummary),
                'subjects_count' => count($uniqueSubjects)
            ]);
    
            $school_data = SchoolSetup::first();
            
            if (request()->has('export') && request()->export === 'excel') {
                return Excel::download(
                    new GraduateYearOverallExport($reportData, $school_data), 
                    'graduate_year_performance_' . $graduationYear . '_' . date('Y-m-d') . '.xlsx'
                );
            }
            return view('finals.analysis.grade-overall-analysis', compact('reportData', 'school_data'));
        } catch (\Exception $e) {
            Log::error('Error generating graduate year exam results', [
                'graduation_year' => $graduationYear,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getStudentTranscriptsList(){
        try {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
            $graduationYear = $selectedTerm->year;
            
            $examYear = $graduationYear - 1;
            $students = FinalStudent::where('graduation_year', $graduationYear)->with([
                    'externalExamResults' => function($query) use ($examYear) {
                        $query->whereHas('externalExam', function($q) use ($examYear) {
                            $q->where('exam_year', $examYear);
                        })->with('externalExam')
                          ->orderByDesc('created_at')
                          ->orderByDesc('id');
                    },
                    'originalStudent.psle',
                    'graduationGrade',
                    'finalKlasses' => function($query) {
                        $query->with('grade');
                    }
                ])->get();
    
            $transcriptsList = [];
            $summaryStats = [
                'total_students' => 0,
                'students_with_results' => 0,
                'overall_pass_rate' => 0,
                'grade_distribution' => [],
                'classes_summary' => []
            ];
    
            foreach ($students as $student) {
                $latestExamResult = $student->externalExamResults->first();
                if (!$latestExamResult) {
                    continue;
                }
    
                $studentClass = $student->finalKlasses->first();
                
                // Use calculated_overall_grade as fallback when overall_grade is null/empty
                $overallGrade = $latestExamResult->overall_grade ?: $latestExamResult->calculated_overall_grade;
                
                $transcriptData = [
                    'student_id' => $student->id,
                    'full_name' => $student->full_name,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'exam_number' => $student->exam_number,
                    'gender' => $student->gender,
                    'gender_full' => $student->gender === 'M' ? 'Male' : 'Female',
                    'id_number' => $student->id_number,
                    'formatted_id_number' => $student->formatted_id_number,
                    'class_name' => $studentClass ? $studentClass->name : 'N/A',
                    'grade_name' => $studentClass ? ($studentClass->grade->name ?? 'N/A') : 'N/A',
                    'graduation_year' => $student->graduation_year,
                    'psle_grade' => $student->originalStudent && $student->originalStudent->psle 
                        ? $student->originalStudent->psle->overall_grade 
                        : null,
                    'exam_type' => $latestExamResult->externalExam->exam_type,
                    'exam_session' => $latestExamResult->externalExam->exam_session,
                    'exam_year' => $latestExamResult->externalExam->exam_year,
                    'overall_grade' => $overallGrade,
                    'overall_points' => $latestExamResult->overall_points,
                    'total_subjects' => $latestExamResult->total_subjects ?? 0,
                    'passes' => $latestExamResult->passes ?? 0,
                    'pass_percentage' => $latestExamResult->pass_percentage ?? 0,
                    'is_pass' => in_array($overallGrade, ['A', 'B', 'C', 'Merit']),
                    'has_transcript' => true
                ];
                
                $transcriptsList[] = $transcriptData;
            }
    
            usort($transcriptsList, function($a, $b) {
                if ($a['class_name'] === $b['class_name']) {
                    return $b['overall_points'] <=> $a['overall_points'];
                }
                return $a['class_name'] <=> $b['class_name'];
            });
    
            $totalWithResults = count($transcriptsList);
            $passCount = collect($transcriptsList)->where('is_pass', true)->count();
            
            $summaryStats = [
                'total_students' => $students->count(),
                'students_with_results' => $totalWithResults,
                'students_without_results' => $students->count() - $totalWithResults,
                'overall_pass_rate' => $totalWithResults > 0 ? round(($passCount / $totalWithResults) * 100, 1) : 0,
                'average_points' => $totalWithResults > 0 ? round(collect($transcriptsList)->avg('overall_points'), 1) : 0,
                'highest_points' => $totalWithResults > 0 ? collect($transcriptsList)->max('overall_points') : 0,
                'lowest_points' => $totalWithResults > 0 ? collect($transcriptsList)->min('overall_points') : 0
            ];
    
            $gradeDistribution = [];
            foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                $count = collect($transcriptsList)->where('overall_grade', $grade)->count();
                if ($count > 0) {
                    $gradeDistribution[$grade] = [
                        'count' => $count,
                        'percentage' => round(($count / $totalWithResults) * 100, 1)
                    ];
                }
            }
            $summaryStats['grade_distribution'] = $gradeDistribution;
            $classesSummary = [];
            foreach ($transcriptsList as $transcript) {
                $className = $transcript['class_name'];
                if (!isset($classesSummary[$className])) {
                    $classesSummary[$className] = [
                        'name' => $className,
                        'grade' => $transcript['grade_name'],
                        'total_students' => 0,
                        'pass_count' => 0,
                        'total_points' => 0
                    ];
                }
                
                $classesSummary[$className]['total_students']++;
                $classesSummary[$className]['total_points'] += $transcript['overall_points'];
                
                if ($transcript['is_pass']) {
                    $classesSummary[$className]['pass_count']++;
                }
            }
    
            foreach ($classesSummary as &$classData) {
                $classData['average_points'] = round($classData['total_points'] / $classData['total_students'], 1);
                $classData['pass_rate'] = round(($classData['pass_count'] / $classData['total_students']) * 100, 1);
            }
            $summaryStats['classes_summary'] = array_values($classesSummary);
    
            $reportData = [
                'graduation_year' => $graduationYear,
                'exam_year' => $examYear,
                'transcripts' => $transcriptsList,
                'summary_stats' => $summaryStats,
                'total_transcripts' => count($transcriptsList)
            ];
    
            $school_data = SchoolSetup::first();
            return view('finals.analysis.students-transcript-list', compact('reportData', 'school_data'));
        } catch (\Exception $e) {
            Log::error('Error generating student transcripts list', [
                'graduation_year' => $graduationYear,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
