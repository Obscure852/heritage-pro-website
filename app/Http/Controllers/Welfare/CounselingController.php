<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\CounselingSession;
use App\Models\Welfare\WelfareCase;
use App\Services\Welfare\CounselingService;
use Illuminate\Http\Request;

class CounselingController extends Controller
{
    protected CounselingService $counselingService;

    public function __construct(CounselingService $counselingService)
    {
        $this->counselingService = $counselingService;
    }

    public function index(Request $request){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('viewAny', CounselingSession::class);

        $filters = $request->only(['status', 'session_type', 'student_id', 'date_from', 'date_to', 'risk_assessment']);

        if (!$user->hasRoles('Administrator')) {
            $sessions = $this->counselingService->getCounsellorSessions($user, $filters, 20);
        } else {
            $sessions = CounselingSession::with(['student', 'counsellor', 'welfareCase'])
                ->orderBy('session_date', 'desc')
                ->paginate(20);
        }

        return view('welfare.counseling.index', compact('sessions', 'filters'));
    }

    public function create(Request $request){
        $this->authorize('create', CounselingSession::class);
        $students = Student::orderBy('first_name')->get();
        $cases = WelfareCase::open()->with('student')->get();

        $counsellors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Counselor', 'Welfare Officer', 'Deputy Principal', 'Principal', 'Teacher']);
        })->orderBy('firstname')->get();

        $selectedStudent = $request->has('student_id')
            ? Student::find($request->student_id)
            : null;

        $selectedCase = $request->has('case_id')
            ? WelfareCase::find($request->case_id)
            : null;

        return view('welfare.counseling.create', compact('students', 'cases', 'counsellors', 'selectedStudent', 'selectedCase'));
    }


    public function store(Request $request)
    {
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('create', CounselingSession::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'welfare_case_id' => 'nullable|exists:welfare_cases,id',
            'counsellor_id' => 'nullable|exists:users,id',
            'session_type' => 'required|in:individual,follow_up,crisis,group,family',
            'session_date' => 'required|date',
            'session_time' => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'presenting_issue' => 'nullable|string|max:500',
        ]);

        $result = $this->counselingService->createSession($validated, $user);

        // Handle duplicate session response
        if (is_array($result) && ($result['duplicate'] ?? false)) {
            $existingSession = $result['existing_session'];
            return redirect()
                ->route('welfare.counseling.edit', $existingSession)
                ->with('warning', $result['message'] . ' Redirected to existing session.');
        }

        return redirect()
            ->route('welfare.counseling.edit', $result)
            ->with('success', 'Counseling session scheduled successfully.');
    }

    public function edit(CounselingSession $session){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('update', $session);

        $session->load(['student', 'counsellor', 'welfareCase']);
        $canViewNotes = $user->can('viewNotes', $session);

        // Get welfare cases for the student
        $cases = \App\Models\Welfare\WelfareCase::where('student_id', $session->student_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counsellors (users with counseling permissions)
        $counsellors = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Counselor', 'School Counselor', 'Welfare Officer']);
        })->orderBy('firstname')->get();

        return view('welfare.counseling.edit', compact('session', 'canViewNotes', 'cases', 'counsellors'));
    }

    public function update(Request $request, CounselingSession $session)
    {
        $this->authorize('update', $session);

        $validated = $request->validate([
            'session_type' => 'required|in:individual,follow_up,crisis,group,family',
            'session_date' => 'required|date',
            'session_time' => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'counsellor_id' => 'nullable|exists:users,id',
            'welfare_case_id' => 'nullable|exists:welfare_cases,id',
            'confidentiality_level' => 'nullable|integer|in:2,3,4',
            'presenting_issue' => 'nullable|string|max:500',
            'session_notes' => 'nullable|string',
            'interventions_used' => 'nullable|string',
            'student_mood' => 'nullable|in:very_low,low,neutral,good,very_good',
            'risk_assessment' => 'nullable|in:none,low,moderate,high,critical',
            'goals_discussed' => 'nullable|string',
            'homework_assigned' => 'nullable|string',
        ]);

        try {
            $this->counselingService->updateSession($session, $validated);
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('success', 'Session updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified session.
     */
    public function destroy(CounselingSession $session)
    {
        $this->authorize('delete', $session);

        try {
            $this->counselingService->deleteSession($session);
            return redirect()
                ->route('welfare.counseling.index')
                ->with('success', 'Session deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Complete a session.
     */
    public function complete(Request $request, CounselingSession $session)
    {
        $this->authorize('complete', $session);

        $validated = $request->validate([
            'session_notes' => 'required|string',
            'interventions_used' => 'nullable|string',
            'student_mood' => 'required|in:very_low,low,neutral,good,very_good',
            'risk_assessment' => 'required|in:none,low,moderate,high,critical',
            'goals_discussed' => 'nullable|string',
            'homework_assigned' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'next_session_date' => 'nullable|date|after:today',
        ]);

        try {
            $this->counselingService->completeSession($session, $validated);
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('success', 'Session completed successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a session.
     */
    public function cancel(Request $request, CounselingSession $session)
    {
        $this->authorize('cancel', $session);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->counselingService->cancelSession($session, $validated['reason'] ?? null);
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('success', 'Session cancelled.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark session as no-show.
     */
    public function noShow(CounselingSession $session)
    {
        $this->authorize('complete', $session);

        try {
            $this->counselingService->markNoShow($session);
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('success', 'Session marked as no-show.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.counseling.edit', $session)
                ->with('error', $e->getMessage());
        }
    }

    public function calendar(){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('viewAny', CounselingSession::class);

        $sessions = $this->counselingService->getUpcomingSessions(
            $user->hasRoles('Administrator') ? null : $user,
            30
        );

        return view('welfare.counseling.calendar', compact('sessions'));
    }

    public function upcoming(){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('viewAny', CounselingSession::class);

        $sessions = $this->counselingService->getUpcomingSessions(
            $user->hasRoles('Administrator') ? null : $user,
            7
        );

        $todaySessions = $this->counselingService->getTodaySessions(
            $user->hasRoles('Administrator') ? null : $user
        );

        $requireFollowUp = $this->counselingService->getSessionsRequiringFollowUp(
            $user->hasRoles('Administrator') ? null : $user
        );

        return view('welfare.counseling.upcoming', compact('sessions', 'todaySessions', 'requireFollowUp'));
    }
}
