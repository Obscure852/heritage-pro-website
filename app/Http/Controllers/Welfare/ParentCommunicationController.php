<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Welfare\ParentCommunication;
use App\Models\Welfare\WelfareCase;
use App\Services\Welfare\WelfareAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentCommunicationController extends Controller
{
    protected WelfareAuditService $auditService;

    public function __construct(WelfareAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = ParentCommunication::with(['student', 'staffMember', 'welfareCase'])
            ->latest('communication_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('follow_up')) {
            $query->where('follow_up_required', true)
                ->where('follow_up_completed', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $communications = $query->paginate(20);

        $stats = [
            'this_week' => ParentCommunication::whereBetween('communication_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'pending_follow_up' => ParentCommunication::where('follow_up_required', true)
                ->where('follow_up_completed', false)->count(),
            'meetings_scheduled' => ParentCommunication::where('method', 'in_person')
                ->where('communication_date', '>=', now())->count(),
        ];

        return view('welfare.communications.index', compact('communications', 'stats'));
    }

    public function create()
    {
        $students = Student::where('status', 'Current')->orderBy('first_name')->get();
        $cases = WelfareCase::whereNotIn('status', ['closed'])->orderBy('case_number', 'desc')->get();

        return view('welfare.communications.create', compact('students', 'cases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'welfare_case_id' => 'nullable|exists:welfare_cases,id',
            'type' => 'required|in:welfare_update,concern,positive_feedback,meeting,incident_notification,general',
            'method' => 'required|in:phone,email,sms,in_person,video_call,letter,home_visit',
            'direction' => 'required|in:outbound,inbound',
            'communication_date' => 'required|date',
            'communication_time' => 'nullable|date_format:H:i',
            'parent_guardian_name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:50',
            'contact_used' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'summary' => 'required|string',
            'detailed_notes' => 'nullable|string',
            'meeting_location' => 'nullable|string|max:255',
            'meeting_duration_minutes' => 'nullable|integer',
            'outcome' => 'nullable|string',
            'action_items' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date|after:communication_date',
            'parent_response' => 'nullable|string',
            'parent_concerns' => 'nullable|string',
        ]);

        $term = \App\Models\Term::find(session('selected_term_id'));

        $validated['staff_member_id'] = Auth::id();
        $validated['term_id'] = session('selected_term_id');
        $validated['year'] = $term ? $term->year : now()->year;
        $validated['follow_up_completed'] = false;

        $communication = ParentCommunication::create($validated);

        $this->auditService->log('create', 'parent_communication', $communication->id, [
            'student_id' => $validated['student_id'],
            'type' => $validated['type'],
        ]);

        return redirect()->route('welfare.communications.edit', $communication)
            ->with('success', 'Communication recorded successfully.');
    }


    public function edit(ParentCommunication $communication)
    {
        $students = Student::where('status', 'Current')->orderBy('first_name')->get();
        $cases = WelfareCase::whereNotIn('status', ['closed'])->orderBy('case_number', 'desc')->get();

        return view('welfare.communications.edit', compact('communication', 'students', 'cases'));
    }

    public function update(Request $request, ParentCommunication $communication)
    {
        $validated = $request->validate([
            'type' => 'required|in:welfare_update,concern,positive_feedback,meeting,incident_notification,general',
            'method' => 'required|in:phone,email,sms,in_person,video_call,letter,home_visit',
            'direction' => 'required|in:outbound,inbound',
            'communication_date' => 'required|date',
            'communication_time' => 'nullable|date_format:H:i',
            'parent_guardian_name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:50',
            'contact_used' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'summary' => 'required|string',
            'detailed_notes' => 'nullable|string',
            'meeting_location' => 'nullable|string|max:255',
            'meeting_duration_minutes' => 'nullable|integer',
            'outcome' => 'nullable|string',
            'action_items' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date',
            'parent_response' => 'nullable|string',
            'parent_concerns' => 'nullable|string',
        ]);

        $communication->update($validated);

        $this->auditService->log('update', 'parent_communication', $communication->id, $validated);

        return redirect()->route('welfare.communications.edit', $communication)
            ->with('success', 'Communication updated successfully.');
    }

    public function destroy(ParentCommunication $communication)
    {
        $this->auditService->log('delete', 'parent_communication', $communication->id);

        $communication->delete();

        return redirect()->route('welfare.communications.index')
            ->with('success', 'Communication deleted.');
    }

    public function completeFollowUp(Request $request, ParentCommunication $communication)
    {
        $validated = $request->validate([
            'outcome' => 'required|string',
        ]);

        $communication->update([
            'follow_up_completed' => true,
            'outcome' => $validated['outcome'],
        ]);

        $this->auditService->log('complete_follow_up', 'parent_communication', $communication->id, $validated);

        return back()->with('success', 'Follow-up marked as complete.');
    }
}
