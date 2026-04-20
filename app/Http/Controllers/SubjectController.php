<?php

namespace App\Http\Controllers;

use App\Helpers\AssessmentHelper;
use App\Helpers\CacheHelper;
use App\Helpers\SyllabusStructureHelper;
use App\Helpers\TermHelper;
use App\Models\Component;
use App\Models\CriteriaBasedStudentTest;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Subject;
use App\Models\Term;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\GradeOption;
use App\Models\GradeOptionSet;
use App\Models\GradingScale;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Schemes\Syllabus;
use App\Models\StudentTest;
use App\Models\Test;
use App\Services\Schemes\SyllabusSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Throwable;

class SubjectController extends Controller{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(){
        try {
            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->back()->with('error', 'No current term found. Please set up the current term.');
            }

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $grades = Grade::where('term_id', $selectedTermId)
                ->orderBy('sequence')
                ->get();

            if ($grades->isEmpty()) {
                return redirect()->back()->with('error', 'No grades found for the selected term. Please add grades first.');
            }

            $terms = StudentController::terms();
            if ($terms->isEmpty()) {
                return redirect()->back()->with('error', 'No terms found. Please add terms.');
            }

            $school_type = SchoolSetup::first();
            if (!$school_type) {
                return redirect()->back()->with('error', 'School setup not found. Please configure school settings.');
            }

            return view('subjects.index', [
                'grades' => $grades,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
                'school_type' => $school_type
            ]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@index: " . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function subjectByGrade($gradeId){
        try {
            $school_data = SchoolSetup::first();
            $selectedTermId = session('selected_term_id');

            if (!$selectedTermId) {
                $selectedTermId = optional(TermHelper::getCurrentTerm())->id;
            }

            if (!$selectedTermId) {
                Log::warning('Subject allocations requested without a selected term', [
                    'grade_id' => $gradeId,
                    'user_id' => auth()->id(),
                ]);

                return view('subjects.subject-grade', [
                    'subjects' => collect(),
                    'school_data' => $school_data,
                    'errorMessage' => 'Current term not set. Please configure the current term first.',
                ]);
            }
            
            $subjects = GradeSubject::with(['subject', 'department', 'grade', 'tests', 'criteriaBasedTests', 'components', 'gradeOptionSets'])
                ->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->orderBy('active', 'desc')
                ->orderBy('sequence', 'asc')
                ->get();

            if ($subjects->isEmpty()) {
                Log::info('No subject allocations found for selected grade and term', [
                    'grade_id' => $gradeId,
                    'term_id' => $selectedTermId,
                    'user_id' => auth()->id(),
                ]);
            }

            return view('subjects.subject-grade', [
                'subjects' => $subjects,
                'school_data' => $school_data,
            ]);
        } catch (Exception $e) {
            Log::error("Error in SubjectsController@subjectByGrade: " . $e->getMessage());
            return response()->view('subjects.subject-grade', [
                'subjects' => collect(),
                'school_data' => SchoolSetup::first(),
                'errorMessage' => 'An unexpected error occurred while loading subjects. Please try again later.',
            ], 200);
        }
    }

    public function syllabusPreview(GradeSubject $gradeSubject, SyllabusSourceService $syllabusSourceService): JsonResponse
    {
        try {
            $gradeSubject->loadMissing(['subject', 'grade']);

            if (!$gradeSubject->subject || !$gradeSubject->grade) {
                return response()->json([
                    'message' => 'The selected subject syllabus could not be resolved.',
                ], 404);
            }

            $subject = $gradeSubject->subject;
            $grade = $gradeSubject->grade;

            $syllabus = Syllabus::query()
                ->where('subject_id', $gradeSubject->subject_id)
                ->where('level', $subject->level ?? $grade->level)
                ->where('is_active', true)
                ->get()
                ->first(function (Syllabus $candidate) use ($grade): bool {
                    return in_array($grade->name, $candidate->grades ?? [], true);
                });

            $structure = $syllabusSourceService->getDisplayStructure($syllabus);
            $sourceUrl = $syllabus?->source_url ?: $subject->syllabus_url;

            if (!SyllabusStructureHelper::hasSections($structure) && filled($sourceUrl)) {
                $structure = $this->fetchRemoteSyllabusStructure($sourceUrl);
            }

            if (!SyllabusStructureHelper::hasSections($structure)) {
                return response()->json([
                    'message' => 'No readable syllabus is available for this subject right now.',
                ], 404);
            }

            return response()->json([
                'title' => $structure['title'] ?? ($subject->name . ' Syllabus'),
                'subject_name' => $subject->name,
                'grade_name' => $grade->name,
                'source_url' => $sourceUrl,
                'structure' => $structure,
            ]);
        } catch (Throwable $e) {
            Log::error('Error in SubjectController@syllabusPreview: ' . $e->getMessage(), [
                'grade_subject_id' => $gradeSubject->id,
            ]);

            return response()->json([
                'message' => 'Unable to load the syllabus right now. Please try again later.',
            ], 500);
        }
    }

