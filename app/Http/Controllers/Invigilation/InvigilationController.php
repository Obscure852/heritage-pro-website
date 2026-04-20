<?php

namespace App\Http\Controllers\Invigilation;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController;
use App\Http\Requests\Invigilation\StoreInvigilationAssignmentRequest;
use App\Http\Requests\Invigilation\StoreInvigilationRoomRequest;
use App\Http\Requests\Invigilation\StoreInvigilationSeriesRequest;
use App\Http\Requests\Invigilation\StoreInvigilationSessionRequest;
use App\Http\Requests\Invigilation\UpdateInvigilationAssignmentRequest;
use App\Http\Requests\Invigilation\UpdateInvigilationRoomRequest;
use App\Http\Requests\Invigilation\UpdateInvigilationSeriesRequest;
use App\Http\Requests\Invigilation\UpdateInvigilationSessionRequest;
use App\Models\Term;
use App\Models\Invigilation\InvigilationAssignment;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSession;
use App\Models\Invigilation\InvigilationSessionRoom;
use App\Services\Invigilation\InvigilationRosterService;
use App\Services\Invigilation\InvigilationPublishNotificationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvigilationController extends Controller
{
    public function __construct(
        protected InvigilationRosterService $invigilationRosterService,
        protected InvigilationPublishNotificationService $invigilationPublishNotificationService,
        protected SettingsService $settingsService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();
        $selectedTermId = (int) ($request->query('term_id') ?: session('selected_term_id', $currentTerm?->id));
        $selectedTerm = Term::query()->find($selectedTermId) ?? $currentTerm;
        $selectedTermId = $selectedTerm?->id;

        $series = InvigilationSeries::query()
            ->with(['term', 'creator:id,firstname,lastname', 'publisher:id,firstname,lastname', 'sessions.rooms.assignments'])
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->orderByRaw("CASE WHEN status = 'published' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'series' => $series->count(),
            'sessions' => $series->sum(fn (InvigilationSeries $item) => $item->sessions->count()),
            'assignments' => $series->sum(fn (InvigilationSeries $item) => $item->sessions->flatMap->rooms->sum(fn (InvigilationSessionRoom $room) => $room->assignments->count())),
        ];

        return view('invigilation.index', [
            'series' => $series,
            'stats' => $stats,
            'terms' => $terms,
            'currentTerm' => $currentTerm,
            'selectedTerm' => $selectedTerm,
            'seriesTypes' => InvigilationSeries::types(),
            'eligibilityPolicies' => InvigilationSeries::eligibilityPolicies(),
            'timetablePolicies' => InvigilationSeries::timetableConflictPolicies(),
            'createDefaults' => InvigilationSettingsController::defaults($this->settingsService),
        ]);
    }

    public function store(StoreInvigilationSeriesRequest $request): RedirectResponse
    {
        $series = InvigilationSeries::query()->create([
            ...$request->validated(),
            'status' => InvigilationSeries::STATUS_DRAFT,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', 'Invigilation series created successfully.');
    }

    public function show(InvigilationSeries $series)
    {
        $series = $this->invigilationRosterService->loadSeriesDetail($series);

        $metrics = $this->invigilationRosterService->detailMetrics($series);
        $issues = $this->invigilationRosterService->buildIssues($series);

        $gradeSubjects = $this->invigilationRosterService->gradeSubjectOptions($series->term_id);
        $teachers = $this->invigilationRosterService->teacherOptions();
        $venues = $this->invigilationRosterService->venueOptions();

        $klassSubjectOptions = $series->sessions->mapWithKeys(
            fn (InvigilationSession $session) => [$session->id => $this->invigilationRosterService->klassSubjectOptions($session)]
        );
        $optionalSubjectOptions = $series->sessions->mapWithKeys(
            fn (InvigilationSession $session) => [$session->id => $this->invigilationRosterService->optionalSubjectOptions($session)]
        );
        $addSessionKlassSubjectOptions = $gradeSubjects->mapWithKeys(
            fn ($gradeSubject) => [$gradeSubject->id => $this->invigilationRosterService->klassSubjectOptionsForGradeSubject($series->term_id, (int) $gradeSubject->id)]
        );
        $addSessionOptionalSubjectOptions = $gradeSubjects->mapWithKeys(
            fn ($gradeSubject) => [$gradeSubject->id => $this->invigilationRosterService->optionalSubjectOptionsForGradeSubject($series->term_id, (int) $gradeSubject->id)]
        );

        return view('invigilation.show', [
            'series' => $series,
            'metrics' => $metrics,
            'issues' => $issues,
            'issueSummary' => $this->invigilationRosterService->issueSummary($series),
            'gradeSubjects' => $gradeSubjects,
            'teachers' => $teachers,
            'venues' => $venues,
            'klassSubjectOptions' => $klassSubjectOptions,
            'optionalSubjectOptions' => $optionalSubjectOptions,
            'addSessionKlassSubjectOptions' => $addSessionKlassSubjectOptions,
            'addSessionOptionalSubjectOptions' => $addSessionOptionalSubjectOptions,
            'seriesTypes' => InvigilationSeries::types(),
            'statuses' => InvigilationSeries::statuses(),
            'eligibilityPolicies' => InvigilationSeries::eligibilityPolicies(),
            'timetablePolicies' => InvigilationSeries::timetableConflictPolicies(),
        ]);
    }

    public function update(UpdateInvigilationSeriesRequest $request, InvigilationSeries $series): RedirectResponse
    {
        try {
            $this->ensureSeriesEditable($series);
            $series->update($request->validated());
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', 'Invigilation series updated successfully.');
    }

    public function storeSession(StoreInvigilationSessionRequest $request, InvigilationSeries $series): RedirectResponse
    {
        try {
            $this->ensureSeriesEditable($series);
            $validated = $request->validated();
            $this->validateDayOfCycleRequirement($series, $validated);
            $createFirstRoom = !empty($validated['initial_room_venue_id']);

            DB::transaction(function () use ($series, $validated, $createFirstRoom): void {
                $session = $series->sessions()->create([
                    'grade_subject_id' => $validated['grade_subject_id'],
                    'paper_label' => $validated['paper_label'] ?? null,
                    'exam_date' => $validated['exam_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'day_of_cycle' => $validated['day_of_cycle'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                if (!$createFirstRoom) {
                    return;
                }

                $roomPayload = $this->invigilationRosterService->validateRoomPayload($session->load('series'), [
                    'venue_id' => (int) $validated['initial_room_venue_id'],
                    'source_type' => $validated['initial_room_source_type'] ?? InvigilationSessionRoom::SOURCE_MANUAL,
                    'klass_subject_id' => $validated['initial_room_klass_subject_id'] ?? null,
                    'optional_subject_id' => $validated['initial_room_optional_subject_id'] ?? null,
                    'group_label' => $validated['initial_room_group_label'] ?? null,
                    'candidate_count' => $validated['initial_room_candidate_count'] ?? null,
                    'required_invigilators' => $validated['initial_room_required_invigilators'] ?? $series->default_required_invigilators,
                ]);

                $session->rooms()->create($roomPayload);
            });
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', !empty($validated['initial_room_venue_id'])
                ? 'Exam session and first room added successfully.'
                : 'Exam session added successfully.');
    }

    public function updateSession(UpdateInvigilationSessionRequest $request, InvigilationSession $session): RedirectResponse
    {
        try {
            $series = $session->series()->firstOrFail();
            $this->ensureSeriesEditable($series);
            $validated = $request->validated();
            $this->validateDayOfCycleRequirement($series, $validated);

            $session->update($validated);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $session->series_id)
            ->with('message', 'Exam session updated successfully.');
    }

    public function destroySession(InvigilationSession $session): RedirectResponse
    {
        try {
            $series = $session->series()->firstOrFail();
            $this->ensureSeriesEditable($series);
            $seriesId = $session->series_id;
            $session->delete();
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $seriesId)
            ->with('message', 'Exam session removed successfully.');
    }

    public function storeRoom(StoreInvigilationRoomRequest $request, InvigilationSession $session): RedirectResponse
    {
        try {
            $series = $session->series()->firstOrFail();
            $this->ensureSeriesEditable($series);
            $payload = $this->invigilationRosterService->validateRoomPayload($session->load('series'), $request->validated());
            $session->rooms()->create($payload);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $session->series_id)
            ->with('message', 'Session room added successfully.');
    }

    public function updateRoom(UpdateInvigilationRoomRequest $request, InvigilationSessionRoom $room): RedirectResponse
    {
        try {
            $series = $room->session()->with('series')->firstOrFail()->series;
            $this->ensureSeriesEditable($series);
            $payload = $this->invigilationRosterService->validateRoomPayload(
                $room->session()->with('series')->firstOrFail(),
                $request->validated(),
                $room
            );

            $room->update($payload);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $room->session?->series_id)
            ->with('message', 'Session room updated successfully.');
    }

    public function destroyRoom(InvigilationSessionRoom $room): RedirectResponse
    {
        try {
            $session = $room->session()->firstOrFail();
            $this->ensureSeriesEditable($session->series()->firstOrFail());
            $seriesId = $session->series_id;
            $room->delete();
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $seriesId)
            ->with('message', 'Session room removed successfully.');
    }

    public function storeAssignment(StoreInvigilationAssignmentRequest $request, InvigilationSessionRoom $room): RedirectResponse
    {
        try {
            $session = $room->session()->firstOrFail();
            $this->ensureSeriesEditable($session->series()->firstOrFail());
            $validated = $request->validated();

            if (!$this->invigilationRosterService->roomHasCapacityForAssignment($room)) {
                throw ValidationException::withMessages([
                    'assignment' => 'This room already has all required invigilator slots filled.',
                ]);
            }

            if ($this->invigilationRosterService->teacherOverlapsAnotherDuty((int) $validated['user_id'], $room)) {
                throw ValidationException::withMessages([
                    'user_id' => 'That teacher already has an overlapping invigilation duty.',
                ]);
            }

            $room->assignments()->create([
                'user_id' => (int) $validated['user_id'],
                'assignment_order' => $this->invigilationRosterService->nextAssignmentOrders($room, 1)[0],
                'assignment_source' => InvigilationAssignment::SOURCE_MANUAL,
                'locked' => (bool) $validated['locked'],
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $room->session?->series_id)
            ->with('message', 'Invigilator added successfully.');
    }

    public function updateAssignment(UpdateInvigilationAssignmentRequest $request, InvigilationAssignment $assignment): RedirectResponse
    {
        try {
            $room = $assignment->sessionRoom()->with('session.series')->firstOrFail();
            $this->ensureSeriesEditable($room->session->series);
            $validated = $request->validated();
            $userId = (int) $validated['user_id'];

            if ($this->invigilationRosterService->teacherOverlapsAnotherDuty($userId, $room, $assignment->id)) {
                throw ValidationException::withMessages([
                    'user_id' => 'That teacher already has an overlapping invigilation duty.',
                ]);
            }

            $assignment->update([
                'user_id' => $userId,
                'locked' => (bool) $validated['locked'],
                'notes' => $validated['notes'] ?? null,
                'assignment_source' => InvigilationAssignment::SOURCE_MANUAL,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $assignment->sessionRoom?->session?->series_id)
            ->with('message', 'Assignment updated successfully.');
    }

    public function destroyAssignment(InvigilationAssignment $assignment): RedirectResponse
    {
        try {
            $room = $assignment->sessionRoom()->with('session.series')->firstOrFail();
            $this->ensureSeriesEditable($room->session->series);
            $seriesId = $room->session->series_id;
            $assignment->delete();
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $seriesId)
            ->with('message', 'Assignment removed successfully.');
    }

    public function generate(InvigilationSeries $series, Request $request): RedirectResponse
    {
        try {
            $this->ensureSeriesEditable($series);
            $result = $this->invigilationRosterService->generateAssignments($series, $request->user()?->id);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        $message = sprintf('Roster generation completed. %d assignment(s) created.', $result['created']);
        if (!empty($result['shortages'])) {
            $message .= ' Some rooms still need manual coverage.';
        }

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', $message);
    }

    public function publish(InvigilationSeries $series, Request $request): RedirectResponse
    {
        try {
            $this->invigilationRosterService->publish($series, $request->user()?->id);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        $notificationSummary = $this->invigilationPublishNotificationService->notifyAssignedTeachers(
            $series->fresh(),
            $request->user()
        );

        $message = 'Invigilation series published successfully.';

        if (($notificationSummary['sent_count'] ?? 0) > 0) {
            $message .= sprintf(
                ' %d direct message(s) sent to assigned teacher(s).',
                (int) $notificationSummary['sent_count']
            );
        } elseif (($notificationSummary['recipient_count'] ?? 0) > 0 && !($notificationSummary['enabled'] ?? false)) {
            $message .= ' Assigned staff were not messaged because direct messages are disabled.';
        } elseif (($notificationSummary['failed_count'] ?? 0) > 0) {
            $message .= ' Some direct messages could not be delivered.';
        }

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', $message);
    }

    public function unpublish(InvigilationSeries $series): RedirectResponse
    {
        try {
            $this->invigilationRosterService->unpublish($series);
        } catch (ValidationException $exception) {
            return $this->validationRedirect($exception);
        }

        return redirect()
            ->route('invigilation.show', $series)
            ->with('message', 'Invigilation series returned to draft mode.');
    }

    protected function validateDayOfCycleRequirement(InvigilationSeries $series, array $validated): void
    {
        if (
            $series->timetable_conflict_policy === InvigilationSeries::TIMETABLE_CHECK
            && empty($validated['day_of_cycle'])
        ) {
            throw ValidationException::withMessages([
                'day_of_cycle' => 'A day of cycle is required when timetable conflict checking is enabled.',
            ]);
        }
    }

    protected function validationRedirect(ValidationException $exception): RedirectResponse
    {
        return redirect()
            ->back()
            ->withErrors($exception->errors())
            ->withInput()
            ->with('error', collect($exception->errors())->flatten()->first());
    }

    protected function ensureSeriesEditable(InvigilationSeries $series): void
    {
        if ($series->isEditable()) {
            return;
        }

        throw ValidationException::withMessages([
            'series' => 'Only draft invigilation series can be edited.',
        ]);
    }
}
