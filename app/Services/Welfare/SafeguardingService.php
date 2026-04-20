<?php

namespace App\Services\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\SafeguardingCategory;
use App\Models\Welfare\SafeguardingConcern;
use App\Models\Welfare\WelfareAuditLog;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SafeguardingService
{
    protected WelfareCaseService $caseService;

    public function __construct(WelfareCaseService $caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * Report a safeguarding concern.
     *
     * Returns array with 'duplicate' key if a concern already exists for this
     * student + date + category combination.
     *
     * @param array $data
     * @param User $reporter
     * @return SafeguardingConcern|array
     * @throws \Exception
     */
    public function reportConcern(array $data, User $reporter): SafeguardingConcern|array
    {
        return DB::transaction(function () use ($data, $reporter) {
            $dateIdentified = $data['date_identified'] ?? now()->toDateString();

            // Check for duplicate with lock (student + date + category)
            $existing = SafeguardingConcern::where('student_id', $data['student_id'])
                ->where('date_identified', $dateIdentified)
                ->where('category_id', $data['category_id'])
                ->whereNotIn('status', [SafeguardingConcern::STATUS_CLOSED])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'duplicate' => true,
                    'existing_concern' => $existing,
                    'message' => 'A safeguarding concern of this category already exists for this student on this date.',
                ];
            }

            // Create welfare case with high priority
            $welfareCase = $this->createSafeguardingCase($data, $reporter);

            $data['welfare_case_id'] = $welfareCase->id;
            $data['reported_by'] = $reporter->id;
            $data['status'] = SafeguardingConcern::STATUS_IDENTIFIED;
            $data['date_identified'] = $dateIdentified;

            $concern = SafeguardingConcern::create($data);

            WelfareAuditLog::log($concern, WelfareAuditLog::ACTION_CREATED);

            // Check if immediate action required
            if ($concern->requiresImmediateAction()) {
                $this->caseService->escalateCase(
                    $welfareCase,
                    null,
                    'Safeguarding concern requires immediate action'
                );
            }

            return $concern->fresh(['welfareCase', 'student', 'category', 'reportedBy']);
        });
    }

    /**
     * Update a safeguarding concern.
     *
     * @param SafeguardingConcern $concern
     * @param array $data
     * @return SafeguardingConcern
     */
    public function updateConcern(SafeguardingConcern $concern, array $data): SafeguardingConcern
    {
        return DB::transaction(function () use ($concern, $data) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            $oldValues = $lockedConcern->toArray();

            $lockedConcern->update($data);

            WelfareAuditLog::log($lockedConcern, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedConcern->fresh();
        });
    }

    /**
     * Record immediate action taken.
     *
     * @param SafeguardingConcern $concern
     * @param string $details
     * @return SafeguardingConcern
     */
    public function recordImmediateAction(SafeguardingConcern $concern, string $details): SafeguardingConcern
    {
        return DB::transaction(function () use ($concern, $details) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['immediate_action_taken' => false];

            $lockedConcern->recordImmediateAction($details);

            WelfareAuditLog::log(
                $lockedConcern,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['immediate_action_taken' => true],
                $details
            );

            return $lockedConcern->fresh();
        });
    }

    /**
     * Notify authorities.
     *
     * @param SafeguardingConcern $concern
     * @param string $reference
     * @return SafeguardingConcern
     */
    public function notifyAuthorities(SafeguardingConcern $concern, string $reference): SafeguardingConcern
    {
        return DB::transaction(function () use ($concern, $reference) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['authorities_notified' => false];

            $lockedConcern->notifyAuthorities($reference);

            WelfareAuditLog::log(
                $lockedConcern,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['authorities_notified' => true, 'authority_reference' => $reference]
            );

            return $lockedConcern->fresh();
        });
    }

    /**
     * Notify parents.
     *
     * @param SafeguardingConcern $concern
     * @param string|null $response
     * @return SafeguardingConcern
     */
    public function notifyParents(SafeguardingConcern $concern, ?string $response = null): SafeguardingConcern
    {
        return DB::transaction(function () use ($concern, $response) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            $oldValues = ['parents_informed' => false];

            $lockedConcern->notifyParents($response);

            WelfareAuditLog::log(
                $lockedConcern,
                WelfareAuditLog::ACTION_UPDATED,
                $oldValues,
                ['parents_informed' => true]
            );

            return $lockedConcern->fresh();
        });
    }

    /**
     * Close a safeguarding concern.
     *
     * @param SafeguardingConcern $concern
     * @param User $closedBy
     * @param string|null $outcome
     * @return SafeguardingConcern
     * @throws \InvalidArgumentException
     */
    public function closeConcern(SafeguardingConcern $concern, User $closedBy, ?string $outcome = null): SafeguardingConcern
    {
        return DB::transaction(function () use ($concern, $closedBy, $outcome) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            // Validate status allows closing
            if ($lockedConcern->status === SafeguardingConcern::STATUS_CLOSED) {
                throw new \InvalidArgumentException(
                    "Concern is already closed."
                );
            }

            $oldValues = ['status' => $lockedConcern->status];

            $lockedConcern->close($closedBy, $outcome);

            WelfareAuditLog::log(
                $lockedConcern,
                WelfareAuditLog::ACTION_CLOSED,
                $oldValues,
                ['status' => SafeguardingConcern::STATUS_CLOSED],
                $outcome
            );

            // Close the welfare case if all concerns are closed
            if ($lockedConcern->welfareCase->safeguardingConcerns()->open()->count() === 0) {
                $this->caseService->closeCase($lockedConcern->welfareCase, 'All safeguarding concerns addressed');
            }

            return $lockedConcern->fresh();
        });
    }

    /**
     * Delete a safeguarding concern.
     *
     * Only newly identified concerns can be deleted.
     *
     * @param SafeguardingConcern $concern
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteConcern(SafeguardingConcern $concern): bool
    {
        return DB::transaction(function () use ($concern) {
            $lockedConcern = SafeguardingConcern::where('id', $concern->id)
                ->lockForUpdate()
                ->first();

            // Only allow deletion of identified status
            if (!in_array($lockedConcern->status, [
                SafeguardingConcern::STATUS_IDENTIFIED,
            ])) {
                throw new \InvalidArgumentException(
                    "Cannot delete concern with status: {$lockedConcern->status}. Only identified concerns can be deleted."
                );
            }

            WelfareAuditLog::log($lockedConcern, WelfareAuditLog::ACTION_DELETED);

            return $lockedConcern->delete();
        });
    }

    /**
     * Get safeguarding concerns with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getConcerns(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SafeguardingConcern::with(['student', 'category', 'reportedBy', 'welfareCase']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('date_identified', 'desc')->paginate($perPage);
    }

    /**
     * Get open concerns.
     *
     * @return Collection
     */
    public function getOpenConcerns(): Collection
    {
        return SafeguardingConcern::with(['student', 'category', 'reportedBy'])
            ->open()
            ->currentTerm()
            ->orderBy('risk_level', 'desc')
            ->orderBy('date_identified', 'desc')
            ->get();
    }

    /**
     * Get critical concerns.
     *
     * @return Collection
     */
    public function getCriticalConcerns(): Collection
    {
        return SafeguardingConcern::with(['student', 'category'])
            ->critical()
            ->open()
            ->orderBy('date_identified', 'desc')
            ->get();
    }

    /**
     * Get concerns awaiting authority notification.
     *
     * @return Collection
     */
    public function getConcernsAwaitingAuthorityNotification(): Collection
    {
        return SafeguardingConcern::with(['student', 'category'])
            ->awaitingAuthorityNotification()
            ->open()
            ->orderBy('date_identified', 'asc')
            ->get();
    }

    /**
     * Get concerns requiring immediate action.
     *
     * @return Collection
     */
    public function getConcernsRequiringImmediateAction(): Collection
    {
        return SafeguardingConcern::with(['student', 'category'])
            ->requiringImmediateAction()
            ->open()
            ->orderBy('date_identified', 'asc')
            ->get();
    }

    /**
     * Get student safeguarding history.
     *
     * @param Student $student
     * @return Collection
     */
    public function getStudentHistory(Student $student): Collection
    {
        return $student->safeguardingConcerns()
            ->with(['category', 'reportedBy', 'closedBy'])
            ->orderBy('date_identified', 'desc')
            ->get();
    }

    /**
     * Get available categories.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return SafeguardingCategory::active()->orderBy('name')->get();
    }

    /**
     * Get safeguarding statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_concerns' => SafeguardingConcern::currentTerm()->count(),
            'open' => SafeguardingConcern::open()->currentTerm()->count(),
            'critical' => SafeguardingConcern::critical()->open()->count(),
            'awaiting_authority_notification' => SafeguardingConcern::awaitingAuthorityNotification()->count(),
            'requiring_immediate_action' => SafeguardingConcern::requiringImmediateAction()->count(),
            'by_category' => $this->getConcernCountsByCategory(),
            'by_risk_level' => $this->getConcernCountsByRiskLevel(),
        ];
    }

    /**
     * Get concern counts by category.
     *
     * @return array
     */
    protected function getConcernCountsByCategory(): array
    {
        return SafeguardingConcern::select('category_id', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category->name => $item->count])
            ->toArray();
    }

    /**
     * Get concern counts by risk level.
     *
     * @return array
     */
    protected function getConcernCountsByRiskLevel(): array
    {
        return SafeguardingConcern::select('risk_level', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level')
            ->toArray();
    }

    /**
     * Create a safeguarding welfare case.
     *
     * @param array $data
     * @param User $reporter
     * @return WelfareCase
     */
    protected function createSafeguardingCase(array $data, User $reporter): WelfareCase
    {
        if (!empty($data['welfare_case_id'])) {
            return WelfareCase::findOrFail($data['welfare_case_id']);
        }

        $safeguardingType = WelfareType::where('code', 'SAFEGUARD')->first();
        
        if (!$safeguardingType) {
            throw new \Exception('Safeguarding welfare type not found. Please ensure the welfare type with code SAFEGUARD exists in the database.');
        }
        
        $category = SafeguardingCategory::find($data['category_id']);
        
        if (!$category) {
            throw new \Exception('Safeguarding category not found.');
        }

        // Determine priority based on risk level and category
        $priority = $this->determinePriority($data['risk_level'] ?? 'medium', $category);

        return $this->caseService->createCase([
            'student_id' => $data['student_id'],
            'welfare_type_id' => $safeguardingType->id,
            'title' => 'Safeguarding Concern: ' . ($category->name ?? 'Unknown'),
            'priority' => $priority,
            'incident_date' => $data['date_identified'] ?? now(),
        ], $reporter);
    }

    /**
     * Determine case priority.
     *
     * @param string $riskLevel
     * @param SafeguardingCategory|null $category
     * @return string
     */
    protected function determinePriority(string $riskLevel, ?SafeguardingCategory $category): string
    {
        // Critical risk or category requiring immediate action = critical priority
        if ($riskLevel === 'critical' || ($category && $category->immediate_action_required)) {
            return WelfareCase::PRIORITY_CRITICAL;
        }

        // High risk or category requiring authority notification = high priority
        if ($riskLevel === 'high' || ($category && $category->notify_authorities)) {
            return WelfareCase::PRIORITY_HIGH;
        }

        return match ($riskLevel) {
            'medium' => WelfareCase::PRIORITY_MEDIUM,
            'low' => WelfareCase::PRIORITY_LOW,
            default => WelfareCase::PRIORITY_HIGH,
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

        if (!empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['reported_by'])) {
            $query->where('reported_by', $filters['reported_by']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date_identified', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date_identified', '<=', $filters['date_to']);
        }

        if (($filters['apply_term_scope'] ?? true) !== false) {
            $query->currentTerm();
        }
    }
}
