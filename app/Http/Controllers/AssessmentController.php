<?php

namespace App\Http\Controllers;


use App\Exports\DepartmentPerformanceExport;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exports\ClassPerformanceExport;
use App\Exports\GradePerformanceAnalysisExport;
use App\Exports\SubjectAnalysisExport;
use App\Helpers\AssessmentHelper;
use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Http\Controllers\CriteriaBasedTestController;
use App\Http\Controllers\Assessment\JuniorAssessmentController;
use App\Http\Controllers\Assessment\PrimaryAssessmentController;
use App\Http\Controllers\Assessment\SeniorAssessmentController;
use App\Jobs\RecalculateGrades;
use App\Models\SchoolSetup;
use App\Jobs\SendBulkReportCards;
use App\Models\CommentBank;
use App\Models\Comment;
use App\Models\CriteriaBasedStudentTest;
use App\Models\Email;
use App\Models\Klass;
use App\Models\Logging;
use App\Models\Student;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Term;
use App\Models\KlassSubject;
use App\Models\GradingScale;
use App\Models\OverallGradingMatrix;
use App\Models\SubjectComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\Holiday;
use App\Models\OptionalSubject;
use App\Models\House;
use App\Models\PSLE;
use App\Models\ScoreComment;
use App\Models\Venue;
use App\Services\PrimaryReportCardBuilder;
use App\Services\SchoolModeResolver;
use App\Services\ThresholdSettingsService;
use Arr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AssessmentController extends Controller{
    private const BULK_REPORT_CARD_QUEUE_THRESHOLD = 10;

    public function __construct(){
        $this->middleware('auth');
    }

    public function index(Request $request){
        $modeResolver = $this->schoolModeResolver();
        $mode = $modeResolver->mode();
        $requestedContext = $modeResolver->resolveAssessmentContext($request->query('context'), $mode)
            ?? $modeResolver->resolveAssessmentContext(session('assessment_gradebook_context'), $mode);

        if ($requestedContext !== null) {
            return redirect()->route($modeResolver->gradebookRouteName($requestedContext, $mode));
        }

        if (!$modeResolver->isCombinedMode($mode)) {
            return redirect()->route($modeResolver->gradebookRouteName(null, $mode));
        }

        $contexts = collect($modeResolver->availableAssessmentContexts($mode))
            ->map(function (string $context) use ($modeResolver) {
                return [
                    'key' => $context,
                    'label' => $modeResolver->assessmentContextLabel($context),
                    'description' => $modeResolver->assessmentContextDescription($context),
                    'url' => $modeResolver->gradebookUrl($context),
                ];
            })
            ->values();

        return view('assessment.shared.gradebook-selector', [
            'contexts' => $contexts,
            'type' => $mode,
        ]);
    }

    public function primaryGradebook(Request $request)
    {
        return $this->renderGradebook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY);
    }

    public function juniorGradebook(Request $request)
    {
        return $this->renderGradebook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR);
    }

    public function seniorGradebook(Request $request)
    {
        return $this->renderGradebook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR);
    }

    private function renderGradebook(Request $request, string $context)
    {
        try {
            $terms = StudentController::terms();
            $currentTerm = TermHelper::getCurrentTerm();
            $modeResolver = $this->schoolModeResolver();
            $type = $modeResolver->mode();
            $resolvedContext = $modeResolver->resolveAssessmentContext($context, $type);

            if ($resolvedContext === null) {
                abort(404);
            }

            if (!$currentTerm) {
                throw new Exception("Current term is not set.");
            }

            session(['assessment_gradebook_context' => $resolvedContext]);

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $subjectLevels = collect($modeResolver->levelsForAssessmentContext($resolvedContext, $type))
                ->map(fn (string $level) => $modeResolver->subjectLevelForLevel($level) ?? $level)
                ->values()
                ->all();

            $subject = GradeSubject::whereHas('subject', function ($query) use ($subjectLevels) {
                $query->whereIn('level', $subjectLevels)->where('name', 'Mathematics');
            })->where('term_id', $selectedTermId)->first();

            if (!$subject) {
                $subject = GradeSubject::where('term_id', $selectedTermId)
                    ->whereHas('subject', function ($query) use ($subjectLevels) {
                        $query->whereIn('level', $subjectLevels);
                    })->first();
                    
                if (!$subject) {
                    $subject = GradeSubject::where('term_id', $selectedTermId)->first();
                }
            }

            $user = Auth::user();
            $hasAdminRole = $user->roles->contains(function ($role) {
                return in_array($role->name, ['Administrator', 'Academic Admin','Assesment Admin','HOD','Teacher','Class Teacher']);
            });

            $levelFilter = function ($query) use ($modeResolver, $resolvedContext, $type) {
                $query->whereIn('level', $modeResolver->levelsForAssessmentContext($resolvedContext, $type));
            };

            if ($hasAdminRole) {
                $classes = Klass::with(['students', 'grade'])
                    ->where('term_id', $selectedTermId)
                    ->whereHas('grade', $levelFilter)
                    ->get();
            } else {
                $directSubordinateIds = $user->subordinates->pluck('id')->toArray();
                $teacherIds = array_merge([$user->id], $directSubordinateIds);
                $classes = Klass::with(['students', 'grade'])
                    ->where('term_id', $selectedTermId)
                    ->whereHas('grade', $levelFilter)
                    ->whereIn('user_id', $teacherIds)
                    ->get();
            }

            $tests = collect();
            if ($subject) {
                $tests = Test::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('type', 'CA')
                    ->ordered()
                    ->get();
            }
            
            $classes = $classes->sort(function ($a, $b) {
                preg_match('/^(\d+)([A-Za-z]*)/', $a->name, $aMatches);
                preg_match('/^(\d+)([A-Za-z]*)/', $b->name, $bMatches);
                
                if (empty($aMatches) && empty($bMatches)) {
                    return strcmp($a->name, $b->name);
                }
                
                if (empty($aMatches)) return 1;
                if (empty($bMatches)) return -1;
                
                $aNum = (int)($aMatches[1] ?? 0);
                $bNum = (int)($bMatches[1] ?? 0);
                
                if ($aNum !== $bNum) {
                    return $aNum - $bNum;
                }
                
                $aLetter = $aMatches[2] ?? '';
                $bLetter = $bMatches[2] ?? '';
                return strcmp($aLetter, $bLetter);
            });
            
            $view = $this->assessmentIndexViewForContext($resolvedContext);

            return view($view, [
                'classes' => $classes,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
                'tests' => $tests,
                'type' => $type,
                'assessmentContext' => $resolvedContext,
            ]);
        } catch (Exception $e) {
            Log::error('Error in Assessment Index: ' . $e->getMessage());
            return redirect()->back()->with('message', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function getKlassesForTerm(Request $request) {
        $termId = $request->term_id;
        $user = Auth::user();
        $hasRole = $user->roles->contains(function ($role) {
            return in_array($role->name, ['Administrator', 'Academic Admin', 'HOD', 'Assessment Admin','Class Teacher','Teacher']);
        });

        $modeResolver = $this->schoolModeResolver();
        $mode = $modeResolver->mode();
        $assessmentContext = $modeResolver->resolveAssessmentContext($request->query('context'), $mode)
            ?? $modeResolver->resolveAssessmentContext(session('assessment_gradebook_context'), $mode);

        if ($assessmentContext === null && !$modeResolver->isCombinedMode($mode)) {
            $assessmentContext = $modeResolver->defaultAssessmentContext($mode);
        }

        $klasses = CacheHelper::getKlassesForTerm($termId, $user, $hasRole, $assessmentContext, $mode);
        return response()->json($klasses);
    }

    public function classAssessmentList(Request $request, $classId, $termId){
        try {
            $modeResolver = $this->schoolModeResolver();
            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            $currentTerm = Term::findOrFail($selectedTermId);
            
            $class = Klass::with(['students' => function ($query) {
                $query->orderBy('first_name', 'asc');
            }])->forTermYear($termId)->findOrFail($classId);
            
            $school_data = SchoolSetup::first();
            $gradeId = $class->grade_id;
            $level = $modeResolver->levelForKlass($class);
            $driver = $modeResolver->assessmentDriverForLevel($level);
            $assessmentContext = $modeResolver->resolveAssessmentContext($request->query('context'), $modeResolver->mode())
                ?? $this->assessmentContextForDriver($driver);

            $view = match ($driver) {
                'primary' => 'assessment.primary.primary-assessment-class-list',
                'junior' => 'assessment.junior.cjss-assessment-class-list',
                default => 'assessment.senior.senior-assessment-class-list',
            };

            $allTests = Test::where('grade_id', $gradeId)->where('term_id',  $termId)->where('type', 'CA')->ordered()->get();
            $tests = $allTests
                ->groupBy('sequence')
                ->map(fn($group) => $group->first())
                ->values();
            
            return view($view, [
                'class' => $class,
                'school_data' => $school_data,
                'tests' => $tests,
                'currentTerm' => $currentTerm,
                'assessmentContext' => $assessmentContext,
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return back()->with('error', 'Unable to find the class with the provided information.');
        }
    }

    public function assessmentMarkbook(Request $request, $term_id = null){
        if ($term_id !== null && is_numeric($term_id)) {
            session(['selected_term_id' => (int) $term_id]);
        }

        $modeResolver = $this->schoolModeResolver();
        $mode = $modeResolver->mode();
        $selectedTermId = session('selected_term_id') ?? optional(TermHelper::getCurrentTerm())->id;
        $accessibleContexts = collect($modeResolver->accessibleMarkbookContexts(Auth::user(), $selectedTermId, $mode))->values();
        $requestedContext = $modeResolver->resolveAssessmentContext($request->query('context'), $mode)
            ?? $modeResolver->resolveAssessmentContext(session('assessment_markbook_context'), $mode);

        if ($accessibleContexts->isEmpty()) {
            abort(403);
        }

        if ($requestedContext !== null) {
            abort_unless($accessibleContexts->contains($requestedContext), 403);
            return redirect()->route($modeResolver->markbookRouteName($requestedContext, $mode));
        }

        if (!$modeResolver->isCombinedMode($mode)) {
            return redirect()->route($modeResolver->markbookRouteName($accessibleContexts->first(), $mode));
        }

        if ($accessibleContexts->count() === 1) {
            return redirect()->route($modeResolver->markbookRouteName($accessibleContexts->first(), $mode));
        }

        $contexts = $accessibleContexts
            ->map(function (string $context) use ($modeResolver) {
                return [
                    'key' => $context,
                    'label' => $modeResolver->markbookContextLabel($context),
                    'description' => $modeResolver->markbookContextDescription($context),
                    'url' => $modeResolver->markbookUrl($context),
                ];
            })
            ->values();

        return view('assessment.shared.markbook-selector', [
            'contexts' => $contexts,
            'type' => $mode,
        ]);
    }

    public function primaryMarkbook(Request $request)
    {
        return $this->renderMarkbook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY);
    }

    public function juniorMarkbook(Request $request)
    {
        return $this->renderMarkbook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR);
    }

    public function seniorMarkbook(Request $request)
    {
        return $this->renderMarkbook($request, SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR);
    }

    private function renderMarkbook(Request $request, string $context)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $modeResolver = $this->schoolModeResolver();
        $schoolMode = $modeResolver->mode();

        try {
            if (!$currentTerm) {
                throw new Exception("Current term is not set.");
            }

            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $resolvedContext = $modeResolver->resolveAssessmentContext($context, $schoolMode);

            if ($resolvedContext === null) {
                abort(404);
            }

            $user = Auth::user();

            abort_unless(
                $modeResolver->canAccessMarkbookContext($user, $resolvedContext, $selectedTermId, $schoolMode),
                403
            );

            session(['assessment_markbook_context' => $resolvedContext]);

            $hasRole = $user->roles->contains(function ($role) {
                return in_array($role->name, ['Administrator', 'Academic Admin', 'HOD', 'Assessment Admin']);
            });

            $allowedLevels = $modeResolver->levelsForAssessmentContext($resolvedContext, $schoolMode);
            $supportsOptionals = collect($allowedLevels)->contains(
                fn (string $level) => $modeResolver->supportsOptionals($level, $schoolMode)
            );

            $levelFilter = function ($query) use ($allowedLevels) {
                $query->whereIn('level', $allowedLevels);
            };

            $klassSubjectsQuery = KlassSubject::with(['klass.grade', 'subject.subject', 'teacher', 'klass.students'])
                ->where('term_id', $selectedTermId)
                ->whereHas('klass.grade', $levelFilter)
                ->whereHas('subject.subject', function ($query) {
                    $query->where('components', 0);
                })
                ->orderBy('klass_id', 'asc');

            if ($hasRole) {
                $klassSubjects = $klassSubjectsQuery->get()
                    ->groupBy(fn (KlassSubject $klassSubject) => optional($klassSubject->subject?->subject)->name ?? 'Unknown Subject');

                if ($supportsOptionals) {
                    $optionalSubjects = OptionalSubject::with(['students', 'gradeSubject.subject', 'grade'])
                        ->where('term_id', $selectedTermId)
                        ->whereHas('grade', $levelFilter)
                        ->orderBy('name', 'asc')
                        ->get()
                        ->groupBy(fn (OptionalSubject $optionalSubject) => optional($optionalSubject->gradeSubject?->subject)->name ?? 'Unknown Subject');
                } else {
                    $optionalSubjects = collect();
                }
            } else {
                $directSubordinateIds = $user->subordinates->pluck('id')->toArray();
                $teacherIds = array_merge([$user->id], $directSubordinateIds);

                $klassSubjects = $klassSubjectsQuery
                    ->where(function ($q) use ($teacherIds) {
                        $q->whereIn('user_id', $teacherIds)
                          ->orWhereIn('assistant_user_id', $teacherIds);
                    })
                    ->get()
                    ->groupBy(fn (KlassSubject $klassSubject) => optional($klassSubject->subject?->subject)->name ?? 'Unknown Subject');

                if ($supportsOptionals) {
                    $optionalSubjects = OptionalSubject::with(['students', 'gradeSubject.subject', 'grade'])
                        ->where('term_id', $selectedTermId)
                        ->whereHas('grade', $levelFilter)
                        ->where(function ($q) use ($teacherIds) {
                            $q->whereIn('user_id', $teacherIds)
                              ->orWhereIn('assistant_user_id', $teacherIds);
                        })
                        ->orderBy('name', 'asc')
                        ->get()
                        ->groupBy(fn (OptionalSubject $optionalSubject) => optional($optionalSubject->gradeSubject?->subject)->name ?? 'Unknown Subject');
                } else {
                    $optionalSubjects = collect();
                }
            }

            $terms = StudentController::terms();
            $schoolSetup = SchoolSetup::first();

            $viewData = [
                'klass_subjects' => $klassSubjects,
                'schoolSetup' => $schoolSetup,
                'currentTerm' => $currentTerm,
                'terms' => $terms,
                'schoolType' => $schoolSetup,
                'assessmentContext' => $resolvedContext,
                'optional_subjects' => $optionalSubjects,
            ];

            $viewName = $this->markbookIndexViewForContext($resolvedContext);

            return view($viewName, $viewData);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function testStudents(Request $request, $subjectId){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        if (!$subjectId || !is_numeric($subjectId)) {
            return redirect()->back()->withErrors('Invalid subject ID provided.');
        }

        try {
            $klassSubject = KlassSubject::with(['klass', 'teacher', 'assistantTeacher', 'klass.students' => function ($query) use ($selectedTermId) {
                $query->wherePivot('term_id', $selectedTermId)->orderBy('first_name', 'asc');
            }])->where('term_id', $selectedTermId)->where('id', $subjectId)->firstOrFail();

            $this->authorize('enterMarks', $klassSubject);
            $schoolType = SchoolSetup::first();

            $level = $this->schoolModeResolver()->levelForKlass($klassSubject->klass);
            $assessmentContext = $this->schoolModeResolver()->resolveAssessmentContext($request->query('context'), $this->schoolModeResolver()->mode())
                ?? $this->assessmentContextForDriver($this->schoolModeResolver()->assessmentDriverForLevel($level));
            $viewName = match ($this->schoolModeResolver()->assessmentDriverForLevel($level)) {
                'primary' => 'assessment.primary.markbook-primary-class-list',
                'junior' => 'assessment.junior.markbook-junior-class-list',
                'senior' => 'assessment.senior.markbook-senior-class-list',
                default => 'assessment.shared.markbook-class-list',
            };

            // Get threshold settings for score highlighting
            $thresholdService = app(ThresholdSettingsService::class);
            $thresholdSettings = $thresholdService->getEffectiveThreshold(
                $klassSubject->klass->grade_id ?? null,
                $klassSubject->grade_subject_id ?? null,
                null
            );

            return view($viewName, [
                'klass' => $klassSubject,
                'schoolType' => $schoolType,
                'thresholdSettings' => $thresholdSettings,
                'assessmentContext' => $assessmentContext,
            ]);
        } catch (AuthorizationException $e) {
            $context = $this->schoolModeResolver()->resolveAssessmentContext($request->query('context'), $this->schoolModeResolver()->mode())
                ?? $this->schoolModeResolver()->resolveAssessmentContext(session('assessment_markbook_context'), $this->schoolModeResolver()->mode());
            Log::warning('Unauthorized class markbook access attempt.', [
                'user_id' => auth()->id(),
                'subject_id' => $subjectId,
                'term_id' => $selectedTermId,
                'message' => $e->getMessage(),
            ]);

            $message = 'You are not authorized to access this class markbook.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect($this->schoolModeResolver()->markbookUrl($context))->withErrors($message);
        } catch (Exception $e) {
            Log::error('Error fetching class students: ' . $e->getMessage());
            return redirect()->back()->withErrors('An unexpected error occurred. Please try again later.');
        }
    }

    public function optionalStudents(Request $request, $subjectId){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        if (!$subjectId || !is_numeric($subjectId)) {
            return redirect()->back()->withErrors('Invalid subject ID provided.');
        }

        try {
            $query = OptionalSubject::where('term_id', $selectedTermId)
                ->where('id', $subjectId)
                ->with(['teacher', 'assistantTeacher', 'grade', 'gradeSubject.grade', 'students' => function ($query) {
                    $query->orderBy('first_name');
                }]);

            $optionalSubject = $query->firstOrFail();

            $this->authorize('assessOptions', $optionalSubject);
            $schoolType = SchoolSetup::first();
            $level = $this->schoolModeResolver()->levelForGrade($optionalSubject->grade);
            $assessmentContext = $this->schoolModeResolver()->resolveAssessmentContext($request->query('context'), $this->schoolModeResolver()->mode())
                ?? $this->assessmentContextForDriver($this->schoolModeResolver()->assessmentDriverForLevel($level));

            // Get threshold settings for score highlighting
            $thresholdService = app(ThresholdSettingsService::class);
            $thresholdSettings = $thresholdService->getEffectiveThreshold(
                $optionalSubject->grade_id ?? null,
                $optionalSubject->grade_subject_id ?? null,
                null
            );

            return view('assessment.shared.markbook-option-list', [
                'klass' => $optionalSubject,
                'schoolType' => $schoolType,
                'thresholdSettings' => $thresholdSettings,
                'assessmentContext' => $assessmentContext,
            ]);
        } catch (AuthorizationException $e) {
            $context = $this->schoolModeResolver()->resolveAssessmentContext($request->query('context'), $this->schoolModeResolver()->mode())
                ?? $this->schoolModeResolver()->resolveAssessmentContext(session('assessment_markbook_context'), $this->schoolModeResolver()->mode());
            Log::warning('Unauthorized optional markbook access attempt.', [
                'user_id' => auth()->id(),
                'subject_id' => $subjectId,
                'term_id' => $selectedTermId,
                'message' => $e->getMessage(),
            ]);

            $message = 'You are not authorized to access this optional subject markbook.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect($this->schoolModeResolver()->markbookUrl($context))->withErrors($message);
        } catch (Exception $e) {
            Log::error('Error fetching optional students: ' . $e->getMessage());
            return redirect()->back()->withErrors('An unexpected error occurred. Please try again later.');
        }
    }


    public function updateTerm(Request $request){
        $termId = $request->input('termId');
        session(['selected_term_id' => $termId]);
        return response()->json(['success' => true]);
    }

    public function fetchClassSubjects(Request $request){
        try {
            $modeResolver = $this->schoolModeResolver();
            $assessmentContext = $modeResolver->resolveAssessmentContext($request->query('context'), $modeResolver->mode())
                ?? $modeResolver->resolveAssessmentContext(session('assessment_markbook_context'), $modeResolver->mode());
            $transformed_data = CacheHelper::getClassSubjects($assessmentContext, $modeResolver->mode());
            return response()->json($transformed_data);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }

    public function generateRemarksForStudent($studentId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->where('type', 'Exam');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($studentId);

        if ($student->tests->count() < 7) {
            return;
        }

        $totalPoints = $this->calculateTotalPoints($studentId);
        $possibleComments = DB::table('comment_banks')
            ->where('min_points', '<=', $totalPoints)
            ->where('max_points', '>=', $totalPoints)
            ->pluck('body');

        if ($possibleComments->isEmpty()) {
            $classTeacherComment = 'No remarks available.';
            $schoolHeadComment   = 'No remarks available.';
        } else {
            $classTeacherComment = $possibleComments->random();
            if ($possibleComments->count() === 1) {
                $schoolHeadComment = $classTeacherComment;
            } else {
                $maxRetries        = 5;
                $retryCount        = 0;
                $schoolHeadComment = $possibleComments->random();

                while (($schoolHeadComment === $classTeacherComment) && ($retryCount < $maxRetries)) {
                    $schoolHeadComment = $possibleComments->random();
                    $retryCount++;
                }
            }
        }

        $overallComment = $student->overallComments->where('term_id', $selectedTermId)->first();
        $classTeacherFinal = $classTeacherComment ?? 'No remarks provided.';
        $schoolHeadFinal   = $schoolHeadComment ?? 'No remarks provided.';

        if ($overallComment) {
            $overallComment->update([
                'class_teacher_remarks' => $classTeacherFinal,
                'school_head_remarks'   => $schoolHeadFinal,
            ]);
        } else {
            $student->overallComments()->create([
                'term_id'               => $selectedTermId,
                'class_teacher_remarks' => $classTeacherFinal,
                'school_head_remarks'   => $schoolHeadFinal,
                'klass_id'              => $student->currentClass()->id,
                'user_id'               => auth()->id(),
                'year'                  => date('Y'),
            ]);
        }
    }

    public function calculateTotalPoints($studentId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            }
        ])->findOrFail($studentId);

        $subjects = $student->tests->pluck('subject')->unique();
        $isForeigner = $student->nationality !== 'Motswana';
        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
            $student,
            $subjects,
            $selectedTermId,
            $isForeigner,
            'Exam' 
        );

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        return $totalPoints;
    }

    public function checkRecalculationProgress($id, Request $request){
        $subjectType = $request->input('subject_type');
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()?->id);

        $progressKey = "recalc_progress_{$id}_{$subjectType}_{$selectedTermId}";
        $progress = Cache::get($progressKey);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No active recalculation found.'
            ], 404);
        }

        return response()->json($progress);
    }

    public function recalculateGradesForGrade($id, Request $request){
        try {
            $klass = Klass::find($id);
            if (!$klass) {
                return back()->with('error', 'Class not found.');
            }

            $subjectType = trim($request->input('subject_type', ''));
            $allowedTypes = ['klass_subjects', 'optional_subjects'];
            
            if (empty($subjectType) || !in_array($subjectType, $allowedTypes, true)) {
                Log::error('Invalid subject type received:', [
                    'received' => $subjectType,
                    'allowed' => $allowedTypes,
                    'class_id' => $id,
                    'user_id' => auth()->id()
                ]);
                return back()->with('error', 'Invalid subject type selected. Please try again.');
            }

            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return back()->with('error', 'No active term found. Please contact administrator.');
            }
            
            $selectedTermId = session('selected_term_id', $currentTerm->id);
            $progressKey = "recalc_progress_{$id}_{$subjectType}_{$selectedTermId}";
            Cache::put($progressKey, [
                'job_id' => uniqid('recalc_', true),
                'percentage' => 0,
                'status' => 'queued',
                'message' => 'Job queued, starting soon...',
                'updated_at' => now()->toIso8601String(),
            ], 7200);

            RecalculateGrades::dispatch($id, $subjectType, $selectedTermId, auth()->id());
            Log::info('Grade recalculation job dispatched successfully', [
                'class_id' => $id,
                'grade_id' => $klass->grade_id,
                'subject_type' => $subjectType,
                'term_id' => $selectedTermId,
                'initiated_by' => auth()->id()
            ]);
            $subjectTypeName = $subjectType === 'klass_subjects' ? 'class subjects' : 'optional subjects';

            $message = "Grade recalculation for {$subjectTypeName} has been started in the background. " .
                  "You can continue working while the system processes the grades. " .
                  "This may take several minutes.";

            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            $context = $this->schoolModeResolver()->resolveAssessmentContext($request->query('context'), $this->schoolModeResolver()->mode())
                ?? $this->schoolModeResolver()->resolveAssessmentContext(session('assessment_markbook_context'), $this->schoolModeResolver()->mode());

            return redirect($this->schoolModeResolver()->markbookUrl($context))->with('message',
            "Grade recalculation for {$subjectTypeName} has been started in the background. " .
            "You can continue working while the system processes the grades. " .
            "This may take several minutes."
        );

        } catch (Exception $e) {
            Log::error('Failed to dispatch RecalculateGrades job', [
                'class_id' => $id,
                'subject_type' => $subjectType ?? 'unknown',
                'error' => $e->getMessage(),
                'initiated_by' => auth()->id()
            ]);

            return back()->with('error', 'Failed to start the recalculation process. Please try again.');
        }
    }

    public function updateMarks(Request $request){
        $request->validate([
            'students.*.tests.*.score'  => 'nullable|integer|min:0',
            'students.*.tests.*.out_of' => 'required|integer|min:1',
            'scope_type' => 'required|string|in:klass_subject,optional_subject',
            'scope_id' => 'required|integer|min:1',
        ]);

        $studentsData = (array) $request->input('students', []);
        $scopeType = (string) $request->input('scope_type');
        $scopeId = (int) $request->input('scope_id');

        [$scope, $subjectId, $termId, $year] = $this->resolveMarkbookSaveScope($request, $scopeType, $scopeId);

        $studentIds = collect(array_keys($studentsData))
            ->map(fn ($studentId) => (int) $studentId)
            ->filter(fn ($studentId) => $studentId > 0)
            ->values();

        $testIds = collect($studentsData)
            ->flatMap(function ($studentData) {
                if (!isset($studentData['tests']) || !is_array($studentData['tests'])) {
                    return [];
                }

                return array_keys($studentData['tests']);
            })
            ->map(fn ($testId) => (int) $testId)
            ->filter(fn ($testId) => $testId > 0)
            ->unique()
            ->values();

        $tests = Test::query()
            ->whereIn('id', $testIds)
            ->where('grade_subject_id', $subjectId)
            ->where('term_id', $termId)
            ->where('year', $year)
            ->get()
            ->keyBy('id');

        $existingScores = StudentTest::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('test_id', $testIds)
            ->get(['student_id', 'test_id', 'score'])
            ->mapWithKeys(function (StudentTest $studentTest) {
                return [
                    $this->studentTestSnapshotKey((int) $studentTest->student_id, (int) $studentTest->test_id) => $studentTest->score === null
                        ? null
                        : (int) $studentTest->score,
                ];
            })
            ->all();

        $auditDelta = [
            'students_touched' => [],
            'tests_affected' => [],
            'entered_count' => 0,
            'updated_count' => 0,
            'cleared_count' => 0,
        ];

        DB::beginTransaction();
        $errors = [];

        try {
            foreach ($studentsData as $studentId => $studentData) {
                $studentId = (int) $studentId;
                $student   = Student::find($studentId);
                $fullName  = $student->fullName ?? 'Unknown Student';

                if (!isset($studentData['tests']) || !is_array($studentData['tests'])) {
                    $errors[] = "Invalid test data for {$fullName}.";
                    continue;
                }

                $rowsToUpdate = [];
                $commentsToSave = [];

                foreach ($studentData['tests'] as $testId => $testData) {
                    $testId = (int) $testId;

                    if (!$tests->has($testId)) {
                        $errors[] = "Invalid test {$testId} for {$fullName}.";
                        continue;
                    }

                    if (!isset($testData['out_of']) || !is_numeric($testData['out_of'])) {
                        $errors[] = "Invalid 'out_of' value for test {$testId} ({$fullName}).";
                        continue;
                    }

                    $outOf = (int) $testData['out_of'];
                    $newScore = array_key_exists('score', $testData) && $testData['score'] !== '' && $testData['score'] !== null
                        ? (int) $testData['score']
                        : null;

                    $snapshotKey = $this->studentTestSnapshotKey($studentId, $testId);
                    $oldScore = $existingScores[$snapshotKey] ?? null;

                    if ($newScore !== null) {
                        $score = $newScore;
                        if ($score > $outOf) {
                            $errors[] = "{$fullName} – score for test {$testId} " .
                                        "cannot exceed {$outOf}.";
                            continue;
                        }
    
                        $percentage = round($score / $outOf * 100);
                        $gradeObj   = $this->getGradePerSubject($subjectId, $percentage);
    
                        $rowsToUpdate[$testId] = [
                            'score'      => $score,
                            'percentage' => $percentage,
                            'grade'      => $gradeObj->grade ?? null,
                            'points'     => $gradeObj->points ?? 0,
                        ];

                        $test = $tests->get($testId);
                        if ($test && $test->type === 'Exam') {
                            if ($comment = AssessmentHelper::getRandomCommentForScore($percentage)) {
                                $commentsToSave[] = [
                                    'student_id'        => $studentId,
                                    'test_id'           => $testId,
                                    'grade_subject_id'  => $subjectId,
                                    'term_id'           => $termId,
                                    'year'              => $year,
                                    'user_id'           => auth()->id(),
                                    'remarks'           => $comment,
                                ];
                            }
                        }
                    } else {
                        $rowsToUpdate[$testId] = [
                            'score'      => null,
                            'percentage' => null,
                            'grade'      => null,
                            'points'     => 0,
                        ];
                    }

                    $transition = $this->determineMarkChangeType($oldScore, $newScore);
                    if ($transition !== null) {
                        $auditDelta['students_touched'][$studentId] = true;
                        $auditDelta['tests_affected'][$testId] = true;
                        $auditDelta[$transition . '_count']++;
                    }
                }

                foreach ($rowsToUpdate as $testId => $data) {
                    StudentTest::updateOrCreate(
                        ['student_id' => $studentId, 'test_id' => $testId],
                        $data
                    );
                }

                foreach ($commentsToSave as $comment) {
                    $st = StudentTest::where('student_id', $comment['student_id'])
                                    ->where('test_id',    $comment['test_id'])
                                    ->first();

                    if (!$st) { continue; }

                    $exists = SubjectComment::where([
                                    ['student_test_id', $st->id],
                                    ['grade_subject_id',$comment['grade_subject_id']],
                                    ['student_id',      $comment['student_id']],
                                    ['term_id',         $comment['term_id']],
                                    ['year',            $comment['year']],
                                ])->exists();
    
                    if (!$exists) {
                        SubjectComment::create(array_merge($comment, ['student_test_id' => $st->id]));
                    }
                }
    
                $allCATests = StudentTest::where('student_id', $studentId)
                    ->whereHas('test', function ($q) use ($subjectId, $termId, $year) {
                        $q->where('type', 'CA')
                          ->where('grade_subject_id', $subjectId)
                          ->where('term_id', $termId)
                          ->where('year', $year);
                    })
                    ->get();
                
                $studentCATests = $allCATests->whereNotNull('score');
                
                if ($studentCATests->isNotEmpty()) {
                    $avgPerc = round($studentCATests->avg('percentage'));
                    $avgGradeObj = $this->getGradePerSubject($subjectId, $avgPerc);
                    
                    $updateData = [
                        'avg_score' => $avgPerc,
                        'avg_grade' => $avgGradeObj->grade ?? null,
                    ];
                } else {
                    $updateData = [
                        'avg_score' => null,
                        'avg_grade' => null,
                    ];
                }
    
                StudentTest::where('student_id', $studentId)
                    ->whereHas('test', function ($q) use ($subjectId, $termId, $year) {
                        $q->where('type', 'CA')
                          ->where('grade_subject_id', $subjectId)
                          ->where('term_id', $termId)
                          ->where('year', $year);
                    })->update($updateData);
            }
    
            foreach (array_keys($studentsData) as $sid) {
                $this->generateRemarksForStudent($sid);
            }
    
            DB::commit();

            $auditPayload = $this->buildMarksSavedAuditPayload(
                $scopeType,
                $scope,
                $tests,
                $auditDelta,
                $termId,
                $year
            );

            if ($auditPayload !== null) {
                $this->storeMarksSavedAuditLog($request, $scopeType, $scopeId, $subjectId, $termId, $year, $auditPayload);
            }

            $message = 'Marks updated successfully!';
            return back()->with('message', $message);
    
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('updateMarks() failed', ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            return back()->with('message', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    private function resolveMarkbookSaveScope(Request $request, string $scopeType, int $scopeId): array
    {
        if ($scopeType === 'klass_subject') {
            $scope = KlassSubject::with(['klass', 'gradeSubject.subject', 'term'])->findOrFail($scopeId);
            $this->authorize('enterMarks', $scope);
        } else {
            $scope = OptionalSubject::with(['gradeSubject.subject', 'term'])->findOrFail($scopeId);
            $this->authorize('assessOptions', $scope);
        }

        $subjectId = (int) $scope->grade_subject_id;
        $termId = (int) $scope->term_id;
        $year = (int) ($scope->term->year ?? $request->input('year'));

        $this->validateMarkbookSaveMatchesScope($request, $subjectId, $termId, $year);

        return [$scope, $subjectId, $termId, $year];
    }

    private function validateMarkbookSaveMatchesScope(Request $request, int $subjectId, int $termId, int $year): void
    {
        $messages = [];

        if ($request->filled('subject') && (int) $request->input('subject') !== $subjectId) {
            $messages['subject'] = 'The selected subject does not match the loaded markbook.';
        }

        if ($request->filled('term') && (int) $request->input('term') !== $termId) {
            $messages['term'] = 'The selected term does not match the loaded markbook.';
        }

        if ($request->filled('year') && (int) $request->input('year') !== $year) {
            $messages['year'] = 'The selected year does not match the loaded markbook.';
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function studentTestSnapshotKey(int $studentId, int $testId): string
    {
        return $studentId . ':' . $testId;
    }

    private function determineMarkChangeType(?int $oldScore, ?int $newScore): ?string
    {
        if ($oldScore === null && $newScore !== null) {
            return 'entered';
        }

        if ($oldScore !== null && $newScore === null) {
            return 'cleared';
        }

        if ($oldScore !== null && $newScore !== null && $oldScore !== $newScore) {
            return 'updated';
        }

        return null;
    }

    private function buildMarksSavedAuditPayload(
        string $scopeType,
        KlassSubject|OptionalSubject $scope,
        $tests,
        array $auditDelta,
        int $termId,
        int $year
    ): ?array {
        $studentIds = array_keys($auditDelta['students_touched']);
        $testIds = array_keys($auditDelta['tests_affected']);

        if (empty($studentIds) || empty($testIds)) {
            return null;
        }

        $scopeBadges = $this->markbookScopeBadges($scopeType, $scope, $studentIds);
        $formattedTests = $this->formatAuditList(
            $tests->only($testIds)
                ->map(fn (Test $test) => $this->formatAuditTestLabel($test))
                ->values()
                ->all()
        );

        return [
            'scope_type' => $scopeType,
            'scope_id' => $scope->id,
            'subject_name' => $scope->gradeSubject?->subject?->name ?? 'Unknown Subject',
            'term_id' => $termId,
            'year' => $year,
            'tests_affected' => count($testIds),
            'students_touched' => count($studentIds),
            'entered_count' => $auditDelta['entered_count'],
            'updated_count' => $auditDelta['updated_count'],
            'cleared_count' => $auditDelta['cleared_count'],
            'test_labels' => $tests->only($testIds)
                ->map(fn (Test $test) => $this->formatAuditTestLabel($test))
                ->values()
                ->all(),
            'summary_badges' => array_values(array_filter([
                ...$scopeBadges,
                'Subject: ' . ($scope->gradeSubject?->subject?->name ?? 'Unknown Subject'),
                'Tests: ' . $formattedTests,
                'Students: ' . count($studentIds),
                sprintf(
                    'Changes: Entered %d, Updated %d, Cleared %d',
                    $auditDelta['entered_count'],
                    $auditDelta['updated_count'],
                    $auditDelta['cleared_count']
                ),
            ])),
        ];
    }

    private function markbookScopeBadges(string $scopeType, KlassSubject|OptionalSubject $scope, array $studentIds): array
    {
        if ($scopeType === 'klass_subject') {
            return [
                'Class: ' . ($scope->klass?->name ?? 'Unknown Class'),
            ];
        }

        $classNames = DB::table('student_optional_subjects as sos')
            ->join('klasses as k', 'k.id', '=', 'sos.klass_id')
            ->where('sos.optional_subject_id', $scope->id)
            ->whereIn('sos.student_id', $studentIds)
            ->distinct()
            ->orderBy('k.name')
            ->pluck('k.name')
            ->all();

        return array_values(array_filter([
            'Optional: ' . ($scope->name ?? 'Unknown Optional Subject'),
            !empty($classNames) ? 'Classes: ' . $this->formatAuditList($classNames) : null,
        ]));
    }

    private function formatAuditTestLabel(Test $test): string
    {
        if ($test->type === 'CA') {
            return 'CA ' . $test->sequence;
        }

        if (!empty($test->name)) {
            return $test->name;
        }

        return trim(($test->type ?? 'Test') . ' ' . ($test->sequence ?? ''));
    }

    private function formatAuditList(array $items, int $limit = 3): string
    {
        $items = array_values(array_unique(array_filter($items, fn ($item) => is_string($item) && trim($item) !== '')));

        if (empty($items)) {
            return 'None';
        }

        $visibleItems = array_slice($items, 0, $limit);
        $remaining = count($items) - count($visibleItems);

        $summary = implode(', ', $visibleItems);

        if ($remaining > 0) {
            $summary .= ' +' . $remaining . ' more';
        }

        return $summary;
    }

    private function storeMarksSavedAuditLog(
        Request $request,
        string $scopeType,
        int $scopeId,
        int $subjectId,
        int $termId,
        int $year,
        array $auditPayload
    ): void {
        try {
            Logging::create([
                'location' => SchoolSetupController::getLocationByIp($request->ip()),
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'url' => $request->url(),
                'method' => $request->method(),
                'input' => json_encode([
                    'scope_type' => $scopeType,
                    'scope_id' => $scopeId,
                    'subject' => $subjectId,
                    'term' => $termId,
                    'year' => $year,
                ]),
                'changes' => json_encode([
                    'action' => 'Marks Saved',
                    'data' => $auditPayload,
                ]),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to store marks audit log', [
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'subject' => $subjectId,
                'term' => $termId,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getGradePerSubject($subjectId, $percentage){
        $selected_term_id = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $grade = GradingScale::where('term_id', $selected_term_id)->where('grade_subject_id', $subjectId)->where('min_score', '<=', $percentage)->where('max_score', '>=', $percentage)->first();
        return $grade;
    }

    public function saveSelectedSubject(Request $request){
        $request->validate([
            'selectedSubjectId' => 'required|integer',
        ]);
        session(['selectedSubjectId' => $request->selectedSubjectId]);
        return response()->json(['message' => 'Selected subject saved']);
    }

    protected function getGrade($percentage){
        $grade = GradingScale::where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->firstOrFail();
        return $grade;
    }

    public static function getOverallGrade($grade_id, $percentage){
        $grade = OverallGradingMatrix::where('grade_id', $grade_id)
            ->where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->first();

        if ($grade === null) {
            return null;
        }
        return $grade;
    }

    public function updateComment(Request $request, $id){
        try {
            $student = Student::findOrFail($id);
            $class_teacher = $student->currentClass()->teacher->id;
            $currentTerm = $student->currentClass()->term->id;
            $currentYear = $student->currentClass()->term->year;
    
            $request->validate([
                'class_teacher' => 'nullable|string|max:255',
                'head_teacher' => 'nullable|string|max:255',
            ]);
    
            $hasScores = $this->studentHasScoresInTerm($student, $currentTerm);
            if (!$hasScores) {
                return redirect()->back()->withErrors([
                    'error' => 'Cannot save comments for a student who has no exam scores entered for this term. Please ensure exam scores are entered before adding comments.'
                ]);
            }
    
            $nextStudentId = $request->input('nextStudentId');
            $submitType = $request->input('submitType');
    
            DB::transaction(function () use ($request, $student, $class_teacher, $currentTerm, $currentYear) {
                $comment = Comment::where('student_id', $student->id)
                    ->where('term_id', $currentTerm)
                    ->where('year', $currentYear)
                    ->first();
    
                if ($comment) {
                    $comment->update([
                        'class_teacher_remarks' => $request->input('class_teacher'),
                        'school_head_remarks' => $request->input('head_teacher'),
                    ]);
                } else {
                    Comment::create([
                        'student_id' => $student->id,
                        'klass_id' => $student->currentClass()->id,
                        'user_id' => $class_teacher,
                        'term_id' => $currentTerm,
                        'class_teacher_remarks' => $request->input('class_teacher'),
                        'school_head_remarks' => $request->input('head_teacher'),
                        'year' => $currentYear,
                    ]);
                }
            });
    
            if ($submitType === 'saveNext' && $nextStudentId) {
                return redirect()->route('assessment.comments', ['id' => $nextStudentId]);
            }
            return redirect()->back()->with('message', 'Comment updated successfully');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function overallComments($id){
        $school_data = SchoolSetup::first();
        $modeResolver = $this->schoolModeResolver();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $student = Student::with('criteriaBasedStudentTests')->findOrFail($id);
        $class = $student->currentClassRelation->first();
        $gradeId = $class->grade_id;
        $classId = $class->id;
        $driver = $modeResolver->assessmentDriverForLevel($modeResolver->levelForKlass($class));

        $averagePercentage = null;
        $overallGrade = null;
        $totalPoints = null;
        $grade = null;

        if ($driver === 'primary') {
            $averageAndGrade = $this->calculateAveragePercentageAndGrade($id);
            $averagePercentage = $averageAndGrade['averagePercentage'];
            $overallGrade = $averageAndGrade['overallGrade'];
        } elseif ($driver === 'junior') {
            $subjects = $student->tests->where('term_id', $selectedTermId)->pluck('subject')->unique();
            $isForeigner = $student->nationality !== 'Motswana';
            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $grade = $this->determineGrade($totalPoints, $class);
        } else {
            $subjects = $student->tests->where('term_id', $selectedTermId)->pluck('subject')->unique();
            $allPoints = [];

            foreach ($subjects as $subject) {
                $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
                $allPoints[] = $points;
            }

            rsort($allPoints);
            $bestSixPoints = array_slice($allPoints, 0, 6);
            $totalPoints = array_sum($bestSixPoints);
            $grade = $this->determineGrade($totalPoints, $class);
        }

        $comment_bank = CacheHelper::getCommentBank();

        $user = Auth::user();
        $hasRole = $user->roles->contains(function ($role) {
            return in_array($role->name, ['Administrator', 'Academic Admin', 'HOD', 'Assessment Admin']);
        });

        if ($hasRole) {
            $klassSubjects = KlassSubject::where('grade_id', $gradeId)
                ->where('term_id', $selectedTermId)
                ->where('klass_id', $classId)
                ->get();
        } else {
            $klassSubjects = KlassSubject::where('grade_id', $gradeId)
                ->where('term_id', $selectedTermId)
                ->where('user_id', $user->id)
                ->where('klass_id', $classId)
                ->get();
        }

        $assessments = CriteriaBasedStudentTest::where('student_id', $student->id)
            ->where('term_id', $class->term_id)
            ->where('klass_id', $class->id)
            ->where('grade_id', $class->grade_id)
            ->get()->keyBy(function ($item) {
                return $item->grade_subject_id . '-' . $item->component_id . '-' . $item->criteria_based_test_id;
            });

        $gradeSubjects = GradeSubject::with(['components', 'gradeOptionSets.gradeOptions', 'criteriaBasedTests'])
            ->where('term_id', $class->term_id)
            ->where('grade_id', $gradeId)
            ->get();

        $allStudents = $class->students()
            ->wherePivot('term_id', $class->term_id)
            ->get(['students.id', 'students.first_name'])
            ->pluck('id')
            ->unique()
            ->map(static fn ($studentId) => (int) $studentId)
            ->values()
            ->all();

        $currentIndex = array_search((int) $student->id, $allStudents, true);

        if ($currentIndex === false) {
            $allStudents[] = (int) $student->id;
            $allStudents = array_values(array_unique($allStudents));
            $currentIndex = array_search((int) $student->id, $allStudents, true);
        }

        $nextStudentId = $allStudents[$currentIndex + 1] ?? null;

        $class_teacher_comment = $student->overallComments->where('term_id', $selectedTermId)->first()->class_teacher_remarks ?? '';
        $school_head_comment = $student->overallComments->where('term_id', $selectedTermId)->first()->school_head_remarks ?? '';

        return view('assessment.shared.overall-remarks', [
            'student' => $student,
            'comments' => $comment_bank,
            'klassSubjects' => $klassSubjects,
            'klass' => $class,
            'assessments' => $assessments,
            'gradeSubjects' => $gradeSubjects,
            'averagePercentage' => $averagePercentage,
            'overallGrade' => $overallGrade,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'nextStudentId' => $nextStudentId,
            'currentIndex' => $currentIndex,
            'totalStudents' => count($allStudents),
            'school_data' => $school_data,
            'driver' => $driver,

            'class_teacher_comment' => $class_teacher_comment,
            'school_head_comment' => $school_head_comment,
        ]);
    }

    private function studentHasScoresInTerm(Student $student, $termId){
        $hasExamScores = DB::table('student_tests as st')
            ->join('tests as t', 'st.test_id', '=', 't.id')
            ->where('st.student_id', $student->id)
            ->where('t.term_id', $termId)
            ->where('t.type', 'Exam') 
            ->where('st.deleted_at', null)
            ->where('t.deleted_at', null)
            ->whereNotNull('st.score')
            ->exists();

        $school_data = SchoolSetup::first();

        if ($this->schoolModeResolver()->assessmentDriverForLevel(
            $this->schoolModeResolver()->levelForStudent($student, $termId)
        ) === 'primary') {
            $hasCriteriaScores = DB::table('criteria_based_student_tests as cbst')
                ->join('criteria_based_tests as cbt', 'cbst.criteria_based_test_id', '=', 'cbt.id')
                ->where('cbst.student_id', $student->id)
                ->where('cbst.term_id', $termId)
                ->where('cbt.type', 'Exam')
                ->whereNotNull('cbst.grade_option_id')
                ->exists();
            
            return $hasExamScores || $hasCriteriaScores;
        }

        return $hasExamScores;
    }

    protected function calculateAveragePercentageAndGrade($id){
        $student = Student::with(['tests', 'overallComments'])->findOrFail($id);
        $currentClass = $student->currentClass();
        $klassSubjects = $currentClass->subjectClasses()->with('subject')->get();

        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $totalScore = 0;
        $totalOutOf = 0;

        foreach ($klassSubjects as $klassSubject) {
            $gradeSubject = $klassSubject->subject;

            $examTest = $student->tests()
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $gradeSubject->id)
                ->where('type', 'Exam')
                ->first();

            if ($examTest) {
                $totalScore += $examTest->pivot->score;
                $totalOutOf += $examTest->out_of;
            }
        }

        $averagePercentage = $totalOutOf > 0 ? ($totalScore / $totalOutOf) * 100 : 0;
        $overallGrade = AssessmentController::getOverallGrade($student->currentClass()->grade->id, round($averagePercentage, 1));

        return [
            'averagePercentage' => $averagePercentage,
            'overallGrade' => $overallGrade
        ];
    }

    function optionalSubjectRemarks($studentId, $id, $studentIds, $index){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $student = Student::findOrFail($studentId);

        $option = OptionalSubject::find($id);
        if (!$option) {
            abort(404, 'Optional Subject not found');
        }

        $subjectExam = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $option->gradeSubject->id)
            ->where('type', 'Exam')
            ->first();

        $comments = CacheHelper::getSubjectsComments();
        return view('assessment.shared.optional-subject-comment', [
            'student' => $student,
            'comments' => $comments,
            'klass' => $option,
            'subjectExam' => $subjectExam,
            'studentIds' => $studentIds,
            'index' => $index
        ]);
    }

    public function coreSubjectRemarks($studentId, $id, $studentIds, $index){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $student = Student::findOrFail($studentId);
        $core_subject = KlassSubject::findOrFail($id);

        $subjectExam = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $core_subject->subject->id)
            ->where('type', 'Exam')
            ->first();

        $comments = CacheHelper::getSubjectsComments();
        return view('assessment.shared.core-subjects-comment', [
            'student' => $student,
            'comments' => $comments,
            'klass' => $core_subject,
            'subjectExam' => $subjectExam,
            'studentIds' => $studentIds,
            'index' => $index
        ]);
    }

    public function newSubjectRemark(Request $request){
        try {
            $validatedData = $request->validate([
                'student_id'       => 'required|integer',
                'grade_subject_id' => 'required|integer',
                'term_id'          => 'required|integer',
                'year'             => 'required|integer',
                'user_id'          => 'required|integer',
                'klass_id'         => 'required|integer',
                'remarks'          => 'required|string|max:255',
            ]);

            $studentId  = $validatedData['student_id'];
            $subjectId  = $validatedData['grade_subject_id'];
            $termId     = $validatedData['term_id'];
            $year       = $validatedData['year'];
            $teacherId  = $validatedData['user_id'];
            $klassId    = $validatedData['klass_id'];
            $remarks    = $validatedData['remarks'];

            $studentIds = explode(',', $request->input('student_ids', ''));
            $index      = $request->input('index', 0);
            $context    = $this->schoolModeResolver()->resolveAssessmentContext($request->input('context'), $this->schoolModeResolver()->mode());

            DB::beginTransaction();
            $studentTests = StudentTest::where('student_id', $studentId)
                ->whereHas('tests', function ($query) use ($subjectId, $termId, $year) {
                    $query->where('type', 'Exam')
                        ->where('grade_subject_id', $subjectId)
                        ->where('term_id', $termId)
                        ->where('year', $year);
                })
                ->lockForUpdate()
                ->get();

            if ($studentTests->isEmpty()) {
                Log::error('Student test does not exist for student_id: ' . $studentId);
                DB::rollBack();
                return redirect()->back()->with('error', 'Sorry, marks are not entered for this student!');
            }

            $testId = $studentTests->pluck('id')->first();
            $subjectComment = SubjectComment::where('student_test_id', $testId)
                ->where('student_id', $studentId)
                ->where('grade_subject_id', $subjectId)
                ->lockForUpdate()
                ->first();

            $data = [
                'term_id' => $termId,
                'year'    => $year,
                'user_id' => $teacherId,
                'remarks' => $remarks,
            ];

            if ($subjectComment) {
                $subjectComment->update($data);
            } else {
                SubjectComment::create(array_merge($data, [
                    'student_test_id'   => $testId,
                    'student_id'        => $studentId,
                    'grade_subject_id'  => $subjectId,
                ]));
            }

            DB::commit();

            if ($request->input('action') == 'save_and_next' && $index < count($studentIds) - 1) {
                $nextStudentId = $studentIds[$index + 1];
                return redirect()->route('assessment.core-subject-remarks', [
                    'studentId'   => $nextStudentId,
                    'id'          => $klassId,
                    'studentIds'  => implode(',', $studentIds),
                    'index'       => $index + 1,
                    'context'     => $context,
                ]);
            }

            return redirect()->back()->with('message', 'Remark saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving subject remark: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while saving the remark.');
        }
    }


    public function newOptionalSubjectRemark(Request $request){
        try {
            $validatedData = $request->validate([
                'student_id'       => 'required|integer',
                'grade_subject_id' => 'required|integer',
                'term_id'          => 'required|integer',
                'year'             => 'required|integer',
                'user_id'          => 'required|integer',
                'klass_id'         => 'required|integer',
                'remarks'          => 'required|string|max:255',
            ]);

            $studentId  = $validatedData['student_id'];
            $subjectId  = $validatedData['grade_subject_id'];
            $termId     = $validatedData['term_id'];
            $year       = $validatedData['year'];
            $teacherId  = $validatedData['user_id'];
            $klassId    = $validatedData['klass_id'];
            $remarks    = $validatedData['remarks'];

            $studentIds = explode(',', $request->input('student_ids', ''));
            $index      = $request->input('index', 0);
            $context    = $this->schoolModeResolver()->resolveAssessmentContext($request->input('context'), $this->schoolModeResolver()->mode());

            DB::beginTransaction();

            $studentTests = StudentTest::where('student_id', $studentId)
                ->whereHas('tests', function ($query) use ($subjectId, $termId, $year) {
                    $query->where('type', 'Exam')
                        ->where('grade_subject_id', $subjectId)
                        ->where('term_id', $termId)
                        ->where('year', $year);
                })
                ->lockForUpdate()
                ->get();

            if ($studentTests->isEmpty()) {
                Log::error('Student test does not exist for student_id: ' . $studentId);
                DB::rollBack();
                return redirect()->back()->with('error', 'Sorry, marks are not entered for this student!');
            }

            $testId = $studentTests->pluck('id')->first();
            $subjectComment = SubjectComment::where('student_test_id', $testId)
                ->where('student_id', $studentId)
                ->where('grade_subject_id', $subjectId)
                ->lockForUpdate()
                ->first();

            $data = [
                'term_id' => $termId,
                'year'    => $year,
                'user_id' => $teacherId,
                'remarks' => $remarks,
            ];

            if ($subjectComment) {
                $subjectComment->update($data);
            } else {
                SubjectComment::create(array_merge($data, [
                    'student_test_id'   => $testId,
                    'student_id'        => $studentId,
                    'grade_subject_id'  => $subjectId,
                ]));
            }

            DB::commit();

            if ($request->input('action') == 'option_save_and_next' && $index < count($studentIds) - 1) {
                $nextStudentId = $studentIds[$index + 1];
                return redirect()->route('assessment.optional-subject-remarks', [
                    'studentId'   => $nextStudentId,
                    'id'          => $klassId,
                    'studentIds'  => implode(',', $studentIds),
                    'index'       => $index + 1,
                    'context'     => $context,
                ]);
            }

            return redirect()->back()->with('message', 'Remark saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving optional subject remark: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while saving the remark.');
        }
    }

    function pdfReportCard1($id){
        $student = Student::findOrFail($id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $reportCard = PDF::loadView('assessment.shared.report-card', ['student' => $student, 'school_setup' => $school_setup, 'school_head' => $school_head]);
        return $reportCard->stream('student-report-card.pdf');
    }

    private function calculateGradeRankings($gradeId, $selectedTermId){
        $students = Student::whereHas('classes', function ($query) use ($gradeId, $selectedTermId) {
            $query->whereHas('grade', function ($query) use ($gradeId) {
                $query->where('grades.id', $gradeId);
            })->where('klass_student.term_id', $selectedTermId);
        })->with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');
            }
        ])->get();

        $rankings = [];
        foreach ($students as $student) {
            $subjects = $student->tests->pluck('subject')->unique();
            $isForeigner = $student->nationality !== 'Motswana';
            
            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
                $student, 
                $subjects, 
                $selectedTermId, 
                $isForeigner, 
                'Exam'
            );
            
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            
            $rankings[] = [
                'id' => $student->id,
                'totalPoints' => $totalPoints,
                'student_name' => $student->getFullNameAttribute(),
                'class_name' => $student->currentClass() ? $student->currentClass()->name : 'N/A'
            ];
        }

        usort($rankings, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });

        return $rankings;
    }

    private function getStudentGradePosition($gradeRankings, $studentId){
        foreach ($gradeRankings as $index => $ranking) {
            if ($ranking['id'] == $studentId) {
                return $index + 1;
            }
        }
        
        return null;
    }

    private function calculateGradeAverage($gradeRankings){
        if (empty($gradeRankings)) {
            return 0;
        }

        $totalPoints = array_sum(array_column($gradeRankings, 'totalPoints'));
        return $totalPoints / count($gradeRankings);
    }

    public function emailReportCard(Request $request){
        try {
            $request->validate([
                'studentId' => 'required|integer',
                'to' => 'required|email',
                'subject' => 'required|string',
                'message' => 'required|string',
            ]);

            $student = Student::findOrFail($request->studentId);
            $currentTerm = TermHelper::getCurrentTerm();
            $reportCard = $this->prepareReportCardPdfForStudent($student, $currentTerm);
            $pdf = $reportCard['pdf'];
            $filename = $reportCard['filename'];

            Mail::send('emails.report-card-email', ['messageContent' => $request->message], function ($mail) use ($request, $pdf, $filename) {
                $mail->to($request->to)
                    ->subject($request->subject)
                    ->attachData($pdf->output(), $filename);
            });

            $this->storeReportCardEmailMetadata($student, $request->subject, $request->message, $filename, auth()->id(), $currentTerm->id, 'direct');

            return response()->json(['message' => 'Email sent successfully']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while sending the email'], 500);
        }
    }

    public function prepareReportCardPdfForStudent(Student $student, ?Term $currentTerm = null): array
    {
        $currentTerm ??= TermHelper::getCurrentTerm();

        if (!$currentTerm) {
            throw new \Exception('No active term found for report card generation.');
        }

        $studentTerm = $student->studentTerms()
            ->where('term_id', $currentTerm->id)
            ->where('status', Student::STATUS_CURRENT)
            ->firstOrFail();

        $currentGrade = Grade::query()->findOrFail($studentTerm->grade_id);
        $level = $this->schoolModeResolver()->levelForGrade($currentGrade);
        $criteriaBasedTestController = app(CriteriaBasedTestController::class);

        $pdfResult = match ($level) {
            SchoolSetup::LEVEL_PRE_PRIMARY => $criteriaBasedTestController->generateRECPrimaryReportCardPDF($student->id),
            SchoolSetup::LEVEL_PRIMARY => $this->generatePrimaryReportCardPDF($student->id),
            SchoolSetup::LEVEL_JUNIOR => $this->generateJuniorReportCardPDF($student->id),
            SchoolSetup::LEVEL_SENIOR => $this->generateReportCardSeniorPDF($student->id),
            default => throw new \Exception("Unsupported report card level: {$level}"),
        };

        if (is_array($pdfResult)) {
            return [
                'pdf' => $pdfResult['pdf'],
                'filename' => $pdfResult['filename'],
            ];
        }

        return [
            'pdf' => $pdfResult,
            'filename' => strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf'),
        ];
    }

    private function storeReportCardEmailMetadata(Student $student, string $subject, string $message, string $filename, int $senderId, int $termId, string $type): void
    {
        Email::create([
            'term_id' => $termId,
            'sender_id' => $senderId,
            'sponsor_id' => $student->sponsor_id,
            'receiver_type' => 'sponsor',
            'subject' => $subject,
            'body' => $message,
            'attachment_path' => $filename,
            'status' => 'sent',
            'num_of_recipients' => 1,
            'type' => $type,
        ]);
    }

    private function schoolModeResolver(): SchoolModeResolver
    {
        return app(SchoolModeResolver::class);
    }

    private function primaryReportCardBuilder(): PrimaryReportCardBuilder
    {
        return app(PrimaryReportCardBuilder::class);
    }

    private function assessmentIndexViewForContext(string $context): string
    {
        return match ($context) {
            SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY => 'assessment.primary.primary-index',
            SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR => 'assessment.senior.senior-index',
            default => 'assessment.junior.junior-index',
        };
    }

    private function markbookIndexViewForContext(string $context): string
    {
        return match ($context) {
            SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY => 'assessment.primary.markbook-primary',
            default => 'assessment.junior.markbook-junior',
        };
    }

    private function assessmentContextForDriver(string $driver): string
    {
        return match ($driver) {
            'primary' => SchoolModeResolver::ASSESSMENT_CONTEXT_PRIMARY,
            'senior' => SchoolModeResolver::ASSESSMENT_CONTEXT_SENIOR,
            default => SchoolModeResolver::ASSESSMENT_CONTEXT_JUNIOR,
        };
    }

    public function generatePrimaryReportCardPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildStudentReport((int) $id, $selectedTermId, 0);

        $pdf = PDF::loadView('assessment.primary.primary-report-card-pdf', $data);
        $student = $data['student'];
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    public function generateJuniorReportCardPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $gradeId = $currentClass->grade->id;
        $currentClassId = $currentClass->id;

        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $studentRankings = $this->calculateClassRankings($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $classPosition = $this->getStudentPosition($studentRankings, $id);

        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $gradePosition = $this->getStudentGradePosition($gradeRankings, $id);
        $totalStudentsInGrade = count($gradeRankings);

        $nextTermStartDate = $this->getNextTermStartDate($currentTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $scores = [];
        $isForeigner = $student->nationality !== 'Motswana';

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        $grade = $this->determineGrade($totalPoints, $currentClass);

        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                ->where('term_id', $selectedTermId)
                ->where('klass_id', $currentClassId)
                ->first();

            $teacher = null;
            $teacherName = 'N/A';

            if ($klassSubject && $klassSubject->user_id) {
                $teacher = User::find($klassSubject->user_id);
                $teacherName = $teacher ? $teacher->lastname : 'N/A';
            } else {
                $studentOptionalSubject = DB::table('student_optional_subjects')
                    ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                    ->where('student_optional_subjects.student_id', $student->id)
                    ->where('student_optional_subjects.term_id', $selectedTermId)
                    ->where('student_optional_subjects.klass_id', $currentClassId)
                    ->where('optional_subjects.grade_subject_id', $subject->id)
                    ->first();

                if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                    $teacher = User::find($studentOptionalSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                }
            }

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points ?? 0,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'teacher' => $teacherName,
            ];
        }

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,

            'classPosition' => $classPosition,
            'classAverage' => round($classAverage, 2),
            'totalStudentsInClass' => count($allStudents),

            'gradePosition' => $gradePosition,
            'gradeAverage' => round($gradeAverage, 2),
            'totalStudentsInGrade' => $totalStudentsInGrade,
            'gradeName' => $currentClass->grade->name,

            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,

            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,

            'position' => $classPosition,
        ];

        $pdf = PDF::loadView('assessment.junior.report-card-pdf-junior', $data);
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    protected function calculateClassAverageFromRankings($rankings){
        $totalPoints = array_sum(array_column($rankings, 'totalPoints'));
        $numberOfStudents = count($rankings);
        return $numberOfStudents > 0 ? $totalPoints / $numberOfStudents : 0;
    }

    protected function getStudentPositionSenior($studentRankings, $studentId){
        foreach ($studentRankings as $index => $student) {
            if ($student['studentId'] == $studentId) {
                return $index + 1;
            }
        }
        return 'N/A';
    }

    protected function calculateClassRankingsSenior($students, $selectedTermId){
        $studentTotals = [];

        foreach ($students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $scores = $this->calculateStudentScoresSenior($student, $selectedTermId);
            $totalSlots = 0;
            $totalPoints = 0;

            foreach ($scores as $score) {
                $slotsNeeded = $score['slotsNeeded'];
                if ($totalSlots + $slotsNeeded <= 6) {
                    $totalSlots += $slotsNeeded;
                    if (strtolower($score['subject']) == 'double science') {
                        $totalPoints += $score['points'] * 2;
                    } else {
                        $totalPoints += $score['points'];
                    }
                }
                if ($totalSlots >= 6) {
                    break;
                }
            }

            $studentTotals[] = [
                'studentId' => (string)$student->id,
                'totalPoints' => $totalPoints,
            ];
        }

        usort($studentTotals, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });

        return $studentTotals;
    }


    protected function calculateStudentScoresSenior($student, $selectedTermId, $classId = null){
        $scores = [];
        $subjects = $student->tests->where('term_id', $selectedTermId)->pluck('subject')->unique('id');
        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        if (!$classId) {
            $currentClass = $student->currentClass();
            $classId = $currentClass ? $currentClass->id : null;
        }

        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->where('term_id', $selectedTermId)
                ->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)
                ->where('type', 'CA')
                ->where('term_id', $selectedTermId)
                ->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)
                ->where('term_id', $selectedTermId)
                ->first();

            $teacherName = 'N/A';
            if ($classId) {
                $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $classId)
                    ->first();

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $classId)
                        ->where('optional_subjects.grade_subject_id', $subject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }
            }

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
            $slotsNeeded = ($subjectName == 'double science') ? 2 : 1;

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points,
                'slotsNeeded' => $slotsNeeded,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        usort($scores, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        return $scores;
    }

    public function generateReportCardSeniorPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $nextTermStartDate = $this->getNextTermStartDate($currentTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        $classId = $currentClass ? $currentClass->id : null;

        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
            $teacherName = 'N/A';
            if ($classId) {
                $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $classId)
                    ->first();

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $classId)
                        ->where('optional_subjects.grade_subject_id', $subject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }
            }

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        $scoresForCalculation = $scores;

        foreach ($scoresForCalculation as &$score) {
            if (strtolower($score['subject']) == 'double science') {
                $score['slotsNeeded'] = 2;
            } else {
                $score['slotsNeeded'] = 1;
            }
        }
        unset($score);

        usort($scoresForCalculation, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $totalSlots = 0;
        $totalPoints = 0;
        $bestSubjects = [];

        foreach ($scoresForCalculation as $score) {
            $slotsNeeded = $score['slotsNeeded'];
            if ($totalSlots + $slotsNeeded <= 6) {
                $bestSubjects[] = $score;
                $totalSlots += $slotsNeeded;
                if (strtolower($score['subject']) == 'double science') {
                    $totalPoints += $score['points'] * 2;
                } else {
                    $totalPoints += $score['points'];
                }
            }
            if ($totalSlots >= 6) {
                break;
            }
        }

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPositionSenior($studentRankings, $student->id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
        $absentDays = $manualEntry && $manualEntry->days_absent !== null
            ? $manualEntry->days_absent
            : $student->absentDays()->where('term_id', $selectedTermId)->count();

        $school_fees = $manualEntry && $manualEntry->school_fees_owing !== null
            ? $manualEntry->school_fees_owing
            : null;
        $other_info = $manualEntry && $manualEntry->other_info !== null
            ? $manualEntry->other_info
            : null;

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
            'absentDays' => $absentDays,
            'school_fees' => $school_fees,
            'otherInfo' => $other_info
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-pdf-senior', $data);
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    public function archiveEmailReportCards(Request $request){
        try {
            $request->validate([
                'classId' => 'required|integer',
                'archiveSubject' => 'required|string',
                'archiveMessage' => 'required|string',
            ]);

            $class = Klass::findOrFail($request->classId);
            $schoolSetup = SchoolSetup::first();
            $schoolEmail = $schoolSetup->email_address ?? null;

            if (!$schoolEmail) {
                throw new \Exception("School email not set in SchoolSetup");
            }

            $juniorAssessmentController = app(JuniorAssessmentController::class);
            $seniorAssessmentController = app(SeniorAssessmentController::class);
            $primaryAssessmentController = app(PrimaryAssessmentController::class);
            $criteriaBasedTestController = app(CriteriaBasedTestController::class);
            $classLevel = $this->schoolModeResolver()->levelForKlass($class);

            $currentTerm = TermHelper::getCurrentTerm();
            $pdf = match ($classLevel) {
                SchoolSetup::LEVEL_PRE_PRIMARY => $criteriaBasedTestController->generateEmailRECClassListReportCards($request->classId),
                SchoolSetup::LEVEL_PRIMARY => $primaryAssessmentController->generateEmailPrimaryClassListReportCards($request->classId),
                SchoolSetup::LEVEL_JUNIOR => $juniorAssessmentController->generateEmailJuniorClassListReportCards($request->classId),
                SchoolSetup::LEVEL_SENIOR => $seniorAssessmentController->generateEmailSeniorClassListReportCards($request->classId),
                default => throw new \Exception("Unsupported class level: {$classLevel}"),
            };

            $filename = strtolower($class->name . '_term_' . $currentTerm->term . '_report_cards.pdf');
            $emailData = [
                'messageContent' => $request->archiveMessage,
                'schoolName' => $schoolSetup->school_name,
                'schoolAddress' => $schoolSetup->physical_address,
                'schoolContact' => $schoolSetup->telephone,
                'schoolLogo' => $schoolSetup->logo_path,
            ];

            Mail::send('emails.archive-report-cards', $emailData, function ($mail) use ($request, $pdf, $filename, $schoolEmail) {
                $mail->to($schoolEmail)
                    ->subject($request->archiveSubject)
                    ->attachData($pdf->output(), $filename);
            });

            $this->storeEmailMetadata($request, $filename);
            return response()->json(['message' => 'Report cards archived and sent successfully']);
        } catch (\Exception $e) {
            Log::error('Error archiving report cards: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function storeEmailMetadata(Request $request, $filename){
        $schoolHead = User::where('position', 'School Head')->first();
        $userId = $schoolHead ? $schoolHead->id : auth()->user()->id;

        Email::create([
            'term_id' => session('selected_term_id', TermHelper::getCurrentTerm()->id),
            'sender_id' => auth()->user()->id,
            'user_id' => $userId,
            'receiver_type' => 'archive',
            'subject' => $request->archiveSubject,
            'body' => $request->archiveMessage,
            'attachment_path' => $filename,
            'status' => 'sent',
            'num_of_recipients' => 1,
            'type' => 'Direct',
        ]);
    }

    public function bulkEmailReportCards(Request $request){
        try {
            $validated = $request->validate([
                'bulkSubject' => 'required|string',
                'bulkMessage' => 'required|string',
                'students' => 'required|array',
                'students.*' => 'integer',
            ]);

            $studentIds = collect($validated['students'] ?? [])
                ->filter(fn ($studentId) => is_numeric($studentId))
                ->map(fn ($studentId) => (int) $studentId)
                ->unique()
                ->values();

            if ($studentIds->isEmpty()) {
                return response()->json([
                    'message' => 'No valid students were selected for email delivery.',
                    'status' => 'error',
                ], 422);
            }

            $currentTerm = TermHelper::getCurrentTerm();
            $studentCount = $studentIds->count();
            $senderId = auth()->id();
            $queueThreshold = self::BULK_REPORT_CARD_QUEUE_THRESHOLD;

            if ($studentCount <= $queueThreshold) {
                $deliverySummary = $this->sendBulkReportCardsDirectly(
                    $studentIds->all(),
                    $validated['bulkSubject'],
                    $validated['bulkMessage'],
                    $currentTerm,
                    $senderId
                );

                $message = "Bulk report cards sent directly. {$deliverySummary['sent']} email(s) sent.";

                if ($deliverySummary['skipped'] > 0) {
                    $message .= " {$deliverySummary['skipped']} skipped due to missing parent email.";
                }

                if ($deliverySummary['failed'] > 0) {
                    $message .= " {$deliverySummary['failed']} failed during sending.";
                }

                return response()->json([
                    'message' => $message,
                    'status' => $deliverySummary['failed'] > 0 ? 'partial' : 'sent',
                    'count' => $deliverySummary['sent'],
                ]);
            }

            foreach ($studentIds as $studentId) {
                SendBulkReportCards::dispatch(
                    $studentId,
                    $validated['bulkSubject'],
                    $validated['bulkMessage'],
                    $currentTerm->id,
                    $senderId,
                );
            }
            return response()->json([
                'message' => "Bulk email jobs queued successfully. {$studentCount} email(s) will be processed in the background.",
                'status' => 'queued',
                'count' => $studentCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error queuing bulk emails: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while queuing emails: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    private function sendBulkReportCardsDirectly(array $studentIds, string $subject, string $message, Term $currentTerm, int $senderId): array
    {
        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($studentIds as $studentId) {
            try {
                $student = Student::with(['sponsor'])->find($studentId);

                if (!$student || !$student->sponsor || !$student->sponsor->email) {
                    $skippedCount++;
                    continue;
                }

                $reportCard = $this->prepareReportCardPdfForStudent($student, $currentTerm);

                Mail::send('emails.report-card-email', ['messageContent' => $message], function ($mail) use ($student, $subject, $reportCard) {
                    $mail->to($student->sponsor->email)
                        ->subject($subject)
                        ->attachData($reportCard['pdf']->output(), $reportCard['filename']);
                });

                $this->storeReportCardEmailMetadata(
                    $student,
                    $subject,
                    $message,
                    $reportCard['filename'],
                    $senderId,
                    $currentTerm->id,
                    'Bulk'
                );

                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error("Failed to send bulk report card directly for student ID {$studentId}: " . $e->getMessage());
            }
        }

        return [
            'sent' => $sentCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
        ];
    }

    private function calculateClassRankings($students, $selectedTermId){
        $rankings = [];
        foreach ($students as $student) {
            $subjects = $student->tests->pluck('subject')->unique();
            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $student->nationality !== 'Motswana', 'Exam');
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $rankings[] = [
                'id' => $student->id,
                'totalPoints' => $totalPoints
            ];
        }

        usort($rankings, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });
        return $rankings;
    }

    private function calculateClassAverage($rankings){
        $totalPoints = array_sum(array_column($rankings, 'totalPoints'));
        $numberOfStudents = count($rankings);
        return $numberOfStudents > 0 ? $totalPoints / $numberOfStudents : 0;
    }

    private function getStudentPosition($rankings, $studentId){
        $position = array_search($studentId, array_column($rankings, 'id'));
        return $position !== false ? $position + 1 : 'N/A';
    }


    #Used inside junior report cards
    private function calculateSubjectScores(Student $student, GradeSubject $subject, $selectedTermId){
        $examTest = $student->tests()->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', 'Exam')
            ->first();

        $caTest = $student->tests()->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', 'CA')
            ->first();

        $subjectComment = $student->subjectComments()->where('grade_subject_id', $subject->id)
            ->where('term_id', $selectedTermId)
            ->first();

        $examScore = $examTest ? $examTest->pivot->score : null;
        $examPercentage = $examTest ? $examTest->pivot->percentage : null;
        $examPoints = $examTest ? $examTest->pivot->points : null;
        $examGrade = $examTest ? $examTest->pivot->grade : null;

        $caAverage = $caTest ? $caTest->pivot->avg_score : null;
        $caAverageGrade = $caTest ? $caTest->pivot->avg_grade : null;

        return [
            'subject' => $subject->subject->name,
            'caAverage' => $caAverage,
            'score' => $examScore,
            'percentage' => $examPercentage,
            'points' => $examPoints,
            'grade' => $examGrade,
            'comments' => $subjectComment ? $subjectComment->remarks : 'No comments',
            'caAverageGrade' => $caAverageGrade,
        ];
    }

    private function calculateSubjectScoresAnalysis(Student $student, GradeSubject $subject, $selectedTermId, $grade, $type = 'Exam', $sequence = 1){
        $tests = $student->tests()
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('grade_id', $grade)
            ->where('sequence', $sequence)
            ->orderBy('sequence', 'asc')
            ->get();

        if ($type === 'Exam') {
            $test = $tests->first();
        } else {
            $test = $tests->first();
        }

        $score = $test ? $test->pivot->score : null;
        $percentage = $test ? $test->pivot->percentage : null;
        $points = $test ? $test->pivot->points : null;
        $grade = $test ? $test->pivot->grade : null;

        return [
            'subject' => $subject->subject->name,
            'score' => $score,
            'percentage' => $percentage,
            'points' => $points,
            'grade' => $grade,
        ];
    }

    private function calculatePoints($student, $subjects, $selectedTermId, $isForeigner, $type, $sequence = 1){
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];
    
        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId, $type, $sequence);
            
            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                    continue;
                }

                if (!$subject->type) {
                    $optionalPoints[] = $points;
                    continue;
                }

                $corePoints[] = $points;
                continue;
            }
    
            if ($subject->mandatory) {
                $mandatoryPoints += $points;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPoints[] = $points;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePoints[] = $points;
            }
        }
    
        rsort($optionalPoints);
        rsort($corePoints);
    
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
            $remainingOptionals = array_slice($optionalPoints, 2); 
        } else {
            $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
            $remainingOptionals = array_slice($optionalPoints, 1);
        }
    
        $combinedRemaining = array_merge($remainingOptionals, $corePoints);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        return [$mandatoryPoints, $bestOptionalPoints, $bestFromCombined];
    }
    
    private function getSubjectPoints($student, $subject, $selectedTermId, $type = 'Exam', $sequence = 1){
        $examTest = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('sequence', $sequence)
            ->first();

        if (!empty($examTest)) {
            return $examTest->pivot->points;
        }
        return 0;
    }


    private function getSubjectPointsCA($student, $subject, $selectedTermId, $type, $sequence){
        $caTest = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('sequence', $sequence)
            ->first();

        if (!empty($caTest)) {
            return $caTest->pivot->points;
        }
        return 0;
    }

    public function getNextAcademicYearStartDate(){
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        $nextYearFirstTerm = Term::where('year', $nextYear)
            ->orderBy('start_date', 'asc')
            ->first();

        return $nextYearFirstTerm ? $nextYearFirstTerm->start_date->toDateString() : null;
    }


    public function getNextTermStartDate($currentTerm){
        $nextTerm = Term::where('start_date', '>', $currentTerm->end_date)
            ->orderBy('start_date', 'asc')
            ->first();

        return $nextTerm ? $nextTerm->start_date : null;
    }

    private function determineGrade($totalPoints, $currentClass){
        return DB::table('overall_points_matrix')
            ->where('min', '<=', $totalPoints)
            ->where('max', '>=', $totalPoints)
            ->where('academic_year', $currentClass->grade->name)
            ->value('grade');
    }

    function htmlReportCard($id){
        $student = Student::with(['currentClassRelation', 'tests', 'overallComments', 'absentDays', 'subjectComments'])->findOrFail($id);
        $school_data = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        return view('assessment.shared.report-card-2', ['student' => $student, 'school_data' => $school_data, 'school_head' => $school_head]);
    }


    function showAssessment($id){
        $school_data = SchoolSetup::first();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $klass_subject = KlassSubject::with(['subject.tests.students.tests'])
            ->where('term_id', $selectedTermId)
            ->where('id', $id)
            ->first();

        if (!$klass_subject) {
            abort(404, 'Class subject not found.');
        }
        $sorted_tests = $klass_subject->subject->tests->sortBy(function ($test) {
            return ($test->type === 'CA' ? 0 : 1) * 1000 + $test->sequence;
        });

        $gradeDistributions = [];
        $total_students = [];  // Track total students per test for percentage calculations.
        foreach ($sorted_tests as $test) {
            $gradeDistributions[$test->id] = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0]
            ];
            $total_students[$test->id] = 0;
            foreach ($test->students as $student) {
                // Ensure the student and class are loaded and non-null
                if ($student && $student->class && $student->class->id == $klass_subject->klass_id) {
                    if ($student->tests && $test->subject && $test->subject->id == $klass_subject->subject->id) {
                        foreach ($student->tests as $studentTest) {
                            if ($studentTest && $studentTest->id == $test->id) {
                                $grade = $studentTest->pivot->grade;
                                $gender = $student->gender;  // Assuming there's a gender field in Student model
                                if (!isset($gradeDistributions[$test->id][$grade][$gender])) {
                                    $gradeDistributions[$test->id][$grade][$gender] = 0;
                                }
                                $gradeDistributions[$test->id][$grade][$gender]++;
                                $total_students[$test->id]++;
                            }
                        }
                    }
                }
            }
        }
        // Calculate percentages for combined grades
        foreach ($sorted_tests as $test) {
            $gradeDistributions[$test->id]['ABC%'] = array_sum([
                $gradeDistributions[$test->id]['A']['M'] + $gradeDistributions[$test->id]['A']['F'],
                $gradeDistributions[$test->id]['B']['M'] + $gradeDistributions[$test->id]['B']['F'],
                $gradeDistributions[$test->id]['C']['M'] + $gradeDistributions[$test->id]['C']['F'],
            ]) / max($total_students[$test->id], 1) * 100;  // Prevent division by zero

            $gradeDistributions[$test->id]['ABCD%'] = array_sum([
                $gradeDistributions[$test->id]['A']['M'] + $gradeDistributions[$test->id]['A']['F'],
                $gradeDistributions[$test->id]['B']['M'] + $gradeDistributions[$test->id]['B']['F'],
                $gradeDistributions[$test->id]['C']['M'] + $gradeDistributions[$test->id]['C']['F'],
                $gradeDistributions[$test->id]['D']['M'] + $gradeDistributions[$test->id]['D']['F'],
            ]) / max($total_students[$test->id], 1) * 100;
        }

        return view('assessment.shared.subject-analysis-report', [
            'klass_subject' => $klass_subject,
            'school_data' => $school_data,
            'tests' => $sorted_tests,
            'gradeDistributions' => $gradeDistributions
        ]);
    }

    function showGradeWideAssessmentByTeacher($id)
    {
        $school_data = SchoolSetup::first();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $klass_subject = KlassSubject::where('term_id', $selectedTermId)
            ->where('id', $id)
            ->first();

        if (!$klass_subject) {
            abort(404, 'Class subject not found.');
        }

        $klass_subjects = KlassSubject::with(['subject.tests.students.tests', 'teacher'])
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $klass_subject->grade_id)
            ->where('grade_subject_id', $klass_subject->grade_subject_id)
            ->get();

        $teacherGradeDistributions = [];
        foreach ($klass_subjects as $ks) {
            $teacher = $ks->teacher ? $ks->teacher->fullName : 'Unknown Teacher';
            $sorted_tests = $ks->subject->tests->sortBy(function ($test) {
                return ($test->type === 'CA' ? 0 : 1) * 1000 + $test->sequence;
            });

            foreach ($sorted_tests as $test) {
                $gradeDistributions = [
                    'A' => ['M' => 0, 'F' => 0],
                    'B' => ['M' => 0, 'F' => 0],
                    'C' => ['M' => 0, 'F' => 0],
                    'D' => ['M' => 0, 'F' => 0],
                    'total_students' => 0,
                    'name' => ''
                ];

                foreach ($test->students as $student) {
                    if ($student->tests && $test->subject->id == $ks->subject->id) {
                        foreach ($student->tests as $studentTest) {
                            if ($studentTest && $studentTest->id == $test->id) {
                                $grade = $studentTest->pivot->grade;
                                $gender = $student->gender; // Assuming there's a gender field in Student model
                                if (isset($gradeDistributions[$grade][$gender])) {
                                    $gradeDistributions[$grade][$gender]++;
                                    $gradeDistributions['total_students']++;
                                    $gradeDistributions['name'] = $test->name;
                                }
                            }
                        }
                    }
                }
                $total = $gradeDistributions['total_students'];
                if ($total > 0) {
                    $gradeDistributions['ABC%'] = ((
                        $gradeDistributions['A']['M'] + $gradeDistributions['A']['F'] +
                        $gradeDistributions['B']['M'] + $gradeDistributions['B']['F'] +
                        $gradeDistributions['C']['M'] + $gradeDistributions['C']['F']) / $total) * 100;

                    $gradeDistributions['ABCD%'] = ((
                        $gradeDistributions['A']['M'] + $gradeDistributions['A']['F'] +
                        $gradeDistributions['B']['M'] + $gradeDistributions['B']['F'] +
                        $gradeDistributions['C']['M'] + $gradeDistributions['C']['F'] +
                        $gradeDistributions['D']['M'] + $gradeDistributions['D']['F']) / $total) * 100;
                }
                $teacherGradeDistributions[$teacher][$test->id] = $gradeDistributions;
            }
        }

        return view('assessment.subject-analysis-report-by-teacher', [
            'school_data' => $school_data,
            'teacherGradeDistributions' => $teacherGradeDistributions,
            'klass_subject' => $klass_subject
        ]);
    }

    public function analyzePerformanceByDepartment($classId){
        $school_data = SchoolSetup::first();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $klass = Klass::findOrFail($classId);

        $graphData = [
            'departments' => [],
            'grades' => ['A', 'B', 'C', 'D', 'E', 'U'],
            'data' => []
        ];

        $tests = [
            'CA1' => [],
            'CA2' => [],
            'Exam' => []
        ];

        $klass_subjects = KlassSubject::with(['subject.department.departmentHead', 'subject', 'klass.students.tests'])
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $klass->grade_id)
            ->get();

        foreach ($klass_subjects as $ks) {
            $subject = $ks->subject;
            $department = $subject->department->name ?? 'Unknown Department';

            if (!isset($graphData['data'][$department])) {
                $graphData['data'][$department] = ['M' => [], 'F' => []];
                foreach ($graphData['grades'] as $grade) {
                    $graphData['data'][$department]['M'][$grade] = 0;
                    $graphData['data'][$department]['F'][$grade] = 0;
                }
            }

            foreach ($ks->klass->students as $student) {
                $studentTests = $student->tests()
                    ->where('term_id', $selectedTermId)
                    ->where('grade_subject_id', $subject->id)
                    ->whereIn('type', ['CA', 'Exam'])
                    ->get();

                foreach ($studentTests as $test) {
                    $testKey = $test->type === 'Exam' ? 'Exam' : 'CA' . $test->sequence;
                    if (!isset($tests[$testKey])) continue;

                    $grade = $test->pivot->grade ?? 'U';
                    if (!in_array($grade, $graphData['grades'])) {
                        $grade = 'U';
                    }

                    $gender = $student->gender === 'M' ? 'M' : 'F';

                    $graphData['data'][$department][$gender][$grade]++;

                    if (!isset($tests[$testKey][$department])) {
                        $departmentHead = $subject->department->departmentHead ? $subject->department->departmentHead->fullName : 'No Head Assigned';
                        $tests[$testKey][$department] = [
                            'departmentHead' => $departmentHead,
                            'subjects' => [],
                            'total_students' => 0,
                        ];

                        foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $gradeKey) {
                            $tests[$testKey][$department][$gradeKey] = ['M' => 0, 'F' => 0];
                        }

                        foreach ($subject->department->gradeSubjects as $gradeSubject) {
                            $tests[$testKey][$department]['subjects'][$gradeSubject->id] = $gradeSubject->subject->name;
                        }
                    }

                    $tests[$testKey][$department][$grade][$gender]++;
                    $tests[$testKey][$department]['total_students']++;
                }
            }
        }

        foreach ($tests as $testKey => &$departments) {
            foreach ($departments as $department => &$data) {
                $data['AB%'] = $this->calculatePercentageSplit($data, ['A','B']);
                $data['ABC%'] = $this->calculatePercentageSplit($data, ['A','B','C']);
                $data['ABCD%'] = $this->calculatePercentageSplit($data, ['A','B','C','D']);
                $data['DEU%'] = $this->calculatePercentageSplit($data, ['D','E','U']);
            }
        }

        if (request()->has('export')) {
            return Excel::download(
                new DepartmentPerformanceExport($tests),
                "Department_Performance_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.shared.subject-analysis-by-department', [
            'school_data' => $school_data,
            'tests' => $tests,
            'graphData' => $graphData,
        ]);
    }

    function calculatePercentageSplit($data, $grades){
        $sumM = 0;
        $sumF = 0;
        $total = $data['total_students'] ?? 0;

        foreach ($grades as $grade) {
            $sumM += $data[$grade]['M'] ?? 0;
            $sumF += $data[$grade]['F'] ?? 0;
        }

        $percentM = $total > 0 ? round(($sumM / $total)*100, 2) : 0;
        $percentF = $total > 0 ? round(($sumF / $total)*100, 2) : 0;

        return ['M' => $percentM, 'F' => $percentF];
    }

    function showGradeSubjectWideAssessmentByTeacher($id){
        $school_data = SchoolSetup::first();
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $klass_subject = KlassSubject::with('subject.tests')->where('id', $id)->first();

        if (!$klass_subject) {
            abort(404, 'Class subject not found.');
        }

        $klass_subjects = KlassSubject::with(['subject.tests.students.tests', 'teacher'])
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $klass_subject->grade_subject_id)
            ->where('grade_id', $klass_subject->grade_id)
            ->get();

        $tests = [];
        foreach ($klass_subjects as $ks) {
            foreach ($ks->subject->tests as $test) {
                $testId = $test->id;
                $tests[$testId]['name'] = $test->name;
                $tests[$testId]['type'] = $test->type;
                $tests[$testId]['sequence'] = $test->sequence;
                if (!isset($tests[$testId]['teachers'])) {
                    $tests[$testId]['teachers'] = [];
                }

                $teacher = $ks->teacher ? $ks->teacher->fullName : 'Unknown Teacher';
                if (!isset($tests[$testId]['teachers'][$teacher])) {
                    $tests[$testId]['teachers'][$teacher] = [
                        'A' => ['M' => 0, 'F' => 0],
                        'B' => ['M' => 0, 'F' => 0],
                        'C' => ['M' => 0, 'F' => 0],
                        'D' => ['M' => 0, 'F' => 0],
                        'U' => ['M' => 0, 'F' => 0], // Including 'U' in initialization
                        'ABC%' => 0,
                        'ABCD%' => 0,
                        'total_students' => 0
                    ];
                }

                foreach ($test->students as $student) {
                    if ($student->tests) {
                        foreach ($student->tests as $studentTest) {
                            if ($studentTest && $studentTest->id == $testId) {
                                $grade = $studentTest->pivot->grade;
                                $gender = $student->gender ?: 'U';  // Fallback to 'U' if gender is not set
                                if (isset($tests[$testId]['teachers'][$teacher][$grade][$gender])) {
                                    $tests[$testId]['teachers'][$teacher][$grade][$gender]++;
                                    $tests[$testId]['teachers'][$teacher]['total_students']++;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Sort tests, placing 'CA' based on 'sequence' and 'Exam' at the end
        uasort($tests, function ($a, $b) {
            if ($a['type'] == $b['type']) {
                return $a['sequence'] <=> $b['sequence'];
            }
            return ($a['type'] == 'Exam') ? 1 : -1;
        });

        return view('assessment.shared.subject-analysis-by-teacher', [
            'school_data' => $school_data,
            'tests' => $tests,
            'klass_subject' => $klass_subject
        ]);
    }


    function showReports($id){
        $klass = Klass::findOrFail($id);
        $school_setup = SchoolSetup::firstOrFail();
        return view('assessment.shared.report-card-list', ['klass' => $klass, 'school_setup' => $school_setup]);
    }

    function regionResultAnalysis($termId, $gradeId, $year){
        return view('assessment.shared.region-result-analysis', ['grades' => CacheHelper::getGrades(), 'termId' => $termId, 'gradeId' => $gradeId, 'year' => $year]);
    }

    private function calculateGradePercentages($gradeCounts, $totalStudents){
        $percentages = [];
        foreach ($gradeCounts as $key => $count) {
            $percentages[$key] = $totalStudents > 0 ? number_format(($count / $totalStudents) * 100, 0) : 0;
        }
        return $percentages;
    }

    public function overallStats($id){

        $gradeCounts = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'E' => 0,
        ];

        $klass = Klass::findOrFail($id);
        foreach ($klass->students as $student) {
            $examTests = $student->tests->where('type', 'Exam');

            $totalScore = $examTests->sum('pivot.score');
            $total_out_of = $examTests->sum('out_of');

            $average = $total_out_of > 0 ? $totalScore / $total_out_of : 0;

            $percentage = $average * 100;
            $percentage = ! 0 ? number_format($percentage, 0) : 0;

            $grade = $this->getOverallGrade($klass->grade->id, $percentage)->grade ?? '';

            if (isset($gradeCounts[$grade])) {
                $gradeCounts[$grade]++;
            } else {
                $gradeCounts[$grade] = 1;
            }
            $gradeCounts[$grade]++;
        }

        $totalStudents = array_sum($gradeCounts);
        $gradeCounts['AB'] = $gradeCounts['A'] + $gradeCounts['B'];
        $gradeCounts['ABC'] = $gradeCounts['AB'] + $gradeCounts['C'];
        $gradeCounts['DE'] = $gradeCounts['D'] + $gradeCounts['E'];

        $gradePercentages = $this->calculateGradePercentages($gradeCounts, $totalStudents);

        return view('assessment.shared.overall-grade-statistics-report', [
            'gradeCounts' => $gradeCounts,
            'gradePercentages' => $gradePercentages,
            'klass' => $klass,
            'totalStudents' => $totalStudents,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    function caAnalysis($termId, $year, $gradeId){
        $klass_subject = KlassSubject::with('klass.students')->where('term_id', $termId)->where('year', $year)->where('grade_id', $gradeId)->get();
        return view('analysis.analysis-by-ca', ['klass_subjects' => $klass_subject]);
    }


    public function htmlReportCard3($id){
        $student = Student::with(['class', 'tests', 'overallComments', 'absentDays', 'subjectComments'])
            ->findOrFail($id);

        $reportCard = $this->prepareReportCardData($student);
        $school_data = SchoolSetup::first();
        return view('assessment.shared.report-card-3', ['reportCard' => $reportCard, 'school_data' => $school_data]);
    }

    private function prepareReportCardData($student){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $term = Term::where('id', $selectedTermId)->first();
        $schoolDays = Holiday::calculateSchoolDays(Carbon::parse($term->start_date), Carbon::parse($term->end_date), $selectedTermId);
        $absentDays = $student->absentDays->count();
        $reportCardData = [
            'studentName' => $student->first_name . ' ' . $student->last_name,
            'className' => $student->class->name,
            'subjects' => [],
            'total' => [
                'possibleMarks' => 0,
                'actualMarks' => 0,
                'percentage' => 0,
                'grade' => 'N/A',
            ],
            'classTeacherRemarks' => '',
            'headTeacherRemarks' => '',
        ];

        $uniqueSubjects = $student->tests->where('assessment', 1)->pluck('subject')->unique('id');

        foreach ($uniqueSubjects as $subject) {
            $examTest = $student->tests->where('assessment', 1)
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->first();

            if ($examTest) {
                $possibleMarks = $examTest->out_of;
                $actualMarks = $examTest->pivot->score ?? 0;
                $percentage = $examTest->pivot->percentage ?? 0;
                $grade = $examTest->pivot->grade ?? 'N/A';
                $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

                $reportCardData['subjects'][] = [
                    'name' => $subject->subject->name,
                    'possibleMarks' => $possibleMarks,
                    'actualMarks' => $actualMarks,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'comments' => $subjectComment->remarks ?? 'N/A',
                ];

                $reportCardData['total']['possibleMarks'] += $possibleMarks;
                $reportCardData['total']['actualMarks'] += $actualMarks;
            }
        }

        if ($reportCardData['total']['possibleMarks'] > 0) {
            $reportCardData['total']['percentage'] = number_format($reportCardData['total']['actualMarks'] / $reportCardData['total']['possibleMarks'] * 100, 1);
            $avgPercentage = $reportCardData['total']['actualMarks'] / $reportCardData['total']['possibleMarks'] * 100;
            $reportCardData['total']['grade'] = AssessmentController::getOverallGrade($student->grade_id, $avgPercentage);
        }

        $reportCardData['classTeacherRemarks'] = $student->overallComments->where('term_id', $selectedTermId)->first()->class_teacher_remarks ?? '';
        $reportCardData['headTeacherRemarks'] = $student->overallComments->where('term_id', $selectedTermId)->first()->school_head_remarks ?? '';

        $classmates = Student::where('klass_id', $student->klass_id)->get();
        $totalStudents = $classmates->count();

        $classTotalScores = [];
        foreach ($classmates as $classmate) {
            $totalScore = $classmate->tests->where('assessment', 1)->sum('pivot.score');
            $classTotalScores[$classmate->id] = $totalScore;
        }

        arsort($classTotalScores);
        $studentPosition = array_search($student->id, array_keys($classTotalScores)) + 1;

        $classAverageScore = array_sum($classTotalScores) / $totalStudents;
        $classAverageGrade = $this->getOverallGrade($student->grade_id, $classAverageScore);

        $reportCardData['totalStudents'] = $totalStudents;
        $reportCardData['classPosition'] = $studentPosition;

        $reportCardData['classAverage'] = [
            'score' => $classAverageScore,
            'grade' => $classAverageGrade,
        ];


        $classTeacher = $student->class->teacher;
        $reportCardData['classTeacherSignaturePath'] = $classTeacher->signature_path ?? '';
        $reportCardData['classTeacherName'] = $classTeacher->fullName ?? 'N/A';

        $schoolHead = User::where('position', 'School Head')->first();
        $reportCardData['headTeacherSignaturePath'] = $schoolHead->signature_path ?? '';
        $reportCardData['headTeacherName'] = $schoolHead->fullName ?? 'N/A';
        $reportCardData['absentDays'] = "{$absentDays}/{$schoolDays}";

        return $reportCardData;
    }

    public function showGradeTermAnalysisReport($classId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $school_data = SchoolSetup::first();
        $klass = Klass::find($classId);
        $reportData = $this->prepareGradeTermAnalysisReport($klass->grade_id, $selectedTermId);

        return view('assessment.shared.overall-grade-assessment-report', ['reportData' => $reportData, 'school_data' => $school_data]);
    }

    public function prepareGradeTermAnalysisReport($gradeId, $selectedTermId){
        $students = Student::whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('id', $selectedTermId);
        })->with(['tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam')->where('assessment', 1);
            }])->get();

        $gradeSubjects = GradeSubject::with('subject')->where('grade_id', $gradeId)
            ->whereHas('tests', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->where('type', 'Exam')
                    ->where('assessment', 1);
            })->get();

        $reportData = [];
        $gradeCounts = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'ABC' => ['M' => 0, 'F' => 0],
            'DE' => ['M' => 0, 'F' => 0]
        ];

        foreach ($students as $student) {
            $studentData = [
                'name' => $student->first_name . ' ' . $student->last_name,
                'subjects' => [],
                'averageScore' => 0,
                'overallGrade' => 'N/A'
            ];

            $totalScore = 0;
            $countSubjects = 0;

            foreach ($gradeSubjects as $gradeSubject) {
                $test = $student->tests->where('grade_subject_id', $gradeSubject->id)->first();
                if ($test) {
                    $score = $test->pivot->percentage ?? 0;
                    $totalScore += $score;
                    $countSubjects++;

                    $studentData['subjects'][$gradeSubject->subject->name] = [
                        'score' => $score,
                        'grade' => $test->pivot->grade ?? 'N/A'
                    ];
                } else {
                    $studentData['subjects'][$gradeSubject->subject->name] = [
                        'score' => 'N/A',
                        'grade' => 'N/A'
                    ];
                }
            }
            if ($countSubjects > 0) {
                $averageScore = $totalScore / $countSubjects;
                $studentData['averageScore'] = $averageScore;
                if (is_null($test->grade_id)) {
                    $studentData['overallGrade'] = self::getOverallGrade($test->grade_id, round($averageScore))->grade ?? '';
                    continue;
                }
            } else {
                $studentData['averageScore'] = 0;
                $studentData['overallGrade'] = 'N/A';
            }

            $reportData[] = $studentData;
            $genderKey = strtoupper($student->gender) === 'M' ? 'M' : 'F';
            if (isset($gradeCounts[$studentData['overallGrade']])) {
                $gradeCounts[$studentData['overallGrade']][$genderKey]++;
            }

            if (in_array($studentData['overallGrade'], ['A', 'B', 'C'])) {
                $gradeCounts['ABC'][$genderKey]++;
            } elseif (in_array($studentData['overallGrade'], ['D', 'E'])) {
                $gradeCounts['DE'][$genderKey]++;
            }
        }

        $totalStudents = count($students);
        $quality = $totalStudents > 0 ? (($gradeCounts['ABC']['M'] + $gradeCounts['ABC']['F']) / $totalStudents) * 100 : 0;
        $quantity = $totalStudents > 0 ? (($gradeCounts['DE']['M'] + $gradeCounts['DE']['F']) / $totalStudents) * 100 : 0;

        return [
            'reportData' => $reportData,
            'gradeCounts' => $gradeCounts,
            'quality' => number_format($quality, 2),
            'quantity' => number_format($quantity, 2)
        ];
    }

    public function generateSubjectGradeByHouse(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $school_data = SchoolSetup::first();
        $houses = House::where('term_id', $selectedTermId)->get();
        return view('assessment.shared.overall-subject-house', ['school_data' => $school_data, 'houses' => $houses]);
    }

    private function handleSingleGrade(&$subjectCounts, $grade, $genderKey){
        if (in_array($grade, ['A*', 'A', 'B', 'C'])) {
            $subjectCounts[$grade][$genderKey]++;
        }
    }

    private function handleDoubleScience(&$subjectCounts, $grade, $genderKey){
        $grades = str_split($grade, 2);
        foreach ($grades as $singleGrade) {
            if (in_array($singleGrade, ['A*', 'A', 'B', 'C'])) {
                $subjectCounts[$singleGrade][$genderKey]++;
            }
        }
    }

    private function getStudentEnrolledSubjects($student, $klass, $termId) {
        $enrolledSubjects = [];
        $coreSubjects = DB::table('klass_subject')
            ->join('grade_subject', 'klass_subject.grade_subject_id', '=', 'grade_subject.id')
            ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->where('klass_subject.klass_id', $klass->id)
            ->where('klass_subject.term_id', $termId)
            ->whereNull('klass_subject.deleted_at')
            ->pluck('subjects.name')
            ->toArray();
        
        $optionalSubjects = DB::table('student_optional_subjects')
            ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
            ->join('grade_subject', 'optional_subjects.grade_subject_id', '=', 'grade_subject.id')
            ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->where('student_optional_subjects.student_id', $student->id)
            ->where('student_optional_subjects.term_id', $termId)
            ->pluck('subjects.name')
            ->toArray();
        
        return array_unique(array_merge($coreSubjects, $optionalSubjects));
    }

    private function calculateStudentCredits($student, $termId, $type, $sequence){
        $credits = 0;
        $tests = $student->tests()
            ->where('term_id', $termId)
            ->where('type', $type)
            ->where('sequence', $sequence)->get();

        foreach ($tests as $test) {
            $grade = $test->pivot->grade;
            if ($test->subject->name === 'Double Science') {
                $grades = str_split($grade, 2);
                foreach ($grades as $g) {
                    if (in_array($g, ['A*', 'A', 'B', 'C'])) {
                        $credits++;
                    }
                }
            } else {
                if (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                    $credits++;
                }
            }
        }
        return $credits;
    }

    public function generateSubjectExamPerformanceReport($classId,$type,$sequence){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::findOrFail($classId);
        $grade = Grade::findOrFail($klass->grade_id);

        $test1 = Test::where('term_id',$selectedTermId)->where('type',$type)->where('sequence',$sequence)->first();
        $students = Student::whereHas('terms', function ($query) use ($selectedTermId, $klass) {
            $query->where('student_term.term_id', $selectedTermId)
                ->where('grade_id', $klass->grade_id);
        })->get();
    
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('active', 1)
            ->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();
    
        $subjectPerformance = [];
        foreach ($allGradeSubjects as $gradeSubject) {
            $subjectName = $gradeSubject->subject->name;
            $subjectPerformance[$subjectName] = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'NS' => ['M' => 0, 'F' => 0],
                'totalWithScores' => ['M' => 0, 'F' => 0],
                'totalEnrolled' => ['M' => 0, 'F' => 0],
            ];
        }
    
        foreach ($students as $student) {
            $genderKey = ($student->gender === 'Male' || $student->gender === 'M') ? 'M' : 'F';
            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = $gradeSubject->subject->name;
                $isEnrolled = false;
                
                $klassSubject = KlassSubject::where('klass_id', $klass->id)
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('term_id', $selectedTermId)
                    ->first();
                
                if ($klassSubject) {
                    $isEnrolled = true;
                } else {
                    $optionalSubject = OptionalSubject::where('grade_subject_id', $gradeSubject->id)
                        ->where('term_id', $selectedTermId)
                        ->whereHas('students', function($query) use ($student, $selectedTermId) {
                            $query->where('student_id', $student->id)
                                  ->where('student_optional_subjects.term_id', $selectedTermId);
                        })->first();
                    
                    if ($optionalSubject) {
                        $isEnrolled = true;
                    }
                }
                
                if ($isEnrolled) {
                    $subjectPerformance[$subjectName]['totalEnrolled'][$genderKey]++;
                    $test = $student->tests()
                        ->where('term_id', $selectedTermId)
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->where('type', $type)
                        ->where('sequence', $sequence)
                        ->first();
    
                    $grade = $test ? $test->pivot->grade : null;
                    
                    if ($grade && isset($subjectPerformance[$subjectName][$grade])) {
                        $subjectPerformance[$subjectName][$grade][$genderKey]++;
                        $subjectPerformance[$subjectName]['totalWithScores'][$genderKey]++;
                    } else {
                        $subjectPerformance[$subjectName]['NS'][$genderKey]++;
                    }
                }
            }
        }
    
        foreach ($subjectPerformance as $subjectName => &$counts) {
            $totalWithScoresMale = $counts['totalWithScores']['M'];
            $totalWithScoresFemale = $counts['totalWithScores']['F'];

            $percentRanges = [
                'AB%' => ['A', 'B'],
                'ABC%' => ['A', 'B', 'C'],
                'ABCD%' => ['A', 'B', 'C', 'D'],
                'DEU%' => ['D', 'E', 'U']
            ];
    
            foreach ($percentRanges as $range => $grades) {
                $maleCount = array_sum(array_map(fn($g) => $counts[$g]['M'], $grades));
                $femaleCount = array_sum(array_map(fn($g) => $counts[$g]['F'], $grades));
    
                $counts[$range] = [
                    'M' => $totalWithScoresMale > 0 ? round(($maleCount / $totalWithScoresMale) * 100, 2) : 0,
                    'F' => $totalWithScoresFemale > 0 ? round(($femaleCount / $totalWithScoresFemale) * 100, 2) : 0
                ];
            }
        }
        unset($counts);
    
        $subjectTotals = [  
            'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
            'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
            'NS'=>['M'=>0,'F'=>0],
            'totalWithScores'=>['M'=>0,'F'=>0],
            'totalEnrolled'=>['M'=>0,'F'=>0],
            'AB%'=>['M'=>0,'F'=>0], 'ABC%'=>['M'=>0,'F'=>0],
            'ABCD%'=>['M'=>0,'F'=>0], 'DEU%'=>['M'=>0,'F'=>0],
        ];
    
        foreach ($subjectPerformance as $subj => $c) {
            foreach (['A','B','C','D','E','U','NS'] as $g) {
                $subjectTotals[$g]['M'] += $c[$g]['M'];
                $subjectTotals[$g]['F'] += $c[$g]['F'];
            }
            $subjectTotals['totalWithScores']['M'] += $c['totalWithScores']['M'];
            $subjectTotals['totalWithScores']['F'] += $c['totalWithScores']['F'];
            $subjectTotals['totalEnrolled']['M'] += $c['totalEnrolled']['M'];
            $subjectTotals['totalEnrolled']['F'] += $c['totalEnrolled']['F'];
        }
    
        $totalWithScoresMale = $subjectTotals['totalWithScores']['M'];
        $totalWithScoresFemale = $subjectTotals['totalWithScores']['F'];
    
        $percentRanges = [
            'AB%' => ['A', 'B'],
            'ABC%' => ['A', 'B', 'C'],
            'ABCD%' => ['A', 'B', 'C', 'D'],
            'DEU%' => ['D', 'E', 'U']
        ];
    
        foreach ($percentRanges as $range => $grades) {
            $maleCount = array_sum(array_map(fn($g) => $subjectTotals[$g]['M'], $grades));
            $femaleCount = array_sum(array_map(fn($g) => $subjectTotals[$g]['F'], $grades));
    
            $subjectTotals[$range] = [
                'M' => $totalWithScoresMale > 0 ? round(($maleCount / $totalWithScoresMale) * 100, 2) : 0,
                'F' => $totalWithScoresFemale > 0 ? round(($femaleCount / $totalWithScoresFemale) * 100, 2) : 0
            ];
        }
    
        if (request()->has('export')) {
            return Excel::download(
                new SubjectAnalysisExport($subjectPerformance,$subjectTotals),
                "Subject_Analysis_Exam_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
    
        return view('assessment.shared.exam-class-subject-analysis', [
            'subjectPerformance' => $subjectPerformance,
            'subjectTotals' => $subjectTotals,
            'school_data' => $school_setup,
            'test1' => $test1,
            'klass' => $klass,
        ]);
    }

    protected function calculateTeacherPerformanceData($teacher, $class, $subjectName, $tests, $studentIds) {
        $gradeStructure = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0]
        ];
        
        $totalMale = 0;
        $totalFemale = 0;
        
        foreach ($tests as $test) {
            $results = StudentTest::where('test_id', $test->id)->whereIn('student_id', $studentIds)->with('student')->get();
            foreach ($results as $result) {
                if (!$result->student) {
                    continue;
                }
                
                $gender = $result->student->gender;
                $grade = $result->grade;
                
                if (empty($grade) || !isset($gradeStructure[$grade])) {
                    continue;
                }
                
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
        
        if ($totalMale + $totalFemale == 0) {
            return null;
        }
        
        $percentRanges = [
            'AB%'   => ['A','B'],
            'ABC%'  => ['A','B','C'],
            'ABCD%' => ['A','B','C','D'],
            'DEU%'  => ['D','E','U'],
        ];
        
        $percentages = [];
        foreach ($percentRanges as $col => $grades) {
            $mSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['M'], $grades));
            $fSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['F'], $grades));
            
            $percentages[$col] = [
                'M' => $totalMale ? round($mSum / $totalMale * 100, 2) : 0,
                'F' => $totalFemale ? round($fSum / $totalFemale * 100, 2) : 0
            ];
        }
        
        $teacherName = '';
        if (is_object($teacher)) {
            $teacherName = $teacher->full_name;
        }
        
        $className = '';
        if (is_object($class)) {
            $className = $class->name ?? '';
        } else {
            $className = (string)$class;
        }
        
        return [
            'teacher_name' => $teacherName,
            'class_name' => $className,
            'subject_name' => $subjectName,
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'grades' => $gradeStructure,
            'AB%' => $percentages['AB%'],
            'ABC%' => $percentages['ABC%'],
            'ABCD%' => $percentages['ABCD%'],
            'DEU%' => $percentages['DEU%']
        ];
    }

    public function htmlGradePerformanceAnalysisExport(Request $request, $classId, $type, $sequenceId){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $school_setup = SchoolSetup::first();
        $gradingMatrix = DB::table('overall_grading_matrices')->where('term_id', $currentTerm->id)->where('year', $currentTerm->year)->get();

        $students = Student::whereHas('classes', function ($query) use ($gradeId) {
            $query->whereHas('grade', function ($query) use ($gradeId) {
                $query->where('id', $gradeId);
            });
        })->get();

        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();

        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $allStudentData = [];

        $gradeCounts = ['A' => ['M' => 0, 'F' => 0], 'B' => ['M' => 0, 'F' => 0], 'C' => ['M' => 0, 'F' => 0], 'D' => ['M' => 0, 'F' => 0], 'E' => ['M' => 0, 'F' => 0], 'U' => ['M' => 0, 'F' => 0]];
        $gradeCombinationsCounts = ['ABC' => ['M' => 0, 'F' => 0], 'ABCD' => ['M' => 0, 'F' => 0]];

        foreach ($students as $student) {
            $scores = [];
            $totalScore = 0;
            $totalSubjectsCounted = 0;

            foreach ($allGradeSubjects as $gradeSubject) {
                $examTest = $student->tests
                    ->where('term_id', $selectedTermId)
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('type', $type)
                    ->where('sequence', $sequenceId)
                    ->first();

                $score = $examTest ? $examTest->pivot->score : null;
                $grade = $examTest ? $examTest->pivot->grade : null;
                if ($examTest) {
                    $totalScore += $score;
                    $totalSubjectsCounted++;
                }

                if ($grade) {
                    $genderKey = strtolower($student->gender) === 'm' ? 'M' : (strtolower($student->gender) === 'f' ? 'F' : null);
                    $gradeCounts[$grade][$genderKey] = ($gradeCounts[$grade][$genderKey] ?? 0) + 1;
                }

                $scores[$gradeSubject->subject->id] = [
                    'score' => $score,
                    'grade' => $grade
                ];
            }

            $averageScore = $totalSubjectsCounted > 0 ? $totalScore / $totalSubjectsCounted : 0;
            $overallGrade = $this->deriveGrade(round($averageScore, 0), $gradingMatrix);

            $grades = array_column($scores, 'grade');

            $this->updateGradeCombinationCounts($grades, $gradeCombinationsCounts, $student->gender);

            $allStudentData[] = [
                'studentName' => $student->fullName ?? '',
                'gender' => $student->gender,
                'scores' => $scores,
                'totalScore' => $totalScore,
                'averageScore' => $averageScore,
                'overallGrade' => $overallGrade
            ];
        }

        usort($allStudentData, function ($a, $b) {
            return $b['averageScore'] <=> $a['averageScore'];
        });

        foreach ($allStudentData as $index => &$student) {
            $student['position'] = $index + 1;
        }

        $data = [
            'allStudentData' => $allStudentData,
            'subjects' => $allSubjects,
            'gradeCounts' => $gradeCounts,
            'gradeCombinationsCounts' => $gradeCombinationsCounts,
            'school_data' => $school_setup,
            'currentTerm' => $currentTerm,
            'klass' => $klass,
        ];

        return Excel::download(new GradePerformanceAnalysisExport($data), 'grade-performance-analysis.xlsx');
    }

    private function deriveGrade($score, $gradingMatrix){
        if ($score === null) return null;
        foreach ($gradingMatrix as $grade) {
            if ($score >= $grade->min_score && $score <= $grade->max_score) {
                return $grade->grade;
            }
        }
        return 'N/A';
    }

    public function getCommentsBank(){
        try {
            $venues = CacheHelper::getAllVenues();
            $comments = CacheHelper::getCommentBank();
            $scoreComments = CacheHelper::getSubjectsComments();
            return view('assessment.shared.comments-bank-list', ['scoreComments' => $scoreComments,'comments' => $comments,'venues' => $venues]);
        } catch (\Exception $e) {
            Log::error('Error fetching comments bank: ' . $e->getMessage());
            return view('assessment.shared.comments-bank-list', [
                'comments' => [],
                'error' => 'Unable to fetch comments. Please try again later.'
            ]);
        }
    }

    public function createComment(){
        return view('assessment.shared.comments-bank-add');
    }

    public function createSubjectCommentBank(){
        return view('assessment.shared.subjects-comments-bank-add');
    }

    public function editSubjectComment($id){
        try {
            $comment = ScoreComment::findOrFail($id);
            return view('assessment.shared.subjects-comments-bank-add', ['comment' => $comment]);
        } catch (\Exception $e) {
            Log::error('Error editing comment: ' . $e->getMessage());
            return redirect()->route('assessment.comment-bank')->withErrors('Unable to edit the comment. Please try again.');
        }
    }

    public function destroySubjectComment($id){
        try {
            DB::beginTransaction();
            $scoreComment = ScoreComment::findOrFail($id);
            $scoreComment->delete();
            CacheHelper::forgetSubjectComments();
            DB::commit();

            return redirect()->back()->with('message', 'Comment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting comment: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while deleting the comment. Please try again.');
        }
    }

    public function storeSubjectComment(Request $request){
        $validated = $request->validate([
            'min_score' => 'required|integer|between:0,100',
            'max_score' => 'required|integer|between:0,100|gte:min_score',
            'comment'   => 'required|string',
        ]);

        $partialOverlapExists = ScoreComment::where(function ($query) use ($validated) {
                $query->where('min_score', '<=', $validated['max_score'])
                      ->where('max_score', '>=', $validated['min_score']);
            })->where(function ($query) use ($validated) {
                $query->where('min_score', '!=', $validated['min_score'])
                      ->orWhere('max_score', '!=', $validated['max_score']);
            })->exists();

        if ($partialOverlapExists) {
            return redirect()->back()->withInput()->with('error', 'This range partially overlaps with an existing range. Exact duplicates are allowed, but partial overlaps are not.');
        }

        try {
            ScoreComment::create($validated);
            CacheHelper::forgetSubjectComments();
            return redirect()->back()->with('message', 'Score comment created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating score comment: ' . $e->getMessage());
        }
    }

    public function updateSubjectComment(Request $request, $id){
        $validated = $request->validate([
            'min_score' => 'required|integer|between:0,100',
            'max_score' => 'required|integer|between:0,100|gte:min_score',
            'comment'   => 'required|string',
        ]);

        $scoreComment = ScoreComment::findOrFail($id);
        $partialOverlapExists = ScoreComment::where(function ($query) use ($validated) {
                $query->where('min_score', '<=', $validated['max_score'])
                      ->where('max_score', '>=', $validated['min_score']);
            })->where(function ($query) use ($validated) {
                $query->where('min_score', '!=', $validated['min_score'])
                      ->orWhere('max_score', '!=', $validated['max_score']);
            })->where('id', '!=', $scoreComment->id)->exists();
    
        if ($partialOverlapExists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This range partially overlaps with an existing range. Exact duplicates are allowed, but partial overlaps are not.');
        }
    
        try {
            $scoreComment->min_score = $validated['min_score'];
            $scoreComment->max_score = $validated['max_score'];
            $scoreComment->comment = $validated['comment'];
            $scoreComment->save();
    
            CacheHelper::forgetSubjectComments();
            return redirect()->back()->with('message', 'Score comment updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating score comment: ' . $e->getMessage());
        }
    }

    public function storeOverallComment(Request $request) {
        try {
            $validated = $request->validate([
                'min_points' => 'required|integer|min:0|max:100',
                'max_points' => 'required|integer|min:0|max:100|gte:min_points',
                'body' => [
                    'required',
                    'string',
                    Rule::unique('comment_banks')->where(function ($query) use ($request) {
                        return $query->where('min_points', $request->min_points)
                                    ->where('max_points', $request->max_points)
                                    ->whereNull('deleted_at');
                    })
                ]
            ]);
     
            DB::beginTransaction();
            CommentBank::create($validated);
            CacheHelper::forgetCommentBank(); 
            DB::commit();
     
            return redirect()->route('assessment.comment-bank')->with('message', 'Comment created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing comment: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors('An error occurred while saving the comment.');
        }
     }

    public function editComment($id){
        try {
            $comment = CommentBank::findOrFail($id);
            $comments = CacheHelper::getCommentBank();
            return view('assessment.shared.comments-bank-add', ['comment' => $comment, 'comments' => $comments]);
        } catch (\Exception $e) {
            Log::error('Error editing comment: ' . $e->getMessage());
            return redirect()->route('assessment.comment-bank')->withErrors('Unable to edit the comment. Please try again.');
        }
    }

    public function update(Request $request, $id) {
        try {
            $comment = CommentBank::findOrFail($id);
            $validated = $request->validate([
                'min_points' => 'required|integer|min:0|max:100',
                'max_points' => 'required|integer|min:0|max:100|gte:min_points',
                'body' => [
                    'required',
                    'string',
                    Rule::unique('comment_banks')->where(function ($query) use ($request) {
                        return $query->where('min_points', $request->min_points)
                                    ->where('max_points', $request->max_points)
                                    ->whereNull('deleted_at');
                    })->ignore($id)
                ]
            ]);
     
            DB::beginTransaction();
            $comment->update($validated);
            CacheHelper::forgetCommentBank();
            DB::commit();
     
            return redirect()->route('assessment.comment-bank')->with('message', 'Comment updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating comment: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors('An error occurred while updating the comment.');
        }
     }

    public function destroy($id){
        try {
            DB::beginTransaction();
            $comment = CommentBank::findOrFail($id);
            $comment->delete();
            CacheHelper::forgetCommentBank();
            DB::commit();

            return redirect()->route('assessment.comment-bank')->with('message', 'Comment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting comment: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while deleting the comment. Please try again.');
        }
    }

    public function storeVenue(Request $request){
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('venues', 'name')->whereNull('deleted_at')
                ],
                'type' => [
                    'required',
                    'string',
                    'max:191',
                    Rule::in(['classroom', 'hall', 'laboratory', 'other'])
                ],
                'capacity' => 'required|integer|min:1|max:9999',
            ]);

            DB::beginTransaction();

            try {
                $venue = Venue::create([
                    'name' => $request->name,
                    'type' => $request->type,
                    'capacity' => $request->capacity
                ]);

                CacheHelper::forgetAllVenues();
                DB::commit();
                return redirect()->back()->with('message', 'Venue created successfully.');

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Venue creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create venue. Please try again.')->withInput();
        }
    }

    public function updateVenue(Request $request, $id){
        try {
            $venue = Venue::findOrFail($id);
            $request->validate([
                'name' => 'required|string|max:191',
                'type' => ['required', 'string', 'max:191', Rule::in(['classroom', 'hall', 'laboratory', 'other'])],
                'capacity' => 'required|integer|min:1',
            ]);

            if ($request->capacity < $venue->capacity && $venue->utilization_percentage > 0) {
                $currentStudents = $venue->optionalSubjects()->where('active', 1)->withCount('students')->get()->max('students_count');

                if ($currentStudents > $request->capacity) {
                    return redirect()->back()->with('error', "Cannot reduce capacity below current utilization ($currentStudents students).");
                }
            }

            DB::beginTransaction();
            try {
                $venue->update([
                    'name' => $request->name,
                    'type' => $request->type, 
                    'capacity' => $request->capacity
                ]);

                CacheHelper::forgetAllVenues();
                DB::commit();

                return redirect()->back()
                    ->with('message', 'Venue updated successfully.');

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Venue not found.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Venue update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update venue. Please try again.');
        }
    }

    public function destroyVenue($id){
        try {
            $venue = Venue::findOrFail($id);
        
            if ($venue->optionalSubjects()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete venue. It has associated subjects.');
            }

            DB::beginTransaction();
            try {
                $venue->delete();
                CacheHelper::forgetAllVenues();
                DB::commit();

                return redirect()->back()->with('message', 'Venue deleted successfully.');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Venue not found.');
        } catch (\Exception $e) {
            Log::error('Venue deletion failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete venue. Please try again.');
        }
    }
}
