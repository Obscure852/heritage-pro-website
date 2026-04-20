<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CacheHelper;
use App\Models\Klass;
use App\Helpers\TermHelper;
use App\Models\Department;
use App\Models\OptionalSubject;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Term;
use App\Models\Test;
use App\Models\Venue;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\StudentTermRemovalService;
use Cache;
use App\Services\SchoolModeResolver;
use Illuminate\Support\Facades\Gate;

class OptionalSubjectController extends Controller{

    function __construct(){
        $this->middleware('auth');
    }

    private function isFullAcademicAdmin(): bool {
        return auth()->user()->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Academic View', 'Assessment Admin']);
    }

    private function applyOptionalAccessScope($query, User $user)
    {
        if ($this->isFullAcademicAdmin()) {
            return $query;
        }

        return $query->where(function ($optionalQuery) use ($user) {
            $optionalQuery->where('user_id', $user->id)
                ->orWhere('assistant_user_id', $user->id)
                ->orWhereHas('teacher', function ($teacherQuery) use ($user) {
                    $teacherQuery->where('reporting_to', $user->id);
                })
                ->orWhereHas('assistantTeacher', function ($assistantQuery) use ($user) {
                    $assistantQuery->where('reporting_to', $user->id);
                });
        });
    }

    /**
     * @return array<int, string>
     */
    private function optionalLevels(): array
    {
        return app(SchoolModeResolver::class)->optionalLevels();
    }

    /**
     * @return array<int, string>
     */
    private function groupingOptionsForLevel(?string $level, int $termId): array
    {
        $resolvedLevel = app(SchoolModeResolver::class)->normalizeLevel($level);

        if ($resolvedLevel === SchoolSetup::LEVEL_JUNIOR) {
            return ['Core', 'Practicals', 'Generals', 'Other'];
        }

        if ($resolvedLevel !== SchoolSetup::LEVEL_SENIOR) {
            return [];
        }

        $departmentNames = GradeSubject::query()
            ->with('department:id,name')
            ->where('term_id', $termId)
            ->where('type', false)
            ->whereHas('grade', function ($query) use ($termId) {
                $query->where('term_id', $termId)
                    ->where('level', SchoolSetup::LEVEL_SENIOR);
            })
            ->get()
            ->pluck('department.name')
            ->filter()
            ->values();

        if ($departmentNames->isEmpty()) {
            $departmentNames = CacheHelper::getDepartments()->pluck('name')->filter()->values();
        }

        return $departmentNames->push('Other')->unique()->values()->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function groupingOptionsByLevel(int $termId): array
    {
        return [
            SchoolSetup::LEVEL_JUNIOR => $this->groupingOptionsForLevel(SchoolSetup::LEVEL_JUNIOR, $termId),
            SchoolSetup::LEVEL_SENIOR => $this->groupingOptionsForLevel(SchoolSetup::LEVEL_SENIOR, $termId),
        ];
    }

    public function index(){
        try {
            $schoolType = app(SchoolModeResolver::class)->mode();
            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->route('terms.index')->with('error', 'Please set up a current term first.');
            }

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $isOptionalTeacher = !$this->isFullAcademicAdmin();
            $user = auth()->user();
            $optionalLevels = $this->optionalLevels();

            $grades = Grade::where('term_id', $selectedTermId)
                ->whereIn('level', $optionalLevels)
                ->orderBy('sequence')
                ->get();

            if ($isOptionalTeacher) {
                $teacherGradeIds = $this->applyOptionalAccessScope(
                    OptionalSubject::query()->where('term_id', $selectedTermId),
                    $user
                )->pluck('grade_id')->unique();
                $grades = $grades->whereIn('id', $teacherGradeIds)->values();
            }

            $classes = Klass::all();
            $terms = StudentController::terms();

            if ($grades->isEmpty() && $classes->isEmpty()) {
                Log::warning('No grades or classes found for term', [
                    'term_id' => $selectedTermId,
                    'school_type' => $schoolType
                ]);
            }

            return view('optional.index', [
                'classes' => $classes,
                'grades' => $grades,
                'currentTerm' => $currentTerm,
                'terms' => $terms
            ]);
        } catch (Exception $e) {
            Log::error('Error in optional index', [
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading the page.');
        }
    }

    public function getOptionsByTermAndGrade($termId, $gradeId){
        try {
            $user = auth()->user();
            $isOptionalTeacher = !$this->isFullAcademicAdmin();

            $query = OptionalSubject::with(['teacher', 'assistantTeacher', 'students', 'grade', 'gradeSubject.subject'])
                ->where('term_id', $termId)
                ->where('grade_id', $gradeId);

            if ($isOptionalTeacher) {
                $this->applyOptionalAccessScope($query, $user);
            }

            $optionalSubjects = $query->get();
            $school_data = SchoolSetup::first();

            if ($optionalSubjects->isEmpty()) {
                Log::info('No optional subjects found at this time', [
                    'term_id' => $termId,
                    'grade_id' => $gradeId
                ]);
            }
            return view('optional.optional-class-lists', ['classes' => $optionalSubjects,'school_data' => $school_data]);
        } catch (Exception $e) {
            Log::error('Error fetching optional subjects', [
                'term_id' => $termId,
                'grade_id' => $gradeId,
                'error' => $e->getMessage()
            ]);
            return response()->view('optional.optional-class-lists', ['classes' => collect()]);
        }
    }

    public function getSubjectsByGrade($gradeId) {
        $currentTerm = TermHelper::getCurrentTerm();
        try {
            $subjects = GradeSubject::with(['subject', 'grade'])
                ->where('term_id', $currentTerm->id)
                ->where('type', 0)
                ->where('grade_id', $gradeId)
                ->get();
            return response()->json(['success' => true, 'data' => $subjects]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching classes',
                                     'error'   => $e->getMessage()], 500);
        }
    }
    
    public function getCoreSubjectsByGrade($gradeId) {
        $currentTerm = TermHelper::getCurrentTerm();
        try {
            $subjects = GradeSubject::with(['subject', 'grade'])
                ->where('term_id', $currentTerm->id)
                ->where('type', 1) // Core subjects  
                ->where('grade_id', $gradeId)
                ->get();
            return response()->json(['success' => true, 'data' => $subjects]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching classes',
                                     'error'   => $e->getMessage()], 500);
        }
    }

