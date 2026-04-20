<?php

namespace App\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\TermHelper;
use App\Models\Holiday;
use App\Models\SchoolSetup;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Models\ManualAttendanceEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Policies\AttendancePolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use App\Models\User;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Filter grades to only include classes the user has access to.
     * Admins see all classes, teachers see only their own.
     */
    private function filterGradesByUserAccess(Collection $grades, User $user): Collection
    {
        if (AttendancePolicy::isAdmin($user)) {
            return $grades;
        }

        // Class Teachers can view all classes (save button is controlled by @can('class-teacher') in the view)
        if ($user->hasAnyRoles(['Class Teacher'])) {
            return $grades;
        }

        return $grades->map(function ($grade) use ($user) {
            $grade->klasses = $grade->klasses->filter(function ($klass) use ($user) {
                return $klass->user_id === $user->id;
            });
            return $grade;
        })->filter(function ($grade) {
            return $grade->klasses->isNotEmpty();
        });
    }

    private function buildRegisterWeekDays(Carbon $weekStart, Carbon $termStart, Carbon $termEnd, string $weekClass): array
    {
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);

            if ($date->isWeekend() || $date->lt($termStart) || $date->gt($termEnd)) {
                continue;
            }

            $days[] = [
                'day' => $date->format('D')[0],
                'date' => $date->format('Y-m-d'),
                'weekClass' => $weekClass,
                'positionClass' => '',
            ];
        }

        if ($days !== []) {
            $days[0]['positionClass'] = 'week-start';
            $lastIndex = array_key_last($days);
            $days[$lastIndex]['positionClass'] = trim($days[$lastIndex]['positionClass'] . ' week-end');
        }

        return $days;
    }

    private function buildAttendanceRegisterWindow(Carbon $requestedWeekStart, Carbon $termStart, Carbon $termEnd): array
    {
        $termStartWeekStart = $termStart->copy()->startOfWeek(Carbon::MONDAY);
        $termEndWeekStart = $termEnd->copy()->startOfWeek(Carbon::MONDAY);

        $currentWeekStart = $requestedWeekStart->copy()->startOfWeek(Carbon::MONDAY);

        if ($currentWeekStart->lt($termStartWeekStart)) {
            $currentWeekStart = $termStartWeekStart;
        } elseif ($currentWeekStart->gt($termEndWeekStart)) {
            $currentWeekStart = $termEndWeekStart;
        }

        $weekOneDays = $this->buildRegisterWeekDays($currentWeekStart, $termStart, $termEnd, 'week-1');
        $weekTwoDays = $this->buildRegisterWeekDays($currentWeekStart->copy()->addWeek(), $termStart, $termEnd, 'week-2');
        $daysOfWeek = array_merge($weekOneDays, $weekTwoDays);

        $visibleStartDate = $daysOfWeek[0]['date'] ?? null;
        $visibleEndDate = $daysOfWeek[array_key_last($daysOfWeek)]['date'] ?? null;

        return [
            'currentWeekStart' => $currentWeekStart,
            'weekOneDays' => $weekOneDays,
            'weekTwoDays' => $weekTwoDays,
            'daysOfWeek' => $daysOfWeek,
            'visibleStartDate' => $visibleStartDate,
            'visibleEndDate' => $visibleEndDate,
            'canGoPrevious' => $currentWeekStart->gt($termStartWeekStart),
            'canGoNext' => $currentWeekStart->lt($termEndWeekStart),
        ];
    }

    public function index(): View
    {
        Cache::forget('grades');
        $grades = CacheHelper::getGrades();
        $terms = StudentController::terms();
        $currentTerm = TermHelper::getCurrentTerm();

        $grades = $this->filterGradesByUserAccess($grades, Auth::user());

        // Get any pre-selected class/week from session (set when redirecting from direct class-list URL)
        $selectedClassId = Session::pull('selectedClassId');
        $selectedWeekStart = Session::pull('selectedWeekStart');

        return view('attendance.index', [
            'grades' => $grades,
            'currentTerm' => $currentTerm,
            'terms' => $terms,
            'attendanceCodes' => Attendance::getCodesWithDetails(),
            'preSelectedClassId' => $selectedClassId,
            'preSelectedWeekStart' => $selectedWeekStart,
        ]);
    }

    public function showClassList(Request $request, $classId, $termId, $weekStart = null): View|JsonResponse|RedirectResponse
    {
        // If not an AJAX request, redirect to the main attendance page
        // The class-list partial should only be loaded via AJAX from the index page
        if (!$request->ajax() && !$request->input('is_ajax', false)) {
            // Store the selected class in session so it's pre-selected on the index page
            Session::put('selectedClassId', $classId);
            Session::put('selected_term_id', $termId);
            Session::put('selectedWeekStart', $weekStart);
            return redirect()->route('attendance.index');
        }

        $klass = Klass::where('id', $classId)->where('term_id', $termId)->firstOrFail();

        // Authorization check - ensure user can view this class's attendance
        $this->authorize('viewAttendance', $klass);

        // Use the term from the class, not the "current" term
        $term = $klass->term;
        $selectedTermId = session('selected_term_id', $term->id);

        $start_date = $term->start_date->copy();
        $end_date = $term->end_date->copy();
    
        $totalDays = $start_date->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end_date);
    
        try {
            $requestedWeekStart = $weekStart ? Carbon::parse($weekStart) : Carbon::now();
        } catch (\Exception $e) {
            $requestedWeekStart = Carbon::now();
        }

        $registerWindow = $this->buildAttendanceRegisterWindow($requestedWeekStart, $start_date, $end_date);
        $currentWeekStart = $registerWindow['currentWeekStart'];

        $attendanceRecords = collect();
        if ($registerWindow['visibleStartDate'] && $registerWindow['visibleEndDate']) {
            $attendanceRecords = Attendance::where('klass_id', $classId)
                ->where('term_id', $termId)
                ->whereBetween('date', [
                    $registerWindow['visibleStartDate'],
                    $registerWindow['visibleEndDate'],
                ])->get()->groupBy(function ($record) {
                    return $record->date->format('Y-m-d');
                })->map(function ($row) {
                    return $row->keyBy('student_id');
                });
        }

        Session::put('class_id', $klass->id);
        Session::put('term_id', $termId);
    
        if ($request->input('is_ajax', false)) {
            return response()->json([
                'success' => true,
                'newWeekStart' => $currentWeekStart->format('Y-m-d'),
                'daysOfWeek' => $registerWindow['daysOfWeek'],
                'weekOneDays' => $registerWindow['weekOneDays'],
                'weekTwoDays' => $registerWindow['weekTwoDays'],
                'visibleStartDate' => $registerWindow['visibleStartDate'],
                'visibleEndDate' => $registerWindow['visibleEndDate'],
                'canGoPrevious' => $registerWindow['canGoPrevious'],
                'canGoNext' => $registerWindow['canGoNext'],
                'attendanceRecords' => $attendanceRecords->toArray(),
            ]);
        }

        return view('attendance.class-list', [
            'klass' => $klass,
            'daysOfWeek' => $registerWindow['daysOfWeek'],
            'weekOneDays' => $registerWindow['weekOneDays'],
            'weekTwoDays' => $registerWindow['weekTwoDays'],
            'totalDays' => $totalDays,
            'totalHolidayDays' => $this->getTotalHolidayDays($term->id),
            'currentWeekStart' => $currentWeekStart->format('Y-m-d'),
            'visibleStartDate' => $registerWindow['visibleStartDate'],
            'visibleEndDate' => $registerWindow['visibleEndDate'],
            'canGoPrevious' => $registerWindow['canGoPrevious'],
            'canGoNext' => $registerWindow['canGoNext'],
            'attendanceRecords' => $attendanceRecords,
            'termId' => $selectedTermId,
            'attendanceCodes' => Attendance::getCodesWithDetails(),
        ]);
    }


    public function showManualEntryForm($studentId, $studentIds, $index) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $student = Student::findOrFail($studentId);

        $manualEntry = ManualAttendanceEntry::where('student_id', $student->id)
            ->where('term_id', $selectedTermId)
            ->first();

        return view('attendance.manual-attendance-entry', [
            'termId' => $selectedTermId,
            'student' => $student,
            'manualEntry' => $manualEntry,
            'studentIds' => $studentIds,
            'index' => $index,
        ]);
    }
    

    public function showMonthlyAttendance(Request $request, $classId): View
    {
        $klass = Klass::findOrFail($classId);
        $this->authorize('viewAttendance', $klass);

        $selectedTerm = TermHelper::getCurrentTerm();
        $startDate = Carbon::parse($selectedTerm->start_date);
        $endDate = Carbon::parse($selectedTerm->end_date);
    
        $attendanceRecords = Attendance::where('klass_id', $classId)
            ->where('term_id', $selectedTerm->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    
        $attendanceCodes = Attendance::getValidCodes();
        $attendanceCounts = array_fill_keys($attendanceCodes, 0);
    
        foreach ($attendanceRecords as $record) {
            if (array_key_exists($record->status, $attendanceCounts)) {
                $attendanceCounts[$record->status]++;
            }
        }
    
        $attendanceByStudent = $attendanceRecords->groupBy('student_id');
        $attendanceData = [];
        foreach ($attendanceByStudent as $studentId => $records) {
            $statuses = $records->pluck('status')->filter(function ($status) {
                return is_string($status) || is_int($status);
            })->toArray();
    
            $studentData = [
                'student_id' => $studentId,
                'attendance' => array_count_values($statuses)
            ];
            $attendanceData[] = $studentData;
        }
    
        return view('attendance.attendance-codes-class', [
            'classId' => $classId,
            'term' => $selectedTerm,
            'attendanceCounts' => $attendanceCounts,
            'attendanceData' => $attendanceData,
        ]);
    }
    


    public function navigateWeek(Request $request): View|JsonResponse|RedirectResponse
    {
        $request->validate([
            'currentWeekStart' => 'required|date',
            'direction' => 'required|integer|in:-1,1',
        ]);

        $classId = session('class_id');
        $termId = session('term_id');

        if (!$classId || !$termId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please refresh the page.'
            ], 422);
        }

        $currentWeekStart = Carbon::parse($request->currentWeekStart);
        $direction = $request->direction;

        if ($direction == -1) {
            $newWeekStart = $currentWeekStart->subWeek();
        } else {
            $newWeekStart = $currentWeekStart->addWeek();
        }

        $request->merge(['is_ajax' => true]);
        return $this->showClassList($request, $classId, $termId, $newWeekStart->format('Y-m-d'));
    }

    public function generateAttendanceSummary(Request $request, $classId): View
    {
        $klass = Klass::with(['students', 'term'])->findOrFail($classId);

        // Authorization check
        $this->authorize('viewAttendance', $klass);

        $term = Term::findOrFail($klass->term_id);

        $school_data = SchoolSetup::firstOrFail();

        $startDate = Carbon::parse($term->start_date);
        $endDate = Carbon::parse($term->end_date);

        $attendanceCodes = Attendance::getValidCodes();
        $absentCodes = Attendance::getAbsentCodes();

        // Load all attendance records in a single query to avoid N+1
        $allAttendances = Attendance::where('klass_id', $classId)
            ->where('term_id', $klass->term_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $summary = [];
        foreach ($klass->students as $student) {
            $counts = array_fill_keys($attendanceCodes, 0);

            $studentAttendances = $allAttendances->get($student->id, collect());

            foreach ($studentAttendances as $attendance) {
                if (in_array($attendance->status, $attendanceCodes)) {
                    $counts[$attendance->status]++;
                }
            }

            $absentTotal = 0;
            foreach ($absentCodes as $absentCode) {
                $absentTotal += $counts[$absentCode] ?? 0;
            }

            $summary[] = [
                'student_name' => $student->fullname,
                'gender' => $student->gender,
                'counts' => $counts,
                'absent_total' => $absentTotal,
                'total' => array_sum($counts)
            ];
        }

        return view('attendance.students-attendance-class-summary', [
            'summary' => $summary,
            'codes' => $attendanceCodes,
            'klass' => $klass,
            'term' => $term,
            'school_data' => $school_data
        ]);
    }

    public function getManualEntry(Request $request): JsonResponse
    {
        $request->validate([
            'studentId' => 'required|exists:students,id',
            'termId' => 'required|exists:terms,id',
        ]);

        $manualEntry = ManualAttendanceEntry::where('student_id', $request->studentId)
            ->where('term_id', $request->termId)
            ->first();

        if ($manualEntry) {
            return response()->json([
                'success' => true,
                'data' => [
                    'days_absent' => $manualEntry->days_absent,
                    'school_fees_owing' => $manualEntry->school_fees_owing,
                    'other_info' => $manualEntry->other_info,
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'No manual entry found']);
    }

    public function holidays(): View
    {
        $this->authorize('manageSettings');

        $currentTerm = TermHelper::getCurrentTerm();
        $selectedYear = $currentTerm ? (int) $currentTerm->year : (int) date('Y');

        // Get years from terms table
        $years = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return view('attendance.holidays', [
            'years' => $years,
            'selectedYear' => $selectedYear,
            'currentTerm' => $currentTerm
        ]);
    }


    public function holidayList($termId): View
    {
        $this->authorize('manageSettings');

        // Validate termId exists
        Term::findOrFail($termId);

        $holidays = Holiday::where('term_id', $termId)->get();
        return view('attendance.holiday-list', ['holidays' => $holidays]);
    }


    public function addDays(Request $request): RedirectResponse
    {
        $this->authorize('manageSettings');

        $messages = [
            'term_id.required' => 'The term field is required.',
            'term_id.integer' => 'The term ID must be a valid integer.',
            'year.required' => 'The year field is required.',
            'year.date_format' => 'The year must be in the format YYYY.',
            'name.required' => 'The name of the holiday is required.',
            'name.string' => 'The holiday name must be a valid string.',
            'name.max' => 'The holiday name must not exceed 200 characters.',
            'start_date.required' => 'The start date of the holiday is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.required' => 'The end date of the holiday is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];

        try {
            $validatedData = $request->validate([
                'term_id' => 'required|integer|exists:terms,id',
                'year' => 'required|date_format:Y',
                'name' => 'required|string|max:200',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ], $messages);

            DB::transaction(function () use ($validatedData) {
                Holiday::updateOrCreate(
                    [
                        'term_id' => $validatedData['term_id'],
                        'year' => $validatedData['year'],
                        'name' => $validatedData['name'],
                    ],
                    $validatedData
                );
            });

            return redirect()->back()->with('message', 'Holiday updated or created successfully!');
        } catch (ValidationException $e) {
            Log::error('Validation error while adding or updating holiday', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while adding or updating holiday', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Could not add or update the holiday. Please try again later.');
        }
    }


    public function updateHoliday(Request $request, $id): RedirectResponse
    {
        $this->authorize('manageSettings');

        $request->validate([
            'name' => 'required|string|max:191',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $holiday = Holiday::findOrFail($id);
                $holiday->name = $request->input('name');
                $holiday->start_date = $request->input('start_date');
                $holiday->end_date = $request->input('end_date');
                $holiday->save();
            });

            return redirect()->back()->with('message', 'Holiday updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating holiday', [
                'error' => $e->getMessage(),
                'holiday_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the holiday.');
        }
    }


    public function deleteHoliday($id): RedirectResponse
    {
        $this->authorize('manageSettings');

        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();
            return redirect()->back()->with('message', 'Holiday deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the holiday.');
        }
    }



    public function getTotalHolidayDays($termId): int
    {
        $holidays = Holiday::where('term_id', $termId)->get();
        $totalHolidayDays = 0;

        foreach ($holidays as $holiday) {
            $from = Carbon::parse($holiday->start_date);
            $to = Carbon::parse($holiday->end_date);

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                if (!$date->isWeekend()) {
                    $totalHolidayDays++;
                }
            }
        }

        return $totalHolidayDays;
    }


    public function store(Request $request): RedirectResponse
    {
        $validCodes = Attendance::getValidCodes();
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*' => 'array',
            'attendance.*.*' => ['nullable', 'string', Rule::in($validCodes)],
            'term' => 'required|integer|exists:terms,id',
            'klass' => 'required|integer|exists:klasses,id',
            'year' => 'required|integer',
        ]);

        $attendanceData = $request->input('attendance');
        $term = $request->input('term');
        $klassId = $request->input('klass');
        $year = $request->input('year');
        $currentWeekStart = $request->input('currentWeekStart');

        // Authorization check - ensure user can edit this class's attendance
        $klass = Klass::findOrFail($klassId);
        $this->authorize('editAttendance', $klass);

        // Preserve the current week and class selection for after redirect
        Session::put('selectedClassId', $klassId);
        Session::put('selected_term_id', $term);
        Session::put('selectedWeekStart', $currentWeekStart);

        try {
            DB::transaction(function () use ($attendanceData, $term, $klassId, $year) {
                foreach ($attendanceData as $studentId => $dailyAttendance) {
                    foreach ($dailyAttendance as $date => $status) {
                        $parsedDate = Carbon::parse($date);
                        Attendance::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'klass_id' => $klassId,
                                'term_id' => $term,
                                'date' => $parsedDate->format('Y-m-d')
                            ],
                            [
                                'status' => $status,
                                'year' => $year
                            ]
                        );
                    }
                }
            });

            return redirect()->back()->with('message', 'Attendance updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error saving attendance', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while saving attendance. Please try again.');
        }
    }
    
    

    public function classAttendanceReport(Request $request, $classId): View
    {
        $klass = Klass::with(['students', 'teacher'])->findOrFail($classId);

        // Authorization check
        $this->authorize('viewAttendance', $klass);

        $school_data = SchoolSetup::firstOrFail();

        $currentWeekStart = $request->input('currentWeekStart', now());
        $year = Carbon::parse($currentWeekStart)->year;
        $month = Carbon::parse($currentWeekStart)->month;
        
        $dates = collect();
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates->push($date->copy());
        }

        $attendances = Attendance::select('student_id', 'date', 'status')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('klass_id', $klass->id)
            ->get()
            ->groupBy('student_id');

        return view('attendance.students-attendance-class', compact(
            'klass', 'school_data','dates', 
            'attendances', 'year', 'month'
        ));
    }
 
    public function storeSelectedClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grade' => 'required|integer',
        ]);
        session(['selectedClassId' => $validated['grade']]);
        return response()->json(['message' => 'Selection saved successfully.']);
    }


    public function getGradesAndKlassesForTermWithTeacher(Request $request): JsonResponse
    {
        $request->validate([
            'term_id' => 'required|integer|exists:terms,id',
        ]);

        $termId = $request->term_id;

        $grades = Grade::with(['klasses.teacher' => function ($query) {
            $query->select('id', 'firstname', 'lastname');
        }])
        ->where('term_id', $termId)
        ->get();

        $grades = $this->filterGradesByUserAccess($grades, Auth::user());

        $gradesWithKlasses = $grades->map(function ($grade) {
            return [
                'id' => $grade->id,
                'name' => $grade->name,
                'klasses' => $grade->klasses->map(function ($klass) {
                    return [
                        'id' => $klass->id, 
                        'name' => $klass->name,
                        'teacher' => $klass->teacher ? $klass->teacher->firstname . ' ' . $klass->teacher->lastname : 'No teacher assigned'
                    ];
                })
            ];
        });
        return response()->json($gradesWithKlasses->values()->all());
    }


    public function storeManualEntry(Request $request): RedirectResponse
    {
        $request->validate([
            'studentId' => 'required|exists:students,id',
            'term_id' => 'required|exists:terms,id',
            'daysAbsent' => 'required|integer|min:0',
            'schoolFeesOwing' => 'nullable|numeric|min:0',
            'other' => 'nullable|string|max:200',
        ]);

        $selectedTermId = $request->term_id;
        $studentId = $request->studentId;
        $studentIds = explode(',', $request->student_ids);
        $currentIndex = (int) $request->index;
        $totalStudents = count($studentIds);

        try {
            DB::transaction(function () use ($studentId, $selectedTermId, $request) {
                ManualAttendanceEntry::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'term_id' => $selectedTermId,
                    ],
                    [
                        'days_absent' => $request->daysAbsent,
                        'school_fees_owing' => $request->schoolFeesOwing,
                        'other_info' => $request->other,
                    ]
                );
            });

            if ($request->input('action') === 'save_and_next' && $currentIndex < $totalStudents - 1) {
                $nextIndex = $currentIndex + 1;
                $nextStudentId = $studentIds[$nextIndex];

                return redirect()->route('attendance.get-manual-entry-form', [
                    'studentId' => $nextStudentId,
                    'studentIds' => implode(',', $studentIds),
                    'index' => $nextIndex,
                ]);
            }

            if ($currentIndex == $totalStudents - 1) {
                $message = "All students processed. Entry saved successfully.";
            } else {
                $message = "Entry saved successfully for student " . ($currentIndex + 1) . " of $totalStudents.";
            }

            return redirect()->back()->with('message', $message);
        } catch (\Exception $e) {
            Log::error('Error saving manual attendance entry', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while saving the entry. Please try again.');
        }
    }
}
