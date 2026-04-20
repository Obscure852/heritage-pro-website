<?php

namespace App\Http\Controllers\Timetable;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController;
use App\Models\Grade;
use App\Models\KlassSubject;
use App\Models\Timetable\Timetable;
use App\Models\User;
use App\Services\Timetable\PeriodSettingsService;
use App\Services\Timetable\TimetableViewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimetableViewController extends Controller {
    public function __construct(
        protected TimetableViewService $viewService,
        protected PeriodSettingsService $periodSettingsService
    ) {}

    /**
     * Class timetable view — 6-day x periods grid for a specific class.
     */
    public function classView(Request $request, ?Timetable $timetable = null): View {
        $timetable = $this->viewService->resolveViewableTimetable($timetable?->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        if (!$timetable) {
            return view('timetable.views.class', [
                'timetable' => null,
                'grades' => collect(),
                'daySchedule' => [],
                'gridData' => [],
                'klassId' => null,
                'selectedKlassName' => null,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
            ]);
        }

        if (Gate::allows('manage-timetable') || auth()->user()->hasAnyRoles(['HOD'])) {
            // Admin and HOD: see all classes
            $grades = Grade::with(['klasses' => fn($q) => $q->where('term_id', session('selected_term_id'))->orderBy('name')])
                ->whereHas('klasses', fn($q) => $q->where('term_id', session('selected_term_id')))
                ->orderBy('sequence')
                ->get();
        } else {
            // Class teacher / plain teacher: only see classes they are assigned to
            $grades = Grade::with(['klasses' => fn($q) => $q->where('term_id', session('selected_term_id'))
                    ->where('user_id', auth()->id())
                    ->orderBy('name')
                ])
                ->whereHas('klasses', fn($q) => $q->where('term_id', session('selected_term_id'))
                    ->where('user_id', auth()->id())
                )
                ->orderBy('sequence')
                ->get();
        }

        $daySchedule = $this->periodSettingsService->getDaySchedule();

        $klassId = $request->query('klass_id');

        // Auto-select first class when none specified
        if (!$klassId && $grades->isNotEmpty()) {
            $firstGrade = $grades->first();
            $firstKlass = $firstGrade->klasses->first();
            if ($firstKlass) {
                $klassId = $firstKlass->id;
            }
        }

        $gridData = $klassId
            ? $this->viewService->getClassGridData($timetable->id, (int) $klassId)
            : [];

        // Find selected class name
        $selectedKlassName = null;
        if ($klassId) {
            foreach ($grades as $grade) {
                $klass = $grade->klasses->firstWhere('id', (int) $klassId);
                if ($klass) {
                    $selectedKlassName = $klass->name;
                    break;
                }
            }
        }

        return view('timetable.views.class', compact(
            'timetable', 'grades', 'daySchedule', 'gridData', 'klassId', 'selectedKlassName',
            'terms', 'currentTerm'
        ));
    }

    /**
     * Teacher timetable view — 6-day x periods grid for a specific teacher.
     */
    public function teacherView(Request $request, ?Timetable $timetable = null): View {
        $timetable = $this->viewService->resolveViewableTimetable($timetable?->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        if (!$timetable) {
            return view('timetable.views.teacher', [
                'timetable' => null,
                'teachers' => collect(),
                'daySchedule' => [],
                'gridData' => [],
                'teacherId' => null,
                'selectedTeacherName' => null,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
                'canSelectTeacher' => true,
            ]);
        }

        $canSelectTeacher = true;

        if (Gate::allows('manage-timetable')) {
            // Admin: show all teachers
            $teachers = User::teachingAndCurrent()->orderBy('firstname')->get(['id', 'firstname', 'lastname']);
        } elseif (auth()->user()->hasAnyRoles(['HOD'])) {
            // HOD: show department teachers only (plus self if teaching)
            $departmentIds = auth()->user()->headedDepartments()->pluck('id');
            $teacherIds = KlassSubject::whereHas('gradeSubject', fn($q) => $q->whereIn('department_id', $departmentIds))
                ->where('term_id', session('selected_term_id'))
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            // Include HOD themselves if they teach
            if (auth()->user()->area_of_work === 'Teaching' && !in_array(auth()->id(), $teacherIds)) {
                $teacherIds[] = auth()->id();
            }
            $teachers = User::whereIn('id', $teacherIds)->orderBy('firstname')->get(['id', 'firstname', 'lastname']);
        } else {
            // Teacher: lock to own timetable, no selector
            $canSelectTeacher = false;
            $teachers = collect();
        }

        $daySchedule = $this->periodSettingsService->getDaySchedule();

        $teacherId = $request->query('teacher_id');

        // Auto-detect: if logged-in user is a teacher and no teacher_id specified
        if (!$teacherId && auth()->user()->area_of_work === 'Teaching') {
            $teacherId = auth()->id();
        }

        // Plain teachers are locked to their own ID regardless of query param
        if (!$canSelectTeacher) {
            $teacherId = auth()->id();
        }

        $gridData = $teacherId
            ? $this->viewService->getTeacherGridData($timetable->id, (int) $teacherId)
            : [];

        // Find selected teacher name
        $selectedTeacherName = null;
        if ($teacherId) {
            if ($canSelectTeacher) {
                $teacher = $teachers->firstWhere('id', (int) $teacherId);
                $selectedTeacherName = $teacher ? ($teacher->firstname . ' ' . $teacher->lastname) : null;
            } else {
                // Plain teacher: use own name
                $selectedTeacherName = auth()->user()->firstname . ' ' . auth()->user()->lastname;
            }
        }

        return view('timetable.views.teacher', compact(
            'timetable', 'teachers', 'daySchedule', 'gridData', 'teacherId', 'selectedTeacherName',
            'terms', 'currentTerm', 'canSelectTeacher'
        ));
    }

    /**
     * Master overview — condensed all-classes grid (admin only).
     */
    public function masterView(Request $request, ?Timetable $timetable = null): View {
        $this->authorize('manage-timetable');

        $timetable = $this->viewService->resolveViewableTimetable($timetable?->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        if (!$timetable) {
            return view('timetable.views.master', [
                'timetable' => null,
                'timetables' => collect(),
                'daySchedule' => [],
                'masterData' => ['grids' => [], 'classes' => []],
                'grades' => collect(),
                'gradeFilter' => null,
                'terms' => $terms,
                'currentTerm' => $currentTerm,
            ]);
        }

        $timetables = Timetable::orderBy('created_at', 'desc')->get(['id', 'name', 'status']);
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $masterData = $this->viewService->getMasterGridData($timetable->id);
        $grades = Grade::orderBy('sequence')->get();
        $gradeFilter = $request->query('grade_id');

        return view('timetable.views.master', compact(
            'timetable', 'timetables', 'daySchedule', 'masterData', 'grades', 'gradeFilter',
            'terms', 'currentTerm'
        ));
    }
}
