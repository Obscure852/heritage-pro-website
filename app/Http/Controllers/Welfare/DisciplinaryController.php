<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\DisciplinaryAction;
use App\Models\Welfare\DisciplinaryIncidentType;
use App\Models\Welfare\DisciplinaryRecord;
use App\Models\Welfare\WelfareCase;
use App\Services\Welfare\DisciplinaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller{
    protected DisciplinaryService $disciplinaryService;

    public function __construct(DisciplinaryService $disciplinaryService){
        $this->disciplinaryService = $disciplinaryService;
    }


    public function index(Request $request){
        $this->authorize('viewAny', DisciplinaryRecord::class);

        $filters = $request->only(['status', 'incident_type_id', 'student_id', 'reported_by', 'date_from', 'date_to']);

        $records = $this->disciplinaryService->getRecords($filters, 20);
        $incidentTypes = DisciplinaryIncidentType::active()->get();
        return view('welfare.disciplinary.index', compact('records', 'incidentTypes', 'filters'));
    }


    public function create(Request $request){
        $this->authorize('create', DisciplinaryRecord::class);

        $students = Student::orderBy('first_name')->get();
        $incidentTypes = DisciplinaryIncidentType::active()->orderBy('severity')->get();
        $actions = DisciplinaryAction::active()->orderBy('severity_level')->get();
        $cases = WelfareCase::open()->byType('DISCIP')->with('student')->get();

        $selectedStudent = $request->has('student_id')
            ? Student::find($request->student_id)
            : null;

        return view('welfare.disciplinary.create', compact(
            'students', 'incidentTypes', 'actions', 'cases', 'selectedStudent'
        ));
    }


    public function store(Request $request){
        $this->authorize('create', DisciplinaryRecord::class);
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'welfare_case_id' => 'nullable|exists:welfare_cases,id',
            'incident_type_id' => 'required|exists:disciplinary_incident_types,id',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'witnesses' => 'nullable|string',
            'evidence' => 'nullable|string',
        ]);

        /** @var User $user */
        $user = auth()->user();
        $result = $this->disciplinaryService->createRecord($validated, $user);

        // Handle duplicate detection
        if (is_array($result) && ($result['duplicate'] ?? false)) {
            return redirect()
                ->route('welfare.disciplinary.edit', $result['existing_record'])
                ->with('warning', $result['message']);
        }

        return redirect()
            ->route('welfare.disciplinary.edit', $result)
            ->with('success', 'Disciplinary incident recorded successfully.');
    }


    public function edit(DisciplinaryRecord $record){
        $this->authorize('update', $record);
        $record->load(['student', 'incidentType', 'action', 'reportedBy', 'resolvedBy', 'welfareCase']);

        $incidentTypes = DisciplinaryIncidentType::active()->orderBy('severity')->get();
        $actions = DisciplinaryAction::active()->orderBy('severity_level')->get();
        $availableActions = $this->disciplinaryService->getAvailableActions(
            $record->incidentType?->severity
        );

        return view('welfare.disciplinary.edit', compact('record', 'incidentTypes', 'actions', 'availableActions'));
    }


    public function update(Request $request, DisciplinaryRecord $record){
        $this->authorize('update', $record);

        $validated = $request->validate([
            'incident_type_id' => 'required|exists:disciplinary_incident_types,id',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'witnesses' => 'nullable|string',
            'evidence' => 'nullable|string',
            'status' => 'required|in:reported,investigating,pending_action,action_in_progress,resolved,appealed',
        ]);

        $this->disciplinaryService->updateRecord($record, $validated);

        return redirect()->back()->with('success', 'Record updated successfully.');
    }


    public function destroy(DisciplinaryRecord $record){
        $this->authorize('delete', $record);

        try {
            $this->disciplinaryService->deleteRecord($record);

            return redirect()
                ->route('welfare.disciplinary.index')
                ->with('success', 'Record deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.disciplinary.edit', $record)
                ->with('error', $e->getMessage());
        }
    }


    public function applyAction(Request $request, DisciplinaryRecord $record)
    {
        $this->authorize('applyAction', $record);

        $validated = $request->validate([
            'action_id' => 'required|exists:disciplinary_actions,id',
            'action_start_date' => 'required|date',
            'action_end_date' => 'nullable|date|after_or_equal:action_start_date',
            'action_notes' => 'nullable|string|max:1000',
        ]);

        // Check for severe actions
        $action = DisciplinaryAction::find($validated['action_id']);
        if ($action && $action->severity_level >= 4) {
            $this->authorize('applySevereAction', $record);
        }

        try {
            $this->disciplinaryService->applyAction(
                $record,
                $validated['action_id'],
                Carbon::parse($validated['action_start_date']),
                $validated['action_end_date'] ? Carbon::parse($validated['action_end_date']) : null,
                $validated['action_notes'] ?? null
            );

            return redirect()
                ->route('welfare.disciplinary.edit', $record)
                ->with('success', 'Disciplinary action applied successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.disciplinary.edit', $record)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Resolve the record.
     */
    public function resolve(Request $request, DisciplinaryRecord $record)
    {
        $this->authorize('resolve', $record);

        $validated = $request->validate([
            'resolution' => 'nullable|string|max:1000',
        ]);

        try {
            /** @var User $user */
            $user = auth()->user();
            $this->disciplinaryService->resolveRecord($record, $user, $validated['resolution'] ?? null);

            return redirect()
                ->route('welfare.disciplinary.edit', $record)
                ->with('success', 'Record resolved successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.disciplinary.edit', $record)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record parent notification.
     */
    public function notifyParent(Request $request, DisciplinaryRecord $record)
    {
        $this->authorize('notifyParent', $record);

        $validated = $request->validate([
            'parent_response' => 'nullable|string|max:1000',
        ]);

        $this->disciplinaryService->recordParentNotification($record, $validated['parent_response'] ?? null);

        return redirect()
            ->route('welfare.disciplinary.edit', $record)
            ->with('success', 'Parent notification recorded.');
    }

    /**
     * Display available actions.
     */
    public function actions()
    {
        $this->authorize('viewAny', DisciplinaryRecord::class);

        $actions = DisciplinaryAction::orderBy('severity_level')->get();
        $incidentTypes = DisciplinaryIncidentType::active()->with('defaultAction')->get();

        return view('welfare.disciplinary.actions', compact('actions', 'incidentTypes'));
    }
}
