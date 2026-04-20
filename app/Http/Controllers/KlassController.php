<?php

namespace App\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Models\Klass;
use App\Models\Grade;
use App\Models\KlassSubject;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Helpers\TermHelper;
use App\Models\Attendance;
use App\Models\Comment;
use App\Models\OverallGradingMatrix;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\SubjectComment;
use App\Models\Term;
use App\Models\Test;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\StudentTermRemovalService;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class KlassController extends Controller{

    protected $studentRemovalService;

    public function __construct(StudentTermRemovalService $studentRemovalService){
        $this->middleware('auth');
        $this->studentRemovalService = $studentRemovalService;
    }

    private function isFullAcademicAdmin(): bool {
        return auth()->user()->hasAnyRoles([
            'Administrator',
            'HOD',
            'Academic Admin',
            'Academic Edit',
            'Academic View',
            'Assessment Admin',
        ]);
    }

    private function authorizeClassAllocationAccess(Klass $klass, string $message): void
    {
        abort_unless(
            Gate::allows('class-allocation-teacher', $klass),
            403,
            $message
        );
    }

    public function index(){
        $this->authorize('access-class-allocations');
        $this->cleanDuplicateKlassSubjects();
        $currentTerm = Cache::remember('current_term', now()->addHour(), function () {
            return TermHelper::getCurrentTerm();
        });

        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $terms = Cache::remember('terms_list', now()->addHour(), function () {
            return Term::select('id', 'term', 'year')
                ->orderBy('year', 'desc')
                ->orderBy('term', 'desc')
                ->get();
        });

        $user = auth()->user();
        $isClassTeacher = !$this->isFullAcademicAdmin();

        $grades = Grade::with(['klasses' => function ($query) use ($isClassTeacher, $user) {
            $query->select('id', 'name', 'grade_id', 'user_id')
                  ->orderBy('name', 'asc');
            if ($isClassTeacher) {
                $query->where('user_id', $user->id);
            }
        }])->where('term_id', $selectedTermId)
            ->select('id', 'name', 'term_id')
            ->orderBy('name')
            ->get();

        if ($isClassTeacher) {
            $grades = $grades->filter(fn($grade) => $grade->klasses->isNotEmpty())->values();
        }

        $classes = collect();

        return view('classes.index', compact(
            'classes',
            'grades',
            'currentTerm',
            'terms',
            'isClassTeacher'
        ));
    }

    public function masterSubjectIndex(){
        $this->authorize('manage-academic');
        $masterSubjects = CacheHelper::getSubjectMasterList();
        return view('classes.master-subject-list', ['subjects' => $masterSubjects]);
    }

    function classLists($id){
        $grade = Grade::find($id);
        return view('classes.class-lists', ['grade' => $grade]);
    }


    public function getClassesByTermAndGrade($termId, $gradeId){
        $this->authorize('access-class-allocations');
        $user = auth()->user();
        $isClassTeacher = !$this->isFullAcademicAdmin();

        $query = Klass::with(['teacher', 'grade', 'students', 'monitor', 'monitress'])
                    ->where('term_id', $termId)
                    ->where('grade_id', $gradeId);

        if ($isClassTeacher) {
            $query->where('user_id', $user->id);
        }

        $classes = $query->orderBy('name', 'asc')->get();

        return view('classes.class-lists', ['classes' => $classes]);
    }

    public function create(){
        $this->authorize('manage-academic');
        $teachers = User::where('status', 'Current')->where('area_of_work', 'Teaching')->get();
        $terms = StudentController::terms();
        $grades = CacheHelper::getGrades();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $allocatedMonitorIds = Klass::where('term_id', $selectedTermId)
            ->whereNotNull('monitor_id')
            ->pluck('monitor_id');
        $allocatedMonitressIds = Klass::where('term_id', $selectedTermId)
            ->whereNotNull('monitress_id')
            ->pluck('monitress_id');
        $allocatedIds = $allocatedMonitorIds->merge($allocatedMonitressIds)->unique();

        $maleStudents = Student::where('gender', 'M')
            ->whereNotIn('id', $allocatedIds)
            ->orderBy('first_name')->orderBy('last_name')->get();
        $femaleStudents = Student::where('gender', 'F')
            ->whereNotIn('id', $allocatedIds)
            ->orderBy('first_name')->orderBy('last_name')->get();

        $schoolType = SchoolSetup::first();
        return view('classes.add-new-class', ['teachers' => $teachers, 'terms' => $terms, 'grades' => $grades, 'schoolType' => $schoolType, 'maleStudents' => $maleStudents, 'femaleStudents' => $femaleStudents]);
    }

    public function store(Request $request){
        $this->authorize('manage-academic');
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
        $messages = [
            'name.required' => 'The class name is required.',
            'name.string' => 'The class name must be a valid string.',
            'name.max' => 'The class name should not exceed 255 characters.',
            'user.required' => 'The user field is required.',
            'user.exists' => 'The selected user does not exist.',
            'term.required' => 'The term field is required.',
            'term.exists' => 'The selected term does not exist.',
            'grade.required' => 'The grade field is required.',
            'grade.exists' => 'The selected grade does not exist.',
            'year.required' => 'The year field is required.',
            'year.integer' => 'The year must be a valid integer.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user' => 'required|exists:users,id',
            'term' => 'required|exists:terms,id',
            'grade' => 'required|exists:grades,id',
            'year' => 'required|integer',
            'monitor_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where(function ($query) {
                    $query->where('gender', 'M');
                })
            ],
            'monitress_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where(function ($query) {
                    $query->where('gender', 'F');
                })
            ],
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Ensure selected monitor/monitress are not already allocated in the term (across either role)
        $termIdForAllocation = (int) $request->input('term');
        if ($request->filled('monitor_id')) {
            $taken = Klass::where('term_id', $termIdForAllocation)
                ->where(function($q) use ($request) {
                    $q->where('monitor_id', $request->input('monitor_id'))
                      ->orWhere('monitress_id', $request->input('monitor_id'));
                })
                ->exists();
            if ($taken) {
                return redirect()->back()->with('error', 'Selected monitor is already allocated to another class in this term.')->withInput();
            }
        }
        if ($request->filled('monitress_id')) {
            $taken = Klass::where('term_id', $termIdForAllocation)
                ->where(function($q) use ($request) {
                    $q->where('monitor_id', $request->input('monitress_id'))
                      ->orWhere('monitress_id', $request->input('monitress_id'));
                })
                ->exists();
            if ($taken) {
                return redirect()->back()->with('error', 'Selected monitress is already allocated to another class in this term.')->withInput();
            }
        }

        $existingClass = Klass::where('term_id', TermHelper::getCurrentTerm()->id)
            ->where('name', $request->input('name'))
            ->first();

        if ($existingClass) {
            return redirect()->back()->with('error', 'A class with the same name already exists for the current term.');
        }

        Klass::create([
            'name' => $request->input('name'),
            'user_id' => $request->input('user'),
            'term_id' => $request->input('term'),
            'grade_id' => $request->input('grade'),
            'monitor_id' => $request->input('monitor_id'),
            'monitress_id' => $request->input('monitress_id'),
            'type' => $request->input('type'),
            'year' => $request->input('year'),
        ]);

        CacheHelper::forgetKlassesForTerm($currentTermId,$user);
        return redirect()->back()->with('message', 'Class added successfully');
    }


    public function show($id){
        try {
            if (!is_numeric($id)) {
                return back()->with('error', 'Invalid class identifier');
            }

            $klass = Klass::with(['students', 'grade', 'teacher'])->findOrFail($id);
            $this->authorizeClassAllocationAccess($klass, 'You are not authorized to view this class.');

            return view('classes.allocated-students', ['class' => $klass]);
        } catch (Exception $e) {
            return back()->with('error', 'An error occurred while loading class details');
        }
    }

    public function edit($id){
        try {
            if (!is_numeric($id)) {
                Log::warning('Invalid class ID format for edit', ['id' => $id]);
                return back()->with('error', 'Invalid class identifier');
            }
    
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $currentTerm = TermHelper::getCurrentTerm();
            
            $klass = Klass::with(['grade', 'teacher', 'students'])->findOrFail($id);
            $this->authorizeClassAllocationAccess($klass, 'You are not authorized to edit this class.');
            $teachers = User::teachingAndCurrent()->orderBy('firstname')->get();
            $grades = Grade::where('term_id', $klass->term_id)->orderBy('sequence')->get();
            $terms = StudentController::terms();
            $schoolType = SchoolSetup::first();

            
            $classStudents = $klass->currentStudents($klass->term_id, $klass->year)->get();
            $allocatedMonitorIds = Klass::where('term_id', $klass->term_id)
                ->where('id', '!=', $klass->id)
                ->whereNotNull('monitor_id')
                ->pluck('monitor_id');
            $allocatedMonitressIds = Klass::where('term_id', $klass->term_id)
                ->where('id', '!=', $klass->id)
                ->whereNotNull('monitress_id')
                ->pluck('monitress_id');
            $allocatedIds = $allocatedMonitorIds->merge($allocatedMonitressIds)->unique();

            $maleStudents = $classStudents->filter(function ($student) use ($allocatedIds, $klass) {
                return $student->gender === 'M' && (!$allocatedIds->contains($student->id) || $student->id == $klass->monitor_id);
            })->sortBy('first_name')->values();

            $femaleStudents = $classStudents->filter(function ($student) use ($allocatedIds, $klass) {
                return $student->gender === 'F' && (!$allocatedIds->contains($student->id) || $student->id == $klass->monitress_id);
            })->sortBy('first_name')->values();
    
            $isPastTerm = $selectedTermId != $currentTerm->id;
            $selectedTerm = null;
            if ($isPastTerm) {
                $selectedTerm = Term::find($selectedTermId);
            }
    
            if ($teachers->isEmpty()) {
                Log::warning('No teachers available for class assignment');
                return back()->with('error', 'No teachers available for assignment');
            }
    
            if ($grades->isEmpty()) {
                Log::warning('No grades available for class assignment', [
                    'class_id' => $id,
                    'class_term_id' => $klass->term_id
                ]);
                return back()->with('error', 'No grades available for assignment in this term');
            }
    
            return view('classes.edit-klass', [
                'klass' => $klass,
                'teachers' => $teachers,
                'grades' => $grades,
                'terms' => $terms,
                'schoolType' => $schoolType,
                'maleStudents' => $maleStudents,
                'femaleStudents' => $femaleStudents,
                'isPastTerm' => $isPastTerm,
                'selectedTerm' => $selectedTerm,
                'currentTerm' => $currentTerm
            ]);
        } catch (Exception $e) {
            Log::error('Error loading class edit form', [
                'class_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while loading the edit form');
        }
    }

    public function deleteKlass($klassId) {
        $this->authorize('manage-academic');
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
    
        try {
            if (!is_numeric($klassId)) {
                Log::warning('Invalid class ID format for deletion', ['id' => $klassId]);
                return back()->with('error', 'Invalid class identifier');
            }
    
            $klass = Klass::with('students')->findOrFail($klassId);
            $studentsCount = $klass->students->count();
    
            if ($studentsCount > 0) {
                Log::warning('Attempted to delete class with students', [
                    'class_id' => $klassId,
                    'student_count' => $studentsCount,
                    'user_id' => auth()->id()
                ]);
                return back()->with('error', "This class has {$studentsCount} students, delete the students first");
            }
    
            DB::transaction(function () use ($klass) {
                $klass->subjectClasses()->withTrashed()->get()->each(function ($klassSubject) {
                    $klassSubject->forceDelete();
                });
                $klass->forceDelete();
            });
    
            CacheHelper::forgetKlassesForTerm($currentTermId, $user);
            Log::info('Class force deleted successfully', [
                'class_id' => $klassId,
                'class_name' => $klass->name,
                'user_id' => auth()->id()
            ]);
            return back()->with('message', 'Class deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting class', [
                'class_id' => $klassId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while deleting the class');
        }
    }

    public function removeMultipleStudents(Request $request, $klassId){
        try {
            DB::beginTransaction();
            $studentIds = $request->input('students', []);
            $klass = Klass::findOrFail($klassId);
            $this->authorizeClassAllocationAccess($klass, 'You are not authorized to modify this class.');
            $removedCount = 0;
            $messages = [];
            
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            if ($klass->term_id !== $selectedTermId) {
                return redirect()->back()->with('error', "This class belongs to a different term. Please ensure you're working in the correct term.");
            }
            
            $termId = $selectedTermId;
            foreach ($studentIds as $studentId) {
                $student = Student::findOrFail($studentId);
                $deletedRows = DB::table('klass_student')
                    ->where('student_id', $studentId)
                    ->where('klass_id', $klassId)
                    ->where('term_id', $termId)
                    ->delete();
                
                if ($deletedRows > 0) {
                    $removedCount++;
                    
                    $otherClassesInTerm = DB::table('klass_student')
                        ->where('student_id', $studentId)
                        ->where('term_id', $termId)
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    if (!$otherClassesInTerm) {
                        $this->hardDeleteStudentTermData($student, $termId);
                        $messages[] = "Completely removed from term {$termId}";
                        
                        Log::info('Student completely removed from term', [
                            'student_id' => $studentId,
                            'class_id' => $klassId,
                            'term_id' => $termId,
                            'user_id' => auth()->id()
                        ]);
                    } else {
                        $messages[] = "Kept in term (enrolled in other classes)";
                        
                        Log::info('Student removed from class but kept in term', [
                            'student_id' => $studentId,
                            'class_id' => $klassId,
                            'term_id' => $termId,
                            'user_id' => auth()->id()
                        ]);
                    }
                }
            }
    
            CacheHelper::forgetStudentsData();
            CacheHelper::forgetStudentsCount($termId);
            CacheHelper::forgetStudentsTermData();
    
            DB::commit();
            
            $termInfo = $selectedTermId !== TermHelper::getCurrentTerm()->id 
                ? " from the selected term (Term ID: {$selectedTermId})" 
                : "";
                
            $message = "Successfully removed {$removedCount} student(s) from class {$klass->name}{$termInfo}. ";
            if (!empty($messages)) {
                $message .= 'Actions: ' . implode(', ', array_unique($messages));
            }
    
            return redirect()->back()->with('message', $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove multiple students', [
                'class_id' => $klassId,
                'selected_term_id' => $selectedTermId,
                'class_term_id' => $klass->term_id ?? null,
                'student_ids' => $studentIds ?? [],
                'error' => $e->getMessage()
            ]);
    
            return redirect()->back()->with('error', 'Failed to remove students: ' . $e->getMessage());
        }
    }

    private function hardDeleteStudentTermData($student, $termId) {
        $studentId = $student->id;
        
        DB::table('attendances')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
         
        DB::table('comments')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
        
        $testIds = DB::table('tests')
            ->where('term_id', $termId)
            ->whereNull('deleted_at')
            ->pluck('id');
        
        if ($testIds->isNotEmpty()) {
            $studentTestIds = DB::table('student_tests')
                ->where('student_id', $studentId)
                ->whereIn('test_id', $testIds)
                ->whereNull('deleted_at')
                ->pluck('id');
                
            if ($studentTestIds->isNotEmpty()) {
                DB::table('subject_comments')
                    ->whereIn('student_test_id', $studentTestIds)
                    ->delete();
            }
            
            DB::table('student_tests')
                ->where('student_id', $studentId)
                ->whereIn('test_id', $testIds)
                ->delete();
        }
        
        DB::table('subject_comments')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
        
        DB::table('student_optional_subjects')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
        
        DB::table('manual_attendance_entries')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
        
        DB::table('criteria_based_student_tests')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();

        DB::table('student_house')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->delete();
        
        Log::info('Hard deleted all term data for student', [
            'student_id' => $studentId,
            'term_id' => $termId
        ]);
    }

    public function moveMultipleStudents(Request $request){
        $request->validate([
            'source_class_id' => 'required|exists:klasses,id',
            'new_class_id' => 'required|exists:klasses,id',
            'student_ids'  => 'required'
        ]);
    
        try {
            $studentIds = json_decode($request->input('student_ids'), true);
            if (!is_array($studentIds) || empty($studentIds)) {
                return back()->with('error', 'No students selected or invalid data.');
            }
    
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $sourceKlass = Klass::findOrFail($request->input('source_class_id'));
            $newKlass = Klass::findOrFail($request->input('new_class_id'));

            $this->authorizeClassAllocationAccess($sourceKlass, 'You are not authorized to move students from this class.');
            $this->authorizeClassAllocationAccess($newKlass, 'You are not authorized to move students to the selected class.');
            
            if ($sourceKlass->term_id !== $selectedTermId) {
                return back()->with('error', "The source class belongs to a different term. Please ensure you're working in the correct term.");
            }

            if ($newKlass->term_id !== $selectedTermId) {
                return back()->with('error', "The selected class belongs to a different term. Please select a class from the currently selected term.");
            }
            
            $studentRemovalService = app(StudentTermRemovalService::class);
            $movedCount = 0;
            $school = SchoolSetup::first();
            
            DB::transaction(function () use ($studentIds, $newKlass, $sourceKlass, $selectedTermId, $studentRemovalService, &$movedCount, $school) {
                foreach ($studentIds as $studentId) {
                    $student = Student::findOrFail($studentId);
                    
                    $oldKlass = $student->classes()
                        ->wherePivot('term_id', $selectedTermId)
                        ->first();

                    if (!$oldKlass || $oldKlass->id !== $sourceKlass->id) {
                        throw new Exception('One or more selected students do not belong to the source class.');
                    }
                    
                    if ($oldKlass && $oldKlass->id == $newKlass->id) {
                        continue;
                    }
                    
                    if ($oldKlass) {
                        DB::table('attendances')
                            ->where('student_id', $student->id)
                            ->where('klass_id', $oldKlass->id)
                            ->where('term_id', $selectedTermId)
                            ->delete();
                        
                        DB::table('comments')
                            ->where('student_id', $student->id)
                            ->where('klass_id', $oldKlass->id)
                            ->where('term_id', $selectedTermId)
                            ->delete();
                        
                        $studentOptSubjects = DB::table('student_optional_subjects')
                            ->where('student_id', $student->id)
                            ->where('term_id', $selectedTermId)
                            ->pluck('optional_subject_id');
                        
                        foreach ($studentOptSubjects as $optSubjectId) {
                            $studentRemovalService->removeStudentFromOptionalSubject(
                                $student, 
                                $optSubjectId,
                                $selectedTermId
                            );
                        }
                        
                        $testIds = DB::table('tests')
                            ->where('term_id', $selectedTermId)
                            ->whereNull('deleted_at')
                            ->pluck('id');
                        
                        if ($testIds->isNotEmpty()) {
                            $studentTestIds = DB::table('student_tests')
                                ->where('student_id', $student->id)
                                ->whereIn('test_id', $testIds)
                                ->whereNull('deleted_at')
                                ->pluck('id');
                                
                            DB::table('student_tests')
                                ->where('student_id', $student->id)
                                ->whereIn('test_id', $testIds)
                                ->delete();
    
                            if ($studentTestIds->isNotEmpty()) {
                                DB::table('subject_comments')
                                    ->whereIn('student_test_id', $studentTestIds)
                                    ->delete();
                            }
                        }
                        
                        DB::table('subject_comments')
                            ->where('student_id', $student->id)
                            ->where('term_id', $selectedTermId)
                            ->delete();
                        
                        if ($school->type === 'Primary') {
                            DB::table('criteria_based_student_tests')
                                ->where('student_id', $student->id)
                                ->where('term_id', $selectedTermId)
                                ->delete();
                        }
                    }
                    
                    $student->classes()
                        ->wherePivot('term_id', $selectedTermId)
                        ->detach();
                    
                    $student->classes()->attach($newKlass->id, [
                        'term_id' => $selectedTermId,
                        'grade_id' => $newKlass->grade_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $movedCount++;
                    
                    Log::info('Student moved between classes', [
                        'student_id' => $studentId,
                        'from_class_id' => $oldKlass->id ?? null,
                        'to_class_id' => $newKlass->id,
                        'term_id' => $selectedTermId,
                        'user_id' => auth()->id()
                    ]);
                }
            });
            
            CacheHelper::forgetStudentsData();
            CacheHelper::forgetStudentsCount($selectedTermId);
            CacheHelper::forgetStudentsTermData();
            
            $termInfo = $selectedTermId !== TermHelper::getCurrentTerm()->id 
                ? " in the selected term (Term ID: {$selectedTermId})" 
                : "";
                
            return back()->with('message', "{$movedCount} student(s) moved successfully{$termInfo}. All test scores and optional subject allocations have been cleared.");
            
        } catch (Exception $e) {
            Log::error('Error moving students between classes', [
                'to_class_id' => $request->input('new_class_id'),
                'student_ids' => $studentIds ?? [],
                'selected_term_id' => $selectedTermId ?? null,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'An error occurred while moving the students: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $user = auth()->user();
        
        try {
            if (!is_numeric($id)) {
                Log::warning('Invalid class ID format for update', ['id' => $id]);
                return back()->with('error', 'Invalid class identifier');
            }
    
            $isPastTerm = $selectedTermId != $currentTerm->id;
            $messages = [
                'name.required' => 'The class name is required.',
                'name.string' => 'The class name must be a valid string.',
                'name.max' => 'The class name should not exceed 191 characters.',
                'user_id.required' => 'The user field is required.',
                'user_id.integer' => 'The user ID must be a valid integer.',
                'user_id.exists' => 'The selected user does not exist.',
                'term_id.required' => 'The term field is required.',
                'term_id.integer' => 'The term ID must be a valid integer.',
                'term_id.exists' => 'The selected term does not exist.',
                'grade_id.required' => 'The grade field is required.',
                'grade_id.integer' => 'The grade ID must be a valid integer.',
                'grade_id.exists' => 'The selected grade does not exist.',
                'year.required' => 'The year field is required.',
                'year.date_format' => 'The year must be in the correct format (e.g., YYYY).',
                'type.string' => 'The class type must be a valid string.',
            ];
    
            $validatedData = $request->validate([
                'name' => 'required|string|max:191',
                'user_id' => 'required|integer|exists:users,id',
                'term_id' => 'required|integer|exists:terms,id',
                'grade_id' => 'required|integer|exists:grades,id',
                'year' => 'required|date_format:Y',
                'type' => 'sometimes|nullable|string|in:Triple Award,Double Award,Single Award',
                'monitor_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('students', 'id')->where(function ($query) {
                        $query->where('gender', 'M');
                    })
                ],
                'monitress_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('students', 'id')->where(function ($query) {
                        $query->where('gender', 'F');
                    })
                ],
            ], $messages);
    
            $klass = Klass::findOrFail($id);
            $this->authorizeClassAllocationAccess($klass, 'You are not authorized to update this class.');
            if ($isPastTerm) {
                Log::info('Past term class edit attempt', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'class_id' => $id,
                    'class_name' => $klass->name,
                    'selected_term_id' => $selectedTermId,
                    'current_term_id' => $currentTerm->id,
                    'timestamp' => now()
                ]);
            }
            
            $existingClass = Klass::where('term_id', $validatedData['term_id'])
                ->where('name', $validatedData['name'])
                ->where('id', '!=', $id)
                ->first();
    
            if ($existingClass) {
                return back()->with('error', 'A class with the same name already exists in this term.');
            }
    
            DB::beginTransaction();
            try {
                $originalData = $klass->toArray();
                // Ensure uniqueness across classes in the same term for selected monitor/monitress
                if (!empty($validatedData['monitor_id'])) {
                    $taken = Klass::where('term_id', $validatedData['term_id'])
                        ->where('id', '!=', $id)
                        ->where(function($q) use ($validatedData) {
                            $q->where('monitor_id', $validatedData['monitor_id'])
                              ->orWhere('monitress_id', $validatedData['monitor_id']);
                        })
                        ->exists();
                    if ($taken) {
                        return back()->with('error', 'Selected monitor is already allocated to another class in this term.')->withInput();
                    }
                }
                if (!empty($validatedData['monitress_id'])) {
                    $taken = Klass::where('term_id', $validatedData['term_id'])
                        ->where('id', '!=', $id)
                        ->where(function($q) use ($validatedData) {
                            $q->where('monitor_id', $validatedData['monitress_id'])
                              ->orWhere('monitress_id', $validatedData['monitress_id']);
                        })
                        ->exists();
                    if ($taken) {
                        return back()->with('error', 'Selected monitress is already allocated to another class in this term.')->withInput();
                    }
                }

                $klass->update($validatedData);
                
                if ($isPastTerm) {
                    Log::info('Past term class updated', [
                        'user_id' => $user->id,
                        'user_name' => $user->full_name,
                        'class_id' => $id,
                        'original_data' => $originalData,
                        'new_data' => $validatedData,
                        'selected_term_id' => $selectedTermId,
                        'current_term_id' => $currentTerm->id,
                        'timestamp' => now()
                    ]);
                }
                
                DB::commit();
                CacheHelper::forgetKlassesForTerm($selectedTermId, $user);
                if ($originalData['term_id'] !== $validatedData['term_id']) {
                    CacheHelper::forgetKlassesForTerm($originalData['term_id'], $user);
                }
                
                $message = "Class '{$validatedData['name']}' updated successfully!";
                if ($isPastTerm) {
                    $message .= ' (Past Term Data Modified)';
                }
                return back()->with('message', $message);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            Log::error('Validation failed when updating class', [
                'class_id' => $id,
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'is_past_term' => $isPastTerm ?? false,
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error updating class', [
                'class_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'is_past_term' => $isPastTerm ?? false,
            ]);
    
            return back()->with('error', 'An error occurred while updating the class. Please try again.')->withInput();
        }
    }

    public function newSubjectKlass(Request $request){
        $this->authorize('manage-academic');
        try {
            $validatedData = $request->validate([
                'class' => 'required|string|max:255',
                'subject' => 'required|integer|exists:subjects,id',
                'teacher' => 'required|integer|exists:users,id',
                'term' => 'required|integer|exists:terms,id',
                'grade' => 'required|integer|exists:grades,id',
                'year' => 'required|integer|digits:4',
            ]);

            $exists = KlassSubject::where([
                'klass_id' => $validatedData['class'],
                'subject_id' => $validatedData['subject'],
                'term_id' => $validatedData['term'],
                'year' => $validatedData['year'],
            ])->exists();

            if ($exists) {
                Log::warning('Attempted to create duplicate class subject', [
                    'data' => $validatedData,
                    'user_id' => auth()->id()
                ]);
                return back()->with('error', 'This subject is already assigned to this class')
                    ->withInput();
            }

            DB::beginTransaction();

            try {
                $klassSubject = KlassSubject::create([
                    'klass_id' => $validatedData['class'],
                    'subject_id' => $validatedData['subject'],
                    'user_id' => $validatedData['teacher'],
                    'term_id' => $validatedData['term'],
                    'grade_id' => $validatedData['grade'],
                    'year' => $validatedData['year']
                ]);

                DB::commit();
                CacheHelper::forgetClassSubjects();

                Log::info('Class subject created successfully', [
                    'class_subject_id' => $klassSubject->id,
                    'data' => $validatedData,
                    'user_id' => auth()->id()
                ]);

                return back()->with('message', 'Class subject added successfully!');
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ValidationException $e) {
            Log::error('Validation failed when creating class subject', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error creating class subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while adding the class subject')->withInput();
        }
    }

    public function showNewKlassSubject(){
        $this->authorize('manage-academic');
        try {
            $classes = Klass::where('status', 'Current')->orderBy('name')->get();

            $teachers = User::where('status', 'Current')
                ->where('area_of_work', 'Teaching')
                ->orderBy('first_name')
                ->get();

            $subjects = CacheHelper::getSubjectMasterList();
            $terms = StudentController::terms();
            $grades = CacheHelper::getGrades();

            if ($classes->isEmpty()) {
                Log::warning('No active classes available');
                return back()->with('error', 'No active classes available');
            }

            if ($teachers->isEmpty()) {
                Log::warning('No active teachers available');
                return back()->with('error', 'No active teachers available');
            }

            if ($subjects->isEmpty()) {
                Log::warning('No subjects available');
                return back()->with('error', 'No subjects available');
            }

            if ($grades->isEmpty()) {
                Log::warning('No grades available');
                return back()->with('error', 'No grades available');
            }

            Log::info('Class subject assignment form accessed', [
                'class_count' => $classes->count(),
                'teacher_count' => $teachers->count(),
                'subject_count' => $subjects->count(),
                'user_id' => auth()->id()
            ]);

            return view('classes.new-subject-teacher', [
                'classes' => $classes,
                'teachers' => $teachers,
                'subjects' => $subjects,
                'terms' => $terms,
                'grades' => $grades
            ]);
        } catch (Exception $e) {
            Log::error('Error loading class subject assignment form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading the form');
        }
    }

    public function klassSubjectList(){
        $this->authorize('manage-academic');
        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $klassSubjects = KlassSubject::with([
                'klass',
                'subject.subject',
                'teacher',
                'grade'
            ])->where('term_id', $selectedTermId)->orderBy('grade_id')->get();
            $grades = $this->gradesWithKlassesForTerm($selectedTermId, Auth::user());

            $currentTerm = TermHelper::getCurrentTerm();
            $terms = StudentController::terms();

            if (!$currentTerm) {
                Log::error('No current term found');
                return back()->with('error', 'No active term found');
            }

            $hasGradesButNoClasses = $grades->isNotEmpty()
                && $grades->every(fn ($g) => $g->klasses->isEmpty());

            if ($grades->isEmpty()) {
                Log::warning('No active grades found for term', [
                    'term_id' => $selectedTermId,
                ]);
            }

            return view('classes.subject-teacher-list', [
                'classes' => $klassSubjects,
                'grades' => $grades,
                'currentTerm' => $currentTerm,
                'terms' => $terms,
                'hasGradesButNoClasses' => $hasGradesButNoClasses,
            ]);
        } catch (Exception $e) {
            Log::error('Error loading class subjects list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'term_id' => $selectedTermId ?? null,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading the class subjects list');
        }
    }

    public function allocateStudents($id, $termId){
        $klass = Klass::findOrFail($id);
        $this->authorizeClassAllocationAccess($klass, 'You are not authorized to allocate students to this class.');
        $class = $klass;
        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $isPastTerm = (int) $termId !== (int) TermHelper::getCurrentTerm()->id;

        $students = Student::whereHas('terms', function ($query) use ($termId, $class, $isPastTerm) {
            $query->where('student_term.term_id', $termId)
                ->where('student_term.grade_id', $class->grade_id);

            if (!$isPastTerm) {
                $query->where('student_term.status', 'Current');
            }
        })->whereDoesntHave('classes', function ($query) use ($termId) {
            $query->where('klass_student.term_id', $termId);
        })->get();

        if ($klass->term_id !== $selectedTermId) {
            return redirect()->back()->with('error', 'This class belongs to a different term. Please ensure you\'re working in the correct term.');
        }

        return view('classes.class-students-allocations', ['students' => $students, 'class' => $class]);
    }

    public function moveStudents(Request $request, $id){
        $klass = Klass::findOrFail($id);
        $this->authorizeClassAllocationAccess($klass, 'You are not authorized to allocate students to this class.');
        $studentsIds = array_filter($request->input('students', []), function ($value) {
            return $value !== '0';
        });
    
        if (empty($studentsIds)) {
            return redirect()->back()->withErrors(['error' => 'No students were selected for allocation. Please select at least one student.']);
        }
    
        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
    
        DB::transaction(function () use ($studentsIds, $id, $selectedTermId) {
            $klass = Klass::find($id);
    
            if (!$klass) {
                return redirect()->back()->with('error', 'The class does not exist!');
            }
    
            if ($klass->term_id !== $selectedTermId) {
                Log::warning('Attempted to allocate students to class from different term', [
                    'class_id' => $id,
                    'class_term_id' => $klass->term_id,
                    'selected_term_id' => $selectedTermId,
                    'user_id' => auth()->id()
                ]);
                return redirect()->back()->with('error', 'This class belongs to a different term. Please ensure you\'re working in the correct term.');
            }
    
            $selectedTerm = Term::findOrFail($selectedTermId);
            $pivotData = [];
            foreach ($studentsIds as $studentId) {
                $pivotData[$studentId] = [
                    'term_id'   => $selectedTermId,
                    'year'      => $selectedTerm->year,
                    'grade_id'  => $klass->grade_id,
                    'active'    => true,
                ];
            }
    
            $klass->students()->attach($pivotData);
    
            Log::info('Students allocated to class', [
                'class_id' => $id,
                'student_count' => count($studentsIds),
                'term_id' => $selectedTermId,
                'is_past_term' => $selectedTermId < TermHelper::getCurrentTerm()->id,
                'user_id' => auth()->id()
            ]);
        });

        Cache::forget("students_term_count");
        Cache::forget("students_term_data");
        CacheHelper::forgetStudentsTermData();
    
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $termInfo = $selectedTermId !== $currentTermId 
            ? " to the selected term (Term ID: {$selectedTermId})" 
            : "";
    
        return redirect()->back()->with('message', "Students added successfully{$termInfo}!");
    }

    public function academicAllocations($classId, $termId){
        $this->authorize('manage-academic');
        try {
            $class = Klass::where('id', $classId)->where('term_id', $termId)->firstOrFail();

            $klass_subjects = KlassSubject::where('klass_id', $class->id)
                ->where('term_id', $termId)
                ->where('grade_id', $class->grade_id)
                ->whereHas('subject.subject', function ($query) {
                    $query->where('type', 1);
                })->get()->keyBy('grade_subject_id');

            $grade_subjects = GradeSubject::where('term_id', $termId)
                ->where('grade_id', $class->grade_id)
                ->whereHas('subject', function ($query) {
                    $query->where('type', 1);
                })->get();

            $teachers = User::where('status', 'Current')->where('area_of_work', 'Teaching')->orderBy('firstname','asc')->get();
            $terms = CacheHelper::getTerms();
            $currentTerm = TermHelper::getCurrentTerm();
            $venues = CacheHelper::getAllVenues();


            return view('classes.klass-subject-grade', [
                'class' => $class,
                'grade_subjects' => $grade_subjects,
                'teachers' => $teachers,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
                'venues' => $venues,
                'klass_subjects' => $klass_subjects,
            ]);
        } catch (Exception $e) {
            Log::error("Error in academicAllocations: " . $e->getMessage());
            abort(404, "Class not found for termId: $termId, classId: $classId");
        }
    }

    public function coreSubjects(Request $request){
        $this->authorize('manage-academic');
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
        try {
            $class = $request->input('class');
            $subjects = $request->input('subjects');
            $teachers = $request->input('teachers');
            $assistants = $request->input('assistants');
            $term = $request->input('term_id');
            $grade = $request->input('grade_id');
            $venue = $request->input('venues');
            $year = $request->input('year');

            if (!$subjects || !is_array($subjects)) {
                throw new Exception('No subjects provided');
            }

            DB::transaction(function () use ($class, $subjects, $teachers, $assistants, $term, $grade, $venue, $year) {
                foreach ($subjects as $index => $subjectName) {
                    $teacherId = $teachers[$index] ?? null;
                    $teacherId = ($teacherId === '' || $teacherId === null) ? null : (int) $teacherId;

                    $assistantId = $assistants[$index] ?? null;
                    $assistantId = ($assistantId === '' || $assistantId === null) ? null : (int) $assistantId;

                    $venueId = $venue[$index] ?? null;
                    $venueId = ($venueId === '' || $venueId === null) ? null : (int) $venueId;

                    $klassSubject = KlassSubject::where([
                        'klass_id' => $class,
                        'grade_subject_id' => $subjectName,
                        'term_id' => $term,
                        'year' => $year
                    ])->lockForUpdate()->first();

                    if ($klassSubject) {
                        $klassSubject->update([
                            'user_id' => $teacherId,
                            'assistant_user_id' => $assistantId,
                            'grade_id' => $grade,
                            'venue_id' => $venueId,
                        ]);
                    } else {
                        KlassSubject::create([
                            'klass_id' => $class,
                            'grade_subject_id' => $subjectName,
                            'term_id' => $term,
                            'year' => $year,
                            'user_id' => $teacherId,
                            'assistant_user_id' => $assistantId,
                            'grade_id' => $grade,
                            'venue_id' => $venueId,
                        ]);
                    }
                }
            });

            CacheHelper::forgetClassSubjects();
            CacheHelper::forgetKlassesForTerm($currentTermId, $user);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Core subjects allocations added successfully!',
                ]);
            }

            return redirect()->back()->with('message', 'Core subjects allocations added successfully!');
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => $message,
                ], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $message);
        }
    }

    public function academicConfigurations(){
        $this->authorize('manage-academic');
        try {
            $terms = StudentController::terms();
            $currentTerm = TermHelper::getCurrentTerm();
            $grades = CacheHelper::getGrades();

            if (!$currentTerm) {
                Log::error('No current term found for academic configurations');
                return back()->with('error', 'No active term found');
            }

            if (!$grades || $grades->isEmpty()) {
                Log::warning('No grades found for academic configurations', [
                    'term_id' => $currentTerm->id
                ]);
            }

            return view('classes.academic-configuration', [
                'grades' => $grades,
                'terms' => $terms,
                'currentTerm' => $currentTerm
            ]);
        } catch (Exception $e) {
            Log::error('Error loading academic configurations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading academic configurations');
        }
    }

    public function getOverallGradingMatrix(){
        try {
            $currentTerm = TermHelper::getCurrentTerm();
            $terms = StudentController::terms();
            $grades = CacheHelper::getGrades();

            if (!$currentTerm) {
                Log::error('No current term found for grading matrix');
                return back()->with('error', 'No active term found');
            }

            if (!$grades || $grades->isEmpty()) {
                Log::warning('No grades found for grading matrix', [
                    'term_id' => $currentTerm->id
                ]);
            }

            return view('subjects.overall-grading-scale', [
                'currentTerm' => $currentTerm,
                'grades' => $grades,
                'terms' => $terms
            ]);
        } catch (Exception $e) {
            Log::error('Error loading grading matrix', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'An error occurred while loading the grading matrix');
        }
    }

    public function editOverallPoints($academicYear){
        $gradeId = Grade::where('name', $academicYear)->first()->id;
        $pointsMatrix = DB::table('overall_points_matrix')
            ->where('academic_year', $academicYear)
            ->orderByDesc('max')
            ->get();
        
        if ($pointsMatrix->isEmpty()) {
            return redirect()->back()->with('error', 'No points matrix found for the specified academic year.');
        }
        
        return view('classes.edit-overall-points', [
            'pointsMatrix' => $pointsMatrix,
            'academicYear' => $academicYear,
            'gradeId' => $gradeId
        ]);
    }

    public function updateOverallPoints(Request $request){
        $request->validate([
            'min' => 'required|array',
            'min.*' => 'required|integer|min:0',
            'max' => 'required|array',
            'max.*' => 'required|integer|min:0',
            'grade' => 'required|array',
            'grade.*' => 'required|string|max:50',
            'id' => 'required|array',
            'id.*' => 'required|integer|exists:overall_points_matrix,id',
            'academic_year' => 'required|string'
        ]);
        
        try {
            DB::beginTransaction();
            foreach ($request->id as $key => $id) {
                DB::table('overall_points_matrix')
                    ->where('id', $id)
                    ->update([
                        'min' => $request->min[$key],
                        'max' => $request->max[$key],
                        'grade' => $request->grade[$key],
                        'updated_at' => now()
                    ]);
            }
            
            if ($request->has('new_rows')) {
                for ($i = 0; $i < count($request->new_rows); $i++) {
                    if (!empty($request->input('new_min.'.$i)) && 
                        !empty($request->input('new_max.'.$i)) && 
                        !empty($request->input('new_grade.'.$i))) {
                        
                        DB::table('overall_points_matrix')->insert([
                            'academic_year' => $request->academic_year,
                            'year' => now()->year,
                            'min' => $request->input('new_min.'.$i),
                            'max' => $request->input('new_max.'.$i),
                            'grade' => $request->input('new_grade.'.$i),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
            
            DB::commit();
            return redirect()->back()->with('message', 'Points matrix updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating points matrix: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the points matrix: ' . $e->getMessage())->withInput();
        }
    }

    public function storeOverallGradingMatrix(Request $request){
        $validator = Validator::make($request->all(), [
            'term_id' => 'required|exists:terms,id',
            'grade_id' => 'required|exists:grades,id',
            'description.*' => 'nullable|string',
            'min_score.*'   => 'nullable|numeric|min:0',
            'max_score.*'   => 'nullable|numeric|min:0',
            'grade.*'       => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $termId = $request->input('term_id');
        $year = $request->input('year');
        $gradeId = $request->input('grade_id');

        DB::transaction(function () use ($request, $termId, $year, $gradeId) {
            $gradingData = array_filter($request->grade, function ($value) {
                return $value !== null;
            });

            foreach ($gradingData as $i => $grade) {
                $existingMatrix = OverallGradingMatrix::where('grade_id', $gradeId)
                    ->where('grade', $grade)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->first();

                $data = [
                    'term_id' => $termId,
                    'year' => $year,
                    'grade_id' => $gradeId,
                    'description' => $request->description[$i] ?? null,
                    'min_score' => $request->min_score[$i] ?? null,
                    'max_score' => $request->max_score[$i] ?? null,
                    'grade' => $grade,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (!$existingMatrix) {
                    OverallGradingMatrix::create($data);
                } else {
                    $existingMatrix->update($data);
                }
            }
        });
        return redirect()->back()->with('message', 'Grading matrix saved successfully.');
    }


    function editOverallGrading($gradeId){
        $overallGrading = OverallGradingMatrix::where('grade_id', $gradeId)->orderBy('min_score', 'desc')->get();
        $gradeId = $overallGrading->first()->grade_id;

        $grade = Grade::findOrFail($gradeId);
        $currentTerm = TermHelper::getCurrentTerm();
        return view('subjects.edit-overall-grading', ['overallGrading' => $overallGrading, 'currentTerm' => $currentTerm, 'grade' => $grade]);
    }

    public function showNavigation($gradeId){
        $schoolType = SchoolSetup::value('type');
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        
        if ($schoolType == 'Primary') {
            $overall = OverallGradingMatrix::where('term_id', $termId)->where('grade_id', $gradeId)->orderByDesc('max_score')->get();
            return view('classes.overall-grading-list', [
                'overall' => $overall,
                'gradeId' => $gradeId,
                'schoolType' => $schoolType
            ]);
        } else if ($schoolType == 'Junior') {
            $grade = Grade::findOrFail($gradeId);
            $overall = DB::table('overall_points_matrix')->where('academic_year', $grade->name)->orderByDesc('max')->get();

            return view('classes.overall-grading-matrix', [
                'overall' => $overall,
                'gradeId' => $gradeId,
                'schoolType' => $schoolType,
                'grade' => $grade
            ]);
        }
        return redirect()->back()->with('error', 'Unrecognized school type.');
    }

    public function getGradesForTerm(Request $request){
        $termId = $request->term_id;
        $grades = Grade::where('term_id', $termId)->get();

        if (!$this->isFullAcademicAdmin()) {
            $userId = auth()->id();
            $classTeacherGradeIds = Klass::where('term_id', $termId)
                ->where('user_id', $userId)
                ->pluck('grade_id')
                ->unique();
            $grades = $grades->whereIn('id', $classTeacherGradeIds)->values();
        }

        return response()->json($grades);
    }

    public function getGradesForTermJunior(Request $request){
        $termId = $request->term_id;
        $grades = Grade::where('term_id', $termId)
            ->where('level', SchoolSetup::LEVEL_JUNIOR)
            ->orderBy('sequence')
            ->get();

        return response()->json($grades);
    }


    public function getGradesAndKlassesForTerm(Request $request){
        $termId = $request->term_id;
        $user = Auth::user();
        $grades = $this->gradesWithKlassesForTerm($termId, $user);

        $gradesWithKlasses = $grades->map(function ($grade) {
            return [
                'id' => $grade->id,
                'name' => $grade->name,
                'klasses' => $grade->klasses->map(function ($klass) {
                    return ['id' => $klass->id, 'name' => $klass->name];
                })
            ];
        });

        return response()->json($gradesWithKlasses->values()->all());
    }

    private function gradesWithKlassesForTerm($termId, $user)
    {
        $grades = Grade::where('term_id', $termId)
            ->where('active', 1)
            ->orderBy('sequence')
            ->get();

        foreach ($grades as $grade) {
            $klassQuery = Klass::where('term_id', $termId)
                ->where('grade_id', $grade->id)
                ->orderBy('name');

            if (!$this->isFullAcademicAdmin()) {
                $klassQuery->where('user_id', $user->id);
            }

            $grade->setRelation('klasses', $klassQuery->get());
        }

        if (!$this->isFullAcademicAdmin()) {
            $grades = $grades->filter(fn ($g) => $g->klasses->isNotEmpty());
        }

        return $grades->values();
    }

    public function storeSelectedClasssGrade(Request $request)
    {
        $validated = $request->validate([
            'gradeId' => 'required|integer'
        ]);

        session(['selectedClassGradeId' => $validated['gradeId']]);
        return response()->json(['message' => 'Selection saved successfully']);
    }



    public function storeSelectedGradeList(Request $request){
        $validated = $request->validate([
            'gradeId' => 'required|integer'
        ]);
        session(['selectedGradeListId' => $validated['gradeId']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }

    public function classTeachersAnalysis(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $classes = Klass::with(['teacher'])->withCount([
                'students as male_count' => function ($query) {
                    $query->where('gender', 'M');
                },
                'students as female_count' => function ($query) {
                    $query->where('gender', 'F');
                }
            ])->where('term_id', $selectedTermId)->get()->each(function ($klass) {
                $klass->total_students = $klass->male_count + $klass->female_count;
            });

        $school_data = SchoolSetup::firstOrFail();
        return view('classes.class-teachers-analysis', [
            'school_data' => $school_data,
            'classes' => $classes
        ]);
    }

    public function teacherCommitments(Request $request){
        $currentTerm = TermHelper::getCurrentTerm();
        $school_data = SchoolSetup::first();
        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No active term found.');
        }

        $termId = $currentTerm->id;
        $teachers = User::with(['klassSubjects' => function ($query) use ($termId) {
            $query->where('term_id', $termId);
        }, 'taughtOptionalSubjects' => function ($query) use ($termId) {
            $query->where('term_id', $termId);
        }])->get();

        $teacherCommitments = [];
        foreach ($teachers as $teacher) {
            $totalStudents = 0;
            $commitments = [];

            foreach ($teacher->klassSubjects as $klassSubject) {
                $class = Klass::find($klassSubject->klass_id);
                if ($class) {
                    $studentCount = $class->currentStudents($termId, $class->year)->count();
                    $totalStudents += $studentCount;

                    $commitments[] = [
                        'type' => 'Regular',
                        'class_name' => $class->name,
                        'subject_name' => $klassSubject->subject->subject->name,
                        'student_count' => $studentCount,
                    ];
                }
            }

            foreach ($teacher->taughtOptionalSubjects as $optionalSubject) {
                $studentCount = $optionalSubject->students()->wherePivot('term_id', $termId)->count();
                $totalStudents += $studentCount;

                $commitments[] = [
                    'type' => 'Optional',
                    'class_name' => $optionalSubject->grade->name,
                    'subject_name' => $optionalSubject->name,
                    'student_count' => $studentCount,
                ];
            }

            if (!empty($commitments)) {
                $teacherCommitments[] = [
                    'teacher_name' => $teacher->fullName,
                    'commitments' => $commitments,
                    'total_students' => $totalStudents,
                ];
            }
        }
        return view('classes.teachers-commitments-analysis', compact('teacherCommitments', 'currentTerm', 'school_data'));
    }

    public function classList($classId){
        try {
            if (!$classId || $classId <= 0) {
                throw new ModelNotFoundException('Invalid class ID provided');
            }

            $selectedTermId = session('selected_term_id');
            if (!$selectedTermId) {
                $currentTerm = TermHelper::getCurrentTerm();
                if (!$currentTerm) {
                    throw new Exception('No active term found. Please set up a term first.');
                }
                $selectedTermId = $currentTerm->id;
            }

            $klass = Klass::with(['teacher'])
                ->where('term_id', $selectedTermId)
                ->where('id', $classId)
                ->firstOrFail();

            $schoolData = SchoolSetup::first();
            if (!$schoolData) {
                throw new Exception('School setup data not found. Please complete school setup.');
            }

            return view('classes.class-analysis', [
                'school_data' => $schoolData,
                'klass' => $klass
            ]);
        } catch (Exception $e) {
            Log::error('Class analysis error:', [
                'message' => $e->getMessage(),
                'class_id' => $classId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.500', [
                'exception' => $e
            ], 500);
        }
    }

    private function cleanDuplicateKlassSubjects(): void {
        $duplicates = DB::table('klass_subject')
            ->select('klass_id', 'grade_subject_id', 'term_id', 'year')
            ->whereNull('deleted_at')
            ->groupBy('klass_id', 'grade_subject_id', 'term_id', 'year')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            return;
        }

        foreach ($duplicates as $group) {
            $ids = DB::table('klass_subject')
                ->where('klass_id', $group->klass_id)
                ->where('grade_subject_id', $group->grade_subject_id)
                ->where('term_id', $group->term_id)
                ->where('year', $group->year)
                ->whereNull('deleted_at')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('id');

            $deleteIds = $ids->skip(1)->values()->toArray();

            DB::table('klass_subject')
                ->whereIn('id', $deleteIds)
                ->delete();
        }

        Log::info('Cleaned up duplicate klass_subject records', [
            'groups' => $duplicates->count(),
        ]);
    }
}
