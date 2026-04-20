<?php

namespace App\Services\Welfare;

use App\Helpers\TermHelper;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareType;
use App\Models\Welfare\WelfareAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WelfareCaseService{
    /**
     * Create a new welfare case with transaction safety and duplicate detection.
     *
     * @param array $data
     * @param User $opener
     * @return WelfareCase|array Returns WelfareCase on success, or array with duplicate info
     * @throws \Exception
     */
    public function createCase(array $data, User $opener): WelfareCase|array{
        return DB::transaction(function () use ($data, $opener) {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()?->id);

            // Check for existing case with pessimistic lock to prevent race conditions
            $existingCaseQuery = WelfareCase::where('student_id', $data['student_id'])
                ->where('welfare_type_id', $data['welfare_type_id'])
                ->where('term_id', $termId);

            // Handle incident_date - check for same date OR both null
            if (!empty($data['incident_date'])) {
                $existingCaseQuery->where('incident_date', $data['incident_date']);
            } else {
                $existingCaseQuery->whereNull('incident_date');
            }

            $existingCase = $existingCaseQuery->lockForUpdate()->first();

            if ($existingCase) {
                return [
                    'duplicate' => true,
                    'existing_case' => $existingCase,
                    'can_reopen' => in_array($existingCase->status, [
                        WelfareCase::STATUS_CLOSED,
                        WelfareCase::STATUS_RESOLVED,
                    ]),
                ];
            }

            $data['opened_by'] = $opener->id;
            $data['status'] = $data['status'] ?? WelfareCase::STATUS_OPEN;

            $case = WelfareCase::create($data);

            WelfareAuditLog::log($case, WelfareAuditLog::ACTION_CREATED, null, $case->toArray());

            return $case->fresh();
        });
    }

    /**
     * Find an existing case for a student with the same type and term.
     *
     * @param int $studentId
     * @param int $typeId
     * @param int $termId
     * @param string|null $incidentDate
     * @return WelfareCase|null
     */
    public function findExistingCase(int $studentId, int $typeId, int $termId, ?string $incidentDate): ?WelfareCase
    {
        $query = WelfareCase::where('student_id', $studentId)
            ->where('welfare_type_id', $typeId)
            ->where('term_id', $termId);

        if ($incidentDate) {
            $query->where('incident_date', $incidentDate);
        } else {
            $query->whereNull('incident_date');
        }

        return $query->first();
    }

    /**
     * Update a welfare case with pessimistic locking.
     *
     * @param WelfareCase $case
     * @param array $data
     * @return WelfareCase
     */
    public function updateCase(WelfareCase $case, array $data): WelfareCase
    {
        return DB::transaction(function () use ($case, $data) {
            // Lock the row to prevent concurrent updates
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            $oldValues = $lockedCase->toArray();

            $lockedCase->update($data);

            WelfareAuditLog::log($lockedCase, WelfareAuditLog::ACTION_UPDATED, $oldValues, $lockedCase->fresh()->toArray());

            return $lockedCase->fresh();
        });
    }

    /**
     * Assign a case to a user with pessimistic locking.
     *
     * @param WelfareCase $case
     * @param User $assignee
     * @return WelfareCase
     */
    public function assignCase(WelfareCase $case, User $assignee): WelfareCase
    {
        return DB::transaction(function () use ($case, $assignee) {
            // Lock the row to prevent concurrent assignment changes
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            $oldValues = ['assigned_to' => $lockedCase->assigned_to];

            $lockedCase->update([
                'assigned_to' => $assignee->id,
                'status' => WelfareCase::STATUS_IN_PROGRESS,
            ]);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_ASSIGNED,
                $oldValues,
                ['assigned_to' => $assignee->id],
                "Case assigned to {$assignee->full_name}"
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Escalate a case with pessimistic locking.
     *
     * @param WelfareCase $case
     * @param User|null $assignTo
     * @param string|null $reason
     * @return WelfareCase
     */
    public function escalateCase(WelfareCase $case, ?User $assignTo = null, ?string $reason = null): WelfareCase
    {
        return DB::transaction(function () use ($case, $assignTo, $reason) {
            // Lock the row to prevent concurrent escalation
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            // Validate case can be escalated (not closed)
            if ($lockedCase->status === WelfareCase::STATUS_CLOSED) {
                throw new InvalidArgumentException('Cannot escalate a closed case.');
            }

            $oldValues = [
                'status' => $lockedCase->status,
                'priority' => $lockedCase->priority,
                'assigned_to' => $lockedCase->assigned_to,
            ];

            $lockedCase->escalate($assignTo?->id);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_ESCALATED,
                $oldValues,
                [
                    'status' => $lockedCase->status,
                    'priority' => $lockedCase->priority,
                    'assigned_to' => $lockedCase->assigned_to,
                ],
                $reason
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Approve a case with pessimistic locking and status validation.
     *
     * @param WelfareCase $case
     * @param User $approver
     * @param string|null $notes
     * @return WelfareCase
     * @throws InvalidArgumentException
     */
    public function approveCase(WelfareCase $case, User $approver, ?string $notes = null): WelfareCase
    {
        return DB::transaction(function () use ($case, $approver, $notes) {
            // Lock the row to prevent concurrent approve/reject race
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            // Validate current state allows approval
            if ($lockedCase->approval_status !== WelfareCase::APPROVAL_PENDING) {
                throw new InvalidArgumentException(
                    "Case cannot be approved. Current approval status: {$lockedCase->approval_status}"
                );
            }

            $oldValues = [
                'approval_status' => $lockedCase->approval_status,
                'status' => $lockedCase->status,
            ];

            $lockedCase->approve($approver, $notes);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_APPROVED,
                $oldValues,
                [
                    'approval_status' => $lockedCase->approval_status,
                    'status' => $lockedCase->status,
                    'approved_by' => $approver->id,
                ],
                $notes
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Reject a case with pessimistic locking and status validation.
     *
     * @param WelfareCase $case
     * @param User $approver
     * @param string $notes
     * @return WelfareCase
     * @throws InvalidArgumentException
     */
    public function rejectCase(WelfareCase $case, User $approver, string $notes): WelfareCase
    {
        return DB::transaction(function () use ($case, $approver, $notes) {
            // Lock the row to prevent concurrent approve/reject race
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            // Validate current state allows rejection
            if ($lockedCase->approval_status !== WelfareCase::APPROVAL_PENDING) {
                throw new InvalidArgumentException(
                    "Case cannot be rejected. Current approval status: {$lockedCase->approval_status}"
                );
            }

            $oldValues = ['approval_status' => $lockedCase->approval_status];

            $lockedCase->reject($approver, $notes);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_REJECTED,
                $oldValues,
                ['approval_status' => $lockedCase->approval_status],
                $notes
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Close a case with pessimistic locking and status validation.
     *
     * @param WelfareCase $case
     * @param string|null $notes
     * @return WelfareCase
     * @throws InvalidArgumentException
     */
    public function closeCase(WelfareCase $case, ?string $notes = null): WelfareCase
    {
        return DB::transaction(function () use ($case, $notes) {
            // Lock the row to prevent concurrent state changes
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            // Validate case is not already closed
            if ($lockedCase->status === WelfareCase::STATUS_CLOSED) {
                throw new InvalidArgumentException('Case is already closed.');
            }

            $oldValues = ['status' => $lockedCase->status];

            $lockedCase->close($notes);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_CLOSED,
                $oldValues,
                ['status' => $lockedCase->status],
                $notes
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Reopen a closed case with pessimistic locking and status validation.
     *
     * @param WelfareCase $case
     * @param string|null $reason
     * @return WelfareCase
     * @throws InvalidArgumentException
     */
    public function reopenCase(WelfareCase $case, ?string $reason = null): WelfareCase
    {
        return DB::transaction(function () use ($case, $reason) {
            // Lock the row to prevent concurrent state changes
            $lockedCase = WelfareCase::where('id', $case->id)->lockForUpdate()->first();

            if (!$lockedCase) {
                throw new InvalidArgumentException('Case not found or has been deleted.');
            }

            // Validate case is closed or resolved before reopening
            if (!in_array($lockedCase->status, [WelfareCase::STATUS_CLOSED, WelfareCase::STATUS_RESOLVED])) {
                throw new InvalidArgumentException(
                    'Only closed or resolved cases can be reopened. Current status: ' . $lockedCase->status
                );
            }

            $oldValues = [
                'status' => $lockedCase->status,
                'closed_at' => $lockedCase->closed_at,
            ];

            $lockedCase->update([
                'status' => WelfareCase::STATUS_OPEN,
                'closed_at' => null,
            ]);

            WelfareAuditLog::log(
                $lockedCase,
                WelfareAuditLog::ACTION_REOPENED,
                $oldValues,
                ['status' => $lockedCase->status],
                $reason
            );

            return $lockedCase->fresh();
        });
    }

    /**
     * Get cases with filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCases(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WelfareCase::with(['student', 'welfareType', 'openedBy', 'assignedTo']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get cases for a specific student.
     *
     * @param Student $student
     * @param array $filters
     * @return Collection
     */
    public function getStudentCases(Student $student, array $filters = []): Collection
    {
        $query = $student->welfareCases()->with(['welfareType', 'openedBy', 'assignedTo']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get cases assigned to a user.
     *
     * @param User $user
     * @param bool $openOnly
     * @return Collection
     */
    public function getAssignedCases(User $user, bool $openOnly = true): Collection
    {
        $query = WelfareCase::with(['student', 'welfareType'])
            ->where('assigned_to', $user->id);

        if ($openOnly) {
            $query->open();
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get cases pending approval.
     *
     * @return Collection
     */
    public function getCasesPendingApproval(): Collection
    {
        return WelfareCase::with(['student', 'welfareType', 'openedBy'])
            ->pendingApproval()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get high priority open cases.
     *
     * @param int $limit
     * @return Collection
     */
    public function getHighPriorityCases(int $limit = 10): Collection
    {
        return WelfareCase::with(['student', 'welfareType', 'assignedTo'])
            ->open()
            ->highPriority()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get dashboard statistics.
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        return [
            'total_open' => WelfareCase::open()->count(),
            'pending_approval' => WelfareCase::pendingApproval()->count(),
            'high_priority' => WelfareCase::open()->highPriority()->count(),
            'by_type' => $this->getCaseCountsByType(),
            'by_status' => $this->getCaseCountsByStatus(),
            'recent_activity' => $this->getRecentActivity(5),
        ];
    }

    /**
     * Get case counts by type.
     *
     * @return Collection
     */
    public function getCaseCountsByType(): Collection
    {
        return WelfareCase::select('welfare_type_id', DB::raw('count(*) as count'))
            ->open()
            ->groupBy('welfare_type_id')
            ->with('welfareType:id,name,code,color')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->welfareType->code => $item->count]);
    }

    /**
     * Get case counts by status.
     *
     * @return array
     */
    public function getCaseCountsByStatus(): array
    {
        return WelfareCase::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get recent activity.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        return WelfareAuditLog::with(['user', 'welfareCase.student'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Apply filters to query.
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['welfare_type_id'])) {
            $query->where('welfare_type_id', $filters['welfare_type_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['opened_by'])) {
            $query->where('opened_by', $filters['opened_by']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('opened_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('opened_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply term scope if not explicitly disabled
        if (($filters['apply_term_scope'] ?? true) !== false) {
            $query->currentTerm();
        }
    }
}
