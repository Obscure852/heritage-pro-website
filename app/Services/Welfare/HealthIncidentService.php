<?php

namespace App\Services\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\HealthIncident;
use App\Models\Welfare\HealthIncidentType;
use App\Models\Welfare\WelfareAuditLog;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HealthIncidentService
{
    protected WelfareCaseService $caseService;

    public function __construct(WelfareCaseService $caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * Record a health incident.
     *
     * Returns array with 'duplicate' key if an incident already exists for this
     * student + date + type combination.
     *
     * @param array $data
     * @param User $reporter
     * @return HealthIncident|array
     * @throws \Exception
     */
    public function recordIncident(array $data, User $reporter): HealthIncident|array
    {
        return DB::transaction(function () use ($data, $reporter) {
            $incidentDate = $data['incident_date'] ?? now()->toDateString();

            // Check for duplicate with lock (student + date + type)
            $existing = HealthIncident::where('student_id', $data['student_id'])
                ->where('incident_date', $incidentDate)
                ->where('incident_type_id', $data['incident_type_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'duplicate' => true,
                    'existing_incident' => $existing,
                    'message' => 'A health incident of this type already exists for this student on this date.',
                ];
            }

            // Get or create welfare case
            $welfareCase = $this->getOrCreateCase($data, $reporter);

            $data['welfare_case_id'] = $welfareCase->id;
            $data['reported_by'] = $reporter->id;
            $data['incident_date'] = $incidentDate;

            $incident = HealthIncident::create($data);

            WelfareAuditLog::log($incident, WelfareAuditLog::ACTION_CREATED);

            // Check if parent notification is required
            if ($incident->requiresParentNotification()) {
                // Could trigger notification here
            }

            return $incident->fresh(['welfareCase', 'student', 'incidentType', 'reportedBy']);
        });
    }

    /**
     * Update a health incident.
     *
     * @param HealthIncident $incident
     * @param array $data
     * @return HealthIncident
     */
    public function updateIncident(HealthIncident $incident, array $data): HealthIncident
    {
        return DB::transaction(function () use ($incident, $data) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $oldValues = $lockedIncident->toArray();

            $lockedIncident->update($data);

            WelfareAuditLog::log($lockedIncident, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedIncident->fresh();
        });
    }

    /**
     * Record treatment given.
     *
     * @param HealthIncident $incident
     * @param string $treatment
     * @param User $treatedBy
     * @param bool $medicationAdministered
     * @param string|null $medicationDetails
     * @return HealthIncident
     */
    public function recordTreatment(
        HealthIncident $incident,
        string $treatment,
        User $treatedBy,
        bool $medicationAdministered = false,
        ?string $medicationDetails = null
    ): HealthIncident {
        return DB::transaction(function () use ($incident, $treatment, $treatedBy, $medicationAdministered, $medicationDetails) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['treatment_given' => $lockedIncident->treatment_given];

            $lockedIncident->update([
                'treatment_given' => $treatment,
                'treated_by' => $treatedBy->id,
                'medication_administered' => $medicationAdministered,
                'medication_details' => $medicationDetails,
            ]);

            WelfareAuditLog::log($lockedIncident, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedIncident->fresh(['treatedBy']);
        });
    }

    /**
     * Record parent notification.
     *
     * @param HealthIncident $incident
     * @param string|null $response
     * @return HealthIncident
     */
    public function recordParentNotification(HealthIncident $incident, ?string $response = null): HealthIncident
    {
        return DB::transaction(function () use ($incident, $response) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['parent_notified' => false];

            $lockedIncident->recordParentNotification($response);

            WelfareAuditLog::log(
                $lockedIncident,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['parent_notified' => true]
            );

            return $lockedIncident->fresh();
        });
    }

    /**
     * Record student sent home.
     *
     * @param HealthIncident $incident
     * @param string $collectedBy
     * @return HealthIncident
     */
    public function recordSentHome(HealthIncident $incident, string $collectedBy): HealthIncident
    {
        return DB::transaction(function () use ($incident, $collectedBy) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['sent_home' => false];

            $lockedIncident->recordSentHome($collectedBy);

            WelfareAuditLog::log(
                $lockedIncident,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['sent_home' => true, 'collected_by' => $collectedBy]
            );

            return $lockedIncident->fresh();
        });
    }

    /**
     * Record hospital visit.
     *
     * @param HealthIncident $incident
     * @param bool $ambulanceCalled
     * @param string|null $hospitalNotes
     * @return HealthIncident
     */
    public function recordHospitalVisit(HealthIncident $incident, bool $ambulanceCalled = false, ?string $hospitalNotes = null): HealthIncident
    {
        return DB::transaction(function () use ($incident, $ambulanceCalled, $hospitalNotes) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['hospital_visit' => false];

            $lockedIncident->update([
                'ambulance_called' => $ambulanceCalled,
            ]);

            $lockedIncident->recordHospitalVisit($hospitalNotes);

            // Escalate the case for hospital visits
            $this->caseService->escalateCase(
                $lockedIncident->welfareCase,
                null,
                'Hospital visit required'
            );

            WelfareAuditLog::log(
                $lockedIncident,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['hospital_visit' => true, 'ambulance_called' => $ambulanceCalled]
            );

            return $lockedIncident->fresh();
        });
    }

    /**
     * Set follow-up requirements.
     *
     * @param HealthIncident $incident
     * @param string $followUpRequired
     * @param \Carbon\Carbon|null $followUpDate
     * @return HealthIncident
     */
    public function setFollowUp(HealthIncident $incident, string $followUpRequired, ?\Carbon\Carbon $followUpDate = null): HealthIncident
    {
        return DB::transaction(function () use ($incident, $followUpRequired, $followUpDate) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            $lockedIncident->update([
                'follow_up_required' => $followUpRequired,
                'follow_up_date' => $followUpDate,
            ]);

            WelfareAuditLog::log($lockedIncident, WelfareAuditLog::ACTION_UPDATED);

            return $lockedIncident->fresh();
        });
    }

    /**
     * Delete a health incident.
     *
     * Only pending or reported incidents can be deleted.
     *
     * @param HealthIncident $incident
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteIncident(HealthIncident $incident): bool
    {
        return DB::transaction(function () use ($incident) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            // Only allow deletion of pending/new incidents
            if ($lockedIncident->status === 'resolved') {
                throw new \InvalidArgumentException(
                    "Cannot delete a resolved incident."
                );
            }

            WelfareAuditLog::log($lockedIncident, WelfareAuditLog::ACTION_DELETED);

            return $lockedIncident->delete();
        });
    }

    /**
     * Resolve a health incident.
     *
     * @param HealthIncident $incident
     * @param User $resolver
     * @param string|null $resolutionNotes
     * @return HealthIncident
     * @throws \InvalidArgumentException
     */
    public function resolveIncident(HealthIncident $incident, User $resolver, ?string $resolutionNotes = null): HealthIncident
    {
        return DB::transaction(function () use ($incident, $resolver, $resolutionNotes) {
            $lockedIncident = HealthIncident::where('id', $incident->id)
                ->lockForUpdate()
                ->first();

            if ($lockedIncident->status === 'resolved') {
                throw new \InvalidArgumentException(
                    "Incident is already resolved."
                );
            }

            $oldValues = ['status' => $lockedIncident->status];

            $lockedIncident->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $resolver->id,
                'resolution_notes' => $resolutionNotes,
            ]);

            WelfareAuditLog::log($lockedIncident, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedIncident->fresh();
        });
    }

    /**
     * Get incidents with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getIncidents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HealthIncident::with(['student', 'incidentType', 'reportedBy', 'treatedBy']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('incident_date', 'desc')
            ->orderBy('incident_time', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get today's incidents.
     *
     * @return Collection
     */
    public function getTodayIncidents(): Collection
    {
        return HealthIncident::with(['student', 'incidentType', 'reportedBy'])
            ->today()
            ->orderBy('incident_time', 'desc')
            ->get();
    }

    /**
     * Get emergency incidents.
     *
     * @param int $days
     * @return Collection
     */
    public function getEmergencyIncidents(int $days = 30): Collection
    {
        return HealthIncident::with(['student', 'incidentType'])
            ->emergency()
            ->where('incident_date', '>=', now()->subDays($days))
            ->orderBy('incident_date', 'desc')
            ->get();
    }

    /**
     * Get incidents requiring follow-up.
     *
     * @return Collection
     */
    public function getIncidentsRequiringFollowUp(): Collection
    {
        return HealthIncident::with(['student', 'incidentType'])
            ->requiringFollowUp()
            ->currentTerm()
            ->orderBy('follow_up_date')
            ->get();
    }

    /**
     * Get incidents requiring parent notification.
     *
     * @return Collection
     */
    public function getIncidentsRequiringParentNotification(): Collection
    {
        return HealthIncident::with(['student', 'incidentType'])
            ->parentNotNotified()
            ->whereHas('incidentType', fn ($q) => $q->where('requires_parent_notification', true))
            ->currentTerm()
            ->orderBy('incident_date', 'desc')
            ->get();
    }

    /**
     * Get student health history.
     *
     * @param Student $student
     * @param int|null $limit
     * @return Collection
     */
    public function getStudentHistory(Student $student, ?int $limit = null): Collection
    {
        $query = $student->healthIncidents()
            ->with(['incidentType', 'reportedBy', 'treatedBy'])
            ->orderBy('incident_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get available incident types.
     *
     * @param string|null $category
     * @return Collection
     */
    public function getIncidentTypes(?string $category = null): Collection
    {
        $query = HealthIncidentType::active()->orderBy('category')->orderBy('name');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    /**
     * Get health statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_incidents' => HealthIncident::currentTerm()->count(),
            'today' => HealthIncident::today()->count(),
            'sent_home' => HealthIncident::currentTerm()->sentHome()->count(),
            'emergency' => HealthIncident::currentTerm()->emergency()->count(),
            'requiring_follow_up' => HealthIncident::requiringFollowUp()->count(),
            'by_type' => $this->getIncidentCountsByType(),
            'by_outcome' => $this->getIncidentCountsByOutcome(),
            'medication_administered' => HealthIncident::currentTerm()->withMedication()->count(),
        ];
    }

    /**
     * Get incident counts by type.
     *
     * @return array
     */
    protected function getIncidentCountsByType(): array
    {
        return HealthIncident::select('incident_type_id', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('incident_type_id')
            ->with('incidentType:id,name')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->incidentType->name => $item->count])
            ->toArray();
    }

    /**
     * Get incident counts by outcome.
     *
     * @return array
     */
    protected function getIncidentCountsByOutcome(): array
    {
        return HealthIncident::select('outcome', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('outcome')
            ->pluck('count', 'outcome')
            ->toArray();
    }

    /**
     * Get or create welfare case.
     *
     * @param array $data
     * @param User $reporter
     * @return WelfareCase
     */
    protected function getOrCreateCase(array $data, User $reporter): WelfareCase
    {
        if (!empty($data['welfare_case_id'])) {
            return WelfareCase::findOrFail($data['welfare_case_id']);
        }

        $healthType = WelfareType::where('code', 'HEALTH')->first();
        $incidentType = HealthIncidentType::find($data['incident_type_id']);

        return $this->caseService->createCase([
            'student_id' => $data['student_id'],
            'welfare_type_id' => $healthType->id,
            'title' => 'Health Incident: ' . ($incidentType->name ?? 'Unknown'),
            'priority' => $this->determinePriority($incidentType),
            'incident_date' => $data['incident_date'] ?? now(),
        ], $reporter);
    }

    /**
     * Determine priority based on incident type.
     *
     * @param HealthIncidentType|null $type
     * @return string
     */
    protected function determinePriority(?HealthIncidentType $type): string
    {
        if (!$type) {
            return WelfareCase::PRIORITY_MEDIUM;
        }

        return match ($type->severity) {
            'emergency' => WelfareCase::PRIORITY_CRITICAL,
            'serious' => WelfareCase::PRIORITY_HIGH,
            'moderate' => WelfareCase::PRIORITY_MEDIUM,
            default => WelfareCase::PRIORITY_LOW,
        };
    }

    /**
     * Apply filters to query.
     *
     * @param $query
     * @param array $filters
     * @return void
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['incident_type_id'])) {
            $query->where('incident_type_id', $filters['incident_type_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['outcome'])) {
            $query->where('outcome', $filters['outcome']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('incident_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('incident_date', '<=', $filters['date_to']);
        }

        if (isset($filters['sent_home']) && $filters['sent_home']) {
            $query->where('sent_home', true);
        }

        if (isset($filters['emergency']) && $filters['emergency']) {
            $query->emergency();
        }

        if (($filters['apply_term_scope'] ?? true) !== false) {
            $query->currentTerm();
        }
    }
}
