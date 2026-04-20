<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class UserApiController extends Controller{
    
    public function index(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing staff.read permission'
                ], 403);
            }
            
            $this->logApiAccessSafely($request, 'list_staff');
            
            $perPage = $request->input('per_page', 50);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $department = $request->input('department');
            $position = $request->input('position');
            $status = $request->input('status', 'Current');
            $areaOfWork = $request->input('area_of_work');
            $sortBy = $request->input('sort_by', 'firstname');
            $sortOrder = $request->input('sort_order', 'asc');
            
            Log::info('Staff API filters applied', [
                'search' => $search,
                'department' => $department,
                'position' => $position,
                'status' => $status,
                'area_of_work' => $areaOfWork,
                'page' => $page,
                'per_page' => $perPage
            ]);
            
            $query = User::query();
            
            $query->where('active', 1);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('middlename', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }
            
            if ($department) {
                $query->where('department', $department);
            }
            
            if ($position) {
                $query->where('position', $position);
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($areaOfWork) {
                $query->where('area_of_work', $areaOfWork);
            }
            
            $query->with([
                'roles',
                'filter',
                'klass' => function($query) {
                    $query->where('active', 1)->with('grade');
                },
                'klassSubjects' => function($query) {
                    $query->where('active', 1)
                          ->with(['gradeSubject', 'klass', 'grade', 'venue']);
                },
                'taughtOptionalSubjects' => function($query) {
                    $query->where('active', 1)
                          ->with(['gradeSubject', 'venue', 'grade']);
                }
            ]);
            
            if (in_array($sortBy, ['firstname', 'lastname', 'created_at', 'email', 'department', 'position'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            $users = $query->paginate($perPage);
            $data = $users->map(function($user) {
                try {
                    $allocatedClasses = $user->klass ?? collect();
                    $teachingSubjects = $user->klassSubjects ?? collect();
                    $optionalSubjects = $user->taughtOptionalSubjects ?? collect();
                    
                    return [
                        'id' => $user->id,
                        'full_name' => trim($user->firstname . ' ' . ($user->middlename ? $user->middlename . ' ' : '') . $user->lastname),
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'middlename' => $user->middlename,
                        'email' => $user->email,
                        'username' => $user->username,
                        'gender' => $user->gender,
                        'date_of_birth' => $user->date_of_birth,
                        'nationality' => $user->nationality,
                        'id_number' => $user->id_number,
                        'employee_id' => $user->id_number,
                        'phone' => $user->phone,
                        'city' => $user->city,
                        'address' => $user->address,
                        'department' => [
                            'name' => $user->department
                        ],
                        'position' => $user->position,
                        'role' => [
                            'name' => $user->position
                        ],
                        'area_of_work' => $user->area_of_work,
                        'status' => $user->status,
                        'year' => $user->year,
                        'reporting_to' => $user->reporting_to,
                        'roles' => $user->roles->map(function($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name
                            ];
                        })->values()->toArray(),
                        'filter' => $user->filter ? [
                            'id' => $user->filter->id,
                            'name' => $user->filter->name ?? null
                        ] : null,
                        'allocated_classes' => $allocatedClasses->map(function($class) {
                            return [
                                'id' => $class->id,
                                'name' => $class->name,
                                'grade_id' => $class->grade_id,
                                'grade_name' => $class->grade ? $class->grade->name : null,
                                'term_id' => $class->term_id,
                                'year' => $class->year,
                                'type' => $class->type ?? null
                            ];
                        })->values()->toArray(),
                        'teaching_subjects' => $teachingSubjects->map(function($subject) {
                            return [
                                'id' => $subject->id,
                                'subject_name' => $subject->gradeSubject ? $subject->gradeSubject->name : null,
                                'subject_code' => $subject->gradeSubject ? $subject->gradeSubject->code : null,
                                'class_id' => $subject->klass_id,
                                'class_name' => $subject->klass ? $subject->klass->name : null,
                                'grade_id' => $subject->grade_id,
                                'grade_name' => $subject->grade ? $subject->grade->name : null,
                                'venue' => $subject->venue ? [
                                    'id' => $subject->venue->id,
                                    'name' => $subject->venue->name,
                                    'type' => $subject->venue->type ?? null,
                                    'capacity' => $subject->venue->capacity ?? null
                                ] : null,
                                'term_id' => $subject->term_id,
                                'year' => $subject->year,
                                'active' => $subject->active
                            ];
                        })->values()->toArray(),
                        'optional_subjects' => $optionalSubjects->map(function($subject) {
                            return [
                                'id' => $subject->id,
                                'name' => $subject->name,
                                'subject_name' => $subject->gradeSubject ? $subject->gradeSubject->name : null,
                                'subject_code' => $subject->gradeSubject ? $subject->gradeSubject->code : null,
                                'grade_id' => $subject->grade_id,
                                'grade_name' => $subject->grade ? $subject->grade->name : null,
                                'grouping' => $subject->grouping,
                                'venue' => $subject->venue ? [
                                    'id' => $subject->venue->id,
                                    'name' => $subject->venue->name,
                                    'type' => $subject->venue->type ?? null,
                                    'capacity' => $subject->venue->capacity ?? null
                                ] : null,
                                'term_id' => $subject->term_id,
                                'active' => $subject->active ?? true
                            ];
                        })->values()->toArray(),
                        'has_allocated_class' => $allocatedClasses->isNotEmpty(),
                        'is_school_head' => $user->position === 'School Head'
                    ];
                } catch (\Exception $e) {
                    Log::warning('Error mapping user data', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return [
                        'id' => $user->id,
                        'full_name' => trim($user->firstname . ' ' . ($user->middlename ? $user->middlename . ' ' : '') . $user->lastname),
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'employee_id' => $user->id_number,
                        'phone' => $user->phone,
                        'department' => [
                            'name' => $user->department
                        ],
                        'position' => $user->position,
                        'role' => [
                            'name' => $user->position
                        ],
                        'status' => $user->status,
                        'area_of_work' => $user->area_of_work,
                        'allocated_classes' => [],
                        'teaching_subjects' => [],
                        'optional_subjects' => [],
                        'has_allocated_class' => false,
                        'is_school_head' => false
                    ];
                }
            });
            
            $availableAreasOfWork = [];
            try {
                $areasOfWork = DB::table('area_of_work')
                    ->select('id', 'name', 'category')
                    ->orderBy('category')
                    ->orderBy('name')
                    ->get();
                    
                $availableAreasOfWork = $areasOfWork->map(function($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name,
                        'category' => $area->category
                    ];
                })->toArray();
            } catch (\Exception $e) {
                Log::warning('Could not fetch area_of_work table: ' . $e->getMessage());
                $areas = User::where('active', 1)
                    ->whereNotNull('area_of_work')
                    ->distinct()
                    ->pluck('area_of_work');
                    
                $availableAreasOfWork = $areas->map(function($area, $index) {
                    return [
                        'id' => $index + 1,
                        'name' => $area,
                        'category' => null
                    ];
                })->toArray();
            }
            
            $availableDepartments = User::where('active', 1)
                ->whereNotNull('department')
                ->distinct()
                ->pluck('department')
                ->sort()
                ->values();
            
            try {
                $availablePositions = DB::table('user_positions')
                    ->select('id', 'name')
                    ->orderBy('id')
                    ->get()
                    ->map(function($position) {
                        return [
                            'id' => $position->id,
                            'name' => $position->name
                        ];
                    });
            } catch (\Exception $e) {
                Log::warning('Could not fetch from user_positions table: ' . $e->getMessage());
                $availablePositions = User::where('active', 1)
                    ->whereNotNull('position')
                    ->distinct()
                    ->pluck('position')
                    ->map(function($position, $index) {
                        return [
                            'id' => $index + 1,
                            'name' => $position
                        ];
                    });
            }
            
            try {
                $availableStatuses = DB::table('users_status')
                    ->select('id', 'name')
                    ->orderBy('id')
                    ->get()
                    ->map(function($status) {
                        return [
                            'id' => $status->id,
                            'name' => $status->name
                        ];
                    });
            } catch (\Exception $e) {
                Log::warning('Could not fetch from users_status table: ' . $e->getMessage());
                $availableStatuses = User::where('active', 1)
                    ->whereNotNull('status')
                    ->distinct()
                    ->pluck('status')
                    ->map(function($status, $index) {
                        return [
                            'id' => $index + 1,
                            'name' => $status
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully',
                'count' => $users->total(),
                'data' => $data,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ],
                'last_page' => $users->lastPage(),
                'areas_of_work' => $availableAreasOfWork,
                'statuses' => $availableStatuses,
                'filters' => [
                    'departments' => $availableDepartments,
                    'positions' => $availablePositions,
                    'statuses' => $availableStatuses,
                    'areas_of_work' => $availableAreasOfWork,
                    'applied' => array_filter([
                        'search' => $search,
                        'department' => $department,
                        'position' => $position,
                        'status' => $status,
                        'area_of_work' => $areaOfWork
                    ])
                ],
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Staff endpoint error', [
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
    
    public function totals(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('staff.read') && !$user->tokenCan('statistics.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required permission'
                ], 403);
            }
            
            $cacheKey = 'staff_totals';
            
            $data = Cache::remember($cacheKey, 300, function() {
                $totalActive = User::where('active', 1)->count();
                $totalCurrent = User::where('active', 1)->where('status', 'Current')->count();
                $totalAllTime = User::count();
                
                $totalTeaching = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where('area_of_work', 'Teaching')
                    ->count();
                    
                $totalNonTeaching = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where(function($q) {
                        $q->where('area_of_work', '!=', 'Teaching')
                          ->orWhereNull('area_of_work');
                    })
                    ->count();
                
                $totalWithAllocatedClasses = User::where('active', 1)
                    ->where('status', 'Current')
                    ->whereHas('klass', function($q) {
                        $q->where('active', 1);
                    })
                    ->count();
                
                return [
                    'total_active' => $totalActive,
                    'total_current' => $totalCurrent,
                    'total_all_time' => $totalAllTime,
                    'total_teaching' => $totalTeaching,
                    'total_non_teaching' => $totalNonTeaching,
                    'total_with_allocated_classes' => $totalWithAllocatedClasses,
                    'teaching_percentage' => $totalCurrent > 0 
                        ? round(($totalTeaching / $totalCurrent) * 100, 2) 
                        : 0
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Staff totals retrieved successfully',
                'data' => $data,
                'count' => $data['total_current'],
                'total' => $data['total_current'],
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Staff totals endpoint error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch staff totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    public function totalsByGender(Request $request){
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            if (!$user->tokenCan('staff.read') && !$user->tokenCan('statistics.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required permission'
                ], 403);
            }
            
            $cacheKey = 'staff_gender_totals';
            $data = Cache::remember($cacheKey, 300, function() {
                $currentMale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->whereIn('gender', ['Male', 'M', 'male', 'm'])
                    ->count();
                
                $currentFemale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->whereIn('gender', ['Female', 'F', 'female', 'f'])
                    ->count();
                
                $currentOther = User::where('active', 1)
                    ->where('status', 'Current')
                    ->whereNotIn('gender', ['Male', 'M', 'male', 'm', 'Female', 'F', 'female', 'f'])
                    ->whereNotNull('gender')
                    ->count();
                
                $currentUnspecified = User::where('active', 1)
                    ->where('status', 'Current')
                    ->whereNull('gender')
                    ->count();
                
                $allTimeMale = User::whereIn('gender', ['Male', 'M', 'male', 'm'])->count();
                $allTimeFemale = User::whereIn('gender', ['Female', 'F', 'female', 'f'])->count();
                $allTimeOther = User::whereNotIn('gender', ['Male', 'M', 'male', 'm', 'Female', 'F', 'female', 'f'])
                    ->whereNotNull('gender')->count();
                $allTimeUnspecified = User::whereNull('gender')->count();
                
                $teachingMale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where('area_of_work', 'Teaching')
                    ->whereIn('gender', ['Male', 'M', 'male', 'm'])
                    ->count();
                
                $teachingFemale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where('area_of_work', 'Teaching')
                    ->whereIn('gender', ['Female', 'F', 'female', 'f'])
                    ->count();
                
                $nonTeachingMale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where(function($q) {
                        $q->where('area_of_work', '!=', 'Teaching')
                          ->orWhereNull('area_of_work');
                    })
                    ->whereIn('gender', ['Male', 'M', 'male', 'm'])
                    ->count();
                
                $nonTeachingFemale = User::where('active', 1)
                    ->where('status', 'Current')
                    ->where(function($q) {
                        $q->where('area_of_work', '!=', 'Teaching')
                          ->orWhereNull('area_of_work');
                    })
                    ->whereIn('gender', ['Female', 'F', 'female', 'f'])
                    ->count();
                
                return [
                    'current_staff' => [
                        'male' => $currentMale,
                        'female' => $currentFemale,
                        'other' => $currentOther,
                        'unspecified' => $currentUnspecified,
                        'total' => $currentMale + $currentFemale + $currentOther + $currentUnspecified
                    ],
                    'all_time' => [
                        'male' => $allTimeMale,
                        'female' => $allTimeFemale,
                        'other' => $allTimeOther,
                        'unspecified' => $allTimeUnspecified,
                        'total' => $allTimeMale + $allTimeFemale + $allTimeOther + $allTimeUnspecified
                    ],
                    'teaching_staff' => [
                        'male' => $teachingMale,
                        'female' => $teachingFemale,
                        'total' => $teachingMale + $teachingFemale
                    ],
                    'non_teaching_staff' => [
                        'male' => $nonTeachingMale,
                        'female' => $nonTeachingFemale,
                        'total' => $nonTeachingMale + $nonTeachingFemale
                    ],
                    'gender_ratio' => [
                        'overall' => [
                            'male_percentage' => $currentMale + $currentFemale > 0 
                                ? round(($currentMale / ($currentMale + $currentFemale)) * 100, 2) 
                                : 0,
                            'female_percentage' => $currentMale + $currentFemale > 0 
                                ? round(($currentFemale / ($currentMale + $currentFemale)) * 100, 2) 
                                : 0,
                            'ratio' => $currentFemale > 0 
                                ? round($currentMale / $currentFemale, 2) 
                                : 0
                        ],
                        'teaching' => [
                            'male_percentage' => $teachingMale + $teachingFemale > 0 
                                ? round(($teachingMale / ($teachingMale + $teachingFemale)) * 100, 2) 
                                : 0,
                            'female_percentage' => $teachingMale + $teachingFemale > 0 
                                ? round(($teachingFemale / ($teachingMale + $teachingFemale)) * 100, 2) 
                                : 0
                        ],
                        'non_teaching' => [
                            'male_percentage' => $nonTeachingMale + $nonTeachingFemale > 0 
                                ? round(($nonTeachingMale / ($nonTeachingMale + $nonTeachingFemale)) * 100, 2) 
                                : 0,
                            'female_percentage' => $nonTeachingMale + $nonTeachingFemale > 0 
                                ? round(($nonTeachingFemale / ($nonTeachingMale + $nonTeachingFemale)) * 100, 2) 
                                : 0
                        ]
                    ]
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Staff gender totals retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Staff gender totals endpoint error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch staff gender totals',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
            
            if (!$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing staff.read permission'
                ], 403);
            }
            
            $this->logApiAccessSafely($request, 'view_staff', $id);
            
            $staff = User::with([
                'roles',
                'filter',
                'klass',
                'klassSubjects' => function($query) {
                    $query->with(['gradeSubject', 'klass', 'grade', 'venue']);
                },
                'qualifications',
                'workHistory',
                'taughtOptionalSubjects' => function($query) {
                    $query->with(['gradeSubject', 'venue', 'grade', 'students']);
                },
                'headedDepartments',
                'assistedDepartments',
                'housesAsHead',
                'housesAsAssistant',
                'reportsTo',
                'subordinates'
            ])->find($id);
            
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }
            
            $allocatedClasses = $staff->klass()->where('active', 1)->get();
            $teachingSubjects = $staff->klassSubjects()
                ->with(['gradeSubject', 'klass', 'grade', 'venue'])
                ->where('active', 1)
                ->get();
            $optionalSubjects = $staff->taughtOptionalSubjects()
                ->with(['gradeSubject', 'venue', 'grade'])
                ->where('active', 1)
                ->get();
            
            $data = [
                'id' => $staff->id,
                'full_name' => trim($staff->firstname . ' ' . ($staff->middlename ? $staff->middlename . ' ' : '') . $staff->lastname),
                'firstname' => $staff->firstname,
                'lastname' => $staff->lastname,
                'middlename' => $staff->middlename,
                'email' => $staff->email,
                'username' => $staff->username,
                'gender' => $staff->gender,
                'date_of_birth' => $staff->date_of_birth,
                'nationality' => $staff->nationality,
                'id_number' => $staff->id_number,
                'phone' => $staff->phone,
                'city' => $staff->city,
                'address' => $staff->address,
                'department' => $staff->department,
                'position' => $staff->position,
                'area_of_work' => $staff->area_of_work,
                'status' => $staff->status,
                'year' => $staff->year,
                'avatar' => $staff->avatar,
                'signature_path' => $staff->signature_path,
                'sms_signature' => $staff->sms_signature,
                'email_signature' => $staff->email_signature,
                'reporting_to' => $staff->reporting_to ? [
                    'id' => $staff->reportsTo->id,
                    'name' => $staff->reportsTo->full_name,
                    'position' => $staff->reportsTo->position
                ] : null,
                'roles' => $staff->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name
                    ];
                }),
                'filter' => $staff->filter ? [
                    'id' => $staff->filter->id,
                    'name' => $staff->filter->name ?? null
                ] : null,
                'allocated_classes' => $allocatedClasses->map(function($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'grade_id' => $class->grade_id,
                        'grade_name' => $class->grade->name ?? null,
                        'term_id' => $class->term_id
                    ];
                }),
                'teaching_subjects' => $teachingSubjects->map(function($subject) {
                    return [
                        'id' => $subject->id,
                        'subject_name' => $subject->gradeSubject->name ?? null,
                        'subject_code' => $subject->gradeSubject->code ?? null,
                        'class_id' => $subject->klass_id,
                        'class_name' => $subject->klass->name ?? null,
                        'grade_id' => $subject->grade_id,
                        'grade_name' => $subject->grade->name ?? null,
                        'venue' => $subject->venue ? [
                            'id' => $subject->venue->id,
                            'name' => $subject->venue->name,
                            'type' => $subject->venue->type,
                            'capacity' => $subject->venue->capacity
                        ] : null,
                        'term_id' => $subject->term_id,
                        'year' => $subject->year,
                        'active' => $subject->active
                    ];
                }),
                'optional_subjects' => $optionalSubjects->map(function($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'subject_name' => $subject->gradeSubject->name ?? null,
                        'subject_code' => $subject->gradeSubject->code ?? null,
                        'grade_id' => $subject->grade_id,
                        'grade_name' => $subject->grade->name ?? null,
                        'grouping' => $subject->grouping,
                        'venue' => $subject->venue ? [
                            'id' => $subject->venue->id,
                            'name' => $subject->venue->name,
                            'type' => $subject->venue->type,
                            'capacity' => $subject->venue->capacity
                        ] : null,
                        'term_id' => $subject->term_id,
                        'student_count' => $subject->students()->count(),
                        'active' => $subject->active
                    ];
                }),
                'qualifications' => $staff->qualifications->map(function($qual) {
                    return [
                        'id' => $qual->id,
                        'qualification' => $qual->qualification,
                        'qualification_code' => $qual->qualification_code,
                        'level' => $qual->pivot->level,
                        'college' => $qual->pivot->college,
                        'start_date' => $qual->pivot->start_date,
                        'completion_date' => $qual->pivot->completion_date
                    ];
                }),
                'work_history' => $staff->workHistory->map(function($work) {
                    return [
                        'id' => $work->id,
                        'workplace' => $work->workplace,
                        'type_of_work' => $work->type_of_work,
                        'role' => $work->role,
                        'start_date' => $work->start,
                        'end_date' => $work->end,
                        'created_at' => $work->created_at,
                        'updated_at' => $work->updated_at
                    ];
                }),
                'subordinates' => $staff->subordinates->map(function($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->full_name,
                        'position' => $sub->position,
                        'department' => $sub->department
                    ];
                }),
                'departments' => [
                    'headed' => $staff->headedDepartments->map(function($dept) {
                        return [
                            'id' => $dept->id,
                            'name' => $dept->name ?? null
                        ];
                    }),
                    'assisted' => $staff->assistedDepartments->map(function($dept) {
                        return [
                            'id' => $dept->id,
                            'name' => $dept->name ?? null
                        ];
                    })
                ],
                'houses' => [
                    'as_head' => $staff->housesAsHead->map(function($house) {
                        return [
                            'id' => $house->id,
                            'name' => $house->name ?? null,
                            'color' => $house->color ?? null
                        ];
                    }),
                    'as_assistant' => $staff->housesAsAssistant->map(function($house) {
                        return [
                            'id' => $house->id,
                            'name' => $house->name ?? null,
                            'color' => $house->color ?? null
                        ];
                    })
                ],
                'has_allocated_class' => $staff->hasAllocatedClass(),
                'is_school_head' => $staff->isSchoolHead(),
                'has_valid_phone' => $staff->hasValidPhoneNumber(),
                'has_valid_email' => $staff->hasValidEmail()
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Staff member retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Staff show endpoint error', [
                'error' => $e->getMessage(),
                'staff_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving staff member',
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
            
            if (!$user->tokenCan('statistics.view') && !$user->tokenCan('staff.read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required permission'
                ], 403);
            }
            
            $this->logApiAccessSafely($request, 'view_staff_statistics');
            
            $basicStats = User::where('active', 1)
                ->select(
                    DB::raw('COUNT(*) as total_staff'),
                    DB::raw('COUNT(CASE WHEN status = "Current" THEN 1 END) as current_count'),
                    DB::raw('COUNT(CASE WHEN gender = "Male" OR gender = "M" THEN 1 END) as male_count'),
                    DB::raw('COUNT(CASE WHEN gender = "Female" OR gender = "F" THEN 1 END) as female_count'),
                    DB::raw('COUNT(CASE WHEN area_of_work = "Teaching" THEN 1 END) as teaching_count'),
                    DB::raw('COUNT(CASE WHEN area_of_work != "Teaching" OR area_of_work IS NULL THEN 1 END) as non_teaching_count')
                )->first();
            
            $byDepartment = User::where('active', 1)
                ->where('status', 'Current')
                ->select('department', DB::raw('COUNT(*) as count'))
                ->whereNotNull('department')
                ->groupBy('department')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'department');
            
            $byPosition = User::where('active', 1)
                ->where('status', 'Current')
                ->select('position', DB::raw('COUNT(*) as count'))
                ->whereNotNull('position')
                ->groupBy('position')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->pluck('count', 'position');
            
            $teacherStats = User::where('active', 1)
                ->where('area_of_work', 'Teaching')
                ->where('status', 'Current')
                ->select(
                    DB::raw('COUNT(*) as total_teachers'),
                    DB::raw('COUNT(CASE WHEN EXISTS (
                        SELECT 1 FROM klasses WHERE klasses.user_id = users.id AND klasses.active = 1
                    ) THEN 1 END) as allocated_teachers')
                )->first();
            
            $roleDistribution = DB::table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->join('roles', 'role_users.role_id', '=', 'roles.id')
                ->where('users.active', 1)
                ->where('users.status', 'Current')
                ->select('roles.name', DB::raw('COUNT(DISTINCT users.id) as count'))
                ->groupBy('roles.name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'name');
            
            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'summary' => [
                        'total_staff' => $basicStats->total_staff,
                        'current_staff' => $basicStats->current_count,
                        'gender_distribution' => [
                            'male' => $basicStats->male_count,
                            'female' => $basicStats->female_count,
                            'ratio' => $basicStats->female_count > 0 
                                ? round($basicStats->male_count / $basicStats->female_count, 2)
                                : 0
                        ],
                        'work_distribution' => [
                            'teaching' => $basicStats->teaching_count,
                            'non_teaching' => $basicStats->non_teaching_count,
                            'teaching_percentage' => $basicStats->total_staff > 0 
                                ? round(($basicStats->teaching_count / $basicStats->total_staff) * 100, 2)
                                : 0
                        ]
                    ],
                    'by_department' => $byDepartment,
                    'by_position' => $byPosition,
                    'by_role' => $roleDistribution,
                    'teacher_allocation' => [
                        'total_teachers' => $teacherStats->total_teachers,
                        'allocated_teachers' => $teacherStats->allocated_teachers,
                        'unallocated_teachers' => $teacherStats->total_teachers - $teacherStats->allocated_teachers,
                        'allocation_rate' => $teacherStats->total_teachers > 0 
                            ? round(($teacherStats->allocated_teachers / $teacherStats->total_teachers) * 100, 2)
                            : 0
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Statistics Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating statistics',
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
                'resource_type' => 'user',
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