    public function create(){
        $this->authorize('manage-academic');
        try {
            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->back()->with('error', 'Please set up a current term first.');
            }

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $school_data = SchoolSetup::first();
            $optionalLevels = $this->optionalLevels();
            $groupingOptionsByLevel = $this->groupingOptionsByLevel($selectedTermId);

            $teachers = User::where('status', 'Current')->where('area_of_work', 'Teaching')->get();
            $venues = CacheHelper::getAllVenues();
            $grades = Grade::where('term_id', $selectedTermId)
                ->whereIn('level', $optionalLevels)
                ->orderBy('sequence')
                ->get();

            $subjects = GradeSubject::where('type', false)
                ->whereHas('grade', function ($query) use ($selectedTermId, $optionalLevels) {
                    $query->where('term_id', $selectedTermId)
                        ->whereIn('level', $optionalLevels);
                })->get();

            if ($teachers->isEmpty()) {
                Log::warning('No teachers found for optional subject creation');
            }

            if ($subjects->isEmpty()) {
                Log::warning('No subjects found', ['term_id' => $selectedTermId, 'school_type' => $school_data->type]);
            }

            return view('optional.add-new-option', [
                'teachers' => $teachers,
                'venues' => $venues,
                'subjects' => $subjects,
                'grades' => $grades,
                'groupingOptionsByLevel' => $groupingOptionsByLevel,
                'school_data'  => $school_data
            ]);
        } catch (Exception $e) {
            Log::error('Error in optional subject creation page', [
                'error' => $e->getMessage(),
                'term_id' => $selectedTermId ?? null,
                'school_type' => $schoolType ?? null
            ]);

            return redirect()->back()->with('error', 'Unable to load the create form. Please try again.');
        }
    }

    public function store(Request $request){
        $this->authorize('manage-academic');
        $currentTermId = TermHelper::getCurrentTerm()->id;
        $user = auth()->user();
        try {
            $currentTerm = TermHelper::getCurrentTerm();
    
            $messages = [
                'name.required' => 'The name of the optional subject is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name should not exceed 12 characters.',
                'grade_subject_id.required' => 'The subject is required.',
                'grade_subject_id.integer' => 'The subject ID must be a valid integer.',
                'grade_subject_id.exists' => 'The selected subject does not exist.',
                'user_id.required' => 'The user is required.',
                'user_id.integer' => 'The user ID must be a valid integer.',
                'user_id.exists' => 'The selected user does not exist.',
                'venue_id.integer' => 'The venue ID must be a valid integer.',
                'venue_id.exists' => 'The selected venue does not exist.',
                'grade_id.required' => 'The grade is required.',
                'grade_id.integer' => 'The grade ID must be a valid integer.',
                'grade_id.exists' => 'The selected grade does not exist.',
                'grouping.string' => 'The grouping must be a valid string.',
                'grouping.max' => 'The grouping should not exceed 191 characters.',
            ];
    
            $validatedData = $request->validate([
                'name' => 'required|string|max:12',
                'grade_subject_id' => 'required|integer|exists:grade_subject,id',
                'user_id' => 'required|integer|exists:users,id',
                'assistant_user_id' => 'nullable|integer|exists:users,id',
                'venue_id' => 'nullable|integer|exists:venues,id',
                'grade_id' => 'required|integer|exists:grades,id',
                'grouping' => 'nullable|string|max:191',
            ], $messages);
    
            $validatedData['name'] = strtoupper(substr($validatedData['name'], 0, 12));
            $validatedData['term_id'] = $currentTerm->id;
            $validatedData['active'] = 1;
    
            $exists = OptionalSubject::where('name', $validatedData['name'])
                ->where('grade_id', $validatedData['grade_id'])
                ->where('term_id', $currentTerm->id)
                ->where('grade_subject_id', $validatedData['grade_subject_id'])
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This optional subject already exists for the selected grade and term.');
            }
    
            OptionalSubject::create($validatedData);
            CacheHelper::forgetKlassesForTerm($currentTermId,$user);
            CacheHelper::forgetClassSubjects(null, $currentTermId);
            return redirect()->back()->with('message', 'Optional Subject created successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (Exception $e) {
            Log::error('Failed to create optional subject', [
                'error' => $e->getMessage(),
                'data' => $request->except('_token')
            ]);
    
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create optional subject. Please try again.');
        }
    }

    public function checkScores($id){
        $option = OptionalSubject::findOrFail($id);
        $test = Test::where('grade_subject_id', $option->grade_subject_id)
            ->where('term_id', $option->term_id)
            ->first();

        $hasScores = $test ? $test->studentTests()->whereNotNull('score')->exists() : false;
        return response()->json(['hasScores' => $hasScores]);
    }

    public function deleteOption($id){
        $this->authorize('manage-academic');
        try {
            $option = OptionalSubject::findOrFail($id);
            if ($option->students()->exists()) {
                Log::info('Attempted to delete optional subject with enrolled students', [
                    'option_id' => $id,
                    'option_name' => $option->name
                ]);
                return redirect()->route('optional.index')->with('error', 'Cannot delete this optional subject because students are enrolled in it.');
            }

            $option->forceDelete();
            return redirect()->back()->with('message', 'Optional subject deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to delete optional subject', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('optional.index')
                ->with('error', 'Unable to delete the optional subject. Please try again.');
        }
    }

    public function allocateStudents($id){
        $selectedTermId = (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTermId  = TermHelper::getCurrentTerm()->id;
        $isPastTerm     = $selectedTermId < $currentTermId;

        try {
            $optionalSubject = OptionalSubject::with(['gradeSubject.subject'])
                ->findOrFail($id);

            if (!Gate::allows('optional-teacher', $optionalSubject)) {
                abort(403, 'You are not authorized to allocate students to this optional class.');
            }

            if ((int) $optionalSubject->term_id !== $selectedTermId) {
                Log::warning('Attempted to access student allocation for optional subject from different term', [
                    'optional_subject_id' => $id,
                    'subject_term_id'     => $optionalSubject->term_id,
                    'selected_term_id'    => $selectedTermId,
                    'user_id'             => auth()->id()
                ]);

                return redirect()->back()->with(
                    'error',
                    'This optional subject belongs to a different term. Please ensure you\'re working in the correct term.'
                );
            }

            $gradeId        = (int) $optionalSubject->grade_id;
            $gradeSubjectId = (int) $optionalSubject->grade_subject_id;

            $students = Student::query()
                ->with('type')
                ->whereHas('terms', function ($query) use ($selectedTermId, $gradeId, $isPastTerm) {
                    $query->where('student_term.term_id', $selectedTermId)
                        ->where('student_term.grade_id', $gradeId);

                    if (!$isPastTerm) {
                        $query->where('student_term.status', 'Current');
                    }
                })->whereHas('classes', function ($query) use ($selectedTermId) {
                    $query->where('klass_student.term_id', $selectedTermId);
                })->whereDoesntHave('optionalSubjects', function ($query) use ($optionalSubject, $selectedTermId) {
                    $query->where('optional_subjects.id', $optionalSubject->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId);
                })->whereDoesntHave('optionalSubjects', function ($query) use ($gradeSubjectId, $selectedTermId) {
                    $query->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('optional_subjects.grade_subject_id', $gradeSubjectId);
                })->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            Log::info('Optional subject allocation view accessed', [
                'optional_subject_id'     => $id,
                'term_id'                 => $selectedTermId,
                'is_past_term'            => $isPastTerm,
                'eligible_students_count' => $students->count(),
                'user_id'                 => auth()->id()
            ]);

            return view('optional.options-students-allocations', [
                'students'       => $students,
                'class'          => $optionalSubject,
                'selectedTermId' => $selectedTermId,
                'isPastTerm'     => $isPastTerm,
                'termInfo'       => [
                    'selected_term_id' => $selectedTermId,
                    'current_term_id'  => $currentTermId,
                    'is_past_term'     => $isPastTerm
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading optional subject allocation view', [
                'optional_subject_id' => $id,
                'selected_term_id'    => $selectedTermId,
                'error'               => $e->getMessage(),
                'trace'               => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading the allocation view: ' . $e->getMessage());
        }
    }
    public function moveStudents(Request $request, $id){
        $optionalSubject = OptionalSubject::findOrFail($id);
        if (!Gate::allows('optional-teacher', $optionalSubject)) {
            abort(403, 'You are not authorized to allocate students to this optional class.');
        }

        $studentsIds = array_filter($request->input('students', []), function ($value) {
            return $value !== '0';
        });

        if (empty($studentsIds)) {
            return redirect()->back()->withErrors(['error' => 'No student(s) were selected for allocation. Please select at least one student.']);
        }

        try {
            $currentTerm = TermHelper::getCurrentTerm();
            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $isPastTerm = $selectedTermId != $currentTerm->id;
            
    
            DB::transaction(function () use ($studentsIds, $id, $selectedTermId, $isPastTerm) {
                $optionalSubject = OptionalSubject::find($id);
    
                if (!$optionalSubject) {
                    throw new Exception('Optional subject not found.');
                }
    
                if ($optionalSubject->term_id != $selectedTermId) {
                    throw new Exception('This optional subject does not exist in the selected term.');
                }
    
                $successfulAllocations = 0;
                $errors = [];
    
                foreach ($studentsIds as $studentId) {
                    $student = Student::find($studentId);
                    if (!$student) {
                        $errors[] = "Student with ID {$studentId} not found.";
                        continue;
                    }
    
                    $studentInTerm = $student->terms()->wherePivot('term_id', $selectedTermId)->first();
                    if (!$studentInTerm) {
                        $errors[] = "Student {$student->fullName} is not enrolled in the selected term.";
                        continue;
                    }
    
                    $studentClass = $student->classes()->wherePivot('term_id', $selectedTermId)->first();
                    if (!$studentClass) {
                        $errors[] = "Student {$student->fullName} is not assigned to any class in the selected term.";
                        continue;
                    }
    
                    $existingAllocation = $optionalSubject->students()
                        ->wherePivot('student_id', $studentId)
                        ->wherePivot('term_id', $selectedTermId)
                        ->exists();
    
                    if ($existingAllocation) {
                        $errors[] = "Student {$student->fullName} is already allocated to this optional subject.";
                        continue;
                    }
    
                    $optionalSubject->students()->attach($studentId, [
                        'term_id' => $selectedTermId,
                        'klass_id' => $studentClass->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
    
                    $successfulAllocations++;
                }
    
                if ($successfulAllocations === 0 && !empty($errors)) {
                    throw new Exception('No students could be allocated: ' . implode(' ', $errors));
                }
            });
    

            CacheHelper::forgetOptionalSubjectAllocations($id, $selectedTermId);
            CacheHelper::forgetStudentsData();
            CacheHelper::forgetStudentsTermData();
    
            $message = "Successfully allocated student(s) to the optional subject.";
            if ($isPastTerm) {
                $message .= " (Past term allocation completed)";
            }
            return redirect()->back()->with('message', $message);
    
        } catch (Exception $e) {
            Log::error('Error in optional subject student allocation', [
                'user_id' => auth()->id(),
                'optional_subject_id' => $id,
                'student_ids' => $studentsIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()->back()->with('error', 'An error occurred while allocating students: ' . $e->getMessage());
        }
    }

    public function allocatedStudents(int $id){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        try {
            if ($id <= 0) {
                throw new NotFoundHttpException('Invalid optional subject ID provided');
            }

            $optionalSubject = CacheHelper::getOptionalSubjectAllocations($id, $selectedTermId);

            if (!Gate::allows('optional-teacher', $optionalSubject)) {
                abort(403, 'You are not authorized to view this optional class.');
            }

            return view('optional.allocated-students', [
                'class' => $optionalSubject,
                'studentCount' => $optionalSubject->students->count(),
            ]);

        } catch (ModelNotFoundException $e) {
            logger()->error('Optional subject not found', [
                'id' => $id,
                'term_id' => $selectedTermId,
                'exception' => $e->getMessage()
            ]);
            return response()->view('errors.404', [], 404);
        } catch (\Exception $e) {
            logger()->error('Error in allocatedStudents method', [
                'id' => $id,
                'term_id' => $selectedTermId,
                'exception' => $e->getMessage()
            ]);
            return response()->view('errors.500', [], 500);
        }
    }

    public function deleteMultipleStudents(Request $request, $classId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        try {
            DB::beginTransaction();
            $optionalSubject = OptionalSubject::findOrFail($classId);

            if (!Gate::allows('optional-teacher', $optionalSubject)) {
                abort(403, 'You are not authorized to modify this optional class.');
            }
            $studentIds = $request->input('students', []);
            
            if ($optionalSubject->term_id !== $selectedTermId) {
                Log::warning('Attempted to delete students from optional subject in different term', [
                    'optional_subject_id' => $classId,
                    'subject_term_id' => $optionalSubject->term_id,
                    'selected_term_id' => $selectedTermId,
                    'user_id' => auth()->id()
                ]);
                return redirect()->back()->with('error', 'This optional subject belongs to a different term. Please ensure you\'re working in the correct term.');
            }
            
            if (empty($studentIds)) {
                throw new \Exception('No students selected.');
            }
            
            $removalService = new StudentTermRemovalService(); 
            foreach ($studentIds as $studentId) {
                $student = Student::findOrFail($studentId);
                $removalService->removeStudentFromOptionalSubject($student, $optionalSubject->id, $selectedTermId);
                
                Log::info('Student removed from optional subject', [
                    'student_id' => $studentId,
                    'optional_subject_id' => $optionalSubject->id,
                    'term_id' => $selectedTermId,
                    'user_id' => auth()->id()
                ]);
            }
            
            DB::commit();
            CacheHelper::forgetOptionalSubjectAllocations($classId, $selectedTermId);
            $currentTermId = TermHelper::getCurrentTerm()->id;
            $termInfo = $selectedTermId !== $currentTermId ? " from the selected term (Term ID: {$selectedTermId})" : "";
            
            return redirect()->back()->with('message', "Selected students removed successfully{$termInfo} with all associated data!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing students from optional subject', [
                'optional_subject_id' => $classId,
                'selected_term_id' => $selectedTermId,
                'student_ids' => $studentIds ?? [],
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error removing students: ' . $e->getMessage());
        }
    }

    public function moveMultipleStudents(Request $request, $classId){
        $request->validate([
            'new_class_id' => 'required|exists:optional_subjects,id',
            'student_ids' => 'required'
        ]);
    
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        try {
            $studentIds = json_decode($request->input('student_ids'), true);
            if (!is_array($studentIds) || empty($studentIds)) {
                return back()->with('error', 'No students selected or invalid data.');
            }
    
            $fromOptionalSubject = OptionalSubject::findOrFail($classId);
            $toOptionalSubject = OptionalSubject::findOrFail($request->input('new_class_id'));

            abort_unless(
                Gate::allows('optional-teacher', $fromOptionalSubject),
                403,
                'You are not authorized to move students from this optional class.'
            );
            abort_unless(
                Gate::allows('optional-teacher', $toOptionalSubject),
                403,
                'You are not authorized to move students to the selected optional class.'
            );
            
            if ($fromOptionalSubject->term_id !== $selectedTermId) {
                return back()->with('error', 'The source optional class belongs to a different term. Please ensure you\'re working in the correct term.');
            }
            
            if ($toOptionalSubject->term_id !== $selectedTermId) {
                return back()->with('error', 'The destination optional class belongs to a different term. Please select a class from the currently selected term.');
            }
            
            if ($fromOptionalSubject->grade_id !== $toOptionalSubject->grade_id) {
                return back()->with('error', 'Cannot move students between optional classes from different grades.');
            }
    
            $movedCount = 0;
            $removalService = new StudentTermRemovalService();
    
            DB::transaction(function () use ($studentIds, $fromOptionalSubject, $toOptionalSubject, $selectedTermId, $removalService, &$movedCount) {
                foreach ($studentIds as $studentId) {
                    $student = Student::findOrFail($studentId);
                    
                    $removalService->removeStudentFromOptionalSubject($student, $fromOptionalSubject->id, $selectedTermId);
                    $student->optionalSubjects()->attach($toOptionalSubject->id, [
                        'term_id' => $selectedTermId,
                        'klass_id' => $student->currentClass()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $movedCount++;
                    
                    Log::info('Student moved between optional subjects', [
                        'student_id' => $studentId,
                        'from_optional_subject_id' => $fromOptionalSubject->id,
                        'to_optional_subject_id' => $toOptionalSubject->id,
                        'term_id' => $selectedTermId,
                        'user_id' => auth()->id()
                    ]);
                }
            });
    
            CacheHelper::forgetOptionalSubjectAllocations($fromOptionalSubject->id, $selectedTermId);
            CacheHelper::forgetOptionalSubjectAllocations($toOptionalSubject->id, $selectedTermId);
            return back()->with('message', "{$movedCount} student(s) moved successfully from {$fromOptionalSubject->name} to {$toOptionalSubject->name}. All previous optional subject data has been cleared.");
    
        } catch (\Exception $e) {
            Log::error('Error moving students between optional subjects', [
                'from_class_id' => $classId,
                'to_class_id' => $request->input('new_class_id'),
                'selected_term_id' => $selectedTermId,
                'student_ids' => $studentIds ?? [],
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'An error occurred while moving the students: ' . $e->getMessage());
        }
    }

    public function getOptionalSubjectsByGrade($gradeId){
        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $user = auth()->user();

            $query = OptionalSubject::query()
                ->where('grade_id', $gradeId)
                ->where('active', 1)
                ->where('term_id', $selectedTermId);

            $this->applyOptionalAccessScope($query, $user);

            $optionalSubjects = $query->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'data'    => $optionalSubjects
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching optional subjects',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getGradesForTermOptional(Request $request)
    {
        $termId = (int) $request->term_id;
        $user = auth()->user();
        $isOptionalTeacher = !$this->isFullAcademicAdmin();
        $optionalLevels = $this->optionalLevels();

        $grades = Grade::where('term_id', $termId)
            ->whereIn('level', $optionalLevels)
            ->orderBy('sequence')
            ->get();

        if ($isOptionalTeacher) {
            $teacherGradeIds = $this->applyOptionalAccessScope(
                OptionalSubject::query()->where('term_id', $termId),
                $user
            )
                ->pluck('grade_id')
                ->unique();

            $grades = $grades->whereIn('id', $teacherGradeIds)->values();
        }

        return response()->json($grades);
    }


    public function optionDeleteStudent($optionalSubjectId, $studentId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        try {
            $optionalSubject = OptionalSubject::findOrFail($optionalSubjectId);

            if (!Gate::allows('optional-teacher', $optionalSubject)) {
                abort(403, 'You are not authorized to modify this optional class.');
            }

            if (!$optionalSubject->students()->where('student_id', $studentId)->exists()) {
                return redirect()->back()->with('error', 'Student not found in this subject.');
            }

            DB::beginTransaction();
            try {
                $optionalSubject->students()->detach($studentId);
                DB::commit();
                return redirect()->back()->with('message', 'Student removed successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error removing student from optional subject', [
                    'optional_subject_id' => $optionalSubjectId,
                    'student_id' => $studentId,
                    'error' => $e->getMessage()
                ]);
                CacheHelper::forgetOptionalSubjectAllocations($optionalSubjectId,$selectedTermId);
                return redirect()->back()->with('error', 'Failed to remove student from subject.');
            }
        } catch (Exception $e) {
            Log::error('Error removing student from optional subject', [
                'optional_subject_id' => $optionalSubjectId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to find the subject.');
        }
    }

    public function updateOption(Request $request, $id){
        $currentTerm = TermHelper::getCurrentTerm();
        $user = auth()->user();
        
        try {
            $optionalSubject = OptionalSubject::findOrFail($id);
            abort_unless(
                Gate::allows('optional-teacher', $optionalSubject),
                403,
                'You are not authorized to update this optional class.'
            );
            $originalTermId = $optionalSubject->term_id;
            $originalGradeId = $optionalSubject->grade_id;
            $originalGradeSubjectId = $optionalSubject->grade_subject_id;
            
            $isPastTerm = $originalTermId != $currentTerm->id;
            if ($isPastTerm) {
                Log::info('Past term optional subject edit attempt', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'optional_subject_id' => $id,
                    'optional_subject_name' => $optionalSubject->name,
                    'original_term_id' => $originalTermId,
                    'current_term_id' => $currentTerm->id,
                    'timestamp' => now()
                ]);
            }
            
            $messages = [
                'name.required' => 'The name of the optional subject is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name must not exceed 12 characters.',
                'user_id.required' => 'The user field is required.',
                'user_id.integer' => 'The user ID must be a valid integer.',
                'venue_id.integer' => 'The venue ID must be a valid integer.',
                'grouping.string' => 'The grouping must be a valid string.',
                'grouping.max' => 'The grouping must not exceed 191 characters.',
            ];

            $validatedData = $request->validate([
                'name' => 'required|string|max:12',
                'user_id' => 'required|integer|exists:users,id',
                'assistant_user_id' => 'nullable|integer|exists:users,id',
                'venue_id' => 'nullable|integer|exists:venues,id',
                'grouping' => 'nullable|string|max:191',
            ], $messages);
            
            $validatedData['name'] = strtoupper(substr($validatedData['name'], 0, 12));
            
            $validatedData['term_id'] = $originalTermId;
            $validatedData['grade_id'] = $originalGradeId;
            $validatedData['grade_subject_id'] = $originalGradeSubjectId;
            
            $existingSubject = OptionalSubject::where('id', '!=', $id)
                ->where('name', $validatedData['name'])
                ->where('grade_id', $originalGradeId)
                ->where('term_id', $originalTermId)
                ->where('grade_subject_id', $originalGradeSubjectId)
                ->exists();

            if ($existingSubject) {
                return redirect()->back()->with('error', 'An optional subject with the same name already exists in this grade and term.');
            }

            $originalData = $optionalSubject->toArray();
            if ($request->has('term_id') && $request->term_id != $originalTermId) {
                Log::warning('Attempted to change term_id for optional subject', [
                    'optional_subject_id' => $id,
                    'original_term_id' => $originalTermId,
                    'attempted_term_id' => $request->term_id,
                    'user_id' => $user->id,
                    'user_name' => $user->full_name
                ]);
            }
            
            $optionalSubject->update($validatedData);
            if ($isPastTerm) {
                Log::info('Past term optional subject updated', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'optional_subject_id' => $id,
                    'original_data' => $originalData,
                    'new_data' => $validatedData,
                    'preserved_term_id' => $originalTermId,
                    'preserved_grade_id' => $originalGradeId,
                    'preserved_grade_subject_id' => $originalGradeSubjectId,
                    'current_term_id' => $currentTerm->id,
                    'timestamp' => now()
                ]);
            }
            
            // Clear cache for the ORIGINAL term, not session term
            CacheHelper::forgetKlassesForTerm($originalTermId, $user);
            CacheHelper::forgetClassSubjects(null, $originalTermId);
            Cache::forget('optional_subjects_term_' . $originalTermId);
            
            $message = 'Optional Subject updated successfully!';
            if ($isPastTerm) {
                $termInfo = Term::find($originalTermId);
                $message .= " (Term: {$termInfo->term} {$termInfo->year})";
            }
            
            return redirect()->back()->with('message', $message);

        } catch (ValidationException $e) {
            Log::error('Validation error while updating optional subject', [
                'errors' => $e->errors(),
                'optional_subject_id' => $id,
                'user_id' => $user->id,
                'is_past_term' => $isPastTerm ?? false,
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error updating optional subject', [
                'error' => $e->getMessage(),
                'optional_subject_id' => $id,
                'user_id' => $user->id,
                'is_past_term' => $isPastTerm ?? false,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Unable to update the optional subject. Please try again later.');
        }
    }

    public function editOption($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $option = OptionalSubject::with(['grade', 'gradeSubject.subject', 'teacher', 'assistantTeacher', 'term'])->find($id);
        
        if (!$option) {
            return redirect()->back()->with('error', 'Optional subject not found.');
        }

        abort_unless(
            Gate::allows('optional-teacher', $option),
            403,
            'You are not authorized to edit this optional class.'
        );
        
        $optionTermId = $option->term_id;
        $optionYear = $option->term->year;
        
        Log::info("Editing optional subject ID {$id} from term_id {$optionTermId}");
        
        if ($option) {
            $option->display_name = strtoupper(substr($option->name, 0, 12));
        }
        
        $isPastTerm = $optionTermId != $currentTerm->id;
        $options = GradeSubject::where('type', false)
                ->where('term_id', $optionTermId) 
                ->where('year', $optionYear)
                ->whereHas('grade', function ($query) use ($optionTermId) {
                    $query->where('term_id', $optionTermId);
                })->get();
        
        $grades = Grade::where('term_id', $optionTermId)->where('year', $optionYear)->orderBy('sequence')->get();
        Log::info("Available grades for term {$optionTermId}: " . $grades->pluck('name')->implode(', '));
        Log::info("Available grade_subjects for term {$optionTermId}: " . $options->count());
        
        $teachers = User::where('status', 'Current')->where('area_of_work', 'teaching')->get();
        $venues = CacheHelper::getAllVenues();
        $school_data = SchoolSetup::first();
        $groupingOptionsByLevel = $this->groupingOptionsByLevel($optionTermId);
        
        $selectedTerm = Term::find($optionTermId);
        
        return view('optional.edit-option', [
            'option' => $option, 
            'subjects' => $options, 
            'teachers' => $teachers, 
            'grades' => $grades, 
            'venues' => $venues,
            'school_data' => $school_data,
            'groupingOptionsByLevel' => $groupingOptionsByLevel,
            'isPastTerm' => $isPastTerm,
            'selectedTerm' => $selectedTerm,
            'currentTerm' => $currentTerm,
            'optionTermId' => $optionTermId
        ]);
    }
    
    public function storeOptionalSelectedGrade(Request $request){
        $validated = $request->validate([
            'gradeOption' => 'required|integer'
        ]);
        session(['selectedOptionalGradeId' => $validated['gradeOption']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }

    public function optionalSubjectAnalysis($gradeId){
        $this->authorize('manage-academic');
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $term = Term::findOrFail($termId);

        $school_data = SchoolSetup::first();
        $grade = Grade::findOrFail($gradeId);

        $optionalSubjects = OptionalSubject::with([
            'grade',
            'teacher',
            'venue',
            'students',
            'gradeSubject.subject',
            'gradeSubject.department'
        ])->where('term_id', $term->id)->where('grade_id', $grade->id)->get();;

        $teachersQuery = User::whereHas('taughtOptionalSubjects', function ($query) use ($term, $grade) {
            $query->where('term_id', $term->id);
            if ($grade) {
                $query->where('grade_id', $grade->id);
            }
        })->with(['taughtOptionalSubjects' => function ($query) use ($term, $grade) {
            $query->where('term_id', $term->id);
            if ($grade) {
                $query->where('grade_id', $grade->id);
            }
            $query->with('students', 'gradeSubject.department');
        }]);

        $teachers = $teachersQuery->get()->map(function ($teacher) {
            $teacher->department = $teacher->taughtOptionalSubjects
                ->first()
                ->gradeSubject
                ->department
                ->name ?? 'Not Assigned';
            return $teacher;
        });

        $venuesQuery = Venue::whereHas('optionalSubjects', function ($query) use ($term, $grade) {
            $query->where('term_id', $term->id);
            if ($grade) {
                $query->where('grade_id', $grade->id);
            }
        })->with(['optionalSubjects' => function ($query) use ($term, $grade) {
            $query->where('term_id', $term->id);
            if ($grade) {
                $query->where('grade_id', $grade->id);
            }
            $query->with('students');
        }]);

        $venues = $venuesQuery->get()->map(function ($venue) {
            $venue->current_utilization = $venue->utilization_percentage;
            $venue->is_over_capacity = $venue->is_over_capacity;
            return $venue;
        });

        $statistics = [
            'total_subjects' => $optionalSubjects->count(),
            'total_students' => $optionalSubjects->sum(function ($subject) {
                return $subject->students->count();
            }),
            'average_class_size' => $optionalSubjects->isNotEmpty() ?
                round($optionalSubjects->avg(function ($subject) {
                    return $subject->students->count();
                }), 1) : 0,
            'subjects_by_grade' => $optionalSubjects->groupBy('grade_id')
                ->map(function ($subjects) {
                    return $subjects->count();
                }),
            'venue_statistics' => [
                'total_venues' => $venues->count(),
                'over_capacity_venues' => $venues->where('is_over_capacity', true)->count(),
                'average_utilization' => $venues->isNotEmpty() ?
                    round($venues->avg('current_utilization'), 1) : 0,
                'highest_utilization' => $venues->max('current_utilization') ?? 0,
            ]
        ];

        $departmentDistribution = $optionalSubjects
            ->groupBy(function ($subject) {
                return $subject->gradeSubject->department->name ?? 'Unassigned';
            })->map(function ($subjects) {
                return [
                    'count' => $subjects->count(),
                    'students' => $subjects->sum(function ($subject) {
                        return $subject->students->count();
                    })
                ];
            });

        return view('optional.optional-subjects-summary', compact(
            'optionalSubjects',
            'teachers',
            'venues',
            'term',
            'school_data',
            'statistics',
            'departmentDistribution',
            'grade'
        ));
    }

    public function optionalSubjectGroupedByNameReport($gradeId){
        $this->authorize('manage-academic');
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $term = Term::findOrFail($termId);

        $grade = Grade::findOrFail($gradeId);
        $school_data = SchoolSetup::first();

        $groupedSubjects = OptionalSubject::with([
            'grade:id,name',
            'teacher:id,firstname,lastname',
            'assistantTeacher:id,firstname,lastname',
            'students' => function ($query) {
                $query->select([
                    'students.id',
                    'first_name',
                    'last_name',
                    'date_of_birth',
                    'gender',
                    'nationality',
                    'id_number'
                ])
                    ->orderBy('first_name')
                    ->orderBy('last_name');
            },
            'gradeSubject:id,subject_id'
        ])->where('term_id', $term->id)->where('grade_id', $grade->id)->get()->groupBy('name');

        $statistics = [
            'total_subjects' => $groupedSubjects->count(),
            'total_students' => $groupedSubjects->sum(function ($subjects) {
                return $subjects->sum(function ($subject) {
                    return $subject->students->count();
                });
            }),
            'average_students_per_subject' => $groupedSubjects->count() > 0 ?
                round($groupedSubjects->sum(function ($subjects) {
                    return $subjects->sum(function ($subject) {
                        return $subject->students->count();
                    });
                }) / $groupedSubjects->count(), 1) : 0,
            'subject_distribution' => $groupedSubjects->map(function ($subjects) {
                return [
                    'count' => $subjects->count(),
                    'students' => $subjects->sum(function ($subject) {
                        return $subject->students->count();
                    })
                ];
            })
        ];

        return view('optional.optional-grouped-class-lists', compact(
            'groupedSubjects',
            'term',
            'school_data',
            'statistics',
            'grade'
        ));
    }
}
