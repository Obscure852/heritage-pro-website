<?php

namespace App\Services\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\DisciplinaryAction;
use App\Models\Welfare\DisciplinaryRecord;
use App\Models\Welfare\WelfareAuditLog;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DisciplinaryService
{
    protected WelfareCaseService $caseService;

    public function __construct(WelfareCaseService $caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * Create a disciplinary record.
     *
     * Returns array with 'duplicate' key if a record already exists for this
     * student + date + type combination.
     *
     * @param array $data
     * @param User $reporter
     * @return DisciplinaryRecord|array
     * @throws \Exception
     */
    public function createRecord(array $data, User $reporter): DisciplinaryRecord|array
    {
        return DB::transaction(function () use ($data, $reporter) {
            $incidentDate = $data['incident_date'] ?? now()->toDateString();

            // Check for duplicate with lock (student + date + type)
            $existing = DisciplinaryRecord::where('student_id', $data['student_id'])
                ->where('incident_date', $incidentDate)
                ->where('incident_type_id', $data['incident_type_id'])
                ->whereNotIn('status', [DisciplinaryRecord::STATUS_RESOLVED])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'duplicate' => true,
                    'existing_record' => $existing,
                    'message' => 'A disciplinary record of this type already exists for this student on this date.',
                ];
            }

            // Get or create welfare case
            $welfareCase = $this->getOrCreateCase($data, $reporter);

            $data['welfare_case_id'] = $welfareCase->id;
            $data['reported_by'] = $reporter->id;
            $data['status'] = $data['status'] ?? DisciplinaryRecord::STATUS_REPORTED;
            $data['incident_date'] = $incidentDate;

            // Convert text inputs to arrays for JSON columns
            if (!empty($data['witnesses']) && is_string($data['witnesses'])) {
                $data['witnesses'] = array_filter(array_map('trim', explode("\n", $data['witnesses'])));
            }
            if (!empty($data['evidence']) && is_string($data['evidence'])) {
                $data['evidence'] = array_filter(array_map('trim', explode("\n", $data['evidence'])));
            }

            $record = DisciplinaryRecord::create($data);

            WelfareAuditLog::log($record, WelfareAuditLog::ACTION_CREATED);

            return $record->fresh(['welfareCase', 'student', 'incidentType', 'reportedBy']);
        });
    }

    /**
     * Update a disciplinary record.
     *
     * @param DisciplinaryRecord $record
     * @param array $data
     * @return DisciplinaryRecord
     */
    public function updateRecord(DisciplinaryRecord $record, array $data): DisciplinaryRecord
    {
        return DB::transaction(function () use ($record, $data) {
            $lockedRecord = DisciplinaryRecord::where('id', $record->id)
                ->lockForUpdate()
                ->first();

            $oldValues = $lockedRecord->toArray();

            $lockedRecord->update($data);

            WelfareAuditLog::log($lockedRecord, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedRecord->fresh();
        });
    }

    /**
     * Apply disciplinary action.
     *
     * @param DisciplinaryRecord $record
     * @param int $actionId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon|null $endDate
     * @param string|null $notes
     * @return DisciplinaryRecord
     * @throws \InvalidArgumentException
     */
    public function applyAction(
        DisciplinaryRecord $record,
        int $actionId,
        \Carbon\Carbon $startDate,
        ?\Carbon\Carbon $endDate = null,
        ?string $notes = null
    ): DisciplinaryRecord {
        return DB::transaction(function () use ($record, $actionId, $startDate, $endDate, $notes) {
            $lockedRecord = DisciplinaryRecord::where('id', $record->id)
                ->lockForUpdate()
                ->first();

            // Validate status allows this action
            if ($lockedRecord->status === DisciplinaryRecord::STATUS_RESOLVED) {
                throw new \InvalidArgumentException(
                    "Cannot apply action to a resolved record."
                );
            }

            $oldValues = [
                'action_id' => $lockedRecord->action_id,
                'status' => $lockedRecord->status,
            ];

            $lockedRecord->applyAction($actionId, $startDate, $endDate, $notes);

            WelfareAuditLog::log($lockedRecord, WelfareAuditLog::ACTION_UPDATED, $oldValues, null, $notes);

            return $lockedRecord->fresh(['action']);
        });
    }

    /**
     * Resolve a disciplinary record.
     *
     * @param DisciplinaryRecord $record
     * @param User $resolver
     * @param string|null $resolution
     * @return DisciplinaryRecord
     * @throws \InvalidArgumentException
     */
    public function resolveRecord(DisciplinaryRecord $record, User $resolver, ?string $resolution = null): DisciplinaryRecord
    {
        return DB::transaction(function () use ($record, $resolver, $resolution) {
            $lockedRecord = DisciplinaryRecord::where('id', $record->id)
                ->lockForUpdate()
                ->first();

            // Validate status allows resolution
            if ($lockedRecord->status === DisciplinaryRecord::STATUS_RESOLVED) {
                throw new \InvalidArgumentException(
                    "Record is already resolved."
                );
            }

            $oldValues = ['status' => $lockedRecord->status];

            $lockedRecord->resolve($resolver, $resolution);

            WelfareAuditLog::log($lockedRecord, WelfareAuditLog::ACTION_UPDATED, $oldValues, null, $resolution);

            // Also close the welfare case if this is the only record
            if ($lockedRecord->welfareCase->disciplinaryRecords()->unresolved()->count() === 0) {
                $this->caseService->closeCase($lockedRecord->welfareCase, 'All disciplinary matters resolved');
            }

            return $lockedRecord->fresh();
        });
    }

    /**
     * Record parent notification.
     *
     * @param DisciplinaryRecord $record
     * @param string|null $response
     * @return DisciplinaryRecord
     */
    public function recordParentNotification(DisciplinaryRecord $record, ?string $response = null): DisciplinaryRecord
    {
        return DB::transaction(function () use ($record, $response) {
            $lockedRecord = DisciplinaryRecord::where('id', $record->id)
                ->lockForUpdate()
                ->first();

            $lockedRecord->recordParentNotification($response);

            WelfareAuditLog::log(
                $lockedRecord,
                WelfareAuditLog::ACTION_UPDATED,
                ['parent_notified' => false],
                ['parent_notified' => true]
            );

            return $lockedRecord->fresh();
        });
    }

    /**
     * Delete a disciplinary record.
     *
     * Only reported status records can be deleted.
     *
     * @param DisciplinaryRecord $record
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteRecord(DisciplinaryRecord $record): bool
    {
        return DB::transaction(function () use ($record) {
            $lockedRecord = DisciplinaryRecord::where('id', $record->id)
                ->lockForUpdate()
                ->first();

            // Only allow deletion of reported status
            if (!in_array($lockedRecord->status, [
                DisciplinaryRecord::STATUS_REPORTED,
            ])) {
                throw new \InvalidArgumentException(
                    "Cannot delete record with status: {$lockedRecord->status}. Only reported records can be deleted."
                );
            }

            WelfareAuditLog::log($lockedRecord, WelfareAuditLog::ACTION_DELETED);

            return $lockedRecord->delete();
        });
    }

    /**
     * Get disciplinary records with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRecords(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DisciplinaryRecord::with(['student', 'incidentType', 'action', 'reportedBy', 'welfareCase']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('incident_date', 'desc')->paginate($perPage);
    }

    /**
     * Get unresolved records.
     *
     * @return Collection
     */
    public function getUnresolvedRecords(): Collection
    {
        return DisciplinaryRecord::with(['student', 'incidentType', 'action'])
            ->unresolved()
            ->currentTerm()
            ->orderBy('incident_date', 'desc')
            ->get();
    }

    /**
     * Get records with active actions.
     *
     * @return Collection
     */
    public function getRecordsWithActiveActions(): Collection
    {
        return DisciplinaryRecord::with(['student', 'action'])
            ->withActiveAction()
            ->currentTerm()
            ->orderBy('action_end_date')
            ->get();
    }

    /**
     * Get student disciplinary history.
     *
     * @param Student $student
     * @param int|null $limit
     * @return Collection
     */
    public function getStudentHistory(Student $student, ?int $limit = null): Collection
    {
        $query = $student->disciplinaryRecords()
            ->with(['incidentType', 'action', 'reportedBy'])
            ->orderBy('incident_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get records requiring parent notification.
     *
     * @return Collection
     */
    public function getRecordsRequiringParentNotification(): Collection
    {
        return DisciplinaryRecord::with(['student', 'incidentType'])
            ->parentNotNotified()
            ->unresolved()
            ->currentTerm()
            ->orderBy('incident_date', 'desc')
            ->get();
    }

    /**
     * Get available disciplinary actions.
     *
     * @param string|null $severity
     * @return Collection
     */
    public function getAvailableActions(?string $severity = null): Collection
    {
        $query = DisciplinaryAction::active()->orderBy('severity_level');

        if ($severity) {
            $query->where('severity_level', '<=', $this->getSeverityLevel($severity));
        }

        return $query->get();
    }

    /**
     * Get disciplinary statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_incidents' => DisciplinaryRecord::currentTerm()->count(),
            'unresolved' => DisciplinaryRecord::currentTerm()->unresolved()->count(),
            'with_active_action' => DisciplinaryRecord::withActiveAction()->count(),
            'awaiting_parent_notification' => DisciplinaryRecord::parentNotNotified()->unresolved()->count(),
            'by_type' => $this->getIncidentCountsByType(),
            'by_severity' => $this->getIncidentCountsBySeverity(),
            'repeat_offenders' => $this->getRepeatOffenders(),
        ];
    }

    /**
     * Get incident counts by type.
     *
     * @return array
     */
    protected function getIncidentCountsByType(): array
    {
        return DisciplinaryRecord::select('incident_type_id', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('incident_type_id')
            ->with('incidentType:id,name')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->incidentType->name => $item->count])
            ->toArray();
    }

    /**
     * Get incident counts by severity.
     *
     * @return array
     */
    protected function getIncidentCountsBySeverity(): array
    {
        return DisciplinaryRecord::select('disciplinary_incident_types.severity', DB::raw('count(*) as count'))
            ->join('disciplinary_incident_types', 'disciplinary_records.incident_type_id', '=', 'disciplinary_incident_types.id')
            ->currentTerm()
            ->groupBy('disciplinary_incident_types.severity')
            ->pluck('count', 'severity')
            ->toArray();
    }

    /**
     * Get students with multiple incidents.
     *
     * @param int $minIncidents
     * @return Collection
     */
    protected function getRepeatOffenders(int $minIncidents = 3): Collection
    {
        return DisciplinaryRecord::select('student_id', DB::raw('count(*) as incident_count'))
            ->currentTerm()
            ->groupBy('student_id')
            ->having('incident_count', '>=', $minIncidents)
            ->with('student:id,first_name,last_name')
            ->orderBy('incident_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get or create a welfare case for the disciplinary record.
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

        $disciplinaryType = WelfareType::where('code', 'DISCIP')->first();

        return $this->caseService->createCase([
            'student_id' => $data['student_id'],
            'welfare_type_id' => $disciplinaryType->id,
            'title' => 'Disciplinary Incident',
            'priority' => $this->determinePriority($data),
            'incident_date' => $data['incident_date'] ?? now(),
        ], $reporter);
    }

    /**
     * Determine priority based on incident type.
     *
     * @param array $data
     * @return string
     */
    protected function determinePriority(array $data): string
    {
        if (!empty($data['incident_type_id'])) {
            $type = \App\Models\Welfare\DisciplinaryIncidentType::find($data['incident_type_id']);
            if ($type) {
                return match ($type->severity) {
                    'severe' => WelfareCase::PRIORITY_CRITICAL,
                    'major' => WelfareCase::PRIORITY_HIGH,
                    'moderate' => WelfareCase::PRIORITY_MEDIUM,
                    default => WelfareCase::PRIORITY_LOW,
                };
            }
        }

        return WelfareCase::PRIORITY_MEDIUM;
    }

    /**
     * Get severity level for ordering.
     *
     * @param string $severity
     * @return int
     */
    protected function getSeverityLevel(string $severity): int
    {
        return match ($severity) {
            'minor' => 1,
            'moderate' => 2,
            'major' => 3,
            'severe' => 4,
            default => 2,
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
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['incident_type_id'])) {
            $query->where('incident_type_id', $filters['incident_type_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['reported_by'])) {
            $query->where('reported_by', $filters['reported_by']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('incident_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('incident_date', '<=', $filters['date_to']);
        }

        if (($filters['apply_term_scope'] ?? true) !== false) {
            $query->currentTerm();
        }
    }
}