    public function create(){
        try {
            $selectedTermId = session('selected_term_id');
            if (!$selectedTermId) {
                $currentTerm = TermHelper::getCurrentTerm();
                if (!$currentTerm) {
                    return back()->with('error', 'Current term not set. Please configure the current term.');
                }
                $selectedTermId = $currentTerm->id; 
            }


            $terms = StudentController::terms();
            if ($terms->isEmpty()) { 
                return back()->with('error', 'No terms found. Please add terms.');
            }

            $grades = CacheHelper::getGrades();
            if ($grades->isEmpty()) {
                return back()->with('error', 'No active grades found for this term. Please add grades.');
            }

            $subjects = CacheHelper::getSubjectMasterList();
            if ($subjects->isEmpty()) {
                return back()->with('error', 'No subjects found. Please add subjects.');
            }

            $departments = CacheHelper::getDepartments();
            if ($departments->isEmpty()) {
                return back()->with('error', 'No departments found. Please add departments.');
            }

            return view('classes.add-subject', [
                'subjects' => $subjects,
                'departments' => $departments,
                'terms' => $terms,
                'grades' => $grades
            ]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@create: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function createComponent($subjectGradeId){
        try {
            $subject = GradeSubject::find($subjectGradeId);

            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            return view('classes.add-component', ['subject' => $subject]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@createComponent: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function viewComponents($subjectGradeId){
        try {
            $subject = GradeSubject::find($subjectGradeId);

            if (!$subject) {
                return back()->with('error', 'Subject not found.'); 
            }

            return view('classes.components-view', ['subject' => $subject]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@viewComponents: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }


    public function addComponent(Request $request){
        $request->validate([
            'termId' => 'required|exists:terms,id',
            'gradeSubjectId' => 'required|exists:grade_subject,id',
            'gradeId' => 'required|exists:grades,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        DB::beginTransaction();
        try {
            $component = new Component();
            $component->term_id = $request->input('termId');
            $component->grade_subject_id = $request->input('gradeSubjectId');
            $component->grade_id = $request->input('gradeId');
            $component->name = $request->input('name');
            $component->description = $request->input('description');
            $component->save();
    
            DB::commit();
            return redirect()->back()->with('message', 'Component created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create component: ' . $e->getMessage());
            return back()->with('Failed to create component. Please try again.')->withInput();
        }
    }

    public function editComponent($subjectId, $componentId){
        try {
            $subject = GradeSubject::find($subjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            $component = $subject->components()->find($componentId);
            if (!$component) {
                return back()->with('error', 'Component not found for this subject.');
            }
            return view('classes.edit-component', ['subject' => $subject, 'component' => $component]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@editComponent: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function deleteSubjectComponent($componentId){
        try {
            $component = Component::find($componentId);
            if (!$component) {
                return back()->with('error', 'Component not found.');
            }

            $criteriaBaseStudentTestCount = CriteriaBasedStudentTest::where('component_id', $componentId)->count();

            if ($criteriaBaseStudentTestCount > 0) {
                return back()->with('error', 'Cannot delete component. It is associated with existing gradings. Remove assessments then delete!');
            }

            $component->delete();
            return back()->with('message', 'Component deleted successfully.');

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@deleteSubjectComponent: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.'); 
        }
    }

    public function updateComponent(Request $request, $componentId){
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $component = Component::findOrFail($componentId);
        $component->name = $request->input('name');
        $component->description = $request->input('description');
        $component->save();

        return redirect()->back()->with('message', 'Component updated successfully.');
    }


    public function createGradeOption(){
        return view('classes.add-grade-option');
    }

    public function linkToSubject($subjectId){
        try {
            $subject = GradeSubject::find($subjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            $gradeOptionSets = GradeOptionSet::all();

            $gradeSubjects = GradeSubject::with('gradeOptionSets')
                ->whereHas('subject', function ($query) {
                    $query->where('level', 'Preschool');
                })->get();

            return view('classes.link-grade-option', [
                'subject' => $subject,
                'gradeOptionSets' => $gradeOptionSets,
                'gradeSubjects' => $gradeSubjects, 
            ]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@linkToSubject: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function updateGradeOptions(Request $request){
        $validated = $request->validate([
            'labels' => 'required|array',
            'labels.*' => 'required|string|max:255',
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string|max:255',
        ]);

        foreach ($validated['labels'] as $index => $label) {
            $gradeOption = GradeOption::find($index);
            $gradeOption->update([
                'label' => $label,
                'description' => $validated['descriptions'][$index] ?? null,
            ]);
        }
        return redirect()->back()->with('message', 'Grade options saved successfully!');
    }

    public function editGradeOption($id){
        try {
            $gradeOptionSet = GradeOptionSet::find($id);
            if (!$gradeOptionSet) {
                return back()->with('error', 'Grade option set not found.');
            }

            $gradeOptions = GradeOption::where('grade_option_set_id', $gradeOptionSet->id)->get();
            if ($gradeOptions->isEmpty()) {
                return back()->with('error', 'No grade options found for this set.');
            }
            return view('classes.update-grade-option', compact('gradeOptionSet', 'gradeOptions'));

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@editGradeOption: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function addLinkToSubject(Request $request){
        try {
            $request->validate([
                'grade_subject_id' => 'required|exists:grade_subject,id',
                'grade_option_set_id' => 'required|exists:grade_option_sets,id'
            ]);

            $gradeSubjectId = $request->input('grade_subject_id');
            $gradeOptionSetId = $request->input('grade_option_set_id');

            $subject = GradeSubject::find($gradeSubjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            if ($subject->gradeOptionSets()->where('grade_option_sets.id', $gradeOptionSetId)->exists()) {
                return back()->with('error', 'This grade option set is already linked to this subject.');
            }

            $subject->gradeOptionSets()->syncWithoutDetaching([$gradeOptionSetId]);
            return back()->with('message', 'Grade option set linked successfully to the subject.');

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@addLinkToSubject: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }


    public function unlinkOptionSet(Request $request){
        try {
            $request->validate([
                'grade_subject_id' => 'required|exists:grade_subject,id',
                'grade_option_set_id' => 'required|exists:grade_option_sets,id'
            ]);

            $gradeSubjectId = $request->input('grade_subject_id');
            $gradeOptionSetId = $request->input('grade_option_set_id');

            $subject = GradeSubject::find($gradeSubjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            if (!$subject->gradeOptionSets()->where('grade_option_sets.id', $gradeOptionSetId)->exists()) {
                return back()->with('error', 'This grade option set is not linked to this subject.');
            }


            $subject->gradeOptionSets()->detach($gradeOptionSetId);
            return back()->with('message', 'Grade option set unlinked successfully from the subject.');

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@unlinkOptionSet: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }


    public function addGradeOption(Request $request){
        try {
            $request->validate([
                'name' => 'required|string',
                'labels' => 'required|array',
                'labels.*' => 'nullable|string',
                'descriptions.*' => 'nullable|string'
            ]);

            $gradeOptionSet = GradeOptionSet::create([
                'name' => $request->input('name')
            ]);

            if (!$gradeOptionSet) {
                return back()->with('error', 'Failed to create grade option set.');
            }
        
            $maxSequence = GradeOption::max('sequence') ?? 0;
            $gradeOptions = [];

            foreach ($request->input('labels', []) as $index => $label) {
                if (empty($label)) {
                    continue;
                }

                $maxSequence++;
                $description = $request->input('descriptions.'.$index);
                $gradeOptions[] = [
                    'grade_option_set_id' => $gradeOptionSet->id,
                    'label' => $label,
                    'description' => $description,
                    'sequence' => $maxSequence,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($gradeOptions)) {
                GradeOption::insert($gradeOptions);
            } else {
                return back()->with('error', 'No valid grade options provided.');
            }

            return back()->with('message', 'Grade options created successfully.');

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@addGradeOption: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }
    

    function getGradingScale($id){
        try {
            $subject = GradeSubject::find($id);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return back()->with('error', 'Current term not set. Please configure the current term.');
            }

            return view('subjects.add-grading-scale', compact('subject', 'currentTerm'));

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@getGradingScale: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }


    public function saveGradingScale(Request $request){
        $request->validate([
            'grade_subject_id' => 'required|integer',
            'term_id' => 'required|integer',
            'grade_id' => 'required|integer',
            'min_score.*' => 'nullable|numeric|min:0',
            'max_score.*' => 'nullable|numeric|min:0',
            'grade.*' => 'nullable|string|max:255',
            'description.*' => 'nullable|string',
            'points.*' => 'nullable|numeric|min:0|max:9',
        ]);
    
        $subjectId = $request->input('grade_subject_id');
        $termId = $request->input('term_id');
        $gradeId = $request->input('grade_id');
        $descriptions = $request->input('description');
        $min_scores = $request->input('min_score');
        $max_scores = $request->input('max_score');
        $grades = $request->input('grade');
        $points = $request->input('points');
    
        DB::transaction(function () use ($subjectId, $termId, $gradeId, $descriptions, $min_scores, $max_scores, $grades, $points) {
            foreach ($descriptions as $index => $description) {
                if (!is_null($description) || !is_null($min_scores[$index]) || !is_null($max_scores[$index]) || !is_null($grades[$index]) || !is_null($points[$index])) {
                    GradingScale::create([
                        'grade_subject_id' => $subjectId,
                        'term_id' => $termId,
                        'grade_id' => $gradeId,
                        'description' => $description,
                        'min_score' => $min_scores[$index],
                        'max_score' => $max_scores[$index],
                        'grade' => $grades[$index],
                        'points' => $points[$index],
                    ]);
                }
            }
        });
        return redirect()->back()->with('message', 'Grading scale saved successfully!');
    }
    

    public function show($id){
        try {
            $subject = GradeSubject::find($id);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            $selectedTermId = session('selected_term_id');
            if (!$selectedTermId) {
                $currentTerm = TermHelper::getCurrentTerm();
                if (!$currentTerm) {
                    return back()->with('error', 'Current term not found. Please configure the current term.');
                }
                $selectedTermId = $currentTerm->id;
            }

            $subjects = GradeSubject::where('grade_id', $subject->grade_id)
                                ->where('term_id', $selectedTermId)
                                ->get();

            $departments = CacheHelper::getDepartments();
            $grades = CacheHelper::getGrades();

            return view('subjects.edit-subject', [
                'grade_subject' => $subject,
                'departments' => $departments,
                'subjects' => $subjects,
                'grades' => $grades,
            ]);

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@show: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function store(Request $request){
        $currentTerm = TermHelper::getCurrentTerm();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'The current term is not configured. Please configure the current term before proceeding.');
        }

        try {
            $messages = [
                'sequence.required' => 'The sequence field is required.',
                'sequence.integer' => 'The sequence must be a valid number.',
                'sequence.min' => 'The sequence must be at least 1.',
                'sequence.max' => 'The sequence cannot be greater than 35.',
                'grade_id.required' => 'The grade field is required.',
                'grade_id.integer' => 'The grade ID must be a valid integer.',
                'subject_id.required' => 'The subject field is required.',
                'subject_id.integer' => 'The subject ID must be a valid integer.',
                'type.required' => 'The type field is required.',
                'type.boolean' => 'The type must be either true or false.',
                'department_id.required' => 'The department field is required.',
                'department_id.integer' => 'The department ID must be a valid integer.',
                'mandatory.required' => 'The mandatory field is required.',
                'mandatory.boolean' => 'The mandatory field must be either true or false.',
                'active.required' => 'The active field is required.',
                'active.boolean' => 'The active field must be either true or false.',
            ];

            $validatedData = $request->validate([
                'sequence' => 'required|integer|min:1|max:35',
                'grade_id' => 'required|integer|exists:grades,id',
                'subject_id' => 'required|integer|exists:subjects,id',
                'type' => 'required|boolean',
                'department_id' => 'required|integer|exists:departments,id',
                'mandatory' => 'required|boolean',
                'active' => 'required|boolean',
            ], $messages);

            $validatedData['year'] = $currentTerm->year;
            $validatedData['term_id'] = $currentTerm->id;

            $created = DB::transaction(function () use ($validatedData) {
                $existingGradeSubject = GradeSubject::where('grade_id', $validatedData['grade_id'])
                    ->where('term_id', $validatedData['term_id'])
                    ->where('subject_id', $validatedData['subject_id'])
                    ->lockForUpdate()
                    ->first();

                if ($existingGradeSubject) {
                    return false;
                }

                GradeSubject::create($validatedData);
                return true;
            });

            if (!$created) {
                return redirect()->back()->with('error', 'The subject already exists for the selected grade and term.');
            }

            return redirect()->back()->with('message', 'Subject added successfully!');
            
        } catch (ValidationException $e) {
            Log::error('Validation failed while adding the subject', [
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            if ($this->isGradeSubjectDuplicateException($e)) {
                return redirect()->back()->with('error', 'The subject already exists for the selected grade and term.')->withInput();
            }

            Log::error('Database error while adding the subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'An unexpected error occurred while adding the subject. Please try again later.')->withInput();
        } catch (Exception $e) {
            Log::error('An unexpected error occurred while adding the subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'An unexpected error occurred while adding the subject. Please try again later.')->withInput();
        }
    }

    public function updateGradeSubject(Request $request, $id) {
        try {
            $selectedTermId = session('selected_term_id');
            if(!$selectedTermId){
                $currentTerm = TermHelper::getCurrentTerm();
            }

            $currentTerm = $selectedTermId ? Term::find($selectedTermId) : TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return back()->with('error', 'Current term not found. Please configure the current term.');
            }
    
            $messages = [
                'sequence.required'   => 'The sequence field is required.',
                'sequence.integer'    => 'The sequence must be a valid number.',
                'sequence.min'        => 'The sequence must be at least 1.',
                'sequence.max'        => 'The sequence cannot be greater than 35.',
                'subject_id.required' => 'The subject field is required.',
                'subject_id.integer'  => 'The subject ID must be a valid integer.',
                'subject_id.exists'   => 'The selected subject does not exist.',
                'type.required'       => 'The type field is required.',
                'type.boolean'        => 'The type field must be either true or false.',
                'mandatory.required'  => 'The mandatory field is required.',
                'mandatory.boolean'   => 'The mandatory field must be either true or false.',
                'active.required'     => 'The active field is required.',
                'active.boolean'      => 'The active field must be either true or false.',
                'department_id.required' => 'The department field is required.',
                'department_id.integer'  => 'The department ID must be a valid integer.',
                'department_id.exists'   => 'The selected department does not exist.',
                'grade_id.required'   => 'The grade field is required.',
                'grade_id.integer'    => 'The grade ID must be a valid integer.',
                'grade_id.exists'     => 'The selected grade does not exist.',
                'confirm_change.required_if' => 'You must confirm this change as it will delete related data.',
            ];
    
            $validatedData = $request->validate([
                'subject_id'    => 'required|integer|exists:subjects,id',
                'type'          => 'required|boolean',
                'mandatory'     => 'required|boolean',
                'active'        => 'required|boolean',
                'department_id' => 'required|integer|exists:departments,id',
                'grade_id'      => 'required|integer|exists:grades,id',
                'sequence'      => 'required|integer|min:1|max:35',
            ], $messages);

            $gradeSubject = GradeSubject::find($id);
            if (!$gradeSubject) {
                return back()->with('error', 'Subject not found.');
            }

            if ((int) $validatedData['grade_id'] !== (int) $gradeSubject->grade_id) {
                return back()
                    ->with('error', 'Changing the grade of an existing subject allocation is not allowed. Create the subject in the target grade instead.')
                    ->withInput();
            }

            $validatedData['grade_id'] = $gradeSubject->grade_id;

            // Check for duplicate subject in the same grade and term (excluding current record)
            $duplicate = GradeSubject::where('grade_id', $validatedData['grade_id'])
                ->where('term_id', $currentTerm->id)
                ->where('subject_id', $validatedData['subject_id'])
                ->where('id', '!=', $id)
                ->first();

            if ($duplicate) {
                return back()->with('error', 'This subject already exists for the selected grade and term.');
            }
    
            $oldType = $gradeSubject->type;
            $newType = $validatedData['type'] ? '1' : '0';
            
            if ($oldType !== $newType) {
                $affectedData = [];
                
                if ($oldType === '1' && $newType === '0') {
                    $klassSubjects = KlassSubject::where('grade_subject_id', $gradeSubject->id)->get();
                    $testsCount = Test::where('grade_subject_id', $gradeSubject->id)->count();
                    
                    if ($klassSubjects->count() > 0 || $testsCount > 0) {
                        if (!$request->has('confirm_change')) {
                            $affectedData = [
                                'klass_subjects' => $klassSubjects->count(),
                                'tests' => $testsCount,
                                'student_tests' => StudentTest::whereHas('test', function($q) use ($gradeSubject) {
                                    $q->where('grade_subject_id', $gradeSubject->id);
                                })->count(),
                            ];
                            
                            return back()->with([
                                'warning' => 'Changing this subject from Core to Optional will delete related data.',
                                'affected_data' => $affectedData,
                                'form_data' => $validatedData,
                                'grade_subject_id' => $id,
                                'requires_confirmation' => true
                            ]);
                        }
                        
                        $request->validate([
                            'confirm_change' => 'required|accepted',
                        ], $messages);
                    }
                } 
                elseif ($oldType === '0' && $newType === '1') {
                    $optionalSubjects = OptionalSubject::where('grade_subject_id', $gradeSubject->id)->get();
                    $testsCount = Test::where('grade_subject_id', $gradeSubject->id)->count();
                    
                    if ($optionalSubjects->count() > 0 || $testsCount > 0) {
                        if (!$request->has('confirm_change')) {
                            $affectedData = [
                                'optional_subjects' => $optionalSubjects->count(),
                                'tests' => $testsCount,
                                'student_tests' => StudentTest::whereHas('test', function($q) use ($gradeSubject) {
                                    $q->where('grade_subject_id', $gradeSubject->id);
                                })->count(),
                            ];
                            
                            return back()->with([
                                'warning' => 'Changing this subject from Optional to Core will delete related data.',
                                'affected_data' => $affectedData,
                                'form_data' => $validatedData,
                                'grade_subject_id' => $id,
                                'requires_confirmation' => true
                            ]);
                        }
                        
                        $request->validate([
                            'confirm_change' => 'required|accepted',
                        ], $messages);
                    }
                }
            }
    
            $validatedData['type'] = $newType;
            $validatedData['year'] = $currentTerm->year;
            $cleanupMessage = '';
            $detailedCleanup = [];
            $currentTermId = $currentTerm->id;

            DB::transaction(function () use ($gradeSubject, $validatedData, $oldType, $newType, &$cleanupMessage, &$detailedCleanup, $currentTermId, $id) {
                // Race-condition-safe duplicate check with lock
                $duplicate = GradeSubject::where('grade_id', $validatedData['grade_id'])
                    ->where('term_id', $currentTermId)
                    ->where('subject_id', $validatedData['subject_id'])
                    ->where('id', '!=', $id)
                    ->lockForUpdate()
                    ->first();

                if ($duplicate) {
                    throw new \RuntimeException('This subject already exists for the selected grade and term.');
                }

                if ($oldType !== $newType) {
                    $tests = Test::where('grade_subject_id', $gradeSubject->id)->get();
                    if ($tests->count() > 0) {
                        $testIds = $tests->pluck('id')->toArray();
                        $studentTestCount = StudentTest::whereIn('test_id', $testIds)->count();
                        
                        if ($studentTestCount > 0) {
                            StudentTest::whereIn('test_id', $testIds)->forceDelete();
                            $detailedCleanup[] = "{$studentTestCount} student test records removed";
                        }
                        
                        foreach ($tests as $test) {
                            $test->forceDelete();
                        }
                        $detailedCleanup[] = "{$tests->count()} test records removed";
                    }
                    
                    if ($oldType === '1' && $newType === '0') {
                        $klassSubjects = KlassSubject::where('grade_subject_id', $gradeSubject->id)->get();
                        
                        if ($klassSubjects->count() > 0) {
                            foreach ($klassSubjects as $klassSubject) {
                                $klassSubject->forceDelete();
                            }
                            $detailedCleanup[] = "{$klassSubjects->count()} core subject class tests removed";
                        }
                    }
                    elseif ($oldType === '0' && $newType === '1') {
                        $optionalSubjects = OptionalSubject::where('grade_subject_id', $gradeSubject->id)->get();
                        
                        if ($optionalSubjects->count() > 0) {
                            $optionalSubjectIds = $optionalSubjects->pluck('id')->toArray();
                            $studentAllocationsCount = DB::table('student_optional_subjects')->whereIn('optional_subject_id', $optionalSubjectIds)->count();
                                
                            if ($studentAllocationsCount > 0) {
                                DB::table('student_optional_subjects')->whereIn('optional_subject_id', $optionalSubjectIds)->delete();
                                $detailedCleanup[] = "{$studentAllocationsCount} student optional subject allocations removed";
                            }
                            
                            foreach ($optionalSubjects as $optionalSubject) {
                                $optionalSubject->forceDelete();
                            }
                            $detailedCleanup[] = "{$optionalSubjects->count()} optional subject records removed";
                        }
                    }
                }

                // Handle department change - remove duplicate grade subjects from old department
                $oldDepartmentId = $gradeSubject->department_id;
                $newDepartmentId = $validatedData['department_id'];

                if ($oldDepartmentId !== $newDepartmentId) {
                    // Find and remove any duplicate grade subjects in the old department
                    // for the same subject, grade, and term combination
                    $duplicateGradeSubjects = GradeSubject::where('subject_id', $validatedData['subject_id'])
                        ->where('grade_id', $validatedData['grade_id'])
                        ->where('term_id', $currentTermId)
                        ->where('department_id', $oldDepartmentId)
                        ->where('id', '!=', $gradeSubject->id)
                        ->get();

                    if ($duplicateGradeSubjects->count() > 0) {
                        foreach ($duplicateGradeSubjects as $duplicate) {
                            $duplicate->forceDelete();
                        }
                        $detailedCleanup[] = "{$duplicateGradeSubjects->count()} duplicate grade subject(s) removed from previous department";
                    }
                }

                $gradeSubject->update($validatedData);
            });

            if (!empty($detailedCleanup)) {
                $cleanupMessage = "Subject updated successfully!";
                $cleanupMessage .= '<div class="mt-3">';
                $cleanupMessage .= '<h6 class="mb-3"><i class="mdi mdi-check-circle text-success me-1"></i> The following related data was removed:</h6>';
                
                $iconMap = [
                    'test' => 'clipboard',
                    'tests' => 'clipboard',
                    'criteria' => 'list-check',
                    'component' => 'cube',
                    'components' => 'cube',
                    'optional' => 'select-multiple',
                    'allocation' => 'select-multiple',
                    'class' => 'book-content',
                    'assignment' => 'book-content',
                    'comment' => 'message-detail',
                    'comments' => 'message-detail',
                    'content' => 'file',
                    'enrollment' => 'user-check',
                    'scale' => 'bar-chart-alt-2',
                    'scales' => 'bar-chart-alt-2',
                    'grade' => 'check-square',
                    'option' => 'check-square'
                ];
                
                $cleanupMessage .= '<div class="d-flex flex-wrap gap-2">';
                foreach ($detailedCleanup as $detail) {
                    $icon = 'trash';
                    foreach ($iconMap as $keyword => $iconName) {
                        if (stripos($detail, $keyword) !== false) {
                            $icon = $iconName;
                            break;
                        }
                    }
                    
                    $cleanupMessage .= '<div class="badge bg-warning text-white px-3 py-2 d-flex align-items-center">';
                    $cleanupMessage .= '<i class="bx bx-' . $icon . ' me-1 text-white"></i>';
                    $cleanupMessage .= '<span>' . $detail . '</span>';
                    $cleanupMessage .= '</div>';
                }
                
                $cleanupMessage .= '</div>';
                $cleanupMessage .= '</div>';
            } else {
                $cleanupMessage = "Subject updated successfully!";
            }
            
            CacheHelper::forgetClassSubjects(null, $currentTerm->id);
            return back()->with('message', $cleanupMessage);
    
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            if ($this->isGradeSubjectDuplicateException($e)) {
                return back()->with('error', 'This subject already exists for the selected grade and term.')->withInput();
            }

            Log::error("Database error updating grade subject: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage())->withInput();
        } catch (Exception $e) {
            Log::error("Error updating grade subject: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    private function isGradeSubjectDuplicateException(Throwable $exception): bool
    {
        $message = $exception->getMessage();
        $code = (string) $exception->getCode();

        return in_array($code, ['19', '23000'], true)
            && (
                str_contains($message, 'grade_subject_term_grade_subject_unique')
                || str_contains($message, 'UNIQUE constraint failed: grade_subject.term_id, grade_subject.grade_id, grade_subject.subject_id')
                || str_contains($message, 'Duplicate entry')
            );
    }

    public function deleteSubject($id) {
        try {
            $schoolType = SchoolSetup::first()->type;
            
            $subject = GradeSubject::with([
                'subject', 
                'grade',
                'tests',
                'criteriaBasedTests',
                'components',
                'optionalSubjects',
                'gradingScales'
            ])->find($id);
            
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }
    
            $relatedCounts = [];
            $testIds = $subject->tests()->pluck('id')->toArray();
            $criteriaTestIds = method_exists($subject, 'criteriaBasedTests') 
                ? $subject->criteriaBasedTests()->pluck('id')->toArray() 
                : [];
            $optionalSubjectIds = ($subject->type === '0' || $subject->type === null)
                ? $subject->optionalSubjects()->pluck('id')->toArray()
                : [];
            
            $studentTestCount = DB::table('student_tests')->whereIn('test_id', $testIds)->count();
            $relatedCounts['student_tests'] = $studentTestCount;
            
            $criteriaBasedStudentTestCount = !empty($criteriaTestIds)
                ? DB::table('criteria_based_student_tests')
                    ->whereIn('criteria_based_test_id', $criteriaTestIds)
                    ->count()
                : 0;
            $relatedCounts['criteria_based_student_tests'] = $criteriaBasedStudentTestCount;
            $studentOptionalSubjectCount = !empty($optionalSubjectIds)
                ? DB::table('student_optional_subjects')
                    ->whereIn('optional_subject_id', $optionalSubjectIds)
                    ->count()
                : 0;
            $relatedCounts['student_optional_subjects'] = $studentOptionalSubjectCount;
            $klassSubjectCount = KlassSubject::where('grade_subject_id', $subject->id)->count();
            $relatedCounts['klass_subjects'] = $klassSubjectCount;
            
            $commentCount = DB::table('subject_comments')
                ->where('grade_subject_id', $subject->id)
                ->count();
            $relatedCounts['subject_comments'] = $commentCount;
            
            $gradingScaleCount = method_exists($subject, 'gradingScales')
                ? $subject->gradingScales()->count()
                : 0;
            $relatedCounts['grading_scales'] = $gradingScaleCount;

            $gradeOptionsCount = ($schoolType === 'Primary' && method_exists($subject, 'gradeOptions'))
                ? DB::table('subject_grade_options')
                    ->where('grade_subject_id', $subject->id)
                    ->count()
                : 0;
            $relatedCounts['grade_options'] = $gradeOptionsCount;
            
            $gradeOptionSetsCount = ($schoolType === 'Primary' && method_exists($subject, 'gradeOptionSets'))
                ? DB::table('subject_grade_option_set')
                    ->where('grade_subject_id', $subject->id)
                    ->count()
                : 0;
            $relatedCounts['grade_option_sets'] = $gradeOptionSetsCount;
            
            DB::transaction(function () use ($subject, $testIds, $criteriaTestIds, $optionalSubjectIds, $relatedCounts, $schoolType) {
                if (!empty($testIds) && $relatedCounts['student_tests'] > 0) {
                    DB::table('student_tests')
                        ->whereIn('test_id', $testIds)
                        ->delete();
                }
                
                if ($subject->tests()->count() > 0) {
                    $subject->tests()->forceDelete();
                }
                
                if (!empty($criteriaTestIds) && $relatedCounts['criteria_based_student_tests'] > 0) {
                    DB::table('criteria_based_student_tests')
                        ->whereIn('criteria_based_test_id', $criteriaTestIds)
                        ->delete();
                }
                
                if (method_exists($subject, 'criteriaBasedTests') && $subject->criteriaBasedTests->count() > 0) {
                    $subject->criteriaBasedTests()->forceDelete();
                }
                
                if (method_exists($subject, 'components') && $subject->components->count() > 0) {
                    $subject->components()->forceDelete();
                }
                
                if ($subject->type === '0' || $subject->type === null) {
                    if (!empty($optionalSubjectIds) && $relatedCounts['student_optional_subjects'] > 0) {
                        DB::table('student_optional_subjects')
                            ->whereIn('optional_subject_id', $optionalSubjectIds)
                            ->delete();
                    }
                    
                    if ($subject->optionalSubjects->count() > 0) {
                        $subject->optionalSubjects()->forceDelete();
                    }
                }
                
                if ($relatedCounts['klass_subjects'] > 0) {
                    KlassSubject::where('grade_subject_id', $subject->id)->forceDelete(); // Using forceDelete here
                }
                
                if (method_exists($subject, 'gradingScales') && $relatedCounts['grading_scales'] > 0) {
                    $subject->gradingScales()->forceDelete();
                }
                
                if ($relatedCounts['subject_comments'] > 0) {
                    DB::table('subject_comments')
                        ->where('grade_subject_id', $subject->id)
                        ->delete();
                }
                
                if ($schoolType === 'Primary' && method_exists($subject, 'gradeOptions') && $relatedCounts['grade_options'] > 0) {
                    DB::table('subject_grade_options')
                        ->where('grade_subject_id', $subject->id)
                        ->delete();
                }
                
                if ($schoolType === 'Primary' && method_exists($subject, 'gradeOptionSets') && $relatedCounts['grade_option_sets'] > 0) {
                    DB::table('subject_grade_option_set')
                        ->where('grade_subject_id', $subject->id)
                        ->delete();
                }
                
                $subject->forceDelete();
            });
    
            CacheHelper::forgetClassSubjects();
            $messageHtml = 'Subject and all related items removed successfully!';
            $nonZeroCounts = array_filter($relatedCounts, function($count) {
                return $count > 0;
            });

            if (!empty($nonZeroCounts)) {
                $messageHtml .= '<div class="mt-3">';
                $messageHtml .= '<h6 class="mb-3"><i class="mdi mdi-check-circle text-success me-1"></i> Successfully deleted items:</h6>';
                
                $relationLabels = [
                    'student_tests' => 'Student test records',
                    'criteria_based_student_tests' => 'Criteria-based student test records',
                    'student_optional_subjects' => 'Student optional subject allocations',
                    'klass_subjects' => 'Class subject tests',
                    'subject_comments' => 'Subject comments',
                    'grading_scales' => 'Grading scales',
                    'grade_options' => 'Grade option links',
                    'grade_option_sets' => 'Grade option set links'
                ];
                
                $relationIcons = [
                    'student_tests' => 'clipboard',
                    'criteria_based_student_tests' => 'list-check',
                    'student_optional_subjects' => 'select-multiple',
                    'klass_subjects' => 'book-content',
                    'subject_comments' => 'message-detail',
                    'grading_scales' => 'bar-chart-alt-2',
                    'grade_options' => 'check-square',
                    'grade_option_sets' => 'collection'
                ];
                
                $messageHtml .= '<div class="d-flex flex-wrap gap-2">';
                
                foreach ($nonZeroCounts as $relation => $count) {
                    $label = $relationLabels[$relation] ?? ucwords(str_replace('_', ' ', $relation));
                    $icon = $relationIcons[$relation] ?? 'check';
                    
                    $messageHtml .= '<div class="badge bg-warning text-white px-3 py-2 d-flex align-items-center">';
                    $messageHtml .= '<i class="bx bx-' . $icon . ' me-1 text-white"></i>';
                    $messageHtml .= '<span>' . $count . ' ' . $label . '</span>';
                    $messageHtml .= '</div>';
                }
                
                $messageHtml .= '</div>'; 
                $messageHtml .= '</div>';
            }
            
            return back()->with('message', $messageHtml);
        } catch (Exception $e) {
            Log::error("Error deleting subject: " . $e->getMessage(), [
                'id' => $id, 
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function editGradingScale($id){
        try {
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $currentTerm = TermHelper::getCurrentTerm();
            $selectedTerm = Term::findOrFail($selectedTermId);
            
            if (!$selectedTerm) {
                return back()->with('error', 'Term not found. Please configure the current term.');
            }
            
            $isPastTerm = $selectedTermId != $currentTerm->id;
            $subject = GradeSubject::where('id', $id)->where('term_id', $selectedTermId)->first();
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }
    
            $gradingScales = $subject->gradingScale($subject->grade->id)->get();
            $subjects = GradeSubject::where('term_id', $selectedTermId)
                ->where('grade_id', $subject->grade_id)
                ->where('active', true)
                ->orderBy('sequence', 'asc')
                ->get();
            
            return view('subjects.edit-grading-scale', compact(
                'subject', 
                'gradingScales', 
                'currentTerm', 
                'subjects',
                'isPastTerm',
                'selectedTerm'
            ));
            
        } catch (Exception $e) {
            Log::error("Error in SubjectsController@editGradingScale: " . $e->getMessage(), ['id' => $id]);
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function updateGradingScale(Request $request, $id){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $user = auth()->user();
        
        try {
            $isPastTerm = $selectedTermId != $currentTerm->id;
            $request->validate([
                'term_id' => 'required',
                'grade_id' => 'required',
                'min_score.*' => 'nullable|numeric|min:0',
                'max_score.*' => 'nullable|numeric|min:0',
                'grade.*' => 'nullable|string|max:255',
                'description.*' => 'nullable|string',
                'points.*' => 'nullable|numeric|min:0|max:9',
            ]);
    
            $subject = GradeSubject::where('id', $id)->where('term_id', $selectedTermId)->first();
            if (!$subject) {
                return redirect()->back()->with('error', 'Subject not found.');
            }

            DB::transaction(function () use ($request, $subject, $isPastTerm, $user, $id, $selectedTermId, $currentTerm) {
                $subject->gradingScale($request->grade_id)->forceDelete();
                $newScalesCount = 0;
                foreach ($request->description as $index => $description) {
                    $data = [
                        'description' => $description,
                        'min_score'   => $request->min_score[$index],
                        'max_score'   => $request->max_score[$index],
                        'grade'       => $request->grade[$index],
                        'points'      => $request->points[$index],
                        'term_id'     => $selectedTermId,
                        'grade_id'    => $request->grade_id,
                    ];
    
                    if (array_filter($data, function ($value) {
                        return $value !== null && $value !== '';
                    })) {
                        $subject->gradingScale($request->grade_id)->create($data);
                        $newScalesCount++;
                    }
                }
                
                if ($isPastTerm) {
                    Log::info('Past term grading scale updated', [
                        'user_id' => $user->id,
                        'user_name' => $user->full_name,
                        'grade_subject_id' => $id,
                        'subject_name' => $subject->subject->name ?? 'Unknown',
                        'new_scales_count' => $newScalesCount,
                        'selected_term_id' => $selectedTermId,
                        'current_term_id' => $currentTerm->id,
                        'timestamp' => now()
                    ]);
                }
            });
    
            $message = 'Grading scale updated successfully!';
            if ($isPastTerm) {
                $message .= ' (Past Term Data Modified)';
            }
    
            return redirect()->back()->with('message', $message);
            
        } catch (ValidationException $e) {
            Log::error('Validation error while updating grading scale', [
                'errors' => $e->errors(),
                'grade_subject_id' => $id,
                'user_id' => $user->id,
                'is_past_term' => $isPastTerm ?? false,
            ]);
            
            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (Exception $e) {
            Log::error('Error updating grading scale', [
                'error' => $e->getMessage(),
                'grade_subject_id' => $id,
                'user_id' => $user->id,
                'is_past_term' => $isPastTerm ?? false,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()->with('error', 'Unable to update the grading scale. Please try again later.');
        }
    }

    public function copyGradingScale(Request $request, $fromSubjectId){
        $request->validate([
            'copyToSubject' => 'required|exists:grade_subject,id',
        ]);

        $fromSubject = GradeSubject::findOrFail($fromSubjectId);
        $toSubject = GradeSubject::findOrFail($request->input('copyToSubject'));

        $gradingScales = $fromSubject->gradingScale($fromSubject->grade->id)->get();
        $existingGradingScales = $toSubject->gradingScale($toSubject->grade->id)->get();
        foreach ($existingGradingScales as $scale) {
            $scale->forceDelete();
        }

        foreach ($gradingScales as $gradingScale) {
            $newGradingScale = $gradingScale->replicate();
            $newGradingScale->grade_subject_id = $toSubject->id;
            $newGradingScale->save();
        }

        return redirect()->back()->with('message', 'Grading scale copied successfully');
    }

    public function storeMasterSubject(){
        $departments = CacheHelper::getDepartments();
        return view('subjects.add-master-subject',['departments' => $departments]);
    }

    public function editMasterSubject($subjectId){
        try {
            $subject = Subject::find($subjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }

            $departments = CacheHelper::getDepartments();
            if ($departments->isEmpty()) { 
                return back()->with('error', 'No departments found. Please add departments first.');
            }
            return view('subjects.edit-master-subject', compact('subject', 'departments'));

        } catch (Exception $e) {
            Log::error("Error in SubjectsController@editMasterSubject: " . $e->getMessage(), ['subjectId' => $subjectId]);
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }


    public function updateMasterSubject(Request $request, $subjectId){
        try {
            $messages = [
                'abbrev.required' => 'The abbreviation is required.',
                'abbrev.string' => 'The abbreviation must be a valid string.',
                'abbrev.max' => 'The abbreviation must not exceed 10 characters.',
                'name.required' => 'The subject name is required.',
                'name.string' => 'The subject name must be a valid string.',
                'name.max' => 'The subject name must not exceed 255 characters.',
                'level.required' => 'The level field is required.',
                'level.string' => 'The level must be a valid string.',
                'components.required' => 'The components field is required.',
                'components.boolean' => 'The components field must be either true or false.',
                'description.string' => 'The description must be a valid string.',
            ];
    
            $validatedData = $request->validate([
                'abbrev' => 'required|string|max:10',
                'name' => 'required|string|max:255',
                'level' => 'required|string',
                'components' => 'required|boolean',
                'description' => 'nullable|string',
                'is_double' => 'nullable|boolean',
            ], $messages);

            $validatedData['is_double'] = $request->boolean('is_double');
    
            $subject = Subject::find($subjectId);
            if (!$subject) {
                return back()->with('error', 'Subject not found.');
            }
    
            if ($subject->update($validatedData)) {
                CacheHelper::forgetSubjectMasterList();
                return redirect()->route('subjects.edit-master-subject', $subjectId)
                                ->with('message', 'Subject updated successfully.');
            } else {
                return back()->with('error', 'Failed to update subject. Please try again.');
            }
    
        } catch (ValidationException $e) {
            Log::error('Validation error while updating master subject', [
                'errors' => $e->errors(),
                'subject_id' => $subjectId,
                'user_id' => auth()->id(),
            ]);
            return back()->withErrors($e->errors())->withInput();
    
        } catch (Exception $e) {
            Log::error('Error updating subject', [
                'error' => $e->getMessage(),
                'subject_id' => $subjectId,
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function addSubject(Request $request){
        try {
            $messages = [
                'abbrev.required' => 'The abbreviation field is required.',
                'abbrev.string' => 'The abbreviation must be a valid string.',
                'abbrev.max' => 'The abbreviation must not exceed 255 characters.',
                'name.required' => 'The subject name is required.',
                'name.string' => 'The subject name must be a valid string.',
                'name.max' => 'The subject name must not exceed 255 characters.',
                'level.required' => 'The level field is required.',
                'level.string' => 'The level must be a valid string.',
                'level.max' => 'The level must not exceed 255 characters.',
                'components.required' => 'The components field is required.',
                'components.boolean' => 'The components field must be either true or false.',
                'description.string' => 'The description must be a valid string.',
            ];
    
            $validatedData = $request->validate([
                'abbrev' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'level' => 'required|string|max:255',
                'components' => 'required|boolean',
                'description' => 'nullable|string',
                'is_double' => 'nullable|boolean',
            ], $messages);

            $validatedData['is_double'] = $request->boolean('is_double');
    
            $subject = Subject::create($validatedData);
            if (!$subject) {
                return back()->with('error', 'Failed to create subject.');
            }
    
            CacheHelper::forgetSubjectMasterList();
            return back()->with('message', 'Subject added successfully!');
    
        } catch (ValidationException $e) {
            Log::error('Validation error while adding subject', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return back()->withErrors($e->errors())->withInput();
    
        } catch (Exception $e) {
            Log::error('Error adding subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Could not add the subject. Please try again later.');
        }
    }

    public function storeSelectedSubjectGrade(Request $request){
        $validated = $request->validate([
            'gradeId' => 'required|integer'
        ]);
        session(['selectedSubjectGradeId' => $validated['gradeId']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }

    private function fetchRemoteSyllabusStructure(string $sourceUrl): ?array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($sourceUrl);

            if (!$response->successful()) {
                Log::warning('Subject syllabus remote fetch failed.', [
                    'source_url' => $sourceUrl,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $structure = SyllabusStructureHelper::parsePayload($response->body());

            return SyllabusStructureHelper::hasSections($structure) ? $structure : null;
        } catch (Throwable $e) {
            Log::warning('Subject syllabus remote fetch threw an exception.', [
                'source_url' => $sourceUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
