<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Term;
use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class StudentApiController extends Controller{
    
    public function index(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('students.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing students.read permission'
                ], 403);
            }
            
            $this->logApiAccessSafely($request, 'list_students');
            $perPage = $request->input('per_page', 50);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $gradeId = $request->input('grade_id');
            $status = $request->input('status');
            $sortBy = $request->input('sort_by', 'first_name');
            $sortOrder = $request->input('sort_order', 'asc');
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            Log::info('Student API filters applied', [
                'search' => $search,
                'grade_id' => $gradeId,
                'status' => $status,
                'term_id' => $termId,
                'page' => $page,
                'per_page' => $perPage
            ]);
            
            $query = Student::query();
            $query->whereHas('studentTerms', function ($q) use ($termId) {
                $q->where('term_id', $termId)->where('status', 'Current');
            });
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('exam_number', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
                });
            }
            
            if ($gradeId) {
                $query->whereHas('studentTerms', function ($q) use ($gradeId, $termId) {
                    $q->where('term_id', $termId)
                      ->where('grade_id', $gradeId);
                });
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $query->with([
                'sponsor',
                'currentGrade',
                'type'
            ]);
            
            if (in_array($sortBy, ['first_name', 'last_name', 'created_at', 'exam_number'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            $students = $query->paginate($perPage);
            $data = $students->map(function($student) use ($termId) {
                $currentClass = $student->classes()
                    ->wherePivot('term_id', $termId)
                    ->first();
                
                $currentHouse = $student->houses()
                    ->wherePivot('term_id', $termId)
                    ->first();
                
                return [
                    'id' => $student->id,
                    'exam_number' => $student->exam_number,
                    'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'middle_name' => $student->middle_name,
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'nationality' => $student->nationality,
                    'status' => $student->status ?? 'Current',
                    'id_number' => $student->id_number,
                    'email' => $student->email,
                    'year' => $student->year,
                    'current_grade' => $student->currentGrade ? [
                        'id' => $student->currentGrade->id,
                        'name' => $student->currentGrade->name,
                        'level' => $student->currentGrade->level
                    ] : null,
                    'current_class' => $currentClass ? [
                        'id' => $currentClass->id,
                        'name' => $currentClass->name
                    ] : null,
                    'house' => $currentHouse ? [
                        'id' => $currentHouse->id,
                        'name' => $currentHouse->name,
                        'color' => $currentHouse->color ?? null
                    ] : null,
                    'student_type' => $student->type ? [
                        'id' => $student->type->id,
                        'name' => $student->type->type,
                        'description' => $student->type->description,
                        'exempt' => $student->type->exempt
                    ] : null,
                    'sponsor' => $student->sponsor ? [
                        'id' => $student->sponsor->id,
                        'name' => trim($student->sponsor->first_name . ' ' . $student->sponsor->last_name),
                        'email' => $student->sponsor->email,
                        'phone' => $student->sponsor->phone
                    ] : null
                ];
            });
            
            $availableGrades = Grade::where('term_id', $termId)
                ->where('active', true)
                ->orderBy('sequence')
                ->get()
                ->map(function($grade) {
                    return [
                        'id' => $grade->id,
                        'name' => $grade->name,
                        'level' => $grade->level,
                        'sequence' => $grade->sequence
                    ];
                });
            
            $availableStatuses = collect();
            try {
                if (Schema::hasTable('student_statuses')) {
                    $columns = Schema::getColumnListing('student_statuses');
                    Log::info('student_statuses columns: ' . implode(', ', $columns));
                    
                    $selectColumns = [];
                    if (in_array('id', $columns)) $selectColumns[] = 'id';
                    if (in_array('name', $columns)) $selectColumns[] = 'name';
                    if (in_array('status', $columns)) $selectColumns[] = 'status';
                    if (in_array('code', $columns)) $selectColumns[] = 'code';
                    
                    if (!empty($selectColumns)) {
                        $statuses = DB::table('student_statuses')->select($selectColumns)->get();
                        
                        $availableStatuses = $statuses->map(function($status) use ($columns) {
                            $name = $status->name ?? $status->status ?? 'Unknown';
                            
                            return [
                                'id' => $status->id ?? null,
                                'name' => $name,
                                'code' => $status->code ?? $name
                            ];
                        });
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch from student_statuses table: ' . $e->getMessage());
            }
            
            if ($availableStatuses->isEmpty()) {
                try {
                    $uniqueStatuses = Student::whereNotNull('status')
                        ->distinct()
                        ->pluck('status')
                        ->filter()
                        ->values();
                    
                    if ($uniqueStatuses->isNotEmpty()) {
                        $availableStatuses = $uniqueStatuses->map(function($status, $index) {
                            return [
                                'id' => $index + 1,
                                'name' => $status,
                                'code' => $status
                            ];
                        });
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch unique statuses from students: ' . $e->getMessage());
                }
            }
            
            if ($availableStatuses->isEmpty()) {
                $availableStatuses = collect([
                    ['id' => 1, 'name' => 'Current', 'code' => 'Current'],
                    ['id' => 2, 'name' => 'Graduated', 'code' => 'Graduated'],
                    ['id' => 3, 'name' => 'Withdrawn', 'code' => 'Withdrawn'],
                    ['id' => 4, 'name' => 'Transferred', 'code' => 'Transferred'],
                    ['id' => 5, 'name' => 'Suspended', 'code' => 'Suspended'],
                    ['id' => 6, 'name' => 'Expelled', 'code' => 'Expelled']
                ]);
            }
            
            $totalCount = Student::whereHas('studentTerms', function ($q) use ($termId) {
                $q->where('term_id', $termId)->where('status', 'Current');
            })->count();
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'Students retrieved successfully',
                'count' => $students->total(),
                'total' => $totalCount,
                'data' => $data,
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'from' => $students->firstItem(),
                    'to' => $students->lastItem()
                ],
                'filters' => [
                    'grades' => $availableGrades,
                    'statuses' => $availableStatuses,
                    'applied' => array_filter([
                        'search' => $search,
                        'grade_id' => $gradeId,
                        'status' => $status,
                        'term_id' => $termId
                    ])
                ],
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Students endpoint error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }


    public function schoolTotals(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            $cacheKey = 'school_combined_totals_' . $termId . '_' . md5(json_encode($request->all()));
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $studentData = $this->getStudentTotalsData($termId);
                $staffData = $this->getStaffTotalsData();
                
                return [
                    'students' => $studentData,
                    'staff' => $staffData
                ];
            });
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'School totals retrieved successfully',
                'data' => $data,
                'term' => $selectedTerm ? [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ] : null,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('School combined totals endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch school totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function getStudentTotalsData($termId){
        $totalCurrentTerm = DB::table('students')
            ->join('student_term', 'students.id', '=', 'student_term.student_id')
            ->where('student_term.term_id', $termId)
            ->where('student_term.status', 'Current')
            ->whereNull('students.deleted_at')
            ->count();
        
        $totalAllTime = Student::count();
        $activeStudents = Student::where('status', 'Current')->count();
        $genderStats = DB::table('students')->join('student_term', 'students.id', '=', 'student_term.student_id')
            ->where('student_term.term_id', $termId)
            ->where('student_term.status', 'Current')
            ->whereNull('students.deleted_at')
            ->selectRaw("
                SUM(CASE 
                    WHEN UPPER(gender) IN ('M', 'MALE') THEN 1 
                    ELSE 0 
                END) as male,
                SUM(CASE 
                    WHEN UPPER(gender) IN ('F', 'FEMALE') THEN 1 
                    ELSE 0 
                END) as female,
                SUM(CASE 
                    WHEN gender IS NOT NULL 
                    AND UPPER(gender) NOT IN ('M', 'MALE', 'F', 'FEMALE') 
                    THEN 1 
                    ELSE 0 
                END) as other,
                SUM(CASE 
                    WHEN gender IS NULL THEN 1 
                    ELSE 0 
                END) as unspecified
            ")->first();
        
        $byStatus = DB::table('students')
            ->join('student_term', 'students.id', '=', 'student_term.student_id')
            ->where('student_term.term_id', $termId)
            ->where('student_term.status', 'Current')
            ->whereNull('students.deleted_at')
            ->select('students.status', DB::raw('COUNT(*) as count'))
            ->groupBy('students.status')
            ->pluck('count', 'status')
            ->toArray();
        
        $byGrade = DB::table('students')
            ->join('student_term', 'students.id', '=', 'student_term.student_id')
            ->join('grades', 'student_term.grade_id', '=', 'grades.id')
            ->where('student_term.term_id', $termId)
            ->where('student_term.status', 'Current')
            ->where('grades.active', true)
            ->whereNull('students.deleted_at')
            ->select('grades.name as grade_name', 'grades.level', DB::raw('COUNT(*) as count'))
            ->groupBy('grades.id', 'grades.name', 'grades.level')
            ->orderBy('grades.sequence')
            ->get()->map(function($item) {
                return [
                    'grade' => $item->grade_name,
                    'level' => $item->level,
                    'count' => (int) $item->count
                ];
            })->toArray();
        
        return [
            'total_current_term' => $totalCurrentTerm,
            'total_all_time' => $totalAllTime,
            'active_students' => $activeStudents,
            'gender_distribution' => [
                'male' => (int) ($genderStats->male ?? 0),
                'female' => (int) ($genderStats->female ?? 0),
                'other' => (int) ($genderStats->other ?? 0),
                'unspecified' => (int) ($genderStats->unspecified ?? 0)
            ],
            'status_breakdown' => $byStatus,
            'grade_distribution' => $byGrade,
            'statistics' => [
                'male_percentage' => $totalCurrentTerm > 0 
                    ? round(($genderStats->male / $totalCurrentTerm) * 100, 2) 
                    : 0,
                'female_percentage' => $totalCurrentTerm > 0 
                    ? round(($genderStats->female / $totalCurrentTerm) * 100, 2) 
                    : 0
            ]
        ];
    }

    private function getStaffTotalsData(){
        $stats = DB::table('users')->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total_all_time,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as total_active,
                SUM(CASE WHEN active = 1 AND status = 'Current' THEN 1 ELSE 0 END) as total_current,
                SUM(CASE WHEN active = 1 AND status = 'Current' AND area_of_work = 'Teaching' THEN 1 ELSE 0 END) as total_teaching,
                SUM(CASE WHEN active = 1 AND status = 'Current' AND (area_of_work != 'Teaching' OR area_of_work IS NULL) THEN 1 ELSE 0 END) as total_non_teaching,
                SUM(CASE WHEN active = 1 AND status = 'Current' AND area_of_work = 'Administration' THEN 1 ELSE 0 END) as total_administration,
                SUM(CASE WHEN active = 1 AND status = 'Current' AND area_of_work = 'IT Support' THEN 1 ELSE 0 END) as total_it_support
            ")->first();
        
        $genderStats = DB::table('users')
            ->where('active', 1)
            ->where('status', 'Current')
            ->whereNull('deleted_at')
            ->selectRaw("
                SUM(CASE 
                    WHEN UPPER(gender) IN ('M', 'MALE') THEN 1 
                    ELSE 0 
                END) as male,
                SUM(CASE 
                    WHEN UPPER(gender) IN ('F', 'FEMALE') THEN 1 
                    ELSE 0 
                END) as female,
                SUM(CASE 
                    WHEN gender IS NOT NULL 
                    AND UPPER(gender) NOT IN ('M', 'MALE', 'F', 'FEMALE') 
                    THEN 1 
                    ELSE 0 
                END) as other,
                SUM(CASE 
                    WHEN gender IS NULL THEN 1 
                    ELSE 0 
                END) as unspecified
            ")
            ->first();
        
        $teachingGenderStats = DB::table('users')
            ->where('active', 1)
            ->where('status', 'Current')
            ->where('area_of_work', 'Teaching')
            ->whereNull('deleted_at')
            ->selectRaw("
                SUM(CASE WHEN UPPER(gender) IN ('M', 'MALE') THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN UPPER(gender) IN ('F', 'FEMALE') THEN 1 ELSE 0 END) as female
            ")
            ->first();
        
        $byDepartment = DB::table('users')
            ->where('active', 1)
            ->where('status', 'Current')
            ->whereNotNull('department')
            ->whereNull('deleted_at')
            ->select('department', DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'department')
            ->toArray();
        
        $byPosition = DB::table('users')
            ->where('active', 1)
            ->where('status', 'Current')
            ->whereNotNull('position')
            ->whereNull('deleted_at')
            ->select('position', DB::raw('COUNT(*) as count'))
            ->groupBy('position')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'position')
            ->toArray();
        
        $withAllocatedClasses = DB::table('users')
            ->where('users.active', 1)
            ->where('users.status', 'Current')
            ->whereNull('users.deleted_at')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('klasses')
                    ->whereColumn('klasses.user_id', 'users.id')
                    ->where('klasses.active', 1);
            })
            ->count();
        
        $teachingPercentage = $stats->total_current > 0 
            ? round(($stats->total_teaching / $stats->total_current) * 100, 2) 
            : 0;
        
        return [
            'total_active' => (int) $stats->total_active,
            'total_current' => (int) $stats->total_current,
            'total_all_time' => (int) $stats->total_all_time,
            'total_teaching' => (int) $stats->total_teaching,
            'total_non_teaching' => (int) $stats->total_non_teaching,
            'total_administration' => (int) $stats->total_administration,
            'total_it_support' => (int) $stats->total_it_support,
            'total_with_allocated_classes' => $withAllocatedClasses,
            'teaching_percentage' => $teachingPercentage,
            'gender_distribution' => [
                'male' => (int) ($genderStats->male ?? 0),
                'female' => (int) ($genderStats->female ?? 0),
                'other' => (int) ($genderStats->other ?? 0),
                'unspecified' => (int) ($genderStats->unspecified ?? 0)
            ],
            'teaching_staff_gender' => [
                'male' => (int) ($teachingGenderStats->male ?? 0),
                'female' => (int) ($teachingGenderStats->female ?? 0),
                'male_percentage' => $stats->total_teaching > 0 
                    ? round(($teachingGenderStats->male / $stats->total_teaching) * 100, 2) 
                    : 0,
                'female_percentage' => $stats->total_teaching > 0 
                    ? round(($teachingGenderStats->female / $stats->total_teaching) * 100, 2) 
                    : 0
            ],
            'by_department' => $byDepartment,
            'by_position' => $byPosition,
            'statistics' => [
                'allocation_rate' => $stats->total_teaching > 0 
                    ? round(($withAllocatedClasses / $stats->total_teaching) * 100, 2) 
                    : 0,
                'staff_student_ratio' => $stats->total_teaching > 0 
                    ? round(DB::table('students')->where('status', 'Current')->count() / $stats->total_teaching, 2) 
                    : 0
            ]
        ];
    }
    
    public function totals(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            $cacheKey = 'student_totals_' . $termId;
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $currentTermQuery = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                });
                
                return [
                    'total_current_term' => $currentTermQuery->count(),
                    'total_all_time' => Student::count(),
                    'active_students' => Student::where('status', 'Current')->count(),
                ];
            });
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'Student totals retrieved successfully',
                'data' => $data,
                'count' => $data['total_current_term'],
                'total' => $data['total_current_term'],
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student totals endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function totalsByGender(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $cacheKey = 'student_gender_totals_' . $termId;
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $currentTermMale = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->whereIn('gender', ['Male', 'M', 'male', 'm'])->count();
                
                $currentTermFemale = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->whereIn('gender', ['Female', 'F', 'female', 'f'])->count();
                
                $currentTermOther = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->whereNotIn('gender', ['Male', 'M', 'male', 'm', 'Female', 'F', 'female', 'f'])
                ->whereNotNull('gender')->count();
                
                $currentTermUnspecified = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->whereNull('gender')->count();
                
                $allTimeMale = Student::whereIn('gender', ['Male', 'M', 'male', 'm'])->count();
                $allTimeFemale = Student::whereIn('gender', ['Female', 'F', 'female', 'f'])->count();
                $allTimeOther = Student::whereNotIn('gender', ['Male', 'M', 'male', 'm', 'Female', 'F', 'female', 'f'])
                    ->whereNotNull('gender')->count();
                $allTimeUnspecified = Student::whereNull('gender')->count();
                
                return [
                    'selected_term' => [
                        'male' => $currentTermMale,
                        'female' => $currentTermFemale,
                        'other' => $currentTermOther,
                        'unspecified' => $currentTermUnspecified,
                        'total' => $currentTermMale + $currentTermFemale + $currentTermOther + $currentTermUnspecified
                    ],
                    'all_time' => [
                        'male' => $allTimeMale,
                        'female' => $allTimeFemale,
                        'other' => $allTimeOther,
                        'unspecified' => $allTimeUnspecified,
                        'total' => $allTimeMale + $allTimeFemale + $allTimeOther + $allTimeUnspecified
                    ],
                    'gender_ratio' => [
                        'male_percentage' => $currentTermMale + $currentTermFemale > 0 
                            ? round(($currentTermMale / ($currentTermMale + $currentTermFemale)) * 100, 2) 
                            : 0,
                        'female_percentage' => $currentTermMale + $currentTermFemale > 0 
                            ? round(($currentTermFemale / ($currentTermMale + $currentTermFemale)) * 100, 2) 
                            : 0
                    ]
                ];
            });
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'Student gender totals retrieved successfully',
                'data' => $data,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student gender totals endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student gender totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function totalsByStatus(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $cacheKey = 'student_status_totals_' . $termId;
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $allStatuses = Student::whereNotNull('status')
                    ->distinct()
                    ->pluck('status')
                    ->filter()
                    ->values();
                
                if ($allStatuses->isEmpty()) {
                    $allStatuses = collect(['Current', 'Graduated', 'Withdrawn', 'Transferred', 'Suspended', 'Expelled']);
                }
                
                $selectedTermByStatus = [];
                foreach ($allStatuses as $status) {
                    $count = Student::whereHas('studentTerms', function ($q) use ($termId) {
                        $q->where('term_id', $termId)->where('status', 'Current');
                    })->where('status', $status)->count();
                    
                    if ($count > 0) {
                        $selectedTermByStatus[$status] = $count;
                    }
                }
                
                $allTimeByStatus = Student::select('status', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('status')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                
                $selectedTermTotal = array_sum($selectedTermByStatus);
                $allTimeTotal = array_sum($allTimeByStatus);
                
                return [
                    'selected_term' => [
                        'by_status' => $selectedTermByStatus,
                        'total' => $selectedTermTotal
                    ],
                    'all_time' => [
                        'by_status' => $allTimeByStatus,
                        'total' => $allTimeTotal
                    ],
                    'available_statuses' => $allStatuses->toArray()
                ];
            });
            
            $selectedTerm = Term::find($termId);
            
            return response()->json([
                'success' => true,
                'message' => 'Student status totals retrieved successfully',
                'data' => $data,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student status totals endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student status totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function totalsByGrade(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $cacheKey = 'student_grade_totals_' . $termId;
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $grades = Grade::where('term_id', $termId)
                    ->where('active', true)
                    ->orderBy('sequence')
                    ->get();
                
                $gradeData = [];
                $totalStudents = 0;
                
                foreach ($grades as $grade) {
                    $count = Student::whereHas('studentTerms', function ($q) use ($grade, $termId) {
                        $q->where('term_id', $termId)
                          ->where('status', 'Current')
                          ->where('grade_id', $grade->id);
                    })->count();
                    
                    $gradeData[] = [
                        'grade_id' => $grade->id,
                        'grade_name' => $grade->name,
                        'grade_level' => $grade->level,
                        'student_count' => $count
                    ];
                    
                    $totalStudents += $count;
                }
                
                return [
                    'by_grade' => $gradeData,
                    'total_students' => $totalStudents,
                    'total_grades' => count($grades)
                ];
            });
            
            $selectedTerm = Term::find($termId);
            
            return response()->json([
                'success' => true,
                'message' => 'Student grade totals retrieved successfully',
                'data' => $data,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student grade totals endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student grade totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function summary(Request $request){
        try {
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $cacheKey = 'student_summary_' . $termId;
            
            $data = Cache::remember($cacheKey, 300, function() use ($termId) {
                $selectedTermQuery = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                });
                
                $totalSelectedTerm = $selectedTermQuery->count();
                $totalAllTime = Student::count();
                
                $genderData = [
                    'male' => (clone $selectedTermQuery)->whereIn('gender', ['Male', 'M', 'male', 'm'])->count(),
                    'female' => (clone $selectedTermQuery)->whereIn('gender', ['Female', 'F', 'female', 'f'])->count(),
                ];
                
                $statusData = (clone $selectedTermQuery)
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                
                $grades = Grade::where('term_id', $termId)
                    ->where('active', true)
                    ->orderBy('sequence')
                    ->get();
                
                $gradeData = [];
                foreach ($grades as $grade) {
                    $count = Student::whereHas('studentTerms', function ($q) use ($grade, $termId) {
                        $q->where('term_id', $termId)
                          ->where('status', 'Current')
                          ->where('grade_id', $grade->id);
                    })->count();
                    
                    if ($count > 0) {
                        $gradeData[$grade->name] = $count;
                    }
                }
                
                $recentEnrollments = Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->where('created_at', '>=', now()->subDays(30))->count();
                
                return [
                    'totals' => [
                        'selected_term' => $totalSelectedTerm,
                        'all_time' => $totalAllTime,
                        'active' => Student::where('status', 'Current')->count(),
                        'recent_enrollments' => $recentEnrollments
                    ],
                    'gender_distribution' => $genderData,
                    'status_distribution' => $statusData,
                    'grade_distribution' => $gradeData,
                    'statistics' => [
                        'gender_ratio' => [
                            'male_percentage' => $genderData['male'] + $genderData['female'] > 0 
                                ? round(($genderData['male'] / ($genderData['male'] + $genderData['female'])) * 100, 2) 
                                : 0,
                            'female_percentage' => $genderData['male'] + $genderData['female'] > 0 
                                ? round(($genderData['female'] / ($genderData['male'] + $genderData['female'])) * 100, 2) 
                                : 0
                        ],
                        'average_per_grade' => count($gradeData) > 0 
                            ? round($totalSelectedTerm / count($gradeData), 0)
                            : 0
                    ]
                ];
            });
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'Student summary retrieved successfully',
                'data' => $data,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'closed' => (bool) $selectedTerm->closed,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms(),
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student summary endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student summary',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function availableTerms(Request $request){
        try {
            return response()->json([
                'success' => true,
                'message' => 'Available terms retrieved successfully',
                'data' => $this->getAvailableTerms(),
                'current_term_id' => TermHelper::currentTermId(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Available terms endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available terms',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    private function getAvailableTerms(){
        $cacheKey = 'available_terms_for_api';
        
        return Cache::remember($cacheKey, 600, function() {
            $currentTermId = TermHelper::currentTermId();
            
            $terms = Term::orderBy('year', 'desc')
                ->orderBy('term', 'desc')
                ->limit(30)
                ->get()
                ->map(function($term) use ($currentTermId) {
                    return [
                        'id' => $term->id,
                        'name' => 'Term ' . $term->term . '/' . $term->year,
                        'term' => $term->term,
                        'year' => $term->year,
                        'start_date' => $term->start_date->format('Y-m-d'),
                        'end_date' => $term->end_date->format('Y-m-d'),
                        'closed' => (bool) $term->closed,
                        'is_current' => $term->id == $currentTermId,
                        'status' => $term->closed ? 'closed' : ($term->id == $currentTermId ? 'current' : 'open')
                    ];
                });
            
            return $terms;
        });
    }
    
    public function show(Request $request, $id){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('students.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing students.read permission'
                ], 403);
            }
            
            $this->logApiAccessSafely($request, 'view_student', $id);
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $student = Student::with([
                'sponsor',
                'currentGrade',
                'type',
                'houses' => function ($query) use ($termId) {
                    $query->where('student_house.term_id', $termId);
                },
                'currentClassRelation'
            ])->find($id);
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }
            
            $currentClass = $student->currentClassRelation?->first();
            $currentHouse = $student->houses?->first();
            
            $data = [
                'id' => $student->id,
                'exam_number' => $student->exam_number,
                'full_name' => trim($student->first_name . ' ' . $student->last_name),
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth,
                'nationality' => $student->nationality,
                'id_number' => $student->id_number,
                'email' => $student->email,
                'status' => $student->status,
                'year' => $student->year,
                'current_grade' => $student->currentGrade ? [
                    'id' => $student->currentGrade->id,
                    'name' => $student->currentGrade->name,
                    'level' => $student->currentGrade->level
                ] : null,
                'current_class' => $currentClass ? [
                    'id' => $currentClass->id,
                    'name' => $currentClass->name
                ] : null,
                'house' => $currentHouse ? [
                    'id' => $currentHouse->id,
                    'name' => $currentHouse->name
                ] : null,
                'student_type' => $student->type ? [
                    'id' => $student->type->id,
                    'name' => $student->type->type,
                    'description' => $student->type->description,
                    'exempt' => $student->type->exempt
                ] : null,
                'sponsor' => $student->sponsor ? [
                    'id' => $student->sponsor->id,
                    'name' => trim($student->sponsor->first_name . ' ' . $student->sponsor->last_name),
                    'email' => $student->sponsor->email,
                    'phone' => $student->sponsor->phone
                ] : null
            ];
            
            $selectedTerm = Term::find($termId);
            return response()->json([
                'success' => true,
                'message' => 'Student retrieved successfully',
                'data' => $data,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'is_current' => $termId == TermHelper::currentTermId()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Student show endpoint error', [
                'error' => $e->getMessage(),
                'student_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving student',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function statistics(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('statistics.view') && !$user->tokenCan('students.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required permission'
                ], 403);
            }
            
            $termId = $request->input('term_id', TermHelper::currentTermId());
            
            $stats = [
                'total_current' => Student::whereHas('studentTerms', function ($q) use ($termId) {
                    $q->where('term_id', $termId)->where('status', 'Current');
                })->count(),
                'total_all' => Student::count(),
                'by_gender' => [
                    'male' => Student::whereIn('gender', ['Male', 'M'])->count(),
                    'female' => Student::whereIn('gender', ['Female', 'F'])->count()
                ],
                'by_status' => Student::select('status', DB::raw('COUNT(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray()
            ];
            
            $selectedTerm = Term::find($termId);
            
            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats,
                'term' => [
                    'id' => $termId,
                    'name' => 'Term ' . $selectedTerm->term . '/' . $selectedTerm->year,
                    'year' => $selectedTerm->year,
                    'is_current' => $termId == TermHelper::currentTermId()
                ],
                'available_terms' => $this->getAvailableTerms()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Statistics endpoint error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    private function logApiAccessSafely(Request $request, string $action, $resourceId = null){
        try {
            if (!Schema::hasTable('api_access_logs')) {
                Log::info('API access log table does not exist, skipping logging');
                return;
            }
            
            DB::table('api_access_logs')->insert([
                'user_id' => $request->user()?->id,
                'action' => $action,
                'resource_type' => 'student',
                'resource_id' => $resourceId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => json_encode([
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'parameters' => $request->except(['password', 'token'])
                ]),
                'accessed_at' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log API access: ' . $e->getMessage());
        }
    }
}
