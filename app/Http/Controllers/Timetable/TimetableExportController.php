<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\SchoolSetup;
use App\Models\Timetable\Timetable;
use App\Models\User;
use App\Services\Timetable\PeriodSettingsService;
use App\Services\Timetable\TimetableViewService;
use App\Exports\Timetable\ClassTimetableExport;
use App\Exports\Timetable\MasterTimetableExport;
use App\Exports\Timetable\TeacherTimetableExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TimetableExportController extends Controller {
    public function __construct(
        protected TimetableViewService $viewService,
        protected PeriodSettingsService $periodSettingsService
    ) {}

    /**
     * Download class timetable as PDF.
     */
    public function classPdf(Request $request, Timetable $timetable): Response {
        abort_unless($request->query('klass_id'), 400, 'Class ID required.');

        $klassId = (int) $request->query('klass_id');

        // Per-resource check: must be class teacher or admin/HOD
        if (Gate::denies('manage-timetable') && !auth()->user()->hasAnyRoles(['HOD'])) {
            $klass = Klass::find($klassId);
            abort_unless($klass && $klass->user_id === auth()->id(), 403, 'You can only export your assigned class timetable.');
        }

        $gridData = $this->viewService->getClassGridData($timetable->id, $klassId);
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $school_data = SchoolSetup::first();
        $logoBase64 = $this->encodeLogoBase64($school_data);
        $className = Klass::where('id', $klassId)->value('name') ?? 'Class';
        $timetableName = $timetable->name;

        $pdf = Pdf::loadView('timetable.exports.class-pdf', compact(
            'gridData', 'daySchedule', 'school_data', 'logoBase64', 'className', 'timetableName'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('class-timetable-' . Str::slug($className) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download teacher timetable as PDF.
     */
    public function teacherPdf(Request $request, Timetable $timetable): Response {
        abort_unless($request->query('teacher_id'), 400, 'Teacher ID required.');

        $teacherId = (int) $request->query('teacher_id');

        // Per-resource check: must be own timetable, department teacher (for HOD), or admin
        if (Gate::denies('manage-timetable') && $teacherId !== auth()->id()) {
            // HOD can export for their department teachers
            if (auth()->user()->hasAnyRoles(['HOD'])) {
                $departmentIds = auth()->user()->headedDepartments()->pluck('id');
                $departmentTeacherIds = KlassSubject::whereHas('gradeSubject', fn($q) => $q->whereIn('department_id', $departmentIds))
                    ->where('term_id', session('selected_term_id'))
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();
                abort_unless(in_array($teacherId, $departmentTeacherIds), 403, 'You can only export timetables for teachers in your department.');
            } else {
                abort(403, 'You can only export your own timetable.');
            }
        }

        $gridData = $this->viewService->getTeacherGridData($timetable->id, $teacherId);
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $school_data = SchoolSetup::first();
        $logoBase64 = $this->encodeLogoBase64($school_data);

        $teacher = User::where('id', $teacherId)->first(['firstname', 'lastname']);
        $teacherName = $teacher ? ($teacher->firstname . ' ' . $teacher->lastname) : 'Teacher';
        $timetableName = $timetable->name;

        $pdf = Pdf::loadView('timetable.exports.teacher-pdf', compact(
            'gridData', 'daySchedule', 'school_data', 'logoBase64', 'teacherName', 'timetableName'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('teacher-timetable-' . Str::slug($teacherName) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download master timetable overview as PDF.
     */
    public function masterPdf(Request $request, Timetable $timetable): Response {
        $this->authorize('manage-timetable');

        $masterData = $this->viewService->getMasterGridData($timetable->id);
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $school_data = SchoolSetup::first();
        $logoBase64 = $this->encodeLogoBase64($school_data);
        $gradeFilter = $request->query('grade_id');
        $timetableName = $timetable->name;

        $pdf = Pdf::loadView('timetable.exports.master-pdf', compact(
            'masterData', 'daySchedule', 'school_data', 'logoBase64', 'timetableName', 'gradeFilter'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('master-timetable-' . Str::slug($timetable->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download class timetable as Excel.
     */
    public function classExcel(Request $request, Timetable $timetable): BinaryFileResponse {
        $this->authorize('manage-timetable');

        abort_unless($request->query('klass_id'), 400, 'Class ID required.');

        $klassId = (int) $request->query('klass_id');
        $gridData = $this->viewService->getClassGridData($timetable->id, $klassId);
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $className = Klass::where('id', $klassId)->value('name') ?? 'Class';

        return Excel::download(
            new ClassTimetableExport($gridData, $daySchedule, $className, $timetable->name),
            'class-timetable-' . Str::slug($className) . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Download teacher timetable as Excel.
     */
    public function teacherExcel(Request $request, Timetable $timetable): BinaryFileResponse {
        $this->authorize('manage-timetable');

        abort_unless($request->query('teacher_id'), 400, 'Teacher ID required.');

        $teacherId = (int) $request->query('teacher_id');
        $gridData = $this->viewService->getTeacherGridData($timetable->id, $teacherId);
        $daySchedule = $this->periodSettingsService->getDaySchedule();

        $teacher = User::where('id', $teacherId)->first(['firstname', 'lastname']);
        $teacherName = $teacher ? ($teacher->firstname . ' ' . $teacher->lastname) : 'Teacher';

        return Excel::download(
            new TeacherTimetableExport($gridData, $daySchedule, $teacherName, $timetable->name),
            'teacher-timetable-' . Str::slug($teacherName) . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Download master timetable as multi-sheet Excel workbook.
     */
    public function masterExcel(Request $request, Timetable $timetable): BinaryFileResponse {
        $this->authorize('manage-timetable');

        $masterData = $this->viewService->getMasterGridData($timetable->id);
        $daySchedule = $this->periodSettingsService->getDaySchedule();

        return Excel::download(
            new MasterTimetableExport($masterData, $daySchedule, $timetable->name),
            'master-timetable-' . Str::slug($timetable->name) . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Encode school logo as base64 data URI for DomPDF.
     */
    private function encodeLogoBase64(?SchoolSetup $school_data): ?string {
        if (!$school_data || !$school_data->logo_path) {
            return null;
        }

        $logoFullPath = public_path($school_data->logo_path);

        if (!file_exists($logoFullPath)) {
            return null;
        }

        $extension = pathinfo($logoFullPath, PATHINFO_EXTENSION);
        return 'data:image/' . $extension . ';base64,' . base64_encode(file_get_contents($logoFullPath));
    }
}
