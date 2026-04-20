<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\HealthIncident;
use App\Models\Welfare\HealthIncidentType;
use App\Services\Welfare\HealthIncidentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HealthIncidentController extends Controller
{
    protected HealthIncidentService $healthService;

    public function __construct(HealthIncidentService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Display a listing of health incidents.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['incident_type_id', 'student_id', 'outcome', 'date_from', 'date_to', 'sent_home', 'emergency']);

        $incidents = $this->healthService->getIncidents($filters, 20);
        $incidentTypes = $this->healthService->getIncidentTypes();

        return view('welfare.health.index', compact('incidents', 'incidentTypes', 'filters'));
    }

    /**
     * Show the form for creating a new incident.
     */
    public function create(Request $request)
    {
        $students = Student::orderBy('first_name')->get();
        $incidentTypes = $this->healthService->getIncidentTypes();
        $staff = User::where('status', 'Current')->orderBy('firstname')->get();

        $selectedStudent = $request->has('student_id')
            ? Student::find($request->student_id)
            : null;

        return view('welfare.health.create', compact('students', 'incidentTypes', 'staff', 'selectedStudent'));
    }

    /**
     * Store a newly created incident.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'incident_type_id' => 'required|exists:health_incident_types,id',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'symptoms' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'treated_by' => 'nullable|exists:users,id',
        ]);

        $result = $this->healthService->recordIncident($validated, auth()->user());

        // Handle duplicate detection
        if (is_array($result) && ($result['duplicate'] ?? false)) {
            return redirect()
                ->route('welfare.health.edit', $result['existing_incident'])
                ->with('warning', $result['message']);
        }

        return redirect()
            ->route('welfare.health.edit', $result)
            ->with('success', 'Health incident recorded successfully.');
    }


    /**
     * Show the form for editing the incident.
     */
    public function edit(HealthIncident $incident)
    {
        $incident->load(['student', 'incidentType', 'reportedBy', 'treatedBy', 'welfareCase']);

        $incidentTypes = $this->healthService->getIncidentTypes();
        $staff = User::where('status', 'Current')->orderBy('firstname')->get();

        return view('welfare.health.edit', compact('incident', 'incidentTypes', 'staff'));
    }

    /**
     * Update the specified incident.
     */
    public function update(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'incident_type_id' => 'required|exists:health_incident_types,id',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'symptoms' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'treated_by' => 'nullable|exists:users,id',
            'outcome' => 'nullable|in:returned_to_class,rested_and_returned,sent_home,hospital,ongoing_monitoring',
            'sent_home' => 'nullable|boolean',
            'called_ambulance' => 'nullable|boolean',
            'follow_up_required' => 'nullable|boolean',
            'follow_up_notes' => 'nullable|string',
        ]);

        $this->healthService->updateIncident($incident, $validated);

        return redirect()
            ->route('welfare.health.edit', $incident)
            ->with('success', 'Incident updated successfully.');
    }

    /**
     * Remove the specified incident.
     */
    public function destroy(HealthIncident $incident)
    {
        try {
            $this->healthService->deleteIncident($incident);

            return redirect()
                ->route('welfare.health.index')
                ->with('success', 'Incident deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.health.edit', $incident)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record treatment given.
     */
    public function recordTreatment(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'treatment_given' => 'required|string',
            'treated_by' => 'required|exists:users,id',
            'medication_administered' => 'boolean',
            'medication_details' => 'nullable|required_if:medication_administered,true|string|max:500',
        ]);

        $treatedBy = User::findOrFail($validated['treated_by']);

        $this->healthService->recordTreatment(
            $incident,
            $validated['treatment_given'],
            $treatedBy,
            $validated['medication_administered'] ?? false,
            $validated['medication_details'] ?? null
        );

        return redirect()
            ->route('welfare.health.edit', $incident)
            ->with('success', 'Treatment recorded successfully.');
    }

    /**
     * Record parent notification.
     */
    public function notifyParent(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'parent_response' => 'nullable|string|max:1000',
        ]);

        $this->healthService->recordParentNotification($incident, $validated['parent_response'] ?? null);

        return redirect()
            ->route('welfare.health.edit', $incident)
            ->with('success', 'Parent notification recorded.');
    }

    /**
     * Record student sent home.
     */
    public function sentHome(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'collected_by' => 'required|string|max:255',
        ]);

        $this->healthService->recordSentHome($incident, $validated['collected_by']);

        return redirect()
            ->route('welfare.health.edit', $incident)
            ->with('success', 'Student departure recorded.');
    }

    /**
     * Record hospital visit.
     */
    public function hospital(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'ambulance_called' => 'boolean',
            'hospital_notes' => 'nullable|string',
        ]);

        $this->healthService->recordHospitalVisit(
            $incident,
            $validated['ambulance_called'] ?? false,
            $validated['hospital_notes'] ?? null
        );

        return redirect()
            ->route('welfare.health.edit', $incident)
            ->with('success', 'Hospital visit recorded.');
    }

    /**
     * Mark incident as resolved.
     */
    public function resolve(Request $request, HealthIncident $incident)
    {
        $validated = $request->validate([
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->healthService->resolveIncident(
                $incident,
                auth()->user(),
                $validated['resolution_notes'] ?? null
            );

            return redirect()
                ->route('welfare.health.edit', $incident)
                ->with('success', 'Incident marked as resolved.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.health.edit', $incident)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display today's incidents.
     */
    public function today()
    {
        $incidents = $this->healthService->getTodayIncidents();
        $requiresFollowUp = $this->healthService->getIncidentsRequiringFollowUp();
        $requiresParentNotification = $this->healthService->getIncidentsRequiringParentNotification();

        return view('welfare.health.today', compact('incidents', 'requiresFollowUp', 'requiresParentNotification'));
    }
}
