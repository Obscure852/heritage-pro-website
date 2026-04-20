<?php

namespace App\Services\Welfare;

use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\CounselingSession;
use App\Models\Welfare\WelfareAuditLog;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CounselingService
{
    protected WelfareCaseService $caseService;

    public function __construct(WelfareCaseService $caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * Create a counseling session.
     *
     * Returns array with 'duplicate' key if a session already exists for this student
     * at the same date/time.
     *
     * @param array $data
     * @param User $counsellor
     * @return CounselingSession|array
     * @throws \Exception
     */
    public function createSession(array $data, User $counsellor): CounselingSession|array
    {
        return DB::transaction(function () use ($data, $counsellor) {
            // Parse session_date if it contains time (format: 'Y-m-d H:i')
            $sessionDate = $data['session_date'];
            $sessionTime = $data['session_time'] ?? null;

            if (isset($data['session_date']) && strpos($data['session_date'], ' ') !== false) {
                $datetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $data['session_date']);
                $sessionDate = $datetime->format('Y-m-d');
                $sessionTime = $datetime->format('H:i:s');
                $data['session_date'] = $sessionDate;
                $data['session_time'] = $sessionTime;
            }

            // Check for existing session at this date/time with pessimistic lock
            $sessionTimeKey = $sessionTime ?? '00:00:00';
            $existingSession = CounselingSession::where('student_id', $data['student_id'])
                ->where('session_date', $sessionDate)
                ->where('session_time_key', $sessionTimeKey)
                ->whereNotIn('status', [CounselingSession::STATUS_CANCELLED])
                ->lockForUpdate()
                ->first();

            if ($existingSession) {
                return [
                    'duplicate' => true,
                    'existing_session' => $existingSession,
                    'message' => 'A session already exists for this student at this date/time.',
                ];
            }

            // Get or create welfare case
            $welfareCase = $this->getOrCreateCase($data, $counsellor);

            // Map 'duration' to 'duration_minutes' if provided
            if (isset($data['duration']) && !isset($data['duration_minutes'])) {
                $data['duration_minutes'] = $data['duration'];
                unset($data['duration']);
            }

            // Map 'counselor_id' to 'counsellor_id' if provided
            if (isset($data['counselor_id']) && !isset($data['counsellor_id'])) {
                $data['counsellor_id'] = $data['counselor_id'];
                unset($data['counselor_id']);
            }

            // Map 'purpose' to 'presenting_issue' if provided
            if (isset($data['purpose']) && !isset($data['presenting_issue'])) {
                $data['presenting_issue'] = $data['purpose'];
                unset($data['purpose']);
            }

            $data['welfare_case_id'] = $welfareCase->id;
            if (!isset($data['counsellor_id'])) {
                $data['counsellor_id'] = $counsellor->id;
            }
            $data['status'] = $data['status'] ?? CounselingSession::STATUS_SCHEDULED;

            // Set required fields with defaults if not provided
            $data['referral_source'] = $data['referral_source'] ?? 'admin'; // Default to admin since it's created by staff
            $data['session_number'] = $data['session_number'] ?? 1;

            $session = CounselingSession::create($data);

            WelfareAuditLog::log($session, WelfareAuditLog::ACTION_CREATED);

            return $session->fresh(['welfareCase', 'student', 'counsellor']);
        });
    }

    /**
     * Update a counseling session.
     *
     * @param CounselingSession $session
     * @param array $data
     * @return CounselingSession
     */
    public function updateSession(CounselingSession $session, array $data): CounselingSession
    {
        return DB::transaction(function () use ($session, $data) {
            // Lock the row before reading for update
            $lockedSession = CounselingSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            $oldValues = $lockedSession->toArray();

            $lockedSession->update($data);

            WelfareAuditLog::log($lockedSession, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedSession->fresh();
        });
    }

    /**
     * Complete a session with notes.
     *
     * @param CounselingSession $session
     * @param array $completionData
     * @return CounselingSession
     * @throws \InvalidArgumentException If session is not in scheduled status
     */
    public function completeSession(CounselingSession $session, array $completionData): CounselingSession
    {
        return DB::transaction(function () use ($session, $completionData) {
            // Lock the row before reading for update
            $lockedSession = CounselingSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            // Validate current state allows completion
            if ($lockedSession->status !== CounselingSession::STATUS_SCHEDULED) {
                throw new \InvalidArgumentException(
                    "Session cannot be completed. Current status: {$lockedSession->status}"
                );
            }

            $oldValues = ['status' => $lockedSession->status];

            $lockedSession->update(array_merge($completionData, [
                'status' => CounselingSession::STATUS_COMPLETED,
            ]));

            WelfareAuditLog::log($lockedSession, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            // Schedule next session if follow-up required
            if (!empty($completionData['next_session_date'])) {
                $lockedSession->update([
                    'follow_up_required' => true,
                    'next_session_date' => $completionData['next_session_date'],
                ]);
            }

            return $lockedSession->fresh();
        });
    }

    /**
     * Cancel a session.
     *
     * @param CounselingSession $session
     * @param string|null $reason
     * @return CounselingSession
     * @throws \InvalidArgumentException If session is not in scheduled status
     */
    public function cancelSession(CounselingSession $session, ?string $reason = null): CounselingSession
    {
        return DB::transaction(function () use ($session, $reason) {
            // Lock the row before reading for update
            $lockedSession = CounselingSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            // Validate current state allows cancellation
            if ($lockedSession->status !== CounselingSession::STATUS_SCHEDULED) {
                throw new \InvalidArgumentException(
                    "Only scheduled sessions can be cancelled. Current status: {$lockedSession->status}"
                );
            }

            $oldValues = ['status' => $lockedSession->status];

            $lockedSession->cancel();

            WelfareAuditLog::log($lockedSession, WelfareAuditLog::ACTION_UPDATED, $oldValues, null, $reason);

            return $lockedSession->fresh();
        });
    }

    /**
     * Mark session as no-show.
     *
     * @param CounselingSession $session
     * @return CounselingSession
     * @throws \InvalidArgumentException If session is not in scheduled status
     */
    public function markNoShow(CounselingSession $session): CounselingSession
    {
        return DB::transaction(function () use ($session) {
            // Lock the row before reading for update
            $lockedSession = CounselingSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            // Validate current state allows marking as no-show
            if ($lockedSession->status !== CounselingSession::STATUS_SCHEDULED) {
                throw new \InvalidArgumentException(
                    "Only scheduled sessions can be marked as no-show. Current status: {$lockedSession->status}"
                );
            }

            $oldValues = ['status' => $lockedSession->status];

            $lockedSession->markNoShow();

            WelfareAuditLog::log($lockedSession, WelfareAuditLog::ACTION_UPDATED, $oldValues);

            return $lockedSession->fresh();
        });
    }

    /**
     * Delete a counseling session.
     *
     * Only scheduled or cancelled sessions can be deleted.
     *
     * @param CounselingSession $session
     * @return bool
     * @throws \InvalidArgumentException If session cannot be deleted
     */
    public function deleteSession(CounselingSession $session): bool
    {
        return DB::transaction(function () use ($session) {
            // Lock the row before reading for update
            $lockedSession = CounselingSession::where('id', $session->id)
                ->lockForUpdate()
                ->first();

            // Only allow deletion of scheduled or cancelled sessions
            if (!in_array($lockedSession->status, [
                CounselingSession::STATUS_SCHEDULED,
                CounselingSession::STATUS_CANCELLED
            ])) {
                throw new \InvalidArgumentException(
                    "Cannot delete session with status: {$lockedSession->status}"
                );
            }

            WelfareAuditLog::log($lockedSession, WelfareAuditLog::ACTION_DELETED);

            return $lockedSession->delete();
        });
    }

    /**
     * Get sessions for a counsellor.
     *
     * @param User $counsellor
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCounsellorSessions(User $counsellor, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CounselingSession::with(['student', 'welfareCase'])
            ->where('counsellor_id', $counsellor->id);

        $this->applyFilters($query, $filters);

        return $query->orderBy('session_date', 'desc')
            ->orderBy('session_time', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get upcoming sessions.
     *
     * @param User|null $counsellor
     * @param int $days
     * @return Collection
     */
    public function getUpcomingSessions(?User $counsellor = null, int $days = 7): Collection
    {
        $query = CounselingSession::with(['student', 'welfareCase'])
            ->upcoming()
            ->where('session_date', '<=', now()->addDays($days));

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return $query->get();
    }

    /**
     * Get today's sessions.
     *
     * @param User|null $counsellor
     * @return Collection
     */
    public function getTodaySessions(?User $counsellor = null): Collection
    {
        $query = CounselingSession::with(['student', 'welfareCase'])
            ->today();

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return $query->orderBy('session_time')->get();
    }

    /**
     * Get sessions requiring follow-up.
     *
     * @param User|null $counsellor
     * @return Collection
     */
    public function getSessionsRequiringFollowUp(?User $counsellor = null): Collection
    {
        $query = CounselingSession::with(['student', 'welfareCase'])
            ->completed()
            ->requiringFollowUp();

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return $query->orderBy('session_date', 'desc')->get();
    }

    /**
     * Get high-risk sessions.
     *
     * @param int $days
     * @return Collection
     */
    public function getHighRiskSessions(int $days = 30): Collection
    {
        return CounselingSession::with(['student', 'welfareCase', 'counsellor'])
            ->highRisk()
            ->where('session_date', '>=', now()->subDays($days))
            ->orderBy('session_date', 'desc')
            ->get();
    }

    /**
     * Get student counseling history.
     *
     * @param Student $student
     * @param int|null $limit
     * @return Collection
     */
    public function getStudentHistory(Student $student, ?int $limit = null): Collection
    {
        $query = $student->counselingSessions()
            ->with(['counsellor', 'welfareCase'])
            ->orderBy('session_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get counseling statistics.
     *
     * @param User|null $counsellor
     * @return array
     */
    public function getStatistics(?User $counsellor = null): array
    {
        $query = CounselingSession::query();

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return [
            'total_sessions' => (clone $query)->currentTerm()->count(),
            'completed' => (clone $query)->currentTerm()->completed()->count(),
            'scheduled' => (clone $query)->scheduled()->count(),
            'high_risk' => (clone $query)->currentTerm()->highRisk()->count(),
            'requiring_follow_up' => (clone $query)->requiringFollowUp()->count(),
            'by_type' => $this->getSessionCountsByType($counsellor),
            'by_mood' => $this->getMoodDistribution($counsellor),
        ];
    }

    /**
     * Get session counts by type.
     *
     * @param User|null $counsellor
     * @return array
     */
    protected function getSessionCountsByType(?User $counsellor = null): array
    {
        $query = CounselingSession::select('session_type', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('session_type');

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return $query->pluck('count', 'session_type')->toArray();
    }

    /**
     * Get mood distribution from completed sessions.
     *
     * @param User|null $counsellor
     * @return array
     */
    protected function getMoodDistribution(?User $counsellor = null): array
    {
        $query = CounselingSession::select('student_mood', DB::raw('count(*) as count'))
            ->currentTerm()
            ->completed()
            ->whereNotNull('student_mood')
            ->groupBy('student_mood');

        if ($counsellor) {
            $query->where('counsellor_id', $counsellor->id);
        }

        return $query->pluck('count', 'student_mood')->toArray();
    }

    /**
     * Get or create a welfare case for the counseling session.
     *
     * @param array $data
     * @param User $counsellor
     * @return WelfareCase
     */
    protected function getOrCreateCase(array $data, User $counsellor): WelfareCase
    {
        // If case ID provided, use it
        if (!empty($data['welfare_case_id'])) {
            return WelfareCase::findOrFail($data['welfare_case_id']);
        }

        // Create new case
        $counselingType = WelfareType::where('code', 'COUNSEL')->first();

        return $this->caseService->createCase([
            'student_id' => $data['student_id'],
            'welfare_type_id' => $counselingType->id,
            'title' => $data['presenting_issue'] ?? 'Counseling Session',
            'priority' => WelfareCase::PRIORITY_MEDIUM,
        ], $counsellor);
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

        if (!empty($filters['session_type'])) {
            $query->where('session_type', $filters['session_type']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('session_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('session_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['risk_assessment'])) {
            $query->where('risk_assessment', $filters['risk_assessment']);
        }

        if (($filters['apply_term_scope'] ?? true) !== false) {
            $query->currentTerm();
        }
    }
}
