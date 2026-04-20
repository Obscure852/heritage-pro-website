<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Activities\ActivityResult;
use App\Models\Activities\ActivitySessionAttendance;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActivityReportService
{
    public function reportPayload(?User $user, ?Term $selectedTerm, array $filters): array
    {
        $activityOptions = $this->scopedActivityQuery($user, $selectedTerm, [])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'status']);

        $activities = $this->scopedActivityQuery($user, $selectedTerm, $filters)
            ->with(['term:id,term,year', 'feeType:id,name'])
            ->withCount([
                'staffAssignments as active_staff_assignments_count' => fn ($query) => $query->where('active', true),
                'enrollments as active_enrollments_count' => fn ($query) => $query->where('status', ActivityEnrollment::STATUS_ACTIVE),
                'enrollments as historical_enrollments_count' => fn ($query) => $query->where('status', '!=', ActivityEnrollment::STATUS_ACTIVE),
                'sessions',
                'sessions as completed_sessions_count' => fn ($query) => $query->where('status', \App\Models\Activities\ActivitySession::STATUS_COMPLETED),
                'events',
                'events as completed_events_count' => fn ($query) => $query->where('status', \App\Models\Activities\ActivityEvent::STATUS_COMPLETED),
                'feeCharges',
            ])
            ->orderBy('name')
            ->get();

        $activityIds = $activities->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $activityRows = $this->buildActivityRows($activities, $activityIds);

        return [
            'activityOptions' => $activityOptions,
            'selectedActivity' => $filters['activity_id']
                ? $activityOptions->firstWhere('id', (int) $filters['activity_id'])
                : null,
            'activityRows' => $activityRows,
            'summary' => $this->buildSummary($activityRows),
            'houseOutputs' => $this->houseOutputs($activityIds),
            'chargeExceptions' => $this->chargeExceptions($activityIds),
        ];
    }

    public function reportExportRows(?User $user, ?Term $selectedTerm, array $filters): Collection
    {
        return $this->reportPayload($user, $selectedTerm, $filters)['activityRows'];
    }

    private function scopedActivityQuery(?User $user, ?Term $selectedTerm, array $filters): Builder
    {
        $hasGlobalViewAccess = $user?->hasAnyRoles([
            'Administrator',
            'Activities Admin',
            'Activities Edit',
            'Activities View',
        ]) ?? false;

        return Activity::query()
            ->when($selectedTerm, fn (Builder $query) => $query->where('term_id', $selectedTerm->id))
            ->when(
                !$hasGlobalViewAccess && $user,
                fn (Builder $query) => $query->whereHas('staffAssignments', function (Builder $assignmentQuery) use ($user) {
                    $assignmentQuery
                        ->where('user_id', $user->id)
                        ->where('active', true);
                })
            )
            ->when(!empty($filters['activity_id']), fn (Builder $query) => $query->where('id', (int) $filters['activity_id']))
            ->when(!empty($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['category']), fn (Builder $query) => $query->where('category', $filters['category']))
            ->when(!empty($filters['delivery_mode']), fn (Builder $query) => $query->where('delivery_mode', $filters['delivery_mode']))
            ->when(!empty($filters['search']), function (Builder $query) use ($filters) {
                $search = '%' . trim((string) $filters['search']) . '%';

                $query->where(function (Builder $activityQuery) use ($search) {
                    $activityQuery->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('default_location', 'like', $search);
                });
            });
    }

    private function buildActivityRows(Collection $activities, array $activityIds): Collection
    {
        $attendanceStats = $this->attendanceStats($activityIds);
        $resultStats = $this->resultStats($activityIds);
        $chargeStats = $this->chargeStats($activityIds);

        return $activities->map(function (Activity $activity) use ($attendanceStats, $resultStats, $chargeStats) {
            $attendance = $attendanceStats->get($activity->id, [
                'marked_count' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'late_count' => 0,
                'excused_count' => 0,
                'injured_count' => 0,
            ]);

            $results = $resultStats->get($activity->id, [
                'results_count' => 0,
                'awards_count' => 0,
                'points_total' => 0,
                'house_results_count' => 0,
            ]);

            $charges = $chargeStats->get($activity->id, [
                'total_count' => 0,
                'posted_count' => 0,
                'pending_count' => 0,
                'blocked_count' => 0,
                'total_amount' => 0.0,
                'outstanding_amount' => 0.0,
            ]);

            $markedCount = (int) ($attendance['marked_count'] ?? 0);
            $presentCount = (int) ($attendance['present_count'] ?? 0);

            return [
                'id' => $activity->id,
                'name' => $activity->name,
                'code' => $activity->code,
                'category' => Activity::categories()[$activity->category] ?? ucfirst((string) $activity->category),
                'delivery_mode' => Activity::deliveryModes()[$activity->delivery_mode] ?? ucfirst((string) $activity->delivery_mode),
                'status' => Activity::statuses()[$activity->status] ?? ucfirst((string) $activity->status),
                'status_key' => $activity->status,
                'term_label' => $activity->term ? 'Term ' . $activity->term->term . ' - ' . $activity->term->year : ('Year ' . $activity->year),
                'fee_type' => $activity->feeType?->name,
                'active_staff_assignments_count' => (int) $activity->active_staff_assignments_count,
                'active_enrollments_count' => (int) $activity->active_enrollments_count,
                'historical_enrollments_count' => (int) $activity->historical_enrollments_count,
                'sessions_count' => (int) $activity->sessions_count,
                'completed_sessions_count' => (int) $activity->completed_sessions_count,
                'events_count' => (int) $activity->events_count,
                'completed_events_count' => (int) $activity->completed_events_count,
                'attendance_marked_count' => $markedCount,
                'attendance_present_count' => $presentCount,
                'attendance_absent_count' => (int) ($attendance['absent_count'] ?? 0),
                'attendance_present_rate' => $markedCount > 0 ? round(($presentCount / $markedCount) * 100, 1) : null,
                'results_count' => (int) ($results['results_count'] ?? 0),
                'awards_count' => (int) ($results['awards_count'] ?? 0),
                'points_total' => (int) ($results['points_total'] ?? 0),
                'house_results_count' => (int) ($results['house_results_count'] ?? 0),
                'charge_total_count' => (int) ($charges['total_count'] ?? 0),
                'charge_posted_count' => (int) ($charges['posted_count'] ?? 0),
                'charge_pending_count' => (int) ($charges['pending_count'] ?? 0),
                'charge_blocked_count' => (int) ($charges['blocked_count'] ?? 0),
                'charge_total_amount' => (float) ($charges['total_amount'] ?? 0),
                'charge_outstanding_amount' => (float) ($charges['outstanding_amount'] ?? 0),
            ];
        })->values();
    }

    private function attendanceStats(array $activityIds): Collection
    {
        if (empty($activityIds)) {
            return collect();
        }

        return collect(
            DB::table('activity_session_attendance as attendance')
                ->join('activity_sessions as sessions', 'sessions.id', '=', 'attendance.activity_session_id')
                ->whereIn('sessions.activity_id', $activityIds)
                ->whereNull('sessions.deleted_at')
                ->selectRaw(
                    "sessions.activity_id,
                    COUNT(attendance.id) as marked_count,
                    SUM(CASE WHEN attendance.status = ? THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN attendance.status = ? THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN attendance.status = ? THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN attendance.status = ? THEN 1 ELSE 0 END) as excused_count,
                    SUM(CASE WHEN attendance.status = ? THEN 1 ELSE 0 END) as injured_count",
                    [
                        ActivitySessionAttendance::STATUS_PRESENT,
                        ActivitySessionAttendance::STATUS_ABSENT,
                        ActivitySessionAttendance::STATUS_LATE,
                        ActivitySessionAttendance::STATUS_EXCUSED,
                        ActivitySessionAttendance::STATUS_INJURED,
                    ]
                )
                ->groupBy('sessions.activity_id')
                ->get()
                ->mapWithKeys(fn ($row) => [(int) $row->activity_id => (array) $row])
                ->all()
        );
    }

    private function resultStats(array $activityIds): Collection
    {
        if (empty($activityIds)) {
            return collect();
        }

        return collect(
            DB::table('activity_results as results')
                ->join('activity_events as events', 'events.id', '=', 'results.activity_event_id')
                ->whereIn('events.activity_id', $activityIds)
                ->whereNull('events.deleted_at')
                ->selectRaw(
                    "events.activity_id,
                    COUNT(results.id) as results_count,
                    SUM(CASE WHEN results.award_name IS NOT NULL AND results.award_name <> '' THEN 1 ELSE 0 END) as awards_count,
                    COALESCE(SUM(results.points), 0) as points_total,
                    SUM(CASE WHEN results.participant_type = ? THEN 1 ELSE 0 END) as house_results_count",
                    [ActivityResult::PARTICIPANT_HOUSE]
                )
                ->groupBy('events.activity_id')
                ->get()
                ->mapWithKeys(fn ($row) => [(int) $row->activity_id => (array) $row])
                ->all()
        );
    }

    private function chargeStats(array $activityIds): Collection
    {
        if (empty($activityIds)) {
            return collect();
        }

        return collect(
            DB::table('activity_fee_charges as charges')
                ->leftJoin('student_invoices as invoices', 'invoices.id', '=', 'charges.student_invoice_id')
                ->whereIn('charges.activity_id', $activityIds)
                ->whereNull('charges.deleted_at')
                ->selectRaw(
                    "charges.activity_id,
                    COUNT(charges.id) as total_count,
                    SUM(CASE WHEN charges.billing_status = ? THEN 1 ELSE 0 END) as posted_count,
                    SUM(CASE WHEN charges.billing_status = ? THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN charges.billing_status = ? THEN 1 ELSE 0 END) as blocked_count,
                    COALESCE(SUM(charges.amount), 0) as total_amount,
                    COALESCE(SUM(CASE WHEN charges.billing_status = ? AND COALESCE(invoices.balance, 0) > 0 THEN charges.amount ELSE 0 END), 0) as outstanding_amount",
                    [
                        ActivityFeeCharge::STATUS_POSTED,
                        ActivityFeeCharge::STATUS_PENDING,
                        ActivityFeeCharge::STATUS_BLOCKED,
                        ActivityFeeCharge::STATUS_POSTED,
                    ]
                )
                ->groupBy('charges.activity_id')
                ->get()
                ->mapWithKeys(fn ($row) => [(int) $row->activity_id => (array) $row])
                ->all()
        );
    }

    private function buildSummary(Collection $rows): array
    {
        return [
            'activity_count' => $rows->count(),
            'active_count' => $rows->where('status_key', Activity::STATUS_ACTIVE)->count(),
            'draft_count' => $rows->where('status_key', Activity::STATUS_DRAFT)->count(),
            'closed_count' => $rows->whereIn('status_key', [Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED])->count(),
            'active_roster_total' => (int) $rows->sum('active_enrollments_count'),
            'historical_roster_total' => (int) $rows->sum('historical_enrollments_count'),
            'session_total' => (int) $rows->sum('sessions_count'),
            'attendance_marked_total' => (int) $rows->sum('attendance_marked_count'),
            'event_total' => (int) $rows->sum('events_count'),
            'result_total' => (int) $rows->sum('results_count'),
            'award_total' => (int) $rows->sum('awards_count'),
            'points_total' => (int) $rows->sum('points_total'),
            'charge_total' => (int) $rows->sum('charge_total_count'),
            'pending_charge_total' => (int) $rows->sum('charge_pending_count'),
            'blocked_charge_total' => (int) $rows->sum('charge_blocked_count'),
            'charge_amount_total' => (float) $rows->sum('charge_total_amount'),
            'outstanding_amount_total' => (float) $rows->sum('charge_outstanding_amount'),
        ];
    }

    private function houseOutputs(array $activityIds): Collection
    {
        if (empty($activityIds)) {
            return collect();
        }

        return collect(
            DB::table('activity_results as results')
                ->join('activity_events as events', 'events.id', '=', 'results.activity_event_id')
                ->join('activities', 'activities.id', '=', 'events.activity_id')
                ->leftJoin('houses', 'houses.id', '=', 'results.participant_id')
                ->whereIn('activities.id', $activityIds)
                ->whereNull('activities.deleted_at')
                ->where('results.participant_type', ActivityResult::PARTICIPANT_HOUSE)
                ->selectRaw(
                    "results.participant_id as house_id,
                    COALESCE(houses.name, 'Unknown House') as house_name,
                    COUNT(results.id) as results_count,
                    SUM(CASE WHEN results.award_name IS NOT NULL AND results.award_name <> '' THEN 1 ELSE 0 END) as award_count,
                    COALESCE(SUM(results.points), 0) as points_total,
                    COUNT(DISTINCT results.activity_event_id) as event_count,
                    COUNT(DISTINCT activities.id) as activity_count"
                )
                ->groupBy('results.participant_id', 'houses.name')
                ->orderByDesc('points_total')
                ->orderBy('house_name')
                ->get()
        );
    }

    private function chargeExceptions(array $activityIds): Collection
    {
        if (empty($activityIds)) {
            return collect();
        }

        return ActivityFeeCharge::query()
            ->with([
                'activity:id,name,code',
                'student:id,first_name,last_name',
                'invoice:id,invoice_number,status,balance',
            ])
            ->whereIn('activity_id', $activityIds)
            ->whereIn('billing_status', [
                ActivityFeeCharge::STATUS_PENDING,
                ActivityFeeCharge::STATUS_BLOCKED,
            ])
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();
    }
}
