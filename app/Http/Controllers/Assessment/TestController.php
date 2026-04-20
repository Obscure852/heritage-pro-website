<?php

namespace App\Http\Controllers\Assessment;

use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController;
use App\Models\ValueAdditionSubjectMapping;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\PassingThresholdSetting;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Schemes\SchemeOfWorkEntry;
use App\Models\Schemes\SyllabusObjective;
use App\Models\Term;
use App\Models\Test;
use App\Services\SchoolModeResolver;
use App\Services\ThresholdSettingsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TestController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Get the view path based on school type.
     * Checks for school-type specific view first, falls back to shared.
     */
    protected function getViewPath(string $viewName): string {
        $mode = app(SchoolModeResolver::class)->mode();
        $schoolType = strtolower(match ($mode) {
            \App\Models\SchoolSetup::TYPE_PRE_F3 => 'junior',
            \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR => 'senior',
            \App\Models\SchoolSetup::TYPE_K12 => 'senior',
            default => $mode ?? 'junior',
        });
        $specificPath = "assessment.tests.{$schoolType}.{$viewName}";

        if (view()->exists($specificPath)) {
            return $specificPath;
        }
        return "assessment.tests.shared.{$viewName}";
    }

    /**
     * Display the main test list page with grade/term selection.
     * Route: assessment.test-list
     */
    public function index() {
        $termId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $terms = StudentController::terms();

        $grades = CacheHelper::getGrades();
        $currentTerm = TermHelper::getCurrentTerm();
        $school_data = SchoolSetup::first();

        if (!$school_data) {
            return redirect()->back()->withErrors('School setup data not found.');
        }

        $weeklyTests = collect();
        $resolvedMode = app(SchoolModeResolver::class)->mode();

        if (in_array($resolvedMode, [\App\Models\SchoolSetup::TYPE_SENIOR, \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR, \App\Models\SchoolSetup::TYPE_K12], true)) {
            $weeklyTests = Test::where('term_id', $termId)->where('type', 'Exercise')->get();
        } elseif (in_array($resolvedMode, [\App\Models\SchoolSetup::TYPE_JUNIOR, \App\Models\SchoolSetup::TYPE_PRE_F3], true)) {
            $weeklyTests = Test::where('term_id', $termId)->where('type', 'Weekly Test')->get();
        }

        // Get threshold settings for admin tab
        $thresholdSettings = collect();
        $gradeSubjects = collect();

        if (auth()->user()->can('manage-academic')) {
            $thresholdService = app(ThresholdSettingsService::class);
            $thresholdSettings = $thresholdService->getAllSystemSettings();

            // Get grade subjects grouped by grade_id for cascading select
            $gradeSubjects = GradeSubject::with('subject', 'grade')
                ->where('term_id', $termId)
                ->orderBy('grade_id')
                ->get()
                ->groupBy('grade_id');
        }

        $valueAdditionMappingDataSets = collect();
        $valueAdditionMappingData = null;

        if (auth()->user()->can('manage-academic')) {
            $valueAdditionMappingDataSets = collect(
                $this->buildValueAdditionMappingDataSets(
                    app(SchoolModeResolver::class)->valueAdditionSchoolTypes($resolvedMode)
                )
            )->values();

            $valueAdditionMappingData = $valueAdditionMappingDataSets->first();
        }

        return view($this->getViewPath('tests-list'), [
            'grades' => $grades,
            'terms' => $terms,
            'currentTerm' => $currentTerm,
            'school_data' => $school_data,
            'weeklyTests' => $weeklyTests,
            'thresholdSettings' => $thresholdSettings,
            'gradeSubjects' => $gradeSubjects,
            'valueAdditionMappingData' => $valueAdditionMappingData,
            'valueAdditionMappingDataSets' => $valueAdditionMappingDataSets,
        ]);
    }

    /**
     * AJAX: Get tests for a specific term and grade.
     * Route: assessment.tests-lists
     */
    public function listByTermAndGrade($termId, $gradeId) {
        if (!$termId || !is_numeric($termId) || !$gradeId || !is_numeric($gradeId)) {
            return response()->json(['error' => 'Invalid term or grade ID provided.'], 400);
        }

        try {
            $availableSubjects = GradeSubject::with('subject')
                ->where('term_id', $termId)
                ->where('grade_id', $gradeId)
                ->whereHas('subject')
                ->get()
                ->filter(function ($gradeSubject) {
                    return $gradeSubject->subject && $gradeSubject->subject->name;
                })
                ->sortBy(function ($gradeSubject) {
                    return strtolower($gradeSubject->subject->name);
                })
                ->values();

            $tests = Test::with(['subject.subject', 'subject.grade', 'grade'])
                ->where('term_id', $termId)
                ->whereHas('subject', function ($query) use ($termId, $gradeId) {
                    $query->where('term_id', $termId)
                        ->where('grade_id', $gradeId);
                })
                ->whereHas('subject.subject')
                ->get();

            if ($tests->isEmpty()) {
                return view($this->getViewPath('grade-tests-list'), [
                    'groupedTests' => collect(),
                    'availableSubjects' => $availableSubjects,
                ]);
            }

            $groupedTests = $tests->filter(function ($test) {
                return $test->subject && $test->subject->subject && $test->subject->subject->name;
            })->groupBy(function ($test) {
                return $test->subject->subject->name;
            })->map(function ($subjectTests) {
                return $subjectTests->sortBy(function ($test) {
                    return strtolower($test->type) === 'exam' ? PHP_INT_MAX : ($test->sequence ?? 0);
                });
            });

            return view($this->getViewPath('grade-tests-list'), [
                'groupedTests' => $groupedTests,
                'availableSubjects' => $availableSubjects,
            ]);
        } catch (Exception $e) {
            return view($this->getViewPath('grade-tests-list'), [
                'groupedTests' => collect(),
                'availableSubjects' => collect(),
                'error' => 'An unexpected error occurred while loading tests.'
            ]);
        }
    }

    /**
     * Show the test creation form (school-type specific views).
     * Route: assessment.create-test
     */
    public function create() {
        $schoolSetup = SchoolSetup::first();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $subjects = GradeSubject::where('term_id', $selectedTermId)->where('type', 1)->get();

        $sortedSubjects = $subjects->sort(function ($a, $b) {
            $gradeOrder = ['REC' => 1, 'STD 1' => 2, 'STD 2' => 3, 'STD 3' => 4, 'F1' => 5, 'F3' => 6, 'F4' => 7, 'F5' => 8];
            $aOrder = $gradeOrder[$a->grade->name] ?? 999;
            $bOrder = $gradeOrder[$b->grade->name] ?? 999;
            return $aOrder <=> $bOrder;
        });

        $gradeSubjects = GradeSubject::where('term_id', $selectedTermId)->where('type', 0)->get();

        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();
        $grades = Grade::where('term_id', $selectedTermId)->where('active', 1)->get();

        // For create, scheme entries and objectives cannot be scoped to a specific
        // grade/subject until the user selects them in the form. Pass empty collections
        // so the linking panel renders gracefully with the "not available" message.
        $schemeEntries = collect();
        $syllabusObjectives = collect();

        return view($this->getViewPath('test-setup'), [
            'sortedSubjects' => $sortedSubjects,
            'optional_subjects' => $gradeSubjects,
            'terms' => $terms,
            'grades' => $grades,
            'currentTerm' => $currentTerm,
            'schemeEntries' => $schemeEntries,
            'syllabusObjectives' => $syllabusObjectives,
            'selectedEntryIds' => [],
            'selectedObjectiveIds' => [],
        ]);
    }

    /**
     * Store a new test for core/mandatory subjects.
     * Route: assessment.test-store
     */
    public function store(Request $request) {
        $rules = [
            'sequence' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'abbrev' => 'required|string|max:255',
            'subject' => 'required|integer|exists:grade_subject,id',
            'type' => 'required|string|max:255',
            'assessment' => 'required|boolean',
            'out_of' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'grade' => 'required|integer|exists:grades,id',
            'term' => 'required|integer|exists:terms,id',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'scheme_entry_ids' => 'nullable|array',
            'scheme_entry_ids.*' => 'integer|exists:scheme_of_work_entries,id',
            'syllabus_objective_ids' => 'nullable|array',
            'syllabus_objective_ids.*' => 'integer|exists:syllabus_objectives,id',
        ];

        $messages = [
            'subject.exists' => 'The selected subject does not exist.',
            'grade.exists' => 'The selected grade does not exist.',
            'term.exists' => 'The selected term does not exist.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $gradeSubject = $this->resolveGradeSubject(
                (int) $validatedData['subject'],
                (int) $validatedData['grade'],
                (int) $validatedData['term']
            );

            if (!$gradeSubject) {
                return redirect()->back()->withErrors('The selected subject does not belong to the selected grade.')->withInput();
            }

            $term = Term::find($validatedData['term']);
            if ($term && ($validatedData['start_date'] < $term->start_date || $validatedData['end_date'] > $term->end_date)) {
                return redirect()->back()->withErrors('The test dates must fall within the term dates.')->withInput();
            }

            $test = new Test();
            $test->sequence = $validatedData['sequence'];
            $test->name = $validatedData['name'];
            $test->abbrev = $validatedData['abbrev'];
            $test->grade_subject_id = $gradeSubject->id;
            $test->term_id = $validatedData['term'];
            $test->grade_id = $gradeSubject->grade_id;
            $test->out_of = $validatedData['out_of'];
            $test->year = $validatedData['year'];
            $test->type = $validatedData['type'];
            $test->assessment = $request->boolean('assessment');
            $test->start_date = $validatedData['start_date'];
            $test->end_date = $validatedData['end_date'];
            $test->save();

            if ($request->has('scheme_entry_ids')) {
                $test->schemeEntries()->sync($request->input('scheme_entry_ids', []));
            }
            if ($request->has('syllabus_objective_ids')) {
                $test->syllabusObjectives()->sync($request->input('syllabus_objective_ids', []));
            }

            return redirect()->back()->with('message', 'Test created successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            Log::error('Error creating test: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred while creating the test. Please try again later.');
        }
    }

    /**
     * Store a new test for optional subjects.
     * Route: assessment.optional-store
     */
    public function storeOptional(Request $request) {
        $rules = [
            'sequence' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'abbrev' => 'required|string|max:255',
            'subject' => 'required|integer|exists:grade_subject,id',
            'type' => 'required|string|max:255',
            'assessment' => 'required|boolean',
            'out_of' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'grade' => 'required|integer|exists:grades,id',
            'term' => 'required|integer|exists:terms,id',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'scheme_entry_ids' => 'nullable|array',
            'scheme_entry_ids.*' => 'integer|exists:scheme_of_work_entries,id',
            'syllabus_objective_ids' => 'nullable|array',
            'syllabus_objective_ids.*' => 'integer|exists:syllabus_objectives,id',
        ];

        $messages = [
            'subject.exists' => 'The selected subject does not exist.',
            'grade.exists' => 'The selected grade does not exist.',
            'term.exists' => 'The selected term does not exist.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $optionalSubject = $this->resolveGradeSubject(
                (int) $validatedData['subject'],
                (int) $validatedData['grade'],
                (int) $validatedData['term']
            );

            if (!$optionalSubject) {
                return redirect()->back()->withErrors('The selected subject does not belong to the selected grade.')->withInput();
            }

            $term = Term::find($validatedData['term']);
            if ($term && ($validatedData['start_date'] < $term->start_date || $validatedData['end_date'] > $term->end_date)) {
                return redirect()->back()->withErrors('The test dates must fall within the term dates.')->withInput();
            }

            $test = new Test();
            $test->sequence = $validatedData['sequence'];
            $test->name = $validatedData['name'];
            $test->abbrev = $validatedData['abbrev'];
            $test->grade_subject_id = $optionalSubject->id;
            $test->term_id = $validatedData['term'];
            $test->grade_id = $optionalSubject->grade_id;
            $test->out_of = $validatedData['out_of'];
            $test->year = $validatedData['year'];
            $test->type = $validatedData['type'];
            $test->assessment = $request->boolean('assessment');
            $test->start_date = $validatedData['start_date'];
            $test->end_date = $validatedData['end_date'];
            $test->save();

            if ($request->has('scheme_entry_ids')) {
                $test->schemeEntries()->sync($request->input('scheme_entry_ids', []));
            }
            if ($request->has('syllabus_objective_ids')) {
                $test->syllabusObjectives()->sync($request->input('syllabus_objective_ids', []));
            }

            return redirect()->back()->with('message', 'Test created successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            Log::error('Error creating optional test: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred while creating the test. Please try again later.');
        }
    }

    /**
     * Show the edit form for a test.
     * Route: assessment.ca-exam-edit
     */
    public function edit($id) {
        $test = Test::findOrFail($id);
        $currentTerm = TermHelper::getCurrentTerm();
        $gradeSubject = GradeSubject::with('subject', 'grade')->find($test->grade_subject_id);
        $subjects = GradeSubject::where('grade_id', $gradeSubject?->grade_id ?? $test->grade_id)
            ->where('term_id', $test->term_id)
            ->orderBy('grade_id', 'asc')
            ->get();

        $terms = StudentController::terms();
        $grades = CacheHelper::getGrades();

        // Load scheme entries scoped to this test's grade_subject and term.
        $gradeSubjectId = $test->grade_subject_id;
        $termId = $test->term_id;

        $schemeEntries = SchemeOfWorkEntry::whereHas('scheme', function ($q) use ($termId, $gradeSubjectId) {
            $q->where('term_id', $termId)
              ->where(function ($q2) use ($gradeSubjectId) {
                  $q2->whereHas('klassSubject', function ($q3) use ($gradeSubjectId) {
                      $q3->where('grade_subject_id', $gradeSubjectId);
                  });
              });
        })->with('scheme:id,teacher_id')
          ->orderBy('week_number')
          ->get(['id', 'week_number', 'topic', 'sub_topic', 'scheme_of_work_id']);

        // Load syllabus objectives for the test's subject and grade.
        $subjectId = $gradeSubject?->subject_id;
        $gradeName = $gradeSubject?->grade?->name;

        $syllabusObjectives = collect();
        if ($subjectId && $gradeName) {
            $syllabusObjectives = SyllabusObjective::whereHas('topic.syllabus', function ($q) use ($subjectId, $gradeName) {
                $q->where('subject_id', $subjectId)
                  ->forGrade($gradeName)
                  ->where('is_active', true);
            })->with('topic:id,name')
              ->orderBy('sequence')
              ->get(['id', 'code', 'objective_text', 'syllabus_topic_id']);
        }

        $selectedEntryIds = $test->schemeEntries->pluck('id')->toArray();
        $selectedObjectiveIds = $test->syllabusObjectives->pluck('id')->toArray();

        return view($this->getViewPath('ca-exam-edit'), [
            'test' => $test,
            'subjects' => $subjects,
            'terms' => $terms,
            'grades' => $grades,
            'currentTerm' => $currentTerm,
            'schemeEntries' => $schemeEntries,
            'syllabusObjectives' => $syllabusObjectives,
            'selectedEntryIds' => $selectedEntryIds,
            'selectedObjectiveIds' => $selectedObjectiveIds,
        ]);
    }

    /**
     * Update an existing test.
     * Route: assessment.ca-exam-update
     */
    public function update(Request $request, $id) {
        $request->validate([
            'sequence' => 'required|integer',
            'abbrev' => 'required|string|max:191',
            'out_of' => 'required|numeric',
            'grade_id' => 'required|integer|exists:grades,id',
            'grade_subject_id' => 'required|integer|exists:grade_subject,id',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'scheme_entry_ids' => 'nullable|array',
            'scheme_entry_ids.*' => 'integer|exists:scheme_of_work_entries,id',
            'syllabus_objective_ids' => 'nullable|array',
            'syllabus_objective_ids.*' => 'integer|exists:syllabus_objectives,id',
        ]);

        DB::beginTransaction();
        try {
            $test = Test::where('id', $id)->lockForUpdate()->first();
            if (!$test) {
                Log::error('Error occured trying to update test');
                throw new \Exception("The test with ID $id does not exist.");
            }

            $gradeSubject = $this->resolveGradeSubject(
                (int) $request->input('grade_subject_id'),
                (int) $request->input('grade_id'),
                (int) $test->term_id
            );

            if (!$gradeSubject) {
                throw new \RuntimeException('The selected subject does not belong to the selected grade.');
            }

            $test->update([
                'sequence' => $request->input('sequence'),
                'abbrev' => $request->input('abbrev'),
                'out_of' => $request->input('out_of'),
                'grade_id' => $gradeSubject->grade_id,
                'grade_subject_id' => $gradeSubject->id,
                'type' => $request->input('type'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]);

            if ($request->has('scheme_entry_ids')) {
                $test->schemeEntries()->sync($request->input('scheme_entry_ids', []));
            }
            if ($request->has('syllabus_objective_ids')) {
                $test->syllabusObjectives()->sync($request->input('syllabus_objective_ids', []));
            }

            DB::commit();
            return redirect()->back()->with('message', 'Test updated successfully');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Failed to update the test: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a test and all associated student test records.
     * Route: assessment.ca-exam-delete
     */
    public function destroy($id) {
        try {
            $test = Test::findOrFail($id);
            DB::beginTransaction();

            $studentTestCount = $test->studentTests()->count();
            $test->studentTests()->forceDelete();
            $test->forceDelete();

            DB::commit();

            Log::info('Test deleted successfully', [
                'test_id' => $id,
                'test_name' => $test->name,
                'student_tests_deleted' => $studentTestCount
            ]);

            return redirect()->back()->with('message', "Test '{$test->name}' and {$studentTestCount} associated student records were deleted successfully.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete test', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Unable to delete the test. Please try again or contact support.');
        }
    }

    /**
     * Copy a test to another subject (same grade).
     * Route: assessment.copy-test
     */
    public function copy(Request $request) {
        $request->validate([
            'test_id' => 'required|integer|exists:tests,id',
            'target_subject_id' => 'required|integer|exists:grade_subject,id',
        ]);

        DB::beginTransaction();
        try {
            // Lock source test to prevent concurrent modifications
            $sourceTest = Test::with('subject.subject')->where('id', $request->test_id)->lockForUpdate()->first();

            if (!$sourceTest) {
                throw new \Exception('Source test not found.');
            }

            $sourceSubject = GradeSubject::where('id', $sourceTest->grade_subject_id)
                ->where('term_id', $sourceTest->term_id)
                ->lockForUpdate()
                ->first();

            if (!$sourceSubject) {
                throw new \Exception('Source subject could not be resolved for this test.');
            }

            // Lock target subject to prevent concurrent test creation
            $targetSubject = GradeSubject::with('subject')->where('id', $request->target_subject_id)->lockForUpdate()->first();

            if (!$targetSubject) {
                throw new \Exception('Target subject not found.');
            }

            if ((int) $targetSubject->term_id !== (int) $sourceTest->term_id) {
                throw new \Exception('Cannot copy a test to a subject in a different term.');
            }

            // Verify same grade constraint using canonical grade_subject data
            if ((int) $sourceSubject->grade_id !== (int) $targetSubject->grade_id) {
                throw new \Exception('Cannot copy test across different grades.');
            }

            // Check for duplicate with lock to prevent race condition
            $existingTest = Test::where('grade_subject_id', $targetSubject->id)
                ->where('sequence', $sourceTest->sequence)
                ->where('type', $sourceTest->type)
                ->where('term_id', $sourceTest->term_id)
                ->lockForUpdate()
                ->first();

            if ($existingTest) {
                DB::rollBack();
                return redirect()->back()->with('error', "A {$sourceTest->type} test with sequence {$sourceTest->sequence} already exists for the target subject.");
            }

            // Create the copy
            $newTest = Test::create([
                'sequence' => $sourceTest->sequence,
                'name' => $sourceTest->name,
                'abbrev' => $sourceTest->abbrev,
                'out_of' => $sourceTest->out_of,
                'grade_subject_id' => $targetSubject->id,
                'term_id' => $sourceTest->term_id,
                'grade_id' => $targetSubject->grade_id,
                'type' => $sourceTest->type,
                'assessment' => $sourceTest->assessment,
                'year' => $sourceTest->year,
                'start_date' => $sourceTest->start_date,
                'end_date' => $sourceTest->end_date,
            ]);

            DB::commit();

            Log::info('Test copied successfully', [
                'source_test_id' => $sourceTest->id,
                'new_test_id' => $newTest->id,
                'target_subject_id' => $targetSubject->id,
                'copied_by' => auth()->id(),
            ]);

            return redirect()->back()->with('message', "Test '{$sourceTest->name}' copied successfully to {$targetSubject->subject->name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy test', [
                'test_id' => $request->test_id,
                'target_subject_id' => $request->target_subject_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to copy test: ' . $e->getMessage());
        }
    }

    /**
     * Check if a test has any student marks recorded.
     * Route: assessment.test-has-marks
     */
    public function hasMarks($id) {
        $test = Test::findOrFail($id);
        $hasMarks = $test->studentTests()->exists();
        return response()->json(['hasMarks' => $hasMarks]);
    }

    /**
     * Store selected test grade in session.
     * Route: assessment.store-selected-test
     */
    public function storeSelectedTestGrade(Request $request) {
        $validated = $request->validate([
            'testId' => 'required|integer'
        ]);
        session(['selectedTestId' => $validated['testId']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }

    /**
     * Store selected class in session.
     * Route: assessment.store-selected-class
     */
    public function storeSelectedClass(Request $request) {
        $validated = $request->validate([
            'assessment' => 'required|integer'
        ]);
        session(['selectedClassId' => $validated['assessment']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }

    /**
     * Get value addition subject mappings (AJAX).
     * Route: assessment.value-addition-mappings.index
     */
    public function getValueAdditionMappings(Request $request) {
        $this->authorize('manage-academic');

        $school_data = SchoolSetup::first();
        $resolvedMode = app(SchoolModeResolver::class)->mode();
        $availableSchoolTypes = app(SchoolModeResolver::class)->valueAdditionSchoolTypes($resolvedMode);
        $valueAdditionSchoolType = $request->query('school_type');

        if ($valueAdditionSchoolType === null || $valueAdditionSchoolType === '') {
            $valueAdditionSchoolType = $availableSchoolTypes[0] ?? null;
        }

        if (!$school_data || !in_array($valueAdditionSchoolType, $availableSchoolTypes, true)) {
            return response()->json(['error' => 'Value addition mappings are only available for Senior and Junior schools.'], 400);
        }

        return response()->json($this->buildValueAdditionMappingData($valueAdditionSchoolType));
    }

    /**
     * Store value addition subject mappings (AJAX).
     * Route: assessment.value-addition-mappings.store
     */
    public function storeValueAdditionMappings(Request $request) {
        $this->authorize('manage-academic');

        $request->validate([
            'school_type' => 'required|in:Senior,Junior',
            'exam_type' => 'required|in:JCE,PSLE',
            'mappings' => 'required|array',
            'mappings.*' => 'nullable|array',
            'mappings.*.*' => 'nullable|integer|exists:subjects,id',
        ]);

        $schoolType = $request->input('school_type');
        $examType = $request->input('exam_type');
        $mappings = $request->input('mappings');
        $sourceColumns = collect($this->getValueAdditionSourceColumns($schoolType));
        $validSourceKeys = $sourceColumns->pluck('source_key')->toArray();
        $availableSchoolTypes = app(SchoolModeResolver::class)->valueAdditionSchoolTypes(app(SchoolModeResolver::class)->mode());

        if (!in_array($schoolType, $availableSchoolTypes, true)) {
            return response()->json([
                'success' => false,
                'message' => 'The selected value addition mapping type is not available for this school mode.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($schoolType, $examType, $mappings, $validSourceKeys, $sourceColumns) {
                foreach ($validSourceKeys as $sourceKey) {
                    $subjectIds = array_filter($mappings[$sourceKey] ?? []);
                    $sourceLabel = $sourceColumns->firstWhere('source_key', $sourceKey)['source_label'] ?? $sourceKey;

                    // Delete all existing mappings for this source_key
                    ValueAdditionSubjectMapping::where('school_type', $schoolType)
                        ->where('exam_type', $examType)
                        ->where('source_key', $sourceKey)
                        ->delete();

                    // Insert new rows for each selected subject_id
                    foreach ($subjectIds as $subjectId) {
                        ValueAdditionSubjectMapping::create([
                            'school_type' => $schoolType,
                            'exam_type' => $examType,
                            'source_key' => $sourceKey,
                            'source_label' => $sourceLabel,
                            'subject_id' => $subjectId,
                            'is_active' => true,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Value addition subject mappings saved successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to save value addition mappings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save mappings. Please try again.',
            ], 500);
        }
    }

    private function resolveGradeSubject(int $gradeSubjectId, int $gradeId, int $termId): ?GradeSubject
    {
        return GradeSubject::query()
            ->where('id', $gradeSubjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->first();
    }

    /**
     * Get hardcoded source columns for value addition mapping.
     */
    private function getValueAdditionSourceColumns(string $schoolType): array {
        if ($schoolType === 'Senior') {
            return [
                ['source_key' => 'mathematics', 'source_label' => 'Mathematics'],
                ['source_key' => 'english', 'source_label' => 'English'],
                ['source_key' => 'science', 'source_label' => 'Science'],
                ['source_key' => 'setswana', 'source_label' => 'Setswana'],
                ['source_key' => 'design_and_technology', 'source_label' => 'Design & Technology'],
                ['source_key' => 'home_economics', 'source_label' => 'Home Economics'],
                ['source_key' => 'agriculture', 'source_label' => 'Agriculture'],
                ['source_key' => 'social_studies', 'source_label' => 'Social Studies'],
                ['source_key' => 'moral_education', 'source_label' => 'Moral Education'],
                ['source_key' => 'religious_education', 'source_label' => 'Religious Education'],
                ['source_key' => 'music', 'source_label' => 'Music'],
                ['source_key' => 'physical_education', 'source_label' => 'Physical Education'],
                ['source_key' => 'art', 'source_label' => 'Art'],
                ['source_key' => 'office_procedures', 'source_label' => 'Office Procedures'],
                ['source_key' => 'accounting', 'source_label' => 'Accounting'],
                ['source_key' => 'french', 'source_label' => 'French'],
            ];
        }

        // Junior (PSLE)
        return [
            ['source_key' => 'agriculture_grade', 'source_label' => 'Agriculture'],
            ['source_key' => 'mathematics_grade', 'source_label' => 'Mathematics'],
            ['source_key' => 'english_grade', 'source_label' => 'English'],
            ['source_key' => 'science_grade', 'source_label' => 'Science'],
            ['source_key' => 'social_studies_grade', 'source_label' => 'Social Studies'],
            ['source_key' => 'setswana_grade', 'source_label' => 'Setswana'],
            ['source_key' => 'capa_grade', 'source_label' => 'CAPA'],
            ['source_key' => 'religious_and_moral_education_grade', 'source_label' => 'Religious & Moral Education'],
        ];
    }

    /**
     * @param  array<int, string>  $schoolTypes
     * @return array<int, array<string, mixed>>
     */
    private function buildValueAdditionMappingDataSets(array $schoolTypes): array
    {
        return collect($schoolTypes)
            ->map(fn (string $schoolType) => $this->buildValueAdditionMappingData($schoolType))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildValueAdditionMappingData(string $schoolType): ?array
    {
        if (!in_array($schoolType, [SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_SENIOR], true)) {
            return null;
        }

        $examType = $schoolType === SchoolSetup::TYPE_SENIOR ? 'JCE' : 'PSLE';

        return [
            'school_type' => $schoolType,
            'exam_type' => $examType,
            'source_columns' => $this->getValueAdditionSourceColumns($schoolType),
            'subjects' => Subject::where('level', $schoolType)->orderBy('name')->get(['id', 'name', 'abbrev']),
            'existing_mappings' => ValueAdditionSubjectMapping::where('school_type', $schoolType)
                ->where('exam_type', $examType)
                ->where('is_active', true)
                ->get()
                ->groupBy('source_key')
                ->map(fn ($group) => $group->pluck('subject_id')->toArray())
                ->toArray(),
        ];
    }
}
