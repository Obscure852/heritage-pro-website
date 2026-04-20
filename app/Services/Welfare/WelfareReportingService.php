<?php

namespace App\Services\Welfare;

use App\Models\Welfare\CounselingSession;
use App\Models\Welfare\DisciplinaryRecord;
use App\Models\Welfare\HealthIncident;
use App\Models\Welfare\InterventionPlan;
use App\Models\Welfare\SafeguardingConcern;
use App\Models\Welfare\WelfareCase;
use App\Models\Welfare\WelfareAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WelfareReportingService
{
    /**
     * Get comprehensive welfare dashboard data.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        return [
            'summary' => $this->getSummaryStats(),
            'cases' => $this->getCaseStats(),
            'alerts' => $this->getAlerts(),
            'recent_activity' => $this->getRecentActivity(),
            'trends' => $this->getTrends(),
        ];
    }

    /**
     * Get summary statistics.
     *
     * @return array
     */
    public function getSummaryStats(): array
    {
        return [
            'open_cases' => WelfareCase::open()->count(),
            'pending_approval' => WelfareCase::pendingApproval()->count(),
            'high_priority' => WelfareCase::open()->highPriority()->count(),
            'closed_this_term' => WelfareCase::currentTerm()->closed()->count(),
            'students_with_open_cases' => WelfareCase::open()->distinct('student_id')->count('student_id'),
        ];
    }

    /**
     * Get case statistics by type.
     *
     * @return array
     */
    public function getCaseStats(): array
    {
        // Get current term for debugging
        $currentTerm = \App\Helpers\TermHelper::getCurrentTerm();
        if (!$currentTerm) {
            Log::warning('WelfareReportingService: No current term found. Stats will be empty.');
        } else {
            // Log current term info and total counts for debugging
            $totalCases = WelfareCase::count();
            $totalCasesInTerm = WelfareCase::where('term_id', $currentTerm->id)->count();
            
            Log::debug('WelfareReportingService: Using current term', [
                'term_id' => $currentTerm->id,
                'term' => $currentTerm->term,
                'year' => $currentTerm->year,
                'start_date' => $currentTerm->start_date,
                'end_date' => $currentTerm->end_date,
                'total_cases_all_terms' => $totalCases,
                'total_cases_in_current_term' => $totalCasesInTerm,
            ]);
        }

        // Helper function to safely get count
        $safeCount = function ($query) {
            try {
                return $query->count();
            } catch (\Exception $e) {
                Log::warning('WelfareReportingService: Query failed', ['error' => $e->getMessage()]);
                return 0;
            }
        };

        // Get cases by welfare type for the dashboard
        $casesByType = [];
        try {
            $casesByType = WelfareCase::select('welfare_type_id', DB::raw('count(*) as case_count'))
                ->currentTerm()
                ->whereNotNull('welfare_type_id')
                ->groupBy('welfare_type_id')
                ->with('welfareType:id,code')
                ->get()
                ->filter(fn ($item) => $item->welfareType !== null)
                ->mapWithKeys(fn ($item) => [
                    $item->welfareType->code => (int) $item->case_count
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::warning('WelfareReportingService: Cases by type query failed', ['error' => $e->getMessage()]);
        }

        return [
            'by_type' => $casesByType,
            'counseling' => [
                'total' => $safeCount(CounselingSession::currentTerm()),
                'completed' => $safeCount(CounselingSession::currentTerm()->completed()),
                'scheduled' => $safeCount(CounselingSession::currentTerm()->scheduled()),
                'high_risk' => $safeCount(CounselingSession::currentTerm()->highRisk()),
            ],
            'disciplinary' => [
                'total' => $safeCount(DisciplinaryRecord::currentTerm()),
                'unresolved' => $safeCount(DisciplinaryRecord::currentTerm()->unresolved()),
                'with_active_action' => $safeCount(DisciplinaryRecord::currentTerm()->withActiveAction()),
            ],
            'safeguarding' => [
                'total' => $safeCount(SafeguardingConcern::currentTerm()),
                'open' => $safeCount(SafeguardingConcern::currentTerm()->open()),
                'critical' => $safeCount(SafeguardingConcern::currentTerm()->critical()->open()),
            ],
            'health' => [
                'total' => $safeCount(HealthIncident::currentTerm()),
                'today' => $safeCount(HealthIncident::today()),
                'sent_home' => $safeCount(HealthIncident::currentTerm()->sentHome()),
                'emergency' => $safeCount(HealthIncident::currentTerm()->emergency()),
            ],
            'intervention_plans' => [
                'total' => $safeCount(InterventionPlan::currentTerm()),
                'active' => $safeCount(InterventionPlan::active()),
                'overdue' => $safeCount(InterventionPlan::overdue()),
                'review_due' => $safeCount(InterventionPlan::reviewDue()),
            ],
        ];
    }

    /**
     * Get alerts for urgent attention.
     *
     * @return array
     */
    public function getAlerts(): array
    {
        $alerts = [];

        // Critical safeguarding concerns
        $criticalSafeguarding = SafeguardingConcern::critical()->open()->count();
        if ($criticalSafeguarding > 0) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'safeguarding',
                'message' => "{$criticalSafeguarding} critical safeguarding concern(s) require immediate attention",
                'count' => $criticalSafeguarding,
            ];
        }

        // Safeguarding awaiting authority notification
        $awaitingAuth = SafeguardingConcern::awaitingAuthorityNotification()->count();
        if ($awaitingAuth > 0) {
            $alerts[] = [
                'type' => 'urgent',
                'category' => 'safeguarding',
                'message' => "{$awaitingAuth} concern(s) awaiting authority notification",
                'count' => $awaitingAuth,
            ];
        }

        // High-risk counseling sessions
        $highRisk = CounselingSession::currentTerm()->highRisk()->count();
        if ($highRisk > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'counseling',
                'message' => "{$highRisk} high-risk counseling case(s) identified",
                'count' => $highRisk,
            ];
        }

        // Overdue intervention plan reviews
        $overdueReviews = InterventionPlan::reviewDue()->count();
        if ($overdueReviews > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'intervention',
                'message' => "{$overdueReviews} intervention plan review(s) overdue",
                'count' => $overdueReviews,
            ];
        }

        // Cases pending approval
        $pendingApproval = WelfareCase::pendingApproval()->count();
        if ($pendingApproval > 0) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'cases',
                'message' => "{$pendingApproval} case(s) pending approval",
                'count' => $pendingApproval,
            ];
        }

        // Today's health emergencies
        $emergencies = HealthIncident::today()->emergency()->count();
        if ($emergencies > 0) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'health',
                'message' => "{$emergencies} emergency health incident(s) today",
                'count' => $emergencies,
            ];
        }

        return $alerts;
    }

    /**
     * Get recent activity.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        try {
            $logs = WelfareAuditLog::with(['user', 'welfareCase.student'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            Log::debug('WelfareReportingService: Recent activity logs found', [
                'count' => $logs->count(),
            ]);

            return $logs->map(function ($log) {
                try {
                    // Access accessors directly - they should work automatically
                    $actionLabel = $log->action_label ?? ucfirst($log->action ?? 'Unknown');
                    $actionColor = $log->action_color ?? 'gray';
                    $modelName = $log->model_name ?? 'Unknown';
                    
                    // Get user name
                    $userName = 'System';
                    if ($log->user) {
                        $userName = $log->user->full_name ?? ($log->user->firstname . ' ' . $log->user->lastname);
                    }

                    return [
                        'id' => $log->id,
                        'action' => $actionLabel,
                        'model' => $modelName,
                        'user' => $userName,
                        'student' => $log->welfareCase?->student?->full_name ?? null,
                        'case_number' => $log->welfareCase?->case_number ?? null,
                        'time' => $log->created_at ? $log->created_at->diffForHumans() : 'Unknown',
                        'color' => $actionColor,
                    ];
                } catch (\Exception $e) {
                    Log::warning('WelfareReportingService: Error mapping audit log', [
                        'log_id' => $log->id ?? 'unknown',
                        'action' => $log->action ?? 'null',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return null;
                }
            })->filter();
        } catch (\Exception $e) {
            Log::error('WelfareReportingService: Error fetching recent activity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return collect([]);
        }
    }

    /**
     * Get trends data for charts.
     *
     * @param int $months
     * @return array
     */
    public function getTrends(int $months = 6): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        return [
            'cases_by_month' => $this->getCasesByMonth($startDate),
            'cases_by_type' => $this->getCasesByType(),
            'cases_by_priority' => $this->getCasesByPriority(),
        ];
    }

    /**
     * Get cases by month.
     *
     * @param \Carbon\Carbon $startDate
     * @return array
     */
    protected function getCasesByMonth(\Carbon\Carbon $startDate): array
    {
        return WelfareCase::select(
            DB::raw('DATE_FORMAT(opened_at, "%Y-%m") as month'),
            DB::raw('count(*) as count')
        )
            ->where('opened_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    /**
     * Get cases by type.
     *
     * @return array
     */
    protected function getCasesByType(): array
    {
        return WelfareCase::select('welfare_type_id', DB::raw('count(*) as count'))
            ->currentTerm()
            ->groupBy('welfare_type_id')
            ->with('welfareType:id,name,code,color')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->welfareType->name => [
                    'count' => $item->count,
                    'color' => $item->welfareType->color,
                ]
            ])
            ->toArray();
    }

    /**
     * Get cases by priority.
     *
     * @return array
     */
    protected function getCasesByPriority(): array
    {
        return WelfareCase::select('priority', DB::raw('count(*) as count'))
            ->open()
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }

    /**
     * Generate term report.
     *
     * @param int|null $termId
     * @return array
     */
    public function generateTermReport(?int $termId = null): array
    {
        $query = WelfareCase::query();

        if ($termId) {
            $query->where('term_id', $termId);
        } else {
            $query->currentTerm();
        }

        $cases = $query->with(['student', 'welfareType', 'openedBy', 'assignedTo'])->get();

        return [
            'period' => [
                'term_id' => $termId,
                'generated_at' => now()->toDateTimeString(),
            ],
            'totals' => [
                'total_cases' => $cases->count(),
                'open' => $cases->where('status', '!=', 'closed')->count(),
                'closed' => $cases->where('status', 'closed')->count(),
                'resolved' => $cases->where('status', 'resolved')->count(),
            ],
            'by_type' => $cases->groupBy('welfareType.name')
                ->map(fn ($group) => $group->count())
                ->toArray(),
            'by_priority' => $cases->groupBy('priority')
                ->map(fn ($group) => $group->count())
                ->toArray(),
            'by_status' => $cases->groupBy('status')
                ->map(fn ($group) => $group->count())
                ->toArray(),
            'average_resolution_time' => $this->calculateAverageResolutionTime($cases),
            'students_affected' => $cases->pluck('student_id')->unique()->count(),
        ];
    }

    /**
     * Calculate average resolution time in days.
     *
     * @param Collection $cases
     * @return float|null
     */
    protected function calculateAverageResolutionTime(Collection $cases): ?float
    {
        $resolvedCases = $cases->filter(fn ($case) => $case->closed_at !== null);

        if ($resolvedCases->isEmpty()) {
            return null;
        }

        $totalDays = $resolvedCases->sum(function ($case) {
            return $case->opened_at->diffInDays($case->closed_at);
        });

        return round($totalDays / $resolvedCases->count(), 1);
    }

    /**
     * Get student welfare summary.
     *
     * @param int $studentId
     * @return array
     */
    public function getStudentWelfareSummary(int $studentId): array
    {
        return [
            'cases' => [
                'total' => WelfareCase::where('student_id', $studentId)->count(),
                'open' => WelfareCase::where('student_id', $studentId)->open()->count(),
                'this_term' => WelfareCase::where('student_id', $studentId)->currentTerm()->count(),
            ],
            'counseling' => [
                'total_sessions' => CounselingSession::where('student_id', $studentId)->count(),
                'this_term' => CounselingSession::where('student_id', $studentId)->currentTerm()->count(),
            ],
            'disciplinary' => [
                'total' => DisciplinaryRecord::where('student_id', $studentId)->count(),
                'unresolved' => DisciplinaryRecord::where('student_id', $studentId)->unresolved()->count(),
            ],
            'health' => [
                'total' => HealthIncident::where('student_id', $studentId)->count(),
                'this_term' => HealthIncident::where('student_id', $studentId)->currentTerm()->count(),
            ],
            'has_active_intervention' => InterventionPlan::where('student_id', $studentId)->active()->exists(),
        ];
    }

    /**
     * Export welfare data.
     *
     * @param array $filters
     * @return Collection
     */
    public function exportData(array $filters = []): Collection
    {
        $query = WelfareCase::with([
            'student:id,first_name,last_name',
            'welfareType:id,name,code',
            'openedBy:id,firstname,lastname',
            'assignedTo:id,firstname,lastname',
        ]);

        if (!empty($filters['date_from'])) {
            $query->where('opened_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('opened_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['welfare_type_id'])) {
            $query->where('welfare_type_id', $filters['welfare_type_id']);
        }

        return $query->orderBy('opened_at', 'desc')->get();
    }
}
