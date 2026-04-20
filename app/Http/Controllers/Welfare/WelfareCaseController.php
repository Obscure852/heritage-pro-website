<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareCaseAttachment;
use App\Models\Welfare\WelfareCaseNote;
use App\Models\Welfare\WelfareType;
use App\Services\Welfare\WelfareCaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelfareCaseController extends Controller{
    protected WelfareCaseService $caseService;

    public function __construct(WelfareCaseService $caseService){
        $this->caseService = $caseService;
    }

    public function index(Request $request){
        $this->authorize('viewAny', WelfareCase::class);

        $filters = $request->only([
            'status', 'priority', 'welfare_type_id', 'assigned_to',
            'opened_by', 'student_id', 'date_from', 'date_to', 'search'
        ]);

        $cases = $this->caseService->getCases($filters, 20);
        $welfareTypes = WelfareType::active()->get();
        return view('welfare.cases.index', compact('cases', 'welfareTypes', 'filters'));
    }

    public function create(Request $request){
        $this->authorize('create', WelfareCase::class);

        $welfareTypes = WelfareType::active()->get();
        $students = Student::orderBy('first_name')->get();
        $staff = User::where('status', 'Current')->orderBy('firstname')->get();

        
        $selectedStudent = $request->has('student_id')
            ? Student::find($request->student_id)
            : null;

        return view('welfare.cases.create', compact('welfareTypes', 'students', 'staff', 'selectedStudent'));
    }


    public function store(Request $request){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('create', WelfareCase::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'welfare_type_id' => 'required|exists:welfare_types,id',
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'incident_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $result = $this->caseService->createCase($validated, $user);

        // Handle duplicate case response
        if (is_array($result) && ($result['duplicate'] ?? false)) {
            $existingCase = $result['existing_case'];
            $message = 'A case already exists for this student with the same type and incident date.';

            if ($result['can_reopen']) {
                return redirect()
                    ->route('welfare.cases.edit', $existingCase)
                    ->with('warning', $message . ' You may reopen this existing case instead.');
            }

            return redirect()
                ->route('welfare.cases.edit', $existingCase)
                ->with('warning', $message . ' View the existing active case.');
        }

        return redirect()
            ->route('welfare.cases.edit', $result)
            ->with('success', 'Welfare case created successfully. Case number: ' . $result->case_number);
    }


    public function edit(WelfareCase $case){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('update', $case);

        $case->load([
            'student',
            'welfareType',
            'openedBy',
            'assignedTo',
            'approvedBy',
            'notes.createdBy',
            'attachments.uploadedBy',
            'auditLogs.user',
            'counselingSessions',
            'disciplinaryRecords',
            'safeguardingConcerns',
            'healthIncidents',
            // 'bullyingIncidents',
            'interventionPlans',
        ]);

        
        $welfareTypes = WelfareType::active()->get();
        $staff = User::where('status', 'Current')->orderBy('firstname')->get();
        $canViewConfidential = $user->can('viewConfidential', $case);

        return view('welfare.cases.edit', compact('case', 'welfareTypes', 'staff', 'canViewConfidential'));
    }


    public function update(Request $request, WelfareCase $case){
        $this->authorize('update', $case);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,in_progress,pending_approval,resolved,closed,escalated',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            $this->caseService->updateCase($case, $validated);
            return redirect()->back()->with('success', 'Welfare case updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(WelfareCase $case){
        $this->authorize('delete', $case);

        $case->delete();

        return redirect()
            ->route('welfare.cases.index')
            ->with('success', 'Welfare case deleted successfully.');
    }

    public function assign(Request $request, WelfareCase $case){
        $this->authorize('assign', $case);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            $assignee = User::findOrFail($validated['assigned_to']);
            $this->caseService->assignCase($case, $assignee);

            return redirect()
                ->route('welfare.cases.edit', $case)
                ->with('success', 'Case assigned to ' . $assignee->full_name);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function escalate(Request $request, WelfareCase $case){
        $this->authorize('escalate', $case);

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $assignee = !empty($validated['assigned_to'])
                ? User::find($validated['assigned_to'])
                : null;

            $this->caseService->escalateCase($case, $assignee, $validated['reason'] ?? null);

            return redirect()
                ->route('welfare.cases.edit', $case)
                ->with('success', 'Case escalated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, WelfareCase $case){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('approve', $case);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->caseService->approveCase($case, $user, $validated['notes'] ?? null);
            return redirect()->back()->with('success', 'Case approved successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, WelfareCase $case){
        /** @var User|null $user */
        $user = auth()->user();
        $this->authorize('approve', $case);

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        try {
            $this->caseService->rejectCase($case, $user, $validated['notes']);
            return redirect()->back()->with('success', 'Case rejected.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function close(Request $request, WelfareCase $case){
        $this->authorize('close', $case);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->caseService->closeCase($case, $validated['notes'] ?? null);
            return redirect()->back()->with('success', 'Case closed successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reopen(Request $request, WelfareCase $case){
        $this->authorize('reopen', $case);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->caseService->reopenCase($case, $validated['reason']);
            return redirect()->back()->with('success', 'Case reopened successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function audit(WelfareCase $case){
        $this->authorize('viewAudit', $case);

        $logs = $case->auditLogs()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('welfare.cases.audit', compact('case', 'logs'));
    }

    public function addNote(Request $request, WelfareCase $case){
        $this->authorize('addNote', $case);
        $validated = $request->validate([
            'note_type' => 'required|in:general,progress,observation,meeting,follow_up,internal',
            'content' => 'required|string',
            'is_confidential' => 'boolean',
        ]);

        WelfareCaseNote::create([
            'welfare_case_id' => $case->id,
            'created_by' => auth()->id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
            'is_confidential' => $validated['is_confidential'] ?? false,
        ]);

        return redirect()->back()->with('success', 'Note added successfully.');
    }


    public function addAttachment(Request $request, WelfareCase $case){
        $this->authorize('addAttachment', $case);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255',
            'category' => 'required|in:document,image,report,evidence,consent_form,medical,other',
            'is_confidential' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('welfare/attachments/' . $case->id, 'private');

        WelfareCaseAttachment::create([
            'welfare_case_id' => $case->id,
            'uploaded_by' => auth()->id(),
            'file_name' => pathinfo($path, PATHINFO_BASENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $validated['description'],
            'category' => $validated['category'],
            'is_confidential' => $validated['is_confidential'] ?? false,
        ]);

        return redirect()
            ->route('welfare.cases.edit', $case)
            ->with('success', 'Attachment uploaded successfully.');
    }


    public function downloadAttachment(WelfareCaseAttachment $attachment){
        $this->authorize('view', $attachment->welfareCase);

        if (!$attachment->fileExists()) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }
}
