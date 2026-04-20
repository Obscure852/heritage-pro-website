<?php

namespace App\Http\Controllers;

use App\Exports\BoardingAnalysisExport;
use App\Exports\StudentClassStatisticalAnalysisExport;
use App\Exports\ClassListReportExport;
use App\Exports\StudentCustomReportExport;
use App\Exports\StudentImportExportReport;
use App\Helpers\AssessmentHelper;
use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Http\Controllers\AssessmentController;
use App\Models\GradeSubject;
use App\Models\OptionalSubject;
use App\Imports\BooksImport;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookAllocation;
use App\Models\Copy;
use App\Models\Grade;
use App\Models\House;
use App\Models\JCE;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\Publisher;
use App\Models\SchoolSetup;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\StudentBehaviour;
use App\Models\StudentDeparture;
use App\Models\StudentFilter;
use App\Models\StudentMedicalInformation;
use App\Models\StudentStatus;
use App\Models\StudentTerm;
use App\Models\StudentType;
use App\Models\Term;
use App\Models\Test;
use App\Models\User;
use App\Services\StudentService;
use App\Services\Activities\ActivityFeeService;
use App\Services\SchoolModeResolver;
use App\Services\StudentTermRemovalService;
use Auth;
use Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;


class StudentController extends Controller{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService){
        $this->middleware('auth');
        $this->studentService = $studentService;
    }

    private function buildStudentProfileContext(Student $student, ?int $termId = null): array
    {
        $resolvedTermId = $termId ?? (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $modeResolver = app(SchoolModeResolver::class);
        $studentLevel = $this->resolveStudentProfileLevel($student, $resolvedTermId, $modeResolver);
        $studentAssessmentDriver = $studentLevel !== null
            ? $modeResolver->assessmentDriverForLevel($studentLevel)
            : $this->assessmentDriverForContext($modeResolver->defaultAssessmentContext());

        return [
            'termId' => $resolvedTermId,
            'studentLevel' => $studentLevel,
            'studentAssessmentDriver' => $studentAssessmentDriver,
            'schoolType' => $this->legacySchoolTypeForAssessmentDriver($studentAssessmentDriver),
            'showPsleTab' => $studentAssessmentDriver === 'junior',
            'showJceTab' => $studentAssessmentDriver === 'senior',
            'usesPrimaryAcademicLayout' => $studentAssessmentDriver === 'primary',
            'usesJuniorAcademicLayout' => $studentAssessmentDriver === 'junior',
            'usesSeniorAcademicLayout' => $studentAssessmentDriver === 'senior',
        ];
    }

    private function resolveStudentProfileLevel(
        Student $student,
        int $termId,
        SchoolModeResolver $modeResolver
    ): ?string {
        $studentTerm = StudentTerm::with('grade:id,level')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->where('status', 'Current')
            ->first();

        if ($studentTerm?->grade) {
            return $modeResolver->levelForGrade($studentTerm->grade);
        }

        $currentGradeLevel = $modeResolver->levelForStudent($student);
        if ($currentGradeLevel !== null) {
            return $currentGradeLevel;
        }

        $currentClass = $student->relationLoaded('currentClassRelation')
            ? $student->currentClassRelation->first()
            : null;

        if (!$currentClass) {
            $currentClass = $student->classes()
                ->with('grade:id,level')
                ->wherePivot('term_id', $termId)
                ->first();
        } elseif (!$currentClass->relationLoaded('grade')) {
            $currentClass->load('grade:id,level');
        }

        if ($currentClass?->grade) {
            return $modeResolver->levelForGrade($currentClass->grade);
        }

        return match ($modeResolver->defaultAssessmentContext()) {
            SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY => SchoolSetup::LEVEL_PRIMARY,
            SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR => SchoolSetup::LEVEL_SENIOR,
            default => SchoolSetup::LEVEL_JUNIOR,
        };
    }

    private function assessmentDriverForContext(string $context): string
    {
        return match ($context) {
            SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY => 'primary',
            SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR => 'senior',
            default => 'junior',
        };
    }

    private function legacySchoolTypeForAssessmentDriver(string $assessmentDriver): string
    {
        return match ($assessmentDriver) {
            'primary' => SchoolSetup::TYPE_PRIMARY,
            'senior' => SchoolSetup::TYPE_SENIOR,
            default => SchoolSetup::TYPE_JUNIOR,
        };
    }

    private function applyTeacherFilter($query, $selectedTermId) {
        $user = auth()->user();
        $fullAccessRoles = ['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Students Admin', 'Students Edit', 'Student View'];

        if ($user->hasAnyRoles($fullAccessRoles)) {
            return $query;
        }

        // Class teacher: filter to only their assigned classes in the current term
        $klassIds = Klass::where('user_id', $user->id)->where('term_id', $selectedTermId)->pluck('id')->toArray();
        if (!empty($klassIds)) {
            $query->whereHas('classes', function ($q) use ($klassIds, $selectedTermId) {
                $q->whereIn('klass_id', $klassIds)
                  ->where('klass_student.term_id', $selectedTermId);
            });
            return $query;
        }

        // Subject teacher: filter to students from their taught subjects
        $coreStudentIds = KlassSubject::where('term_id', $selectedTermId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
            })
            ->with(['klass.students' => function ($q) use ($selectedTermId) {
                $q->wherePivot('term_id', $selectedTermId);
            }])
            ->get()
            ->pluck('klass.students')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        $optStudentIds = OptionalSubject::where('term_id', $selectedTermId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id);
            })
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        $allStudentIds = array_unique(array_merge($coreStudentIds, $optStudentIds));

        if (!empty($allStudentIds)) {
            $query->whereIn('students.id', $allStudentIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function index(){
        $this->authorize('viewAny', Student::class);

        $terms = self::terms();
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        
        $classes = Klass::where('term_id', $selectedTermId)->get();
        $grades = Grade::where('term_id', $selectedTermId)->get();
        $allStatuses = StudentStatus::orderBy('name')->get();
        
        $data = CacheHelper::getStudentsTermData();
        $school_data = SchoolSetup::first();

        return view('students.index', [
            'currentTerm' => $currentTerm,
            'terms' => $terms,
            'classes' => $classes,
            'grades' => $grades,
            'students' => $data['students'],
            'studentsWithNoClasses' => $data['studentsWithNoClasses'],
            'duplicateStudents' => $data['duplicateStudents'],
            'allStatuses' => $allStatuses,
            'school_data' => $school_data,
        ]);
    }

    public function getBadgeData(Request $request){
        $originalTermId = session('selected_term_id');
        if ($request->has('term_id')) {
            session(['selected_term_id' => $request->term_id]);
        }

        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

            $search = $request->input('search');
            $status = $request->input('status', 'Current');
            $gender = $request->input('gender');
            $class = $request->input('class');
            $grade = $request->input('grade');
            $boarding = $request->input('boarding');

            $studentsQuery = Student::with(['currentClassRelation', 'currentGrade'])
                ->whereHas('terms', function ($query) use ($selectedTermId, $status) {
                    $query->where('student_term.term_id', $selectedTermId)
                          ->where('student_term.status', $status);
                });

            $studentsQuery = $this->applyTeacherFilter($studentsQuery, $selectedTermId);

            if ($search) {
                $studentsQuery->where(function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
                });
            }
            
            if ($gender) {
                $studentsQuery->where('gender', $gender);
            }
            
            if ($class) {
                $studentsQuery->whereHas('classes', function ($query) use ($selectedTermId, $class) {
                    $query->where('klass_student.term_id', $selectedTermId)
                          ->where('klasses.name', $class);
                });
            }
            
            if ($grade) {
                $gradeId = Grade::where('name', $grade)
                               ->where('term_id', $selectedTermId)
                               ->value('id');
                
                if ($gradeId) {
                    $studentsQuery->whereHas('terms', function ($query) use ($selectedTermId, $gradeId) {
                        $query->where('student_term.term_id', $selectedTermId)
                              ->where('student_term.grade_id', $gradeId);
                    });
                }
            }

            if ($boarding !== null && $boarding !== '') {
                $studentsQuery->where('is_boarding', $boarding);
            }

            // Get counts using database aggregation (more efficient than loading all records)
            $totalStudents = (clone $studentsQuery)->count();
            $maleCount = (clone $studentsQuery)->where('gender', 'M')->count();
            $femaleCount = (clone $studentsQuery)->where('gender', 'F')->count();
            $specialNeedsCount = (clone $studentsQuery)->whereNotNull('student_type_id')->count();
            $boardingCount = (clone $studentsQuery)->where('is_boarding', true)->count();
            $dayCount = (clone $studentsQuery)->where('is_boarding', false)->count();

            // Use whereDoesntHave at database level instead of filter() - prevents N+1 queries
            $studentsWithNoClassesQuery = (clone $studentsQuery)->whereDoesntHave('classes', function ($query) use ($selectedTermId) {
                $query->where('klass_student.term_id', $selectedTermId);
            });
            $studentsWithNoClasses = $studentsWithNoClassesQuery->count();

            // Get the actual students with no classes (with eager loading)
            $studentsWithNoClassesCollection = $studentsWithNoClassesQuery
                ->with(['currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                }, 'currentGrade'])
                ->get();

            // Get all students for duplicate detection (only load required fields)
            $allStudentsForDuplicates = (clone $studentsQuery)
                ->select(['id', 'first_name', 'last_name', 'id_number', 'gender', 'date_of_birth'])
                ->with(['currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                }, 'currentGrade'])
                ->get();

            $duplicateStudents = $allStudentsForDuplicates->groupBy(function ($student) {
                return strtolower(trim($student->first_name . ' ' . $student->last_name));
            })->filter(function ($group) {
                return $group->count() > 1;
            })->flatten();

            return response()->json([
                'studentsWithNoClasses' => $studentsWithNoClasses,
                'duplicateStudentsCount' => $duplicateStudents->count(),
                'totalStudents' => $totalStudents,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'specialNeedsCount' => $specialNeedsCount,
                'boardingCount' => $boardingCount,
                'dayCount' => $dayCount,
                'studentsWithNoClassesCollection' => $studentsWithNoClassesCollection->map(function($student) {
                    $currentClass = $student->currentClassRelation->first();
                    return [
                        'id' => $student->id,
                        'full_name' => $student->full_name,
                        'formatted_id_number' => $student->formatted_id_number,
                        'gender' => $student->gender,
                        'date_of_birth' => $student->date_of_birth,
                        'current_grade' => $student->currentGrade ? [
                            'name' => $student->currentGrade->name
                        ] : null,
                        'current_class' => $currentClass ? [
                            'name' => $currentClass->name
                        ] : null
                    ];
                })->toArray(),
                'duplicateStudents' => $duplicateStudents->map(function($student) {
                    $currentClass = $student->currentClassRelation->first();
                    return [
                        'id' => $student->id,
                        'full_name' => $student->full_name,
                        'formatted_id_number' => $student->formatted_id_number,
                        'gender' => $student->gender,
                        'date_of_birth' => $student->date_of_birth,
                        'current_grade' => $student->currentGrade ? [
                            'name' => $student->currentGrade->name
                        ] : null,
                        'current_class' => $currentClass ? [
                            'name' => $currentClass->name
                        ] : null
                    ];
                })->toArray()
            ]);
        } finally {
            if ($request->has('term_id') && $originalTermId !== null) {
                session(['selected_term_id' => $originalTermId]);
            }
        }
    }

    public function getTermData(Request $request){
        $search = $request->input('search');
        $status = $request->input('status', 'Current');
        $gender = $request->input('gender');
        $class = $request->input('class');
        $grade = $request->input('grade');
        $boarding = $request->input('boarding');
        $perPage = $request->input('per_page', 50); // Default 50 students per page

        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $studentsQuery = Student::with([
                'currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                },
                'currentGrade',
                'type'
            ])
            ->whereHas('terms', function ($query) use ($selectedTermId, $status) {
                $query->where('student_term.term_id', $selectedTermId)
                      ->where('student_term.status', $status);
            });

        $studentsQuery = $this->applyTeacherFilter($studentsQuery, $selectedTermId);

        // Search by name
        if ($search) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
            });
        }

        if ($gender) {
            $studentsQuery->where('gender', $gender);
        }

        if ($class) {
            $studentsQuery->whereHas('classes', function ($query) use ($selectedTermId, $class) {
                $query->where('klass_student.term_id', $selectedTermId)
                      ->where('klasses.name', $class);
            });
        }

        if ($grade) {
            $gradeId = Grade::where('name', $grade)
                           ->where('term_id', $selectedTermId)
                           ->value('id');

            if ($gradeId) {
                $studentsQuery->whereHas('terms', function ($query) use ($selectedTermId, $gradeId) {
                    $query->where('student_term.term_id', $selectedTermId)
                          ->where('student_term.grade_id', $gradeId);
                });
            }
        }

        if ($boarding !== null && $boarding !== '') {
            $studentsQuery->where('is_boarding', $boarding);
        }

        // Use pagination instead of loading all students
        $students = $studentsQuery->orderBy('first_name')->orderBy('last_name')->paginate($perPage);

        $classes = Klass::where('term_id', $selectedTermId)->get();
        $grades = Grade::where('term_id', $selectedTermId)->get();
        $allStatuses = StudentStatus::orderBy('name')->get();

        // Use database-level count with whereDoesntHave instead of N+1 filter
        $studentsWithNoClasses = (clone $studentsQuery)->whereDoesntHave('classes', function ($query) use ($selectedTermId) {
            $query->where('klass_student.term_id', $selectedTermId);
        })->count();

        // Get duplicate students using database aggregation
        $duplicateNames = DB::table('students')
            ->select(DB::raw('LOWER(CONCAT(first_name, " ", last_name)) as full_name'))
            ->whereNull('deleted_at')
            ->whereExists(function ($query) use ($selectedTermId, $status) {
                $query->select(DB::raw(1))
                    ->from('student_term')
                    ->whereRaw('student_term.student_id = students.id')
                    ->where('student_term.term_id', $selectedTermId)
                    ->where('student_term.status', $status);
            })
            ->groupBy(DB::raw('LOWER(CONCAT(first_name, " ", last_name))'))
            ->havingRaw('COUNT(*) > 1')
            ->pluck('full_name');

        $duplicateStudents = $duplicateNames->isNotEmpty()
            ? Student::with(['currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                }, 'currentGrade'])
                ->whereRaw('LOWER(CONCAT(first_name, " ", last_name)) IN (' . $duplicateNames->map(fn($n) => "'" . addslashes($n) . "'")->implode(',') . ')')
                ->whereHas('terms', function ($query) use ($selectedTermId, $status) {
                    $query->where('student_term.term_id', $selectedTermId)
                          ->where('student_term.status', $status);
                })
                ->get()
            : collect();

        $viewData = [
            'students' => $students,
            'studentsWithNoClasses' => $studentsWithNoClasses,
            'duplicateStudents' => $duplicateStudents,
            'classes' => $classes,
            'grades' => $grades,
            'allStatuses' => $allStatuses
        ];

        if ($request->ajax()) {
            return view('students.students-term', $viewData);
        }

        return view('students.students-term', $viewData);
    }

    public static function terms(){
        $currentDate = now();
        $currentTerm = TermHelper::getCurrentTerm();

        if (!$currentTerm) {
            $previousYearTerms = Term::whereYear('start_date', $currentDate->year - 1)->orderBy('start_date')->get();
            $currentYearTerms = Term::whereYear('start_date', $currentDate->year)->orderBy('start_date')->get();
            $nextYearTerms = Term::whereYear('start_date', $currentDate->year + 1)->orderBy('start_date')->limit(2)->get();
            $terms = $previousYearTerms->concat($currentYearTerms)->concat($nextYearTerms);
            return $terms;
        }

        $termsBefore = Term::where('id', '<', $currentTerm->id)->orderBy('id', 'desc')->limit(3)->get();
        $termsAfter = Term::where('id', '>', $currentTerm->id)->orderBy('id', 'asc')->limit(5)->get();
        $terms = $termsBefore->reverse()->concat([$currentTerm])->concat($termsAfter);
        return $terms;
    }


    public function setTermSession(Request $request){
        $request->validate([
            'term_id' => 'required|integer|exists:terms,id'
        ]);
        
        $termId = (int) $request->term_id;
        session(['selected_term_id' => $termId]);
    
        $currentTerm = TermHelper::getCurrentTerm();
        $currentTermId = (int) $currentTerm->id;
        
        $is_past_term = $termId < $currentTermId;
    
        CacheHelper::forgetDashboardNotifications();
        CacheHelper::forgetStudentsDashboard($termId);
    
        $request->session()->put('is_past_term', $is_past_term);
        
        return response()->json([
            'message' => 'Term set in session.',
            'selected_term_id' => $termId,
            'is_past_term' => $is_past_term
        ]);
    }

    public function duplicates(){
        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $currentTerm = TermHelper::getCurrentTerm();
            $terms = $this->terms();

            $allStudents = Student::with(['currentClassRelation', 'currentGrade'])
                ->whereHas('terms', function ($query) use ($selectedTermId) {
                    $query->where('student_term.term_id', $selectedTermId)
                        ->where('student_term.status', 'Current');
                })->orderBy('first_name')->orderBy('last_name')->get();

            $grouped = $allStudents->groupBy(function ($student) {
                return strtolower(trim($student->first_name . ' ' . $student->last_name));
            });

            $duplicateGroups = $grouped->filter(function ($group) {
                return $group->count() > 1;
            });

            $duplicateStudents = $duplicateGroups->flatten();
            $schoolData = SchoolSetup::first();

            return view('students.duplicate-students-view', compact(
                'duplicateStudents',
                'currentTerm',
                'terms',
                'schoolData'
            ));
        } catch (Exception $e) {
            Log::error('Error loading duplicate students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading duplicate students');
        }
    }

    public function unallocated(){
        $this->authorize('allocateClass', Student::class);

        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $currentTerm = TermHelper::getCurrentTerm();
            $terms = $this->terms();

            $unallocatedStudents = Student::whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('student_term.term_id', $selectedTermId)
                    ->where('student_term.status', 'Current');
            })
            ->whereDoesntHave('classes', function ($query) use ($selectedTermId) {
                $query->where('klass_student.term_id', $selectedTermId);
            })
            ->with(['currentGrade'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

            $schoolData = SchoolSetup::first();

            return view('students.unallocated-students-view', compact(
                'unallocatedStudents',
                'currentTerm',
                'terms',
                'schoolData'
            ));
        } catch (Exception $e) {
            Log::error('Error loading unallocated students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading unallocated students');
        }
    }

    public function deleteStudent($id){
        $student = Student::findOrFail($id);
        $this->authorize('delete', $student);

        try {
            $result = $this->studentService->deleteStudent($student);

            return redirect()->route('students.index')->with('message', $result['message']);
        } catch (Exception $e) {
            return redirect()->route('students.index')->with(
                'error',
                $e->getMessage() ?: 'An error occurred while trying to delete the student. Please try again.'
            );
        }
    }

    public function deleteMultiple(Request $request){
        $this->authorize('deleteMultiple', Student::class);

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        try {
            $result = $this->studentService->deleteMultipleStudents($request->student_ids);

            $message = "Successfully deleted {$result['deleted_count']} student(s)";
            if ($result['skipped_count'] > 0) {
                $message .= ". Skipped {$result['skipped_count']} student(s) with class allocations";
            }
            $message .= ".";

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $result['deleted_count'],
                'skipped_count' => $result['skipped_count'],
                'deleted_names' => $result['deleted_names'],
                'skipped_names' => $result['skipped_names']
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting multiple students: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while deleting students. Please try again.',
                'success' => false
            ], 500);
        }
    }

    public function getClassesByGrade(Request $request, $gradeId){
        try {
            $query = Klass::query()
                ->where('active', 1)
                ->where('grade_id', $gradeId);

            if ($request->boolean('for_class_allocations')) {
                $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
                $query->where('term_id', $selectedTermId);

                $isClassAllocationAdmin = auth()->user()->hasAnyRoles([
                    'Administrator',
                    'HOD',
                    'Academic Admin',
                    'Academic Edit',
                    'Assessment Admin',
                ]);

                if (!$isClassAllocationAdmin) {
                    $query->where('user_id', auth()->id());
                }
            }

            $classes = $query->get(['id', 'name']);

            return response()->json(['success' => true, 'data' => $classes]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching classes',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id){
        $termId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        // Load student with optimized eager loading - no need for ini_set memory hack
        $student = Student::with([
            'type',
            'currentClassRelation' => function ($q) use ($termId) {
                $q->select('klasses.id', 'klasses.name', 'klasses.grade_id', 'klasses.active')
                  ->where('klass_student.term_id', $termId);
            },
            'currentClassRelation.grade:id,name,level',
            'currentGrade',
            // Load all exam results across all terms for academic history
            'tests' => function ($q) {
                $q->where('type', 'Exam')
                  ->with(['term:id,term,year', 'subject.subject:id,name', 'subject:id,subject_id,grade_id'])
                  ->orderBy('term_id', 'desc');
            },
            'classes',
            // Limit book allocations to recent ones (last 2 years)
            'bookAllocations' => function ($q) {
                $q->select('book_allocations.id', 'student_id', 'copy_id', 'grade_id', 'allocation_date', 'due_date', 'return_date', 'accession_number')
                  ->where('allocation_date', '>=', now()->subYears(2));
            },
            'bookAllocations.book:id,title',
            'bookAllocations.grade:id,name',
            'sponsor:id,first_name,last_name,email',
            'currentHouseRelation' => function ($q) use ($termId) {
                $q->wherePivot('term_id', $termId);
            },
            'studentMedicals',
            // Limit behaviour records to recent ones
            'studentbehaviour' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(50);
            },
            'psle',
            'jce'
        ])->findOrFail($id);

        $this->authorize('view', $student);

        try {
            // Only load houses for the current term
            $houses = House::where('term_id', $termId)->select('id', 'name')->get();

            $currentGradeId = $student->currentGrade->id ?? null;
            $grades = Grade::where('term_id', $termId)->select('id', 'name')->get();
            $sponsors = Sponsor::select('id', 'first_name', 'last_name')->get();
            $statuses = StudentStatus::select('name')->get();
            $books = $currentGradeId
                ? Book::where('grade_id', $currentGradeId)->select('id', 'title', 'grade_id')->get()
                : collect();
            $classes = $currentGradeId
                ? Klass::where('grade_id', $currentGradeId)->where('term_id', $termId)->select('id', 'name', 'grade_id')->get()
                : collect();

            // Detect school type and calculate term totals for academic tab
            $termCalculations = [];
            $currentClass = $student->currentClassRelation ? $student->currentClassRelation->first() : null;
            $school_data = SchoolSetup::first();
            $profileContext = $this->buildStudentProfileContext($student, $termId);
            $activitySummary = app(ActivityFeeService::class)->studentSummary($student, $termId);
            $schoolType = $profileContext['schoolType'];

            try {
                $examGroups = $student->tests ? $student->tests->where('type', 'Exam')->groupBy('term_id') : collect([]);
                $isForeigner = $student->nationality !== 'Motswana';

                foreach ($examGroups as $examTermId => $termExams) {
                    if ($termExams->isEmpty()) {
                        continue;
                    }

                    $termClass = $student->classes()->wherePivot('term_id', $examTermId)->first() ?? $currentClass;
                    $totalPoints = 0;
                    $overallGrade = null;
                    $averagePercentage = null;
                    $bestSubjects = null;
                    $totalScore = 0;
                    $totalOutOf = 0;

                    if ($schoolType === 'Junior') {
                        // Junior: Use AssessmentHelper for mandatory/optional/core calculation
                        if ($termClass && $termClass->grade_id) {
                            $gradeSubjects = GradeSubject::where('grade_id', $termClass->grade_id)
                                ->where('term_id', $examTermId)->get();

                            if ($gradeSubjects->isNotEmpty()) {
                                list($m, $o, $c) = AssessmentHelper::calculatePoints($student, $gradeSubjects, $examTermId, $isForeigner);
                                $totalPoints = $m + $o + $c;
                                $overallGrade = AssessmentHelper::determineGrade($totalPoints, $termClass);
                            }
                        }
                    } elseif ($schoolType === 'Senior') {
                        // Senior: Best 6 subjects by points (slot-based)
                        $scores = [];
                        foreach ($termExams as $test) {
                            $points = $test->pivot->points ?? 0;
                            $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : '';
                            $slotsNeeded = (strtolower($subjectName) === 'double science') ? 2 : 1;
                            $scores[] = [
                                'subject' => $subjectName,
                                'points' => $points,
                                'slotsNeeded' => $slotsNeeded,
                            ];
                        }
                        // Sort by points descending
                        usort($scores, fn($a, $b) => $b['points'] <=> $a['points']);

                        // Select best 6 slots
                        $totalSlots = 0;
                        $bestSubjects = [];
                        foreach ($scores as $score) {
                            if ($totalSlots + $score['slotsNeeded'] <= 6) {
                                $bestSubjects[] = $score;
                                $totalSlots += $score['slotsNeeded'];
                                $totalPoints += ($score['slotsNeeded'] === 2) ? $score['points'] * 2 : $score['points'];
                            }
                            if ($totalSlots >= 6) break;
                        }
                        // Senior: No overall grade calculation
                    } elseif ($schoolType === 'Primary') {
                        // Primary: Average percentage
                        $totalScore = $termExams->sum(fn($t) => $t->pivot->score ?? 0);
                        $totalOutOf = $termExams->sum(fn($t) => $t->out_of ?? 100);
                        $averagePercentage = $totalOutOf > 0 ? round(($totalScore / $totalOutOf) * 100, 1) : 0;
                        // Get overall grade from percentage
                        $gradeId = ($termClass && $termClass->grade_id) ? $termClass->grade_id : null;
                        $overallGrade = $gradeId ? AssessmentController::getOverallGrade($gradeId, $averagePercentage) : null;
                    }

                    $termCalculations[$examTermId] = [
                        'totalPoints' => $totalPoints,
                        'overallGrade' => $overallGrade,
                        'averagePercentage' => $averagePercentage,
                        'bestSubjects' => $bestSubjects,
                        'totalScore' => $totalScore,
                        'totalOutOf' => $totalOutOf,
                    ];
                }
            } catch (Exception $e) {
                Log::warning('Term calculations failed for student', [
                    'student_id' => $id,
                    'error' => $e->getMessage()
                ]);
                // Continue with empty termCalculations - page will still load
            }

            $viewData = [
                'student'         => $student,
                'classes' => $classes,
                'grades' => $grades,
                'sponsors'        => $sponsors,
                'terms' => CacheHelper::getTerms(),
                'statuses' => $statuses,
                'nationalities'   => CacheHelper::getNationalities(),
                'types'           => CacheHelper::getStudentTypes(),
                'filters'         => CacheHelper::getStudentFilters(),
                'books' => $books ?? collect(),
                'bookAllocations' => $student->bookAllocations->groupBy('grade.name'),
                'school_data'     => $school_data,
                'houses' => $houses,
                'termCalculations' => $termCalculations,
            ];

            $viewData = array_merge($viewData, $profileContext);
            $viewData['activitySummary'] = $activitySummary;

            return view('students.students-view', $viewData);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('students.index')->with('error', 'Student not found');
        } catch (Exception $e) {
            Log::error('Student Profile Error', [
                'student_id' => $id,
                'error' => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);
            return redirect()->route('students.index')->with('error', 'An error occurred while loading student details');
        }
    }

    /**
     * Export student progress report to PDF
     */
    public function exportProgressReport($id) {
        $student = Student::with([
            'currentClassRelation' => function ($q) {
                $q->select('klasses.id', 'klasses.name', 'klasses.grade_id', 'klasses.active');
            },
            'currentClassRelation.grade:id,name,level',
            'tests' => function ($q) {
                $q->where('type', 'Exam')
                  ->with(['term:id,term,year', 'subject.subject:id,name', 'subject:id,subject_id,grade_id'])
                  ->orderBy('term_id', 'desc');
            },
            'psle',
            'jce',
            'classes',
        ])->findOrFail($id);

        $this->authorize('view', $student);

        $examGroups = $student->tests
            ? $student->tests->groupBy('term_id')
            : collect([]);

        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentClass = $student->currentClassRelation ? $student->currentClassRelation->first() : null;
        $school_data = SchoolSetup::first();
        $profileContext = $this->buildStudentProfileContext($student, $selectedTermId);
        $schoolType = $profileContext['schoolType'];

        // Calculate points and grade for each term
        $termData = [];
        $isForeigner = $student->nationality !== 'Motswana';

        foreach ($examGroups as $termId => $termExams) {
            $totalPoints = 0;
            $overallGrade = null;
            $averagePercentage = null;
            $bestSubjects = null;

            try {
                if ($termExams->isEmpty()) {
                    $termData[$termId] = [
                        'exams' => $termExams,
                        'totalPoints' => 0,
                        'overallGrade' => null,
                        'averagePercentage' => null,
                        'bestSubjects' => null,
                    ];
                    continue;
                }

                // Get the class for this term
                $termClass = $student->classes()->wherePivot('term_id', $termId)->first() ?? $currentClass;

                if ($schoolType === 'Junior') {
                    // Junior: Use AssessmentHelper for mandatory/optional/core calculation
                    $firstExam = $termExams->first();
                    $termGradeId = ($firstExam && $firstExam->subject)
                        ? $firstExam->subject->grade_id
                        : ($currentClass ? $currentClass->grade_id : null);

                    if ($termGradeId && $termClass) {
                        $gradeSubjects = GradeSubject::where('grade_id', $termGradeId)
                            ->where('term_id', $termId)
                            ->get();

                        if ($gradeSubjects->isNotEmpty()) {
                            list($m, $o, $c) = AssessmentHelper::calculatePoints($student, $gradeSubjects, $termId, $isForeigner);
                            $totalPoints = $m + $o + $c;
                            $overallGrade = AssessmentHelper::determineGrade($totalPoints, $termClass);
                        }
                    }
                } elseif ($schoolType === 'Senior') {
                    // Senior: Best 6 subjects by points (slot-based)
                    $scores = [];
                    foreach ($termExams as $test) {
                        $points = $test->pivot->points ?? 0;
                        $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : '';
                        $slotsNeeded = (strtolower($subjectName) === 'double science') ? 2 : 1;
                        $scores[] = [
                            'subject' => $subjectName,
                            'points' => $points,
                            'slotsNeeded' => $slotsNeeded,
                        ];
                    }
                    // Sort by points descending
                    usort($scores, fn($a, $b) => $b['points'] <=> $a['points']);

                    // Select best 6 slots
                    $totalSlots = 0;
                    $bestSubjects = [];
                    foreach ($scores as $score) {
                        if ($totalSlots + $score['slotsNeeded'] <= 6) {
                            $bestSubjects[] = $score;
                            $totalSlots += $score['slotsNeeded'];
                            $totalPoints += ($score['slotsNeeded'] === 2) ? $score['points'] * 2 : $score['points'];
                        }
                        if ($totalSlots >= 6) break;
                    }
                    // Senior: No overall grade calculation
                } elseif ($schoolType === 'Primary') {
                    // Primary: Average percentage
                    $totalScore = $termExams->sum(fn($t) => $t->pivot->score ?? 0);
                    $totalOutOf = $termExams->sum(fn($t) => $t->out_of ?? 100);
                    $averagePercentage = $totalOutOf > 0 ? round(($totalScore / $totalOutOf) * 100, 1) : 0;
                    // Get overall grade from percentage
                    $gradeId = ($termClass && $termClass->grade_id) ? $termClass->grade_id : null;
                    $overallGrade = $gradeId ? AssessmentController::getOverallGrade($gradeId, $averagePercentage) : null;
                }
            } catch (Exception $e) {
                Log::warning('Term calculation failed in progress report', [
                    'student_id' => $id,
                    'term_id' => $termId,
                    'school_type' => $schoolType,
                    'error' => $e->getMessage()
                ]);
            }

            $termData[$termId] = [
                'exams' => $termExams,
                'totalPoints' => $totalPoints,
                'overallGrade' => $overallGrade,
                'averagePercentage' => $averagePercentage,
                'bestSubjects' => $bestSubjects,
                'totalScore' => $totalScore ?? 0,
                'totalOutOf' => $totalOutOf ?? 0,
            ];
        }

        $data = [
            'student' => $student,
            'termData' => $termData,
            'currentClass' => $currentClass,
            'school_data' => $school_data,
        ];

        $data = array_merge($data, $profileContext);

        // Use different view based on school type
        $viewName = match ($schoolType) {
            'Primary' => 'students.primary-progress-report-pdf',
            'Senior' => 'students.senior-progress-report-pdf',
            default => 'students.junior-progress-report-pdf',
        };

        $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'portrait');
        $filename = 'progress_report_' . Str::slug($student->first_name . '_' . $student->last_name) . '_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function studentsCustomAnalysis(){
        $school_data = SchoolSetup::first();
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $grades = Grade::where('term_id', $termId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();
        $studentTypes = StudentType::orderBy('type')->get();

        return view('students.students-custom-analysis', [
            'school_data' => $school_data,
            'grades' => $grades,
            'termId' => $termId,
            'student_types' => $studentTypes,
        ]);
    }

    public function getClasses(Request $request){
        $gradeId = $request->input('grade_id');
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $classes = Klass::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->with('teacher:id,firstname,lastname')
            ->orderBy('name')
            ->get(['id', 'name', 'user_id'])
            ->map(function ($klass) {
                $teacherName = optional($klass->teacher)->full_name ?? 'Not Assigned';

                return (object) [
                    'id' => $klass->id,
                    'name' => $klass->name,
                    'teacher_name' => $teacherName,
                    'label' => $klass->name . ' (Class Teacher: ' . $teacherName . ')',
                ];
            });

        // Prepend "All Classes" option
        $grade = Grade::where('id', $gradeId)
            ->where('term_id', $termId)
            ->first();
        $allClassesLabel = 'All Classes in ' . ($grade->name ?? 'Grade');
        $allOption = (object) [
            'id' => 'all',
            'name' => $allClassesLabel,
            'teacher_name' => null,
            'label' => $allClassesLabel,
        ];

        return response()->json(collect([$allOption])->merge($classes));
    }

    public function getFields(){
        $school_data = SchoolSetup::first();

        $fields = [
            'exam_number'        => 'Exam Number',
            'first_name'         => 'First Name',
            'last_name'          => 'Last Name',
            'gender'             => 'Gender',
            'date_of_birth'      => 'Date of Birth',
            'class'              => 'Class',
            'house_id'           => 'House',
            'sponsor_id'         => 'Parent Name',
            'sponsor_phone'      => 'Parent Phone',
            'sponsor_telephone'  => 'Parent Telephone',
            'parent_email'       => 'Parent Email',
            'physical_address'   => 'Physical Address',
            'parent_workplace'   => 'Parent Workplace',
            'parent_profession'  => 'Parent Profession',
            'student_email'      => 'Student Email',
            'nationality'        => 'Nationality',
            'student_type'       => 'Type',
            'id_number'          => 'ID Number',
            'status'             => 'Status',
            'klass_subjects'     => 'Class Subjects',
            'optional_subjects'  => 'Optional Subjects',
        ];

        $valueAdditionSchoolTypes = app(SchoolModeResolver::class)->valueAdditionSchoolTypes($school_data->type ?? null);

        if (in_array(SchoolSetup::TYPE_JUNIOR, $valueAdditionSchoolTypes, true)) {
            $fields['psle_overall_grade'] = 'PSLE';
        }

        if (in_array(SchoolSetup::TYPE_SENIOR, $valueAdditionSchoolTypes, true)) {
            $fields['jce_overall'] = 'JCE';
        }

        return response()->json($fields);
    }

    public function generateReport(Request $request){
        $this->authorize('export', Student::class);

        $gradeId      = $request->grade_id;
        $classId      = $request->class_id;
        $statusFilter = $request->status_filter;
        $genderFilter = $request->gender_filter;
        $typeFilter   = $request->student_type_filter;
        $fields       = $request->fields ?? [];
        $school_data  = SchoolSetup::first();
        $termId       = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $desiredOrder = [
            'exam_number', 'first_name', 'last_name', 'gender', 'date_of_birth',
            'psle_overall_grade', 'jce_overall', 'class', 'house_id', 'sponsor_id', 'sponsor_phone',
            'sponsor_telephone', 'parent_email', 'physical_address', 'parent_workplace',
            'parent_profession', 'student_email', 'nationality', 'student_type',
            'id_number', 'status', 'klass_subjects', 'optional_subjects',
        ];

        if (!in_array('class', $fields, true)) {
            $fields[] = 'class';
        }

        if (in_array('sponsor_id', $fields, true) && !in_array('sponsor_phone', $fields, true)) {
            $fields[] = 'sponsor_phone';
        }

        usort($fields, fn ($a, $b) =>
            array_search($a, $desiredOrder, true) <=> array_search($b, $desiredOrder, true)
        );

        $field_headers = [
            'exam_number'        => 'Exam Number',
            'first_name'         => 'First Name',
            'last_name'          => 'Last Name',
            'gender'             => 'Gender',
            'date_of_birth'      => 'Date of Birth',
            'psle_overall_grade' => 'PSLE',
            'jce_overall'        => 'JCE',
            'class'              => 'Class',
            'house_id'           => 'House',
            'sponsor_id'         => 'Parent Name',
            'sponsor_phone'      => 'Parent Phone',
            'sponsor_telephone'  => 'Parent Telephone',
            'parent_email'       => 'Parent Email',
            'physical_address'   => 'Physical Address',
            'parent_workplace'   => 'Parent Workplace',
            'parent_profession'  => 'Parent Profession',
            'student_email'      => 'Student Email',
            'nationality'        => 'Nationality',
            'student_type'       => 'Type',
            'id_number'          => 'ID Number',
            'status'             => 'Status',
            'klass_subjects'     => 'Class Subjects',
            'optional_subjects'  => 'Optional Subjects',
        ];

        $studentTableCols = [
            'exam_number', 'first_name', 'last_name', 'middle_name', 'email',
            'gender', 'date_of_birth', 'nationality', 'id_number', 'status', 'year',
        ];

        $baseCols   = ['id', 'sponsor_id', 'student_type_id'];
        $selectCols = array_unique(array_merge(
            $baseCols,
            array_intersect($fields, $studentTableCols),
            in_array('student_email', $fields, true) ? ['email'] : []
        ));

        // Build query with expanded eager loading for contact fields
        $studentsQuery = Student::query()->with([
            'sponsor:id,first_name,last_name,phone,telephone,email,profession,work_place',
            'sponsor.otherInformation:id,sponsor_id,address',
            'houses' => function($query) use ($termId) {
                $query->select('id', 'name')->wherePivot('term_id', $termId);
            },
            'classes' => function($query) use ($termId) {
                $query->where('klasses.term_id', $termId);
            },
        ]);

        if (in_array('psle_overall_grade', $fields, true)) {
            $studentsQuery->with('psle:id,student_id,overall_grade');
        }
        if (in_array('jce_overall', $fields, true)) {
            $studentsQuery->with('jce:id,student_id,overall');
        }
        if (in_array('student_type', $fields, true)) {
            $studentsQuery->with('type:id,type');
        }

        if (in_array('klass_subjects', $fields, true)) {
            $studentsQuery->with([
                'classes' => function($query) use ($termId) {
                    $query->where('klasses.term_id', $termId)
                          ->with(['subjects.gradeSubject.subject:id,name']);
                }
            ]);
        }

        if (in_array('optional_subjects', $fields, true)) {
            $studentsQuery->with([
                'optionalSubjects' => function($query) use ($termId) {
                    $query->wherePivot('term_id', $termId)
                          ->with('gradeSubject.subject:id,name');
                }
            ]);
        }

        // Apply grade/class filtering
        if ($gradeId === 'all') {
            $studentsQuery->whereHas('classes', function ($q) use ($termId) {
                $q->where('klasses.term_id', $termId);
            });
        } elseif ($classId === 'all') {
            $studentsQuery->whereHas('classes', function ($q) use ($termId, $gradeId) {
                $q->where('klasses.term_id', $termId)
                  ->where('klasses.grade_id', $gradeId);
            });
        } else {
            $studentsQuery->whereHas('classes', function ($q) use ($termId, $classId) {
                $q->where('klass_id', $classId)
                  ->where('klasses.term_id', $termId);
            });
        }

        // Apply additional filters
        if ($statusFilter && $statusFilter !== 'all') {
            $studentsQuery->where('status', $statusFilter);
        }
        if ($genderFilter && $genderFilter !== 'all') {
            $studentsQuery->where('gender', $genderFilter);
        }
        if ($typeFilter && $typeFilter !== 'all') {
            $studentsQuery->where('student_type_id', $typeFilter);
        }

        $students = $studentsQuery->select($selectCols)->orderBy('last_name')->orderBy('first_name')->get();

        // Calculate summary statistics
        $statistics = [
            'total_count'  => $students->count(),
            'male_count'   => $students->where('gender', 'M')->count(),
            'female_count' => $students->where('gender', 'F')->count(),
            'by_status'    => $students->groupBy('status')->map->count(),
            'by_type'      => $students->groupBy(fn($s) => optional($s->type)->type ?? 'Unassigned')->map->count(),
        ];

        $viewData = [
            'students'      => $students,
            'fields'        => $fields,
            'school_data'   => $school_data,
            'field_headers' => array_intersect_key($field_headers, array_flip($fields)),
            'statistics'    => $statistics,
        ];

        // Handle Excel export
        if ($request->input('export_action') === 'excel') {
            return Excel::download(
                new StudentCustomReportExport($students, $fields, array_intersect_key($field_headers, array_flip($fields)), $statistics),
                'student-custom-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // Handle PDF export
        if ($request->input('export_action') === 'pdf') {
            $pdf = Pdf::loadView('exports.students.students-custom-report-pdf', $viewData);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('student-custom-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('students.students-custom-report', $viewData);
    }

    public function classListReport() {
        $school_data = SchoolSetup::first();
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $grades = Grade::where('term_id', $termId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        return view('students.class-list-report', [
            'school_data' => $school_data,
            'grades' => $grades,
            'termId' => $termId,
        ]);
    }

    public function getClassListOptions(Request $request) {
        $gradeId = $request->input('grade_id');
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $classes = Klass::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->with('teacher:id,firstname,lastname')
            ->orderBy('name')
            ->get(['id', 'name', 'user_id'])
            ->map(function ($klass) {
                $teacherName = optional($klass->teacher)->full_name ?? 'Not Assigned';
                return [
                    'id' => 'class_' . $klass->id,
                    'name' => $klass->name,
                    'teacher_name' => $teacherName,
                    'label' => $klass->name . ' (Class Teacher: ' . $teacherName . ')',
                ];
            });

        $optionalSubjects = OptionalSubject::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->with([
                'teacher:id,firstname,lastname',
                'assistantTeacher:id,firstname,lastname',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'user_id', 'assistant_user_id'])
            ->map(function ($optionalSubject) {
                $teacherNames = collect([
                    optional($optionalSubject->teacher)->full_name,
                    optional($optionalSubject->assistantTeacher)->full_name,
                ])->filter()->unique()->values();

                $teacherLabel = $teacherNames->isNotEmpty()
                    ? $teacherNames->implode(' / ')
                    : 'Not Assigned';

                return [
                    'id' => 'optional_' . $optionalSubject->id,
                    'name' => $optionalSubject->name,
                    'teacher_name' => $teacherLabel,
                    'label' => $optionalSubject->name . ' (Subject Teacher: ' . $teacherLabel . ')',
                ];
            });

        return response()->json([
            'classes' => $classes->values(),
            'optional_subjects' => $optionalSubjects->values(),
        ]);
    }

    public function classListPreview(Request $request) {
        $request->validate([
            'selection' => ['required', 'regex:/^(class|optional)_\d+$/'],
        ]);

        $data = $this->resolveClassListData($request->input('selection'));

        return response()->json([
            'list_name' => $data['list_name'],
            'grade_name' => $data['grade_name'],
            'statistics' => $data['statistics'],
            'students' => $data['students']->map(fn($s, $i) => [
                'index' => $i + 1,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'gender' => $s->gender,
                'psle' => optional($s->psle)->overall_grade ?? '-',
            ])->values(),
        ]);
    }

    public function generateClassListReport(Request $request) {
        $this->authorize('export', Student::class);

        $request->validate([
            'selection' => ['required', 'regex:/^(class|optional)_\d+$/'],
            'export_action' => ['required', 'in:excel,pdf'],
        ]);

        $data = $this->resolveClassListData($request->input('selection'));
        $data['school_data'] = SchoolSetup::first();

        if ($request->input('export_action') === 'excel') {
            return Excel::download(
                new ClassListReportExport($data),
                'class-list-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.students.class-list-report-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('class-list-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function resolveClassListData(string $selection): array {
        [$type, $id] = explode('_', $selection, 2);
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        if ($type === 'class') {
            $klass = Klass::findOrFail($id);
            $listName = $klass->name;
            $gradeName = optional($klass->grade)->name ?? 'Grade';

            $students = Student::where('status', 'Current')
                ->whereHas('classes', function ($q) use ($id, $termId) {
                    $q->where('klass_id', $id)
                      ->where('klasses.term_id', $termId);
                })
                ->with('psle:id,student_id,overall_grade')
                ->select('id', 'first_name', 'last_name', 'gender', 'is_boarding')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        } else {
            $optionalSubject = OptionalSubject::findOrFail($id);
            $listName = $optionalSubject->name;
            $gradeName = optional($optionalSubject->grade)->name ?? 'Grade';

            $students = Student::where('status', 'Current')
                ->whereHas('optionalSubjects', function ($q) use ($id, $termId) {
                    $q->where('optional_subject_id', $id)
                      ->where('student_optional_subjects.term_id', $termId);
                })
                ->with('psle:id,student_id,overall_grade')
                ->select('id', 'first_name', 'last_name', 'gender', 'is_boarding')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        $school_data = SchoolSetup::first();

        return [
            'students' => $students,
            'list_name' => $listName,
            'grade_name' => $gradeName,
            'statistics' => [
                'total' => $students->count(),
                'male' => $students->where('gender', 'M')->count(),
                'female' => $students->where('gender', 'F')->count(),
                'boarding' => $students->where('is_boarding', true)->count(),
                'day' => $students->where('is_boarding', false)->count(),
                'show_boarding' => (bool) ($school_data->boarding ?? false),
            ],
        ];
    }

    public function studentIdCards(){
        $this->authorize('viewAny', Student::class);

        $school_data = SchoolSetup::first();
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $grades = Grade::where('term_id', $termId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        return view('students.student-id-cards', [
            'school_data' => $school_data,
            'grades' => $grades,
            'termId' => $termId,
        ]);
    }

    public function previewIdCards(Request $request){
        $this->authorize('viewAny', Student::class);

        $request->validate([
            'grade_id' => 'required',
            'class_id' => 'required',
        ]);

        $gradeId = $request->grade_id;
        $classId = $request->class_id;
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $school_data = SchoolSetup::first();
        $term = Term::find($termId);
        $grades = Grade::where('term_id', $termId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $studentsQuery = Student::with([
            'currentClassRelation' => fn($q) => $q->wherePivot('term_id', $termId),
            'currentClassRelation.grade',
            'currentClassRelation.teacher',
            'sponsor'
        ])->where('status', 'Current');

        if ($classId === 'all') {
            $classIds = Klass::where('grade_id', $gradeId)
                ->where('term_id', $termId)
                ->pluck('id');
            $studentsQuery->whereHas('classes', function ($q) use ($classIds, $termId) {
                $q->whereIn('klasses.id', $classIds)
                  ->where('klass_student.term_id', $termId);
            });
            $selectedClass = 'All Classes';
        } else {
            $studentsQuery->whereHas('classes', function ($q) use ($classId, $termId) {
                $q->where('klasses.id', $classId)
                  ->where('klass_student.term_id', $termId);
            });
            $selectedClass = Klass::where('id', $classId)
                ->where('term_id', $termId)
                ->value('name') ?? 'Unknown';
        }

        $students = $studentsQuery->orderBy('last_name')->orderBy('first_name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for the selected criteria.');
        }

        $selectedGrade = Grade::where('id', $gradeId)
            ->where('term_id', $termId)
            ->value('name') ?? 'Unknown';

        return view('students.student-id-cards-preview', [
            'students' => $students,
            'school_data' => $school_data,
            'term' => $term,
            'grades' => $grades,
            'gradeId' => $gradeId,
            'classId' => $classId,
            'selectedGrade' => $selectedGrade,
            'selectedClass' => $selectedClass,
        ]);
    }

    public function generateIdCards(Request $request){
        $this->authorize('export', Student::class);

        $request->validate([
            'grade_id' => 'required',
            'class_id' => 'required',
        ]);

        $gradeId = $request->grade_id;
        $classId = $request->class_id;
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $school_data = SchoolSetup::first();
        $term = Term::find($termId);

        $studentsQuery = Student::with([
            'currentClassRelation' => fn($q) => $q->wherePivot('term_id', $termId),
            'currentClassRelation.grade',
            'currentClassRelation.teacher',
            'sponsor'
        ])->where('status', 'Current');

        if ($classId === 'all') {
            $classIds = Klass::where('grade_id', $gradeId)
                ->where('term_id', $termId)
                ->pluck('id');
            $studentsQuery->whereHas('classes', function ($q) use ($classIds, $termId) {
                $q->whereIn('klasses.id', $classIds)
                  ->where('klass_student.term_id', $termId);
            });
        } else {
            $studentsQuery->whereHas('classes', function ($q) use ($classId, $termId) {
                $q->where('klasses.id', $classId)
                  ->where('klass_student.term_id', $termId);
            });
        }

        $students = $studentsQuery->orderBy('last_name')->orderBy('first_name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for the selected criteria.');
        }

        $pdf = Pdf::loadView('students.student-id-cards-pdf', [
            'students' => $students,
            'school_data' => $school_data,
            'term' => $term,
        ])->setPaper('a4', 'portrait');

        $filename = 'student-id-cards-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    function getStudentStatistics(){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $school_data = SchoolSetup::first();
        try {
            $klasses = Klass::where('term_id', $selectedTermId)->withCount([
                'students AS boys_count'  => function ($query) {
                    $query->where('gender', 'M')->where('status', 'Current');
                },
                'students AS girls_count' => function ($query) {
                    $query->where('gender', 'F')->where('status', 'Current');
                }
            ])->orderBy('grade_id', 'asc')->get();


            $classNames = $klasses->pluck('name');
            $boysCounts = $klasses->pluck('boys_count');
            $girlsCounts = $klasses->pluck('girls_count');

            return view('students.students-statistical-analysis-term', [
                'klasses'     => $klasses,
                'school_data' => $school_data,
                'classNames'  => $classNames,
                'boysCounts'  => $boysCounts,
                'girlsCounts' => $girlsCounts
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error attempting to fetch students' . $e->getMessage());
        }
    }

    public function getBoardingAnalysis() {
        $school_data = SchoolSetup::first();
        if (!$school_data->boarding) {
            return redirect()->back()->with('error', 'Boarding is not enabled for this school.');
        }

        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        try {
            $klasses = Klass::where('term_id', $selectedTermId)
                ->withCount([
                    'students AS boarding_boys' => function ($query) {
                        $query->where('gender', 'M')->where('status', 'Current')->where('is_boarding', true);
                    },
                    'students AS boarding_girls' => function ($query) {
                        $query->where('gender', 'F')->where('status', 'Current')->where('is_boarding', true);
                    },
                    'students AS day_boys' => function ($query) {
                        $query->where('gender', 'M')->where('status', 'Current')->where('is_boarding', false);
                    },
                    'students AS day_girls' => function ($query) {
                        $query->where('gender', 'F')->where('status', 'Current')->where('is_boarding', false);
                    },
                ])
                ->with(['teacher:id,firstname,lastname', 'grade:id,name'])
                ->orderBy('grade_id', 'asc')
                ->get();

            $classData = $klasses->map(function ($klass) {
                $boardingTotal = $klass->boarding_boys + $klass->boarding_girls;
                $dayTotal = $klass->day_boys + $klass->day_girls;
                return [
                    'name' => $klass->name,
                    'grade_name' => optional($klass->grade)->name ?? 'N/A',
                    'teacher' => optional($klass->teacher)->full_name ?? 'Not Assigned',
                    'boarding_boys' => $klass->boarding_boys,
                    'boarding_girls' => $klass->boarding_girls,
                    'boarding_total' => $boardingTotal,
                    'day_boys' => $klass->day_boys,
                    'day_girls' => $klass->day_girls,
                    'day_total' => $dayTotal,
                    'total' => $boardingTotal + $dayTotal,
                ];
            });

            $gradeData = $classData->groupBy('grade_name')->map(function ($classes, $gradeName) {
                return [
                    'grade_name' => $gradeName,
                    'boarding_boys' => $classes->sum('boarding_boys'),
                    'boarding_girls' => $classes->sum('boarding_girls'),
                    'boarding_total' => $classes->sum('boarding_total'),
                    'day_boys' => $classes->sum('day_boys'),
                    'day_girls' => $classes->sum('day_girls'),
                    'day_total' => $classes->sum('day_total'),
                    'total' => $classes->sum('total'),
                ];
            })->values();

            $grandTotal = [
                'boarding_boys' => $classData->sum('boarding_boys'),
                'boarding_girls' => $classData->sum('boarding_girls'),
                'boarding_total' => $classData->sum('boarding_total'),
                'day_boys' => $classData->sum('day_boys'),
                'day_girls' => $classData->sum('day_girls'),
                'day_total' => $classData->sum('day_total'),
                'total' => $classData->sum('total'),
                'boarding_percentage' => $classData->sum('total') > 0
                    ? round(($classData->sum('boarding_total') / $classData->sum('total')) * 100, 1)
                    : 0,
            ];

            return view('students.students-boarding-analysis', [
                'school_data' => $school_data,
                'classData' => $classData,
                'gradeData' => $gradeData,
                'grandTotal' => $grandTotal,
            ]);
        } catch (Exception $e) {
            Log::error('Error generating boarding analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error generating boarding analysis report.');
        }
    }

    public function getBoardingAnalysisExport() {
        $this->authorize('export', Student::class);

        $school_data = SchoolSetup::first();
        if (!$school_data->boarding) {
            return redirect()->back()->with('error', 'Boarding is not enabled for this school.');
        }

        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        try {
            $klasses = Klass::where('term_id', $selectedTermId)
                ->withCount([
                    'students AS boarding_boys' => function ($query) {
                        $query->where('gender', 'M')->where('status', 'Current')->where('is_boarding', true);
                    },
                    'students AS boarding_girls' => function ($query) {
                        $query->where('gender', 'F')->where('status', 'Current')->where('is_boarding', true);
                    },
                    'students AS day_boys' => function ($query) {
                        $query->where('gender', 'M')->where('status', 'Current')->where('is_boarding', false);
                    },
                    'students AS day_girls' => function ($query) {
                        $query->where('gender', 'F')->where('status', 'Current')->where('is_boarding', false);
                    },
                ])
                ->with(['teacher:id,firstname,lastname', 'grade:id,name'])
                ->orderBy('grade_id', 'asc')
                ->get();

            $classData = $klasses->map(function ($klass) {
                $boardingTotal = $klass->boarding_boys + $klass->boarding_girls;
                $dayTotal = $klass->day_boys + $klass->day_girls;
                return [
                    'name' => $klass->name,
                    'grade_name' => optional($klass->grade)->name ?? 'N/A',
                    'teacher' => optional($klass->teacher)->full_name ?? 'Not Assigned',
                    'boarding_boys' => $klass->boarding_boys,
                    'boarding_girls' => $klass->boarding_girls,
                    'boarding_total' => $boardingTotal,
                    'day_boys' => $klass->day_boys,
                    'day_girls' => $klass->day_girls,
                    'day_total' => $dayTotal,
                    'total' => $boardingTotal + $dayTotal,
                ];
            });

            $gradeData = $classData->groupBy('grade_name')->map(function ($classes, $gradeName) {
                return [
                    'grade_name' => $gradeName,
                    'boarding_boys' => $classes->sum('boarding_boys'),
                    'boarding_girls' => $classes->sum('boarding_girls'),
                    'boarding_total' => $classes->sum('boarding_total'),
                    'day_boys' => $classes->sum('day_boys'),
                    'day_girls' => $classes->sum('day_girls'),
                    'day_total' => $classes->sum('day_total'),
                    'total' => $classes->sum('total'),
                ];
            })->values();

            $data = [
                'classData' => $classData,
                'gradeData' => $gradeData,
            ];

            return Excel::download(new BoardingAnalysisExport($data), 'boarding-analysis-report.xlsx');
        } catch (Exception $e) {
            Log::error('Error exporting boarding analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error exporting boarding analysis report.');
        }
    }

    public function getDeparturesReport(){
        try {
            $school_data = SchoolSetup::first();
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $selectedTerm = Term::findOrFail($termId);
            $year = $selectedTerm->year;

            $yearTerms = Term::where('year', $year)->orderBy('term')->get();

            $allReasons = StudentDeparture::REASONS;
            $departures = StudentDeparture::with(['student' => function ($query) {
                $query->select(['id', 'first_name', 'last_name', 'status'])->with(['currentClassRelation:id,name']);
            }])->where('year', $year)->orderBy('last_day_of_attendance', 'desc')->get();
            $departuresByReason = collect($allReasons)->mapWithKeys(function ($reason) use ($departures) {
                return [$reason => $departures->where('reason_for_leaving', $reason)->values()];
            });

            $summary = collect($allReasons)->mapWithKeys(function ($reason) use ($departures, $yearTerms) {
                $reasonDepartures = $departures->where('reason_for_leaving', $reason);
                $termCounts = $yearTerms->mapWithKeys(function ($term) use ($reasonDepartures) {
                    $termCount = $reasonDepartures->filter(function ($departure) use ($term) {
                        $departureDate = Carbon::parse($departure->last_day_of_attendance);
                        return $departureDate->between(Carbon::parse($term->start_date), Carbon::parse($term->end_date));
                    })->count();

                    return [$term->term => $termCount];
                });

                return [$reason => [
                    'total'                 => $reasonDepartures->count(),
                    'property_not_returned' => $reasonDepartures->where('property_returned', false)->count(),
                    'terms'                 => $termCounts
                ]];
            });

            // Calculate total statistics
            $totalStats = [
                'total'                 => $departures->count(),
                'property_not_returned' => $departures->where('property_returned', false)->count(),
                'by_term'               => $yearTerms->mapWithKeys(function ($term) use ($departures) {
                    $termCount = $departures->filter(function ($departure) use ($term) {
                        $departureDate = Carbon::parse($departure->last_day_of_attendance);
                        return $departureDate->between(Carbon::parse($term->start_date), Carbon::parse($term->end_date));
                    })->count();

                    return [$term->term => $termCount];
                })
            ];

            Log::info('Departures report generated', [
                'year'             => $year,
                'term_id' => $termId,
                'total_departures' => $departures->count(),
                'terms_count'      => $yearTerms->count()
            ]);

            return view('students.students-leaving-analysis-year', compact('school_data', 'departuresByReason', 'summary', 'totalStats', 'yearTerms', 'selectedTerm', 'year', 'allReasons'));
        } catch (Exception $e) {
            Log::error('Error generating departures report', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'term_id' => $termId ?? null,
                'year' => $year ?? null
            ]);

            return redirect()->back()->with('error', 'Error generating departures report: ' . (config('app.debug') ? $e->getMessage() : 'Please try again.'));
        }
    }

    public function getStudentTypesStatistics()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $school_data = SchoolSetup::first();

        try {
            $studentTypes = StudentType::orderBy('type')->get(['id', 'type']);
            $klasses = Klass::where('term_id', $selectedTermId)->with([
                'students' => function ($query) use ($selectedTermId) {
                    $query->select([
                        'students.id',
                        'students.first_name',
                        'students.last_name',
                        'students.gender',
                        'students.student_type_id',
                        'students.status'
                    ])->wherePivot('term_id', $selectedTermId)->where('status', 'Current')->with('type:id,type')->orderBy('first_name');
                },
                'teacher:id,firstname,lastname'
            ])->orderBy('grade_id')->get()->map(function ($klass) use ($studentTypes) {
                $typeCounts = [];
                foreach ($studentTypes as $type) {
                    $typeCounts[$type->type] = [
                        'boys'  => $klass->students->where('student_type_id', $type->id)->where('gender', 'M')->count(),
                        'girls' => $klass->students->where('student_type_id', $type->id)->where('gender', 'F')->count()
                    ];
                    $typeCounts[$type->type]['total'] = $typeCounts[$type->type]['boys'] + $typeCounts[$type->type]['girls'];
                }

                return [
                    'id'          => $klass->id,
                    'name' => $klass->name,
                    'teacher'     => optional($klass->teacher)->full_name ?? 'Not Assigned',
                    'type_counts' => $typeCounts,
                    'total_boys' => $klass->students->where('gender', 'M')->count(),
                    'total_girls' => $klass->students->where('gender', 'F')->count(),
                    'class_total' => $klass->students->count()
                ];
            });

            $totalsByType = [];
            foreach ($studentTypes as $type) {
                $totalsByType[$type->type] = ['boys' => $klasses->sum(function ($klass) use ($type) {
                    return $klass['type_counts'][$type->type]['boys'];
                }), 'girls'                          => $klasses->sum(function ($klass) use ($type) {
                    return $klass['type_counts'][$type->type]['girls'];
                })];
                $totalsByType[$type->type]['total'] = $totalsByType[$type->type]['boys'] + $totalsByType[$type->type]['girls'];
            }

            $grandTotal = [
                'boys'  => $klasses->sum('total_boys'),
                'girls' => $klasses->sum('total_girls'),
                'total' => $klasses->sum('class_total')
            ];

            Log::info('Student types statistics generated', [
                'term_id'        => $selectedTermId,
                'total_students' => $grandTotal['total'],
                'class_count'    => $klasses->count()
            ]);

            return view('students.students-statistical-analysis-term-type', [
                'klasses'      => $klasses,
                'school_data'  => $school_data,
                'studentTypes' => $studentTypes->pluck('type'),
                'totalsByType' => $totalsByType,
                'grandTotal'   => $grandTotal
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching student types statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error attempting to fetch student statistics');
        }
    }

    public function getFilteredStudentsReport()
    {
        $this->authorize('export', Student::class);

        try {
            $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
            $school_data = SchoolSetup::first();

            $filters = StudentFilter::with(['students' => function ($query) use ($selectedTermId) {
                $query->select([
                    'students.id',
                    'students.first_name',
                    'students.last_name',
                    'students.middle_name',
                    'students.gender',
                    'students.student_type_id',
                    'students.student_filter_id'
                ])->whereHas('terms', function ($q) use ($selectedTermId) {
                    $q->where('term_id', $selectedTermId)->where('status', 'Current');
                })->with(['type:id,type', 'currentClassRelation' => function ($query) use ($selectedTermId) {
                    $query->wherePivot('term_id', $selectedTermId)->select('klasses.id', 'klasses.name');
                }, 'houses'                                      => function ($query) use ($selectedTermId) {
                    $query->wherePivot('term_id', $selectedTermId)->select('houses.id', 'houses.name');
                }])->orderBy('first_name')->orderBy('last_name');
            }])->orderBy('name')->get();

            $summary = $filters->map(function ($filter) {
                return [
                    'name'    => $filter->name,
                    'total' => $filter->students->count(),
                    'males'   => $filter->students->where('gender', 'M')->count(),
                    'females' => $filter->students->where('gender', 'F')->count()
                ];
            });

            $totals = [
                'total'   => $summary->sum('total'),
                'males' => $summary->sum('males'),
                'females' => $summary->sum('females')
            ];

            Log::info('Filtered students report generated', [
                'term_id'        => $selectedTermId,
                'filter_count'   => $filters->count(),
                'total_students' => $totals['total']
            ]);

            return view('students.students-analysis-filters', [
                'filters' => $filters,
                'school_data' => $school_data,
                'summary' => $summary,
                'totals' => $totals
            ]);
        } catch (Exception $e) {
            Log::error('Error generating filtered students report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error generating filtered students report');
        }
    }

    public function getStudentStatisticsExport()
    {
        $this->authorize('export', Student::class);

        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        try {
            $klasses = Klass::where('term_id', $selectedTermId)->withCount([
                'students AS boys_count'  => function ($query) {
                    $query->where('gender', 'M')->where('status', 'Current');
                },
                'students AS girls_count' => function ($query) {
                    $query->where('gender', 'F')->where('status', 'Current');
                }
            ])->orderBy('grade_id', 'asc')->get();

            $data = ['klasses' => $klasses];
            return Excel::download(new StudentClassStatisticalAnalysisExport($data), 'students-class-statistical-analysis-term.xlsx');
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error attempting to fetch students: ' . $e->getMessage());
        }
    }

    public function getKlassTeachersList()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        try {
            $klasses = Klass::with(['students', 'teacher'])->where('term_id', $selectedTermId)->get();
            $school_data = SchoolSetup::first();

            return view('students.students-classes-analysis-term', [
                'klasses'     => $klasses,
                'school_data' => $school_data
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error attempting to fetch students' . $e->getMessage());
        }
    }

    function getStudentListAnalysis()
    {
        try {
            $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
            $students = Student::inTerm($selectedTermId)->get();
            $school_data = SchoolSetup::first();
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error attempting to fetch students' . $e->getMessage());
        }
        return view('students.students-analysis-term', ['students' => $students, 'school_data' => $school_data]);
    }

    public function getStudentsSettings(){
        try {
            $student_filters = CacheHelper::getStudentFilters();
            $student_types = CacheHelper::getStudentTypes();

            if ($student_filters instanceof Collection && $student_types instanceof Collection) {
                return view('students.students-settings', ['filters' => $student_filters, 'types' => $student_types,]);
            }

            return response()->view('errors.500', [], 500);
        } catch (Exception $e) {
            Log::error('Error fetching student settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                throw $e;
            }

            return response()->view('errors.500', ['message' => 'An error occurred while loading student settings.'], 500);
        }
    }

    public function getCurriculumMaterials()
    {
        try {
            $textbooks = CacheHelper::getTextBooks();
            $authors = CacheHelper::getAuthors();
            $publishers = CacheHelper::getPublishers();

            if (!$this->validateData($textbooks, $authors, $publishers)) {
                Log::warning('Curriculum materials data retrieval incomplete', [
                    'books_count'      => $textbooks->count() ?? 0,
                    'authors_count'    => $authors->count() ?? 0,
                    'publishers_count' => $publishers->count() ?? 0
                ]);

                return response()->view('errors.500', [], 500);
            }

            return view('students.curriculum-materials', compact('textbooks', 'authors', 'publishers'));
        } catch (Exception $e) {
            Log::error('Error fetching curriculum materials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                throw $e;
            }

            return response()->view('errors.500', ['message' => 'An error occurred while loading curriculum materials.'], 500);
        }
    }

    private function validateData(?Collection $books, ?Collection $authors, ?Collection $publishers)
    {
        return $books instanceof Collection && $authors instanceof Collection && $publishers instanceof Collection;
    }

    public function getClearanceForm($id)
    {
        $school_data = SchoolSetup::first();
        $student = Student::with(['bookAllocations.book', 'bookAllocations.grade'])->findOrFail($id);
        return view('students.students-clearance-form', ['school_data' => $school_data, 'student' => $student]);
    }

    public function deleteAuthor($id)
    {
        try {
            DB::beginTransaction();
            $author = Author::withCount('books')->findOrFail($id);
            if ($author->books_count > 0) {
                return back()->with('error', "Cannot delete author. They have {$author->books_count} associated books.")->withInput();
            }

            $authorName = $author->getFullNameAttribute();
            $author->delete();
            DB::commit();
            CacheHelper::forgetAuthors();

            Log::info('Author deleted successfully', [
                'author_id'  => $id,
                'author_name' => $authorName,
                'deleted_by' => auth()->id() ?? 'system'
            ]);

            return back()->with('message', 'Author deleted successfully!');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Attempt to delete non-existent author', [
                'author_id' => $id,
                'user_id'   => auth()->id() ?? 'system'
            ]);

            return back()->with('error', 'Author not found!')->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting author', [
                'author_id' => $id,
                'error' => $e->getMessage(),
                'user_id'   => auth()->id() ?? 'system'
            ]);

            return back()->with('error', 'An error occurred while deleting the author. Please try again.')->withInput();
        }
    }

    public function addBook()
    {
        try {
            $authors = CacheHelper::getAuthors();
            $publishers = CacheHelper::getPublishers();
            $grades = CacheHelper::getGrades();

            if (!$this->validateFormData($authors, $grades, $publishers)) {
                return response()->view('errors.500', ['message' => 'Unable to load form data completely. Please try again.'], 500);
            }

            return view('students.add-book', compact('authors', 'grades', 'publishers'));
        } catch (Exception $e) {
            Log::error('Error loading book form', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if (config('app.debug')) {
                throw $e;
            }

            return response()->view('errors.500', ['message' => 'An error occurred while loading the form.'], 500);
        }
    }

    private function validateFormData(?Collection $authors, ?Collection $grades, ?Collection $publishers)
    {
        return $authors instanceof Collection && $grades instanceof Collection && $publishers instanceof Collection && $authors->isNotEmpty() && $grades->isNotEmpty();
    }

    public function editBook($id)
    {
        try {
            $book = Book::with(['author', 'publisher', 'grade', 'copies'])->findOrFail($id);

            $authors = CacheHelper::getAuthors();
            $grades = CacheHelper::getGrades();
            $publishers = CacheHelper::getPublishers();

            if (!$authors instanceof Collection || $authors->isEmpty() || !$grades instanceof Collection || $grades->isEmpty() || !$publishers instanceof Collection || $publishers->isEmpty()) {
                Log::error('Invalid or empty data collections', [
                    'book_id'          => $id,
                    'authors_count'    => $authors->count() ?? 0,
                    'grades_count'     => $grades->count() ?? 0,
                    'publishers_count' => $publishers->count() ?? 0
                ]);
                throw new RuntimeException('Required reference data is missing');
            }

            return view('students.add-book', compact('book', 'authors', 'grades', 'publishers'));
        } catch (ModelNotFoundException $e) {
            Log::warning('Attempt to edit non-existent book', [
                'book_id' => $id,
                'user_id' => auth()->id() ?? 'system'
            ]);
            return redirect()->route('students.curriculum-materials')->with('error', 'Book not found.');
        } catch (Exception $e) {
            Log::error('Error loading book edit form', [
                'book_id' => $id,
                'error' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                throw $e;
            }
            return redirect()->route('students.curriculum-materials')->with('error', 'An error occurred while loading the edit form.');
        }
    }

    public function deleteBook($id)
    {
        try {
            DB::beginTransaction();
            $book = Book::with(['copies'])->findOrFail($id);

            if (!$this->canDeleteBook($book)) {
                DB::rollBack();
                return redirect()->route('students.curriculum-materials')->with('error', 'Book cannot be deleted. It may be checked out or have active copies.');
            }

            $bookDetails = ['title' => $book->title, 'isbn' => $book->isbn, 'copies_count' => $book->copies->count(),];

            $book->copies()->delete();
            $book->delete();
            $this->clearRelatedCaches();

            DB::commit();
            Log::info('Book deleted successfully', [
                'book_id'    => $id,
                'book_details' => $bookDetails,
                'deleted_by' => auth()->id() ?? 'system',
                'deleted_at' => now()
            ]);
            return redirect()->route('students.curriculum-materials')->with('message', 'Book deleted successfully.');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Attempt to delete non-existent book', [
                'book_id' => $id,
                'user_id' => auth()->id() ?? 'system'
            ]);

            return redirect()->route('students.curriculum-materials')->with('error', 'Book not found.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting book', [
                'book_id' => $id,
                'error' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? 'system'
            ]);
            return redirect()->route('students.curriculum-materials')->with('error', 'An error occurred while deleting the book.');
        }
    }

    private function canDeleteBook(Book $book)
    {
        $hasCheckedOutCopies = $book->copies->contains(function ($copy) {
            return in_array($copy->status, ['checked_out', 'in_repair']);
        });

        if ($hasCheckedOutCopies) {
            Log::warning('Attempt to delete book with checked out or in-repair copies', [
                'book_id' => $book->id,
                'title'   => $book->title,
                'user_id' => auth()->id() ?? 'system'
            ]);
            return false;
        }

        if (in_array($book->status, ['checked_out', 'in_repair'])) {
            Log::warning('Attempt to delete book with non-deletable status', [
                'book_id' => $book->id,
                'status'  => $book->status,
                'user_id' => auth()->id() ?? 'system'
            ]);
            return false;
        }
        return true;
    }

    private function clearRelatedCaches()
    {
        try {
            CacheHelper::forgetTextBooks();
            CacheHelper::forgetAuthors();
            CacheHelper::forgetPublishers();
        } catch (Exception $e) {
            Log::warning('Failed to clear some caches after author deletion', ['error' => $e->getMessage()]);
        }
    }

    public function importBooks(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        // SECURITY: Removed dangerous Book::truncate() feature
        // Bulk deletion of all books without audit trail is too risky
        // If needed, implement a separate protected endpoint with proper authorization

        $import = new BooksImport;
        Excel::import($import, $request->file('file'));

        $successCount = $import->getSuccessCount();
        $failureCount = $import->getFailureCount();

        $this->clearRelatedCaches();
        return redirect()->back()->with('message', "Books imported successfully. Success: $successCount, Failures: $failureCount");
    }

    public function storeBook(Request $request)
    {
        CacheHelper::forgetTextBooks();
        return $this->storeOrUpdate($request);
    }

    public function storeOrUpdate(Request $request, Book $book = null)
    {
        $isUpdate = $book !== null;
        $validatedData = $request->validate([
            'isbn'             => [
                'required',
                'max:191',
                Rule::unique('books')->ignore($book)
            ],
            'title'      => 'required|max:191',
            'author_id'        => 'required|exists:authors,id',
            'grade_id'         => 'required|exists:grades,id',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'publisher_id'     => 'nullable|max:191',
            'edition' => 'nullable|max:191',
            'genre'            => 'nullable|max:191',
            'language' => 'required|max:191',
            'format'           => 'nullable|max:191',
            'pages'            => 'nullable|integer|min:1',
            'description'      => 'nullable',
            'cover_image'      => 'nullable|image|max:2048',
            'quantity'         => 'required|integer|min:1',
            'status'           => 'required|in:available,checked_out,on_hold,in_repair',
            'location'         => 'nullable|max:191',
            'price'            => 'nullable|numeric|min:0',
            'currency'         => 'required|max:191',
            'barcode' => 'nullable|max:191',
            'call_number'      => 'nullable|max:191',
            'dewey_decimal'    => 'nullable|max:191',
            'series_name'      => 'nullable|max:191',
            'volume_number'    => 'nullable|integer|min:1',
            'keywords'         => 'nullable|max:191',
            'reading_level'    => 'nullable|max:191',
            'condition'        => 'required|in:new,good,fair,poor',
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // SECURITY: Validate file is actually an image using MIME type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            $mimeType = $file->getMimeType();

            if (!in_array($mimeType, $allowedMimeTypes)) {
                return redirect()->back()
                    ->with('error', 'Invalid file type. Only image files are allowed.')
                    ->withInput();
            }

            // Delete old cover image if updating
            if ($isUpdate && $book->cover_image_url) {
                Storage::delete(str_replace('/storage', 'public', $book->cover_image_url));
            }

            // SECURITY: Use random filename
            $extension = $file->guessExtension() ?? 'jpg';
            $filename = 'cover_' . Str::random(32) . '.' . $extension;

            $path = $file->storeAs('cover_images', $filename, 'public');
            $validatedData['cover_image_url'] = Storage::url($path);
        }

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $book->update($validatedData);
                $this->updateCopies($book, $validatedData['quantity']);
                $message = 'Book updated successfully.';
            } else {
                $book = Book::create($validatedData);
                $this->createCopies($book, $validatedData['quantity']);
                $message = 'Book created successfully.';
            }
            DB::commit();
            CacheHelper::forgetTextBooks();

            return redirect()->back()->with('message', $message);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(Request $request){
        $this->authorize('create', Student::class);

        $rules = [
            'first_name'  => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'last_name'   => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'id_number'   => ['required', 'string', 'max:15', Rule::unique('students')->whereNull('deleted_at')],
            'nationality' => 'required|string|max:255',
            'gender'      => 'required|in:M,F',
            'exam_number' => 'nullable|string|max:255',
            'email'       => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (DB::table('users')->where('email', $value)->exists()) {
                            $fail('The email has already been taken.');
                        }
                        if (DB::table('sponsors')->where('email', $value)->exists()) {
                            $fail('The email has already been taken.');
                        }
                        if (DB::table('students')->where('email', $value)->exists()) {
                            $fail('The email has already been taken.');
                        }
                    }
                }
            ],
            'type'         => 'nullable|string|max:50',
            'date_of_birth'=> ['required', 'date_format:d/m/Y'],
            'grade_id'     => 'required|exists:grades,id',
            'filter'       => 'nullable|string|max:50',
            'sponsor_id'   => 'required|exists:sponsors,id',
            'klass_id'     => [
                'nullable',
                'exists:klasses,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $klass = DB::table('klasses')->where('id', $value)->where('grade_id', $request->input('grade_id'))->first();
                        if (!$klass) {
                            $fail('The selected class does not belong to the selected grade.');
                        }
                    }
                },
            ],
            'house'        => 'nullable|exists:houses,id',
        ];
    
        $messages = [
            'first_name.regex'     => 'First name can only contain letters, spaces and hyphens',
            'middle_name.regex'    => 'Middle name can only contain letters, spaces and hyphens',
            'last_name.regex'      => 'Last name can only contain letters, spaces and hyphens',
            'id_number.unique'     => 'This ID number is already registered to another student',
            'date_of_birth.before' => 'Student must be at least 2 years old',
            'date_of_birth.after'  => 'Student age cannot exceed 30 years',
            'gender.in'            => 'Gender must be either M or F',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Please correct the errors below');
        }
    
        $request->merge([
            'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
        ]);

        $existing = DB::table('students')
            ->join('klass_student', 'students.id', '=', 'klass_student.student_id')
            ->join('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->join('grades', 'klasses.grade_id', '=', 'grades.id')
            ->whereNull('students.deleted_at')
            ->where('students.first_name', $request->first_name)
            ->where('students.last_name', $request->last_name)
            ->select('students.id as student_id', 'students.date_of_birth', 'klasses.name as class_name', 'grades.name as grade_name')
            ->get();
    
            if ($existing->isNotEmpty() && !$request->has('bypass_duplicate_check')) {
                $message = "Student(s) with the same name already exists allocated to";
                foreach ($existing as $entry) {
                    $message .= " {$entry->class_name}. Always search a student before attempting to add them.";
                }
                return back()->withInput()->with('error', $message);
            }         
    
        try {
            $term = TermHelper::getCurrentTerm();
            if (!$term) {
                return back()->with('error', 'No current term found! Please configure a term first.')->withInput();
            }
    
            $studentId = DB::transaction(function () use ($request, $term) {
                $studentData = $request->only([
                    'first_name', 'middle_name', 'last_name', 'gender', 'type', 'date_of_birth', 'email',
                    'nationality', 'id_number', 'sponsor_id', 'filter', 'exam_number', 'is_boarding',
                ]);
    
                $studentData['is_boarding'] = $request->boolean('is_boarding');
                $studentData['last_updated_by'] = auth()->user()->full_name ?? auth()->user()->id;
                $studentData['id_number'] = preg_replace('/\s+/', '', $studentData['id_number']);
                $studentData['year'] = $term->year;
                $studentData['status'] = 'Current';
                $studentData['password'] = Hash::make(Str::random(10));
    
                $student = Student::create($studentData);
    
                DB::table('student_term')->insert([
                    'student_id' => $student->id,
                    'term_id'    => $term->id,
                    'grade_id'   => $request->input('grade_id'),
                    'status'     => 'Current',
                    'year'       => $term->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                if ($request->filled('klass_id')) {
                    DB::table('klass_student')->insert([
                        'klass_id'   => $request->input('klass_id'),
                        'student_id' => $student->id,
                        'term_id'    => $term->id,
                        'grade_id'   => $request->input('grade_id'),
                        'active'     => true,
                        'year'       => $term->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $student->refresh();
                }

                if ($request->filled('house')) {
                    DB::table('student_house')->insert([
                        'student_id' => $student->id,
                        'house_id'   => $request->input('house'),
                        'term_id'    => $term->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Audit log using StudentService
                $this->studentService->logAudit('created', $student->id, [
                    'student_name' => $student->fullName,
                    'grade_id' => $request->input('grade_id'),
                    'klass_id' => $request->input('klass_id'),
                    'house_id' => $request->input('house'),
                    'term_id' => $term->id,
                ]);
    
                return $student->id;
            });
    
            CacheHelper::forgetStudentsData();
            CacheHelper::forgetStudentsCount($term->id);
            CacheHelper::forgetStudentsTermData();
            CacheHelper::forgetStudentsDashboard($term->id);
    
            return redirect()->route('students.show', $studentId)->with('message', 'Student created successfully!');
        } catch (Exception $e) {
            Log::error('Error creating student:', [
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
            ]);
    
            return back()->with('error', 'An error occurred while creating the student. Please try again.')->withInput();
        }
    }
    


    public function create(){
        $this->authorize('create', Student::class);

        try {
            $currentTerm = TermHelper::getCurrentTerm();

            if (!$currentTerm) {
                throw new Exception('No current term found');
            }

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $terms = TermHelper::getTerms();
            $grades = Grade::where('term_id', $selectedTermId)->where('active', 1)->get();

            $nationalities = CacheHelper::getNationalities();
            $classes = Klass::where('term_id', $selectedTermId)->get();
            $parents = Sponsor::all();
            $status = StudentStatus::all();
            $types = DB::table('student_types')->select('type')->get();
            $houses = House::where('term_id', $selectedTermId)->get();
            $school_data = SchoolSetup::first();

            return view('students.student-new', [
                'terms'         => $terms,
                'grades' => $grades,
                'nationalities' => $nationalities,
                'classes' => $classes,
                'sponsors'      => $parents,
                'status' => $status,
                'types' => $types,
                'houses' => $houses,
                'school_data' => $school_data,
            ]);
        } catch (Exception $e) {
            Log::error('Error in StudentController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while preparing the student creation form. Please try again.');
        }
    }

    public function update(Request $request, $id){
        $student = Student::findOrFail($id);
        $this->authorize('update', $student);

        $rules = [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s-]+$/'],
            'id_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique('students')->ignore($id)->whereNull('deleted_at'),
            ],
            'gender' => 'required|in:M,F',
            'exam_number' => 'nullable|string|max:50',
            'email'       => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($id) {
                    if (empty($value)) {
                        return;
                    }
                    
                    if (DB::table('users')->where('email', $value)->exists()) {
                        $fail('The email has already been taken.');
                    }
    
                    if (DB::table('sponsors')->where('email', $value)->exists()) {
                        $fail('The email has already been taken.');
                    }
    
                    if (DB::table('students')->where('email', $value)
                        ->where('id', '!=', $id)
                        ->whereNull('deleted_at')
                        ->exists()) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'date_of_birth' => [
                'required',
                'date_format:d/m/Y',
            ],
            'student_type_id' => 'nullable|exists:student_types,id',
            'grade_id' => 'required|exists:grades,id',
            'sponsor_id' => 'required|exists:sponsors,id',
            'nationality' => 'required|string|max:255',
            'status' => ['required', 'string'],
            'student_filter_id' => 'nullable|exists:student_filters,id',
            'parent_is_staff' => 'nullable|boolean',
            'is_boarding' => 'nullable|boolean',
            'last_updated_by' => 'nullable|string|max:255',
            'photo_path' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'house' => 'nullable|exists:houses,id',
    
            'klass_id' => [
                'nullable',
                'exists:klasses,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $klass = DB::table('klasses')
                            ->where('id', $value)
                            ->where('grade_id', $request->grade_id)
                            ->first();
    
                        if (!$klass) {
                            $fail('This student is allocated a class in a different grade!');
                        }
                    }
                },
            ],
        ];
    
        $messages = [
            'first_name.regex'     => 'First name can only contain letters, spaces and hyphens.',
            'middle_name.regex'    => 'Middle name can only contain letters, spaces and hyphens.',
            'last_name.regex'      => 'Last name can only contain letters, spaces and hyphens.',
            'id_number.unique'     => 'This ID number is already registered to another student.',
            'date_of_birth.before' => 'Student must be at least 2 years old.',
            'date_of_birth.after'  => 'Student age cannot exceed 30 years.',
            'gender.in'            => 'Gender must be either M or F.',
            'grade_id.required'    => 'Please select a grade.',
            'sponsor_id.required'  => 'Please select a sponsor.',
            'photo_path.max'       => 'The uploaded photo may not be greater than 2MB.',
            'house.exists'         => 'The selected house does not exist.',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $request->merge([
            'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $term = TermHelper::getCurrentTerm();
                $student = Student::findOrFail($id);
    
                $oldTypeId         = $student->student_type_id;
                $newTypeId         = $request->input('student_type_id');
                $oldParentIsStaff  = $student->parent_is_staff;
                $newParentIsStaff  = $request->input('parent_is_staff');
                $oldNationality    = $student->nationality;
                $newNationality    = $request->input('nationality');
                $oldStatus         = $student->status;
                $newStatus         = $request->input('status');
                $oldGradeId        = $student->studentTerm()->grade_id ?? null;
                $newGradeId        = $request->input('grade_id');
                $oldClassId        = optional($student->currentClass())->id;
                $newClassId        = $request->input('klass_id');
                $currentTermId     = $term->id;
                $year              = $term->year;
                $oldHouseId        = optional($student->house)->id;
                $newHouseId        = $request->input('house');
    
                $typeChanged           = ($oldTypeId !== $newTypeId);
                $nationalityChanged    = ($oldNationality !== $newNationality);
                $statusChanged         = ($oldStatus !== $newStatus);
                $parentIsStaffChanged  = ($oldParentIsStaff !== $newParentIsStaff);
                $gradeChanged          = ($oldGradeId && $oldGradeId != $newGradeId);
                $classChanged          = ($oldClassId && $oldClassId != $newClassId);
                $houseChanged          = ($oldHouseId != $newHouseId);
    
                if ($statusChanged && $newStatus !== 'Current') {
                    $hasClass = DB::table('klass_student')
                        ->where('student_id', $student->id)
                        ->where('term_id', $currentTermId)
                        ->where('active', true)
                        ->exists();
    
                    if ($hasClass) {
                        return redirect()->back()->with('error', 'Remove this student from all classes before changing their status.')->withInput();
                    }
                }
    
                if ($gradeChanged || $classChanged) {
                    $removalResult = app(StudentTermRemovalService::class)->removeFromCurrentTerm($student);
                    Log::info('Student term data removal completed', [
                        'student_id'     => $student->id,
                        'removal_result' => $removalResult
                    ]);
                }
    
                $data = $request->except(['term_id', 'grade_id', 'klass_id', 'photo_path', 'house']);
                $data['id_number'] = preg_replace('/\s+/', '', $data['id_number'] ?? '');
    
                if ($request->hasFile('photo_path')) {
                    $file = $request->file('photo_path');

                    // SECURITY: Validate file is actually an image using MIME type
                    $allowedMimeTypes = ['image/jpeg', 'image/png'];
                    $mimeType = $file->getMimeType();

                    if (!in_array($mimeType, $allowedMimeTypes)) {
                        return redirect()->back()
                            ->with('error', 'Invalid file type. Only JPEG and PNG images are allowed.')
                            ->withInput();
                    }

                    // SECURITY: Validate file size (max 2MB)
                    if ($file->getSize() > 2 * 1024 * 1024) {
                        return redirect()->back()
                            ->with('error', 'Photo must not exceed 2MB.')
                            ->withInput();
                    }

                    // Process and resize to 300x300 square JPEG
                    $contents = file_get_contents($file->getRealPath());
                    $image = imagecreatefromstring($contents);

                    if ($image === false) {
                        return redirect()->back()
                            ->with('error', 'Uploaded file is not a valid image.')
                            ->withInput();
                    }

                    $width = imagesx($image);
                    $height = imagesy($image);
                    $size = min($width, $height);
                    $cropped = imagecrop($image, [
                        'x' => (int)(($width - $size) / 2),
                        'y' => (int)(($height - $size) / 2),
                        'width' => $size,
                        'height' => $size,
                    ]);
                    $resized = imagescale($cropped ?: $image, 300, 300);

                    $uniqueName = 'student_' . Str::random(32) . '.jpg';
                    $dir = storage_path('app/public/student-photos');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $saved = imagejpeg($resized, $dir . '/' . $uniqueName, 90);

                    imagedestroy($image);
                    if ($cropped) {
                        imagedestroy($cropped);
                    }
                    imagedestroy($resized);

                    if (!$saved) {
                        return redirect()->back()
                            ->with('error', 'Failed to save photo. Please try again.')
                            ->withInput();
                    }

                    $publicPath = '/storage/student-photos/' . $uniqueName;
                    $oldPhotoPath = $student->photo_path;
                    $data['photo_path'] = $publicPath;

                    // Delete old photo after new one is confirmed saved
                    if ($oldPhotoPath) {
                        Storage::delete(str_replace('/storage/', 'public/', $oldPhotoPath));
                    }
                }
    
                $student->update($data);
    
                $studentTerm = $student->terms()->where('term_id', $currentTermId)->first();
                $termData = [
                    'grade_id'   => $newGradeId,
                    'status'     => $newStatus,
                    'year'       => $year,
                    'updated_at' => now(),
                ];
    
                if ($studentTerm) {
                    $studentTerm->pivot->update($termData);
                } else {
                    $student->terms()->attach($currentTermId, $termData);
                }
    
                if ($request->filled('klass_id')) {
                    DB::table('klass_student')->updateOrInsert(
                        [
                            'student_id' => $student->id,
                            'term_id'    => $currentTermId,
                        ],
                        [
                            'klass_id'   => $newClassId,
                            'grade_id'   => $newGradeId,
                            'active'     => true,
                            'year'       => $year,
                            'updated_at' => now(),
                        ]
                    );
                }
    
                if ($houseChanged) {
                    DB::table('student_house')
                        ->where('student_id', $student->id)
                        ->where('term_id', $currentTermId)
                        ->delete();
                    
                    if ($newHouseId) {
                        DB::table('student_house')->insert([
                            'student_id' => $student->id,
                            'house_id' => $newHouseId,
                            'term_id' => $currentTermId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    
                    if ($oldHouseId) {
                        CacheHelper::forgetUnallocatedHouseStudents($oldHouseId, $currentTermId);
                    }
                    if ($newHouseId) {
                        CacheHelper::forgetUnallocatedHouseStudents($newHouseId, $currentTermId);
                    }
                }
    
                DB::commit();
    
                // Audit log using StudentService
                $this->studentService->logAudit('updated', $student->id, [
                    'student_name' => $student->fullName,
                    'grade_changed' => $gradeChanged,
                    'class_changed' => $classChanged,
                    'house_changed' => $houseChanged,
                    'new_grade_id' => $newGradeId,
                    'new_class_id' => $newClassId,
                    'new_status' => $newStatus,
                    'term_id' => $currentTermId,
                ]);

                // Clear caches using StudentService
                $this->studentService->clearStudentCaches($currentTermId);

                $message = 'Student updated successfully!';
                if ($gradeChanged || $classChanged) {
                    $message .= " Student data from previous ";
                    $message .= $gradeChanged ? "grade" : "class";
                    $message .= " has been cleared.";
                }
                return redirect()->back()->with('message', $message);
            });
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Student not found.')->withInput();
        } catch (Exception $e) {
            return back()->with('error', 'An error occurred while updating the student. Please try again.')->withInput();
        }
    }

    private function updateCopies(Book $book, int $newQuantity){
        $currentQuantity = $book->copies()->count();
        try {
            if ($newQuantity > $currentQuantity) {
                $this->createCopies($book, $newQuantity - $currentQuantity);
            } elseif ($newQuantity < $currentQuantity) {
                $copiesToRemove = $currentQuantity - $newQuantity;
                $availableCopies = $book->copies()->where('status', 'available')->latest()->take($copiesToRemove)->get();

                if ($availableCopies->count() < $copiesToRemove) {
                    throw new Exception("Not enough available copies to remove. Only {$availableCopies->count()} available, but need to remove {$copiesToRemove}.");
                }

                $book->copies()->whereIn('id', $availableCopies->pluck('id'))->delete();
            } else {
                Log::info("No change in copies for book ID {$book->id}. Quantity remains {$currentQuantity}");
            }
        } catch (Exception $e) {
            Log::error("Error updating copies for book ID {$book->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function createCopies(Book $book, int $quantity){
        for ($i = 0; $i < $quantity; $i++) {
            Copy::create(['book_id' => $book->id, 'accession_number' => $this->generateAccessionNumber(),]);
        }
    }

    private function generateAccessionNumber(){
        return Str::random(8);
    }

    public function updateBook(Request $request, $id){
        $book = Book::findOrFail($id);
        CacheHelper::forgetTextBooks();
        return $this->storeOrUpdate($request, $book);
    }

    public function createAuthor(Request $request){
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name'  => 'required|string|max:191',
        ]);
        Author::create($validatedData);
        CacheHelper::forgetAuthors();
        return redirect()->back()->with('message', 'Author created successfully.');
    }

    public function updateAuthor(Request $request, $id){
        try {
            $author = Author::findOrFail($id);
            $updated = $author->update(['first_name' => $request->first_name, 'last_name' => $request->last_name]);

            if (!$updated) {
                throw new Exception('Failed to update author');
            }

            CacheHelper::forgetAuthors();
            return redirect()->back()->with('message', 'Author updated successfully!');
        } catch (Exception $e) {
            Log::error('Author update error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating author: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addPublisher(Request $request){
        $validatedData = $request->validate(['name' => 'required|string|max:191|unique:publishers,name',]);

        try {
            $publisher = Publisher::firstOrCreate(['name' => $validatedData['name']], $validatedData);

            CacheHelper::forgetPublishers();
            if ($publisher->wasRecentlyCreated) {
                return redirect()->back()->with('message', 'Publisher added successfully!');
            } else {
                return redirect()->back()->with('message', 'Publisher already exists.');
            }
        } catch (Exception $e) {
            Log::error('Error adding publisher: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while adding the publisher. Please try again.');
        }
    }

    public function updatePublisher(Request $request, $id){
        try {
            $publisher = Publisher::findOrFail($id);
            $updated = $publisher->update(['name' => $request->name]);

            if (!$updated) {
                throw new Exception('Failed to update publisher');
            }

            CacheHelper::forgetPublishers();
            return redirect()->back()->with('message', 'Publisher updated successfully!');
        } catch (Exception $e) {
            Log::error('Publisher update error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Error updating publisher');
        }
    }

    public function deletePublisher($id){
        try {
            DB::beginTransaction();
            $publisher = Publisher::with('books')->findOrFail($id);

            if ($publisher->books()->exists()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Cannot delete publisher. Please remove associated books first.');
            }

            $publisher->delete();
            CacheHelper::forgetPublishers();
            DB::commit();

            return redirect()->back()->with('message', 'Publisher was deleted successfully!');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Attempt to delete non-existent publisher', [
                'publisher_id' => $id,
                'user_id'      => auth()->id() ?? 'system'
            ]);
            return redirect()->back()->with('error', 'Publisher not found!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting publisher', [
                'publisher_id' => $id,
                'error' => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'user_id'      => auth()->id() ?? 'system'
            ]);

            return redirect()->back()->with('error', 'An error occurred while deleting the publisher.');
        }
    }

    public function saveStudentFilter(Request $request){
        $request->validate(['name' => 'required|string|max:255']);

        StudentFilter::create(['name' => $request->name]);
        CacheHelper::forgetStudentFilters();
        return redirect()->back()->with('message', 'Filter added successfully!');
    }

    public function updateStudentFilter(Request $request, StudentFilter $filter){
        $request->validate(['name' => 'required|string|max:255']);

        $filter->name = $request->name;
        $filter->save();
        CacheHelper::forgetStudentFilters();
        return response()->json(['success' => true, 'message' => 'Student type updated successfully.']);
    }

    public function destroyStudentFilter($id){
        try {
            DB::beginTransaction();

            $filter = StudentFilter::withCount('students')->findOrFail($id);
            $affectedStudents = $filter->students_count;

            $filter->delete();
            CacheHelper::forgetStudentFilters();
            DB::commit();
            return redirect()->back()->with('message', "Filter deleted successfully! {$affectedStudents} student(s) were unassigned.");
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Filter not found.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete filter', [
                'filter_id' => $id,
                'error' => $e->getMessage(),
                'user_id'   => auth()->id() ?? 'system'
            ]);
            return redirect()->back()->with('error', 'Failed to delete filter. Please try again.');
        }
    }

    public function destroyStudentType($id){
        try {
            DB::beginTransaction();
            $type = StudentType::withCount('students')->findOrFail($id);

            if ($type->students_count > 0) {
                DB::rollBack();
                return redirect()->back()->with('message', "Cannot delete this student type. It is currently assigned to {$type->students_count} student(s).");
            }

            $type->delete();
            CacheHelper::forgetStudentTypes();

            DB::commit();
            return redirect()->back()->with('message', "Type deleted successfully!");
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Type not found.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete type', [
                'type_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? 'system'
            ]);
            return redirect()->back()->with('error', 'Failed to delete type. Please try again.');
        }
    }

    public function saveStudentType(Request $request){
        try {
            $validated = $request->validate([
                'type' => ['required', 'string', 'max:255', 'unique:student_types,type'],
                'description' => ['required', 'string', 'max:255'],
                'exempt' => 'sometimes|boolean',
                'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ], [
                'description.required' => 'The description field is required.',
                'color.regex' => 'The color must be a valid hex color (e.g., #FF5733).',
            ]);

            $validated['exempt'] = $request->has('exempt') ? $validated['exempt'] : false;
            $validated['color'] = $request->input('color') ?: null;
            $studentType = StudentType::create($validated);
            Log::info('Student type created', [
                'id'      => $studentType->id,
                'type' => $studentType->type,
                'user_id' => auth()->id()
            ]);
    
            CacheHelper::forgetStudentTypes();
            return redirect()->back()->with('message', "Student type '{$studentType->type}' created successfully");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Description is required!');
        }
    }

    public function updateStudentType(Request $request, $id){
        try {
            $request->validate([
                'type' => 'required|string|max:255|unique:student_types,type,' . $id,
                'description' => 'required|string|max:255',
                'exempt' => 'sometimes|boolean',
                'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ], [
                'description.required' => 'The description field is required.',
                'color.regex' => 'The color must be a valid hex color (e.g., #FF5733).',
            ]);

            DB::beginTransaction();

            $type = StudentType::withCount('students')->findOrFail($id);
            $type->type = $request->input('type');
            $type->description = $request->input('description');
            $type->color = $request->input('color') ?: null;

            // Check if exempt status is being changed (cast both to boolean for proper comparison)
            $newExempt = (bool) $request->input('exempt');
            $currentExempt = (bool) $type->exempt;

            if ($newExempt !== $currentExempt) {
                // Only block if trying to change exempt and type has students assigned
                if ($type->students_count > 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot change exempt flag because this type is assigned to ' . $type->students_count . ' student(s).'
                    ], 422);
                }
            }
            $type->exempt = $newExempt;

            $type->save();
            CacheHelper::forgetStudentTypes();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student type updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Student type not found.'], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update student type', [
                'type_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? 'system'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student type. Please try again.'
            ], 500);
        }
    }

    function getKlassesWithStudentCounts($termId, $year){
        try {
            $klass_subjects = KlassSubject::where('term_id', $termId)->where('year', $year)->orderBy('klass_id', 'asc')->get();
            $grades = Grade::all();

            if ($klass_subjects->isEmpty()) {
                return redirect()->back()->with('message', 'No classes entered yet.');
            }
            return view('students.students-class-analysis', ['klass_subjects' => $klass_subjects, 'grades' => $grades]);
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    function getKlassesWithStats(){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        try {
            $klass_subjects = KlassSubject::where('term_id', $selectedTermId)->orderBy('grade_id', 'asc')->get();
            $school_data = SchoolSetup::first();
            return view('students.students-klass-analysis-term', [
                'klass_subjects' => $klass_subjects,
                'school_data'    => $school_data
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('message', 'Error occurred' . $e->getMessage());
        }
    }

    public function insertOrUpdateStudentMedicals(Request $request){
        $request->validate([
            'student_id'           => 'required|exists:students,id',
            'health_history'       => 'nullable|string|max:255',
            'immunization_records' => 'file|mimes:pdf,jpg,jpeg,png|max:2048|nullable',
            'other_allergies'      => 'nullable|string|max:255',
            'other_disabilities'   => 'nullable|string|max:255',
            'medical_conditions'   => 'nullable|string|max:255',
        ]);

        $data = $request->except('_token', 'active_tab');
        $data['term_id'] = TermHelper::getCurrentTerm()->id;
        $data['year'] = TermHelper::getCurrentTerm()->year;

        if ($request->hasFile('immunization_records')) {
            $file = $request->file('immunization_records');

            // SECURITY: Validate file is actually an allowed type using MIME type
            $allowedMimeTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg'
            ];
            $mimeType = $file->getMimeType();

            if (!in_array($mimeType, $allowedMimeTypes)) {
                return redirect()->back()
                    ->with('error', 'Invalid file type. Only PDF, JPEG and PNG files are allowed for medical records.')
                    ->withInput();
            }

            // SECURITY: Validate file size (max 2MB)
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                    ->with('error', 'Medical record file must not exceed 2MB.')
                    ->withInput();
            }

            // SECURITY: Use random filename to prevent path traversal and guessing
            $extension = $file->guessExtension() ?? 'pdf';
            $filename = 'medical_' . Str::random(32) . '.' . $extension;

            $path = $file->storeAs('students/medicals', $filename);
            $data['immunization_records'] = $path;
        }

        $booleanFields = [
            'peanuts',
            'red_meat',
            'vegetarian',
            'left_leg',
            'right_leg',
            'left_hand',
            'right_hand',
            'left_eye',
            'right_eye',
            'left_ear',
            'right_ear',
            'a_positive',
            'a_negative',
            'b_positive',
            'b_negative',
            'ab_positive',
            'ab_negative',
            'o_positive',
            'o_negative'
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }

        StudentMedicalInformation::updateOrInsert(['student_id' => $request->input('student_id')], $data);

        session(['active_tab' => $request->input('active_tab')]);
        return redirect()->back()->with('message', 'Record updated successfully!');
    }

    public function addStudentBehaviour(Request $request){
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'date' => 'required|date',
            'behaviour_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'action_taken'   => 'nullable|string|max:255',
            'reported_by' => 'required|string|max:255',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $term = TermHelper::getCurrentTerm();
        $data = $request->except('_token');
        $data['term_id'] = $term->id;
        $data['year'] = $term->year;

        StudentBehaviour::create($data);
        return redirect()->back()->with('message', 'Record updated successfully!');
    }

    public function updateStudentBehaviour(Request $request, $id){
        $request->validate([
            'date'        => 'required|date',
            'behaviour_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'action_taken' => 'nullable|string|max:255',
            'remarks'     => 'nullable|string|max:255',
            'reported_by' => 'required|string|max:255',
        ]);

        $behaviour = StudentBehaviour::findOrFail($id);
        $data = $request->except('_token');
        $behaviour->update($data);
        return redirect()->back()->with('message', 'Record updated successfully!');
    }

    public function editStudentBehaviour($studentId, $id){
        $student = Student::find($studentId);
        $behaviour = StudentBehaviour::find($id);
        return view('students.edit-behaviour-information', ['behaviour' => $behaviour, 'student' => $student]);
    }

    public function removeStudentBehaviour($id){
        $behaviour = StudentBehaviour::find($id);
        $behaviour->delete();
        return redirect()->back()->with('message', 'Record removed successfuly!');
    }

    public function studentBehaviour(Request $request, $id){
        $student = Student::find($id);
        return view('students.add-behaviour-information', ['student' => $student]);
    }

    public function searchNames(Request $request){
        $query = $request->get('term', '');

        // Use parameterized queries to prevent SQL injection
        $students = Student::whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ['%' . $query . '%'])
            ->where('status', 'Current')
            ->get();
        $staff = User::whereRaw('CONCAT(firstname, " ", lastname) LIKE ?', ['%' . $query . '%'])->get();

        $studentNames = $students->map(function ($student) {
            return $student->first_name . ' ' . $student->last_name;
        });

        $staffNames = $staff->map(function ($user) {
            return $user->firstname . ' ' . $user->lastname;
        });

        $names = $studentNames->merge($staffNames);
        return response()->json($names);
    }

    public function createOrUpdatePSLE(Request $request, $studentId){
        try {
            $validated = $request->validate([
                'overall_grade'        => 'nullable|string|in:A,B,C,D,E,F,U',
                'mathematics_grade'    => 'nullable|string|in:A,B,C,D,E,F,U',
                'english_grade'        => 'nullable|string|in:A,B,C,D,E,F,U',
                'science_grade'        => 'nullable|string|in:A,B,C,D,E,F,U',
                'setswana_grade'       => 'nullable|string|in:A,B,C,D,E,F,U',
                'agriculture_grade'    => 'nullable|string|in:A,B,C,D,E,F,U',
                'social_studies_grade' => 'nullable|string|in:A,B,C,D,E,F,U',
                'religious_and_moral_education_grade' => 'nullable|string|in:A,B,C,D,E,F,U',
            ]);

            $student = Student::findOrFail($studentId);
            $profileContext = $this->buildStudentProfileContext($student);

            if (!$profileContext['showPsleTab']) {
                Log::warning('Rejected PSLE grade save for non-junior student', [
                    'student_id' => $studentId,
                    'resolved_level' => $profileContext['studentLevel'],
                    'resolved_driver' => $profileContext['studentAssessmentDriver'],
                    'user_id' => auth()->id(),
                ]);

                return back()->withInput()->with('error', 'PSLE grades can only be saved for Middle School students.');
            }

            $student->psle()->updateOrCreate(['student_id' => $student->id,], [
                'overall_grade'        => $validated['overall_grade'],
                'mathematics_grade'    => $validated['mathematics_grade'],
                'english_grade'        => $validated['english_grade'],
                'science_grade'        => $validated['science_grade'],
                'setswana_grade'       => $validated['setswana_grade'],
                'agriculture_grade'    => $validated['agriculture_grade'],
                'social_studies_grade' => $validated['social_studies_grade'],
                'religious_and_moral_education_grade' => $validated['religious_and_moral_education_grade'],
            ]);
            return back()->with('message', 'PSLE grades saved successfully.');
        } catch (ModelNotFoundException $e) {
            Log::error('Student not found while saving PSLE grades', [
                'student_id' => $studentId,
                'user_id'    => auth()->id()
            ]);
            return back()->with('error', 'Student not found.');
        } catch (Exception $e) {
            Log::error('Error saving PSLE grades', [
                'student_id' => $studentId,
                'user_id' => auth()->id(),
                'error'      => $e->getMessage()
            ]);
            return back()->withInput()->with('error', 'An error occurred while saving the grades. Please try again.');
        }
    }

    public function createOrUpdateJCE(Request $request, $studentId){
        $validatedData = $request->validate([
            'overall'               => 'nullable|in:A,B,C,D,E,F,U',
            'mathematics'           => 'nullable|in:A,B,C,D,E,F,U',
            'english'               => 'nullable|in:A,B,C,D,E,F,U',
            'science'               => 'nullable|in:A,B,C,D,E,F,U',
            'setswana'              => 'nullable|in:A,B,C,D,E,F,U',
            'design_and_technology' => 'nullable|in:A,B,C,D,E,F,U',
            'home_economics'        => 'nullable|in:A,B,C,D,E,F,U',
            'agriculture'           => 'nullable|in:A,B,C,D,E,F,U',
            'social_studies'        => 'nullable|in:A,B,C,D,E,F,U',
            'moral_education'       => 'nullable|in:A,B,C,D,E,F,U',
            'religious_education'   => 'nullable|in:A,B,C,D,E,F,U',
            'music'                 => 'nullable|in:A,B,C,D,E,F,U',
            'physical_education'    => 'nullable|in:A,B,C,D,E,F,U',
            'art'                   => 'nullable|in:A,B,C,D,E,F,U',
            'office_procedures'     => 'nullable|in:A,B,C,D,E,F,U',
            'accounting'            => 'nullable|in:A,B,C,D,E,F,U',
            'french'                => 'nullable|in:A,B,C,D,E,F,U',
        ]);

        $student = Student::findOrFail($studentId);
        $profileContext = $this->buildStudentProfileContext($student);

        if (!$profileContext['showJceTab']) {
            Log::warning('Rejected JCE grade save for non-senior student', [
                'student_id' => $studentId,
                'resolved_level' => $profileContext['studentLevel'],
                'resolved_driver' => $profileContext['studentAssessmentDriver'],
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()->with('error', 'JCE grades can only be saved for High School students.');
        }

        JCE::updateOrCreate(['student_id' => $student->id], $validatedData);
        return redirect()->back()->with('message', 'JCE grades have been saved successfully.');
    }

    public function studentsHouseReport(){
        $this->authorize('viewAny', Student::class);

        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $houses = House::with(['houseHead', 'houseAssistant'])->withCount([
            'students',
            'students as males_count'      => function ($query) {
                $query->where('gender', 'M');
            },
            'students as females_count' => function ($query) {
                $query->where('gender', 'F');
            }
        ])->where('term_id', $selectedTermId)->get();

        $school_data = SchoolSetup::first();
        $genderData = [];
        $chartData = $houses->map(function ($house) use (&$genderData) {
            $genderData[] = ['name' => $house->name . ' Males', 'value' => $house->males_count];
            $genderData[] = ['name' => $house->name . ' Females', 'value' => $house->females_count];
            return ['name' => $house->name, 'value' => $house->students_count];
        });

        return view('students.students-houses-analysis', [
            'houses'    => $houses,
            'school_data' => $school_data,
            'chartData' => $chartData,
            'genderData' => $genderData
        ]);
    }

    public function studentsWithoutHouses(){
        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $school_data = SchoolSetup::first();
        $selectedTerm = Term::find($selectedTermId);

        // Get students without houses for the selected term
        $studentsWithoutHouses = Student::whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('student_term.term_id', $selectedTermId)
                  ->where('student_term.status', 'Current');
        })->whereDoesntHave('houses', function ($query) use ($selectedTermId) {
            $query->where('student_house.term_id', $selectedTermId);
        })->whereHas('classes', function ($query) use ($selectedTermId) {
            $query->where('klass_student.term_id', $selectedTermId);
        })->with([
            'currentClassRelation' => function ($query) {
                $query->select('klasses.id', 'klasses.name');
            },
            'houses' => function ($query) use ($selectedTermId) {
                $query->where('student_house.term_id', $selectedTermId);
            }
        ])->get();

        // Add current house status to each student (double-check for race conditions)
        $studentsWithoutHouses->each(function ($student) use ($selectedTermId) {
            $student->current_house = $student->houses->first();
            $student->has_house = $student->current_house !== null;
        });

        // Group students by class
        $studentsByClass = $studentsWithoutHouses->groupBy(function($student) {
            $class = $student->currentClassRelation->first();
            return $class ? $class->id : 0;
        });

        // Get houses for the selected term
        $houses = House::where('term_id', $selectedTermId)->get()->keyBy('id');

        // For each class, determine which house the majority of classmates are in
        $classHouseMapping = [];
        foreach ($studentsByClass as $classId => $students) {
            if ($classId == 0) continue;

            $className = $students->first()->currentClassRelation->first()->name ?? 'Unknown';

            // Get all students in this class who have a house
            $classmatesWithHouses = Student::whereHas('classes', function ($query) use ($classId, $selectedTermId) {
                $query->where('klasses.id', $classId)
                      ->where('klass_student.term_id', $selectedTermId);
            })->whereHas('houses', function ($query) use ($selectedTermId) {
                $query->where('student_house.term_id', $selectedTermId);
            })->with(['houses' => function($query) use ($selectedTermId) {
                $query->where('student_house.term_id', $selectedTermId);
            }])->get();

            // Count students per house
            $houseCounts = [];
            foreach ($classmatesWithHouses as $classmate) {
                $house = $classmate->houses->first();
                if ($house) {
                    $houseCounts[$house->id] = ($houseCounts[$house->id] ?? 0) + 1;
                }
            }

            // Find the house with the most students
            $suggestedHouseId = null;
            $maxCount = 0;
            foreach ($houseCounts as $houseId => $count) {
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $suggestedHouseId = $houseId;
                }
            }

            $classHouseMapping[$classId] = [
                'name' => $className,
                'suggested_house_id' => $suggestedHouseId,
                'suggested_house_name' => $suggestedHouseId ? ($houses[$suggestedHouseId]->name ?? null) : null,
                'students' => $students
            ];
        }

        // Sort by class name
        uasort($classHouseMapping, function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return view('students.students-without-houses', [
            'studentsByClass' => $classHouseMapping,
            'houses' => $houses,
            'school_data' => $school_data,
            'totalStudents' => $studentsWithoutHouses->count(),
            'selectedTerm' => $selectedTerm
        ]);
    }

    public function allocateStudentToHouse(Request $request){
        $this->authorize('allocateHouse', Student::class);

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'house_id' => 'required|integer|exists:houses,id'
        ]);

        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);

        try {
            return DB::transaction(function () use ($validated, $selectedTermId) {
                $house = House::findOrFail($validated['house_id']);
                $student = Student::findOrFail($validated['student_id']);

                // Verify house belongs to selected term
                if ((int) $house->term_id !== $selectedTermId) {
                    return redirect()->back()->with('error', 'Cannot allocate to a house from a different term.');
                }

                // Verify student is a Current student in the selected term
                $isCurrentStudent = $student->terms()
                    ->where('student_term.term_id', $selectedTermId)
                    ->where('student_term.status', 'Current')
                    ->exists();

                if (!$isCurrentStudent) {
                    return redirect()->back()->with('error', 'Student is not a current student for this term.');
                }

                // Check if student is already allocated to a house for this term
                $existingHouse = $student->houses()->wherePivot('term_id', $selectedTermId)->first();
                if ($existingHouse) {
                    return redirect()->back()->with('error', 'Student is already allocated to ' . $existingHouse->name . ' for this term.');
                }

                // Allocate the student
                $house->students()->attach($student->id, [
                    'term_id' => $selectedTermId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Clear cache
                CacheHelper::forgetUnallocatedHouseStudents($selectedTermId);

                return redirect()->back()->with('message', $student->first_name . ' ' . $student->last_name . ' allocated to ' . $house->name . ' successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Error allocating student to house: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while allocating the student. Please try again.');
        }
    }

    public function allocateAllStudentsToHouse(Request $request){
        $this->authorize('allocateHouse', Student::class);

        $validated = $request->validate([
            'class_id' => 'required|integer|exists:klasses,id',
            'house_id' => 'required|integer|exists:houses,id'
        ]);

        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);

        try {
            return DB::transaction(function () use ($validated, $selectedTermId) {
                $house = House::findOrFail($validated['house_id']);
                $klass = Klass::findOrFail($validated['class_id']);

                // Verify house belongs to selected term
                if ((int) $house->term_id !== $selectedTermId) {
                    return redirect()->back()->with('error', 'Cannot allocate to a house from a different term.');
                }

                // Verify class belongs to selected term
                if ((int) $klass->term_id !== $selectedTermId) {
                    return redirect()->back()->with('error', 'Class does not belong to the selected term.');
                }

                // Get all current students in this class without houses for the selected term
                $students = Student::whereHas('terms', function ($query) use ($selectedTermId) {
                    $query->where('student_term.term_id', $selectedTermId)
                          ->where('student_term.status', 'Current');
                })->whereDoesntHave('houses', function ($query) use ($selectedTermId) {
                    $query->where('student_house.term_id', $selectedTermId);
                })->whereHas('classes', function ($query) use ($validated, $selectedTermId) {
                    $query->where('klasses.id', $validated['class_id'])
                          ->where('klass_student.term_id', $selectedTermId);
                })->get();

                if ($students->isEmpty()) {
                    return redirect()->back()->with('error', 'No students to allocate in ' . $klass->name . '.');
                }

                // Allocate all students
                $pivotData = [];
                foreach ($students as $student) {
                    $pivotData[$student->id] = [
                        'term_id' => $selectedTermId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                $house->students()->attach($pivotData);

                // Clear cache
                CacheHelper::forgetUnallocatedHouseStudents($selectedTermId);

                return redirect()->back()->with('message', $students->count() . ' students from ' . $klass->name . ' allocated to ' . $house->name . ' successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Error allocating students to house: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while allocating students. Please try again.');
        }
    }

    public function studentsIndex(){
        return response()->json(Student::all(), 200);
    }

    public function studentShow($id){
        $student = Student::find($id);
        if ($student) {
            return response()->json($student, 200);
        } else {
            return response()->json(['error' => 'Student not found'], 404);
        }
    }

    public function getBookAllocation($studentId){
        $student = Student::findOrFail($studentId);

        $books = Book::whereHas('copies', function ($query) {
            $query->where('status', 'available');
        })->with(['copies' => function ($query) {
            $query->where('status', 'available');
        }])->get();

        $availableCopies = $books->mapWithKeys(function ($book) {
            return [$book->id => optional($book->copies->first())->accession_number];
        })->toArray();

        return view('students.new-book-allocation', compact('student', 'books', 'availableCopies'));
    }

    public function storeBookAllocation(Request $request){
        $this->normalizeDateInputs($request, ['allocation_date', 'due_date']);

        $validator = Validator::make($request->all(), [
            'student_id'              => 'required|exists:students,id',
            'book_id'                 => 'required|exists:books,id',
            'grade_id'                => 'required|exists:grades,id',
            'accession_number'        => 'required|string',
            'allocation_date'         => 'nullable|date',
            'due_date'                => 'nullable|date|after_or_equal:allocation_date',
            'condition_on_allocation' => 'nullable|in:New,Good,Fair,Poor',
            'notes'                   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        return DB::transaction(function () use ($validated, $request) {
            $copy = Copy::firstOrNew([
                'book_id'          => $validated['book_id'],
                'accession_number' => $validated['accession_number']
            ]);

            if (!$copy->exists) {
                $copy->status = 'available';
                $copy->save();
            } elseif ($copy->status !== 'available') {
                return redirect()->back()->withErrors(['accession_number' => 'This copy is not available for allocation.'])->withInput();
            }

            BookAllocation::create([
                'student_id'              => $validated['student_id'],
                'copy_id' => $copy->id,
                'grade_id'                => $validated['grade_id'],
                'accession_number'        => $validated['accession_number'],
                'allocation_date'         => $validated['allocation_date'] ?? now(),
                'due_date'                => $validated['due_date'],
                'condition_on_allocation' => $validated['condition_on_allocation'],
                'notes'                   => $validated['notes'],
            ]);

            $copy->update(['status' => 'checked_out']);
            $message = 'Book allocated successfully.';
            if ($request->has('save_and_new')) {
                return redirect()->route('students.get-book-allocation', $validated['student_id'])->with('message', $message);
            }
            CacheHelper::forgetBookAllocations();
            return redirect()->route('students.show', ['id' => $validated['student_id']])->with('message', $message);
        });
    }

    public function editBookAllocation($studentId, $allocationId){
        $allocation = BookAllocation::findOrFail($allocationId);
        $student = Student::findOrFail($studentId);

        if ($allocation->student_id !== $student->id) {
            return redirect()->route('students.show', $studentId)->withErrors('The specified allocation does not belong to the selected student.');
        }
        return view('students.edit-book-allocation', compact('allocation', 'student'));
    }

    public function updateBookAllocation(Request $request, $id){
        $this->normalizeDateInputs($request, ['allocation_date', 'due_date', 'return_date']);

        $validator = Validator::make($request->all(), [
            'student_id'              => 'required|exists:students,id',
            'book_id'                 => 'required|exists:books,id',
            'grade_id'                => 'required|exists:grades,id',
            'accession_number'        => 'required|string',
            'allocation_date'         => 'nullable|date',
            'due_date'                => 'nullable|date|after_or_equal:allocation_date',
            'condition_on_allocation' => 'nullable|in:New,Good,Fair,Poor',
            'return_date'             => 'nullable|date',
            'condition_on_return'     => 'nullable|in:Good,Fair,Poor,Damaged,Lost',
            'notes'                   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        return DB::transaction(function () use ($validated, $id, $request) {
            $allocation = BookAllocation::findOrFail($id);
            $copy = Copy::firstOrNew([
                'book_id'          => $validated['book_id'],
                'accession_number' => $validated['accession_number']
            ]);

            if (!$copy->exists) {
                $copy->status = 'available';
                $copy->save();
            } elseif ($copy->status !== 'available' && $allocation->copy_id != $copy->id) {
                return redirect()->back()->withErrors(['accession_number' => 'This copy is not available for allocation.'])->withInput();
            }

            if ($allocation->copy_id != $copy->id) {
                $allocation->copy->update(['status' => 'available']);
                $copy->update(['status' => 'checked_out']);
            }

            $allocation->update([
                'copy_id'                 => $copy->id,
                'grade_id' => $validated['grade_id'],
                'allocation_date'         => $validated['allocation_date'],
                'due_date'                => $validated['due_date'],
                'condition_on_allocation' => $validated['condition_on_allocation'],
                'return_date'             => $validated['return_date'],
                'condition_on_return'     => $validated['condition_on_return'],
                'notes'                   => $validated['notes'],
            ]);

            if ($validated['return_date']) {
                $copy->update(['status' => 'available']);
            }

            CacheHelper::forgetBookAllocations();

            $message = 'Book allocation updated successfully.';
            return redirect()->back()->with('message', $message);
        });
    }

    public function studentBookAllocation(Request $request){
        $this->normalizeDateInputs($request, ['allocation_date', 'due_date', 'return_date']);

        $validator = Validator::make($request->all(), [
            'student_id'              => 'required|exists:students,id',
            'book_id'                 => 'required|exists:books,id',
            'grade_id'                => 'required|exists:grades,id',
            'accession_number'        => 'required|string',
            'allocation_date'         => 'nullable|date',
            'due_date'                => 'nullable|date|after_or_equal:allocation_date',
            'condition_on_allocation' => 'nullable|in:New,Good,Fair,Poor',
            'return_date'             => 'nullable|date',
            'condition_on_return'     => 'nullable|in:Good,Fair,Poor,Damaged,Lost',
            'notes'                   => 'nullable|string',
            'allocation_id'           => 'nullable|exists:book_allocations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $isNewAllocation = empty($validated['allocation_id']);

        return DB::transaction(function () use ($validated, $isNewAllocation, $request) {
            $copy = Copy::firstOrNew([
                'book_id'          => $validated['book_id'],
                'accession_number' => $validated['accession_number']
            ]);

            if (!$copy->exists) {
                $copy->status = 'available';
                $copy->save();
            } elseif ($copy->status !== 'available' && $isNewAllocation) {
                return redirect()->back()->withErrors(['accession_number' => 'This copy is not available for allocation.'])->withInput();
            }

            if ($isNewAllocation) {
                $allocation = BookAllocation::create([
                    'student_id'              => $validated['student_id'],
                    'copy_id'                 => $copy->id,
                    'grade_id'                => $validated['grade_id'],
                    'accession_number'        => $validated['accession_number'],
                    'allocation_date'         => $validated['allocation_date'] ?? now(),
                    'due_date'                => $validated['due_date'],
                    'condition_on_allocation' => $validated['condition_on_allocation'],
                    'notes'                   => $validated['notes'],
                ]);

                $copy->update(['status' => 'checked_out']);

                $message = 'Book allocated successfully.';
            } else {
                $allocation = BookAllocation::findOrFail($validated['allocation_id']);

                if ($allocation->copy_id != $copy->id) {
                    $allocation->copy->update(['status' => 'available']);
                    $copy->update(['status' => 'checked_out']);
                }

                $allocation->update([
                    'copy_id'                 => $copy->id,
                    'grade_id' => $validated['grade_id'],
                    'allocation_date'         => $validated['allocation_date'],
                    'due_date'                => $validated['due_date'],
                    'condition_on_allocation' => $validated['condition_on_allocation'],
                    'return_date'             => $validated['return_date'],
                    'condition_on_return'     => $validated['condition_on_return'],
                    'notes'                   => $validated['notes'],
                ]);

                if ($validated['return_date']) {
                    $copy->update(['status' => 'available']);
                }

                $message = 'Book allocation updated successfully.';
            }

            if ($request->has('save_and_new')) {
                return redirect()->route('students.get-book-allocation', $validated['student_id'])->with('message', $message);
            }
            return redirect()->route('students.show', ['student' => $validated['student_id']])->with('message', $message);
        });
    }

    public function getAvailableCopies(Request $request, $bookId){
        $book = Book::findOrFail($bookId);
        $availableCopies = $book->copies()->where('status', 'available')->select('id', 'accession_number')->paginate(10);

        return response()->json($availableCopies);
    }

    public function storeOrUpdateDepartures(Request $request){
        try {
            $this->normalizeDateInputs($request, ['last_day_of_attendance']);

            $rules = [
                'student_id'                                                                   => 'required|exists:students,id',
                'last_day_of_attendance'                                                       => [
                    'required',
                    'date',
                    'before_or_equal:today',
                    'after:' . now()->subYears(2)->format('Y-m-d')
                ],
                'reason_for_leaving'                                                           => [
                    'required',
                    'string',
                    Rule::in(StudentDeparture::REASONS)
                ],
                'reason_for_leaving_other' => [
                    'nullable',
                    'required_if:reason_for_leaving,Other',
                    'string',
                    'max:255'
                ],
                'new_school_name'                                                              => 'nullable|string|max:255',
                'new_school_contact_number'                                                    => [
                    'nullable',
                    'string',
                    'regex:/^[\d\s\-\+\(\)]+$/',
                    'max:20'
                ],
                'property_returned'                                                            => 'required|boolean',
                'notes'                                                                        => 'nullable|string|max:1000'
            ];

            $messages = [
                'last_day_of_attendance.before_or_equal' => 'The last day of attendance cannot be in the future.',
                'last_day_of_attendance.after'           => 'The last day of attendance cannot be more than 2 years ago.',
                'new_school_contact_number.regex'        => 'The contact number format is invalid. Please use only numbers, spaces, and basic punctuation.',
                'reason_for_leaving.in'                  => 'The selected reason for leaving is invalid.',
                'reason_for_leaving_other.required_if'   => 'Please specify the other reason for leaving.'
            ];

            $validated = validator($request->all(), $rules, $messages)->validate();
            DB::beginTransaction();

            $student = Student::findOrFail($validated['student_id']);
            $departureYear = Carbon::parse($validated['last_day_of_attendance'])->year;
            $departure = StudentDeparture::updateOrCreate(['student_id' => $validated['student_id']], array_merge($validated, [
                'year'         => $departureYear,
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]));

            $student->update(['status' => 'Past', 'last_updated_by' => auth()->user()->fullName ?? 'system']);

            $activityDetails = [
                'student_id'        => $student->id,
                'student_name' => $student->fullName,
                'departure_id'      => $departure->id,
                'last_day'          => $validated['last_day_of_attendance'],
                'year' => $departureYear,
                'reason'            => $validated['reason_for_leaving'],
                'other_reason'      => $validated['reason_for_leaving_other'] ?? null,
                'property_returned' => $validated['property_returned'],
                'processed_by'      => auth()->id() ?? 'system',
                'processed_at' => now()
            ];

            DB::commit();
            Log::info('Student departure processed successfully', $activityDetails);
            return redirect()->back()->with('message', 'Student departure has been successfully recorded.');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error processing student departure', [
                'student_id' => $request->student_id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
                'user_id'    => auth()->id() ?? 'system'
            ]);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'An error occurred while processing the student departure.';

            return redirect()->back()->withInput()->withErrors(['error' => $errorMessage]);
        }
    }

    private function normalizeDateInputs(Request $request, array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $normalized[$field] = $this->normalizeDateInput($request->input($field));
        }

        if (!empty($normalized)) {
            $request->merge($normalized);
        }
    }

    private function normalizeDateInput($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        }

        return $value;
    }

    public function getBookAllocationsReport(Request $request){
        $request->validate(['start_date' => 'nullable|date', 'end_date' => 'nullable|date|after_or_equal:start_date',]);

        $school_data = SchoolSetup::first();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        CacheHelper::forgetBookAllocations();

        $allocations = CacheHelper::getBookAllocations($startDate, $endDate);
        return view('students.students-textbook-allocations', compact('allocations', 'school_data'));
    }

    public function getBooksWithCopiesReport(){
        $school_data = SchoolSetup::first();
        $books = CacheHelper::getBooksWithCopiesReport();
        return view('students.students-textbook-by-status', compact('books', 'school_data'));
    }

    public function getTermImportList(Request $request){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $students = Student::with([
            'type',
            'currentGrade',
            'classes' => function ($query) use ($selectedTermId) {
                $query->wherePivot('term_id', $selectedTermId);
            },
            'sponsor'
        ])->whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId);
        })->get();

        $reportData = $students->map(function ($student) {
            return [
                'connect_id'         => $student->connect_id,
                'first_name'         => $student->first_name,
                'last_name'          => $student->last_name,
                'middle_name'        => $student->middle_name ?? '',
                'gender'             => $student->gender,
                'date_of_birth'      => $student->date_of_birth,
                'nationality'        => $student->nationality,
                'id_number'          => $student->id_number,
                'status'             => $student->status,
                'type'               => optional($student->type)->name,
                'grade'              => optional($student->currentGrade)->name,
                'class'              => optional($student->class)->name,
                'year'               => $student->year,
                'parent_first_name'  => optional($student->sponsor)->first_name,
                'parent_last_name'   => optional($student->sponsor)->last_name,
                'parent_gender'      => optional($student->sponsor)->gender,
                'parent_date_of_birth'=> optional($student->sponsor)->date_of_birth,
                'parent_id_number'   => optional($student->sponsor)->id_number,
                'parent_relation'    => optional($student->sponsor)->relation,
                'parent_status'      => optional($student->sponsor)->status,
                'parent_phone'       => optional($student->sponsor)->phone,
                'parent_profession'  => optional($student->sponsor)->profession,
                'boarding'           => $student->is_boarding ? 'Boarding' : 'Day',
            ];
        })->toArray();

        if ($request->query('export') === 'excel') {
            return Excel::download(new StudentImportExportReport($reportData), 'subject_analysis_report.xlsx');
        }

        $school_data = SchoolSetup::first();
        return view('students.students-list-import', compact('reportData', 'school_data'));
    }

}
