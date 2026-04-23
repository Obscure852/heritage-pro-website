<?php

namespace App\Http\Controllers\Crm;

use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\DiscussionThread;
use App\Models\Lead;
use App\Models\RequestActivity;
use App\Models\SalesStage;
use App\Models\CrmAttendanceSetting;
use App\Services\Crm\AttendanceClockService;
use Illuminate\Contracts\View\View;

class DashboardController extends CrmController
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $tomorrow = now()->copy()->addDay()->startOfDay();

        $activeLeads = $this->scopeOwned(Lead::query()->whereIn('status', ['active', 'qualified']))->count();
        $activeCustomers = $this->scopeOwned(Customer::query()->where('status', '!=', 'inactive'))->count();
        $openSalesRequests = $this->scopeOwned(
            CrmRequest::query()->where('type', 'sales')->whereNull('closed_at')
        )->count();
        $openSupportRequests = $this->scopeOwned(
            CrmRequest::query()
                ->where('type', 'support')
                ->whereNotIn('support_status', ['resolved', 'closed'])
        )->count();
        $overdueFollowUps = $this->scopeOwned(
            CrmRequest::query()
                ->whereNull('closed_at')
                ->whereNotNull('next_action_at')
                ->where('next_action_at', '<', $today)
        )->count();
        $todayFollowUps = $this->scopeOwned(
            CrmRequest::query()
                ->whereNull('closed_at')
                ->whereNotNull('next_action_at')
                ->where('next_action_at', '>=', $today)
                ->where('next_action_at', '<', $tomorrow)
        )->count();
        $openDevelopmentItems = $this->scopeOwned(
            DevelopmentRequest::query()->whereNotIn('status', ['shipped', 'declined'])
        )->count();
        $discussionThreads = DiscussionThread::query()
            ->when(! $this->crmUser()->canManageOperationalRecords(), function ($query) {
                $query->where(function ($threadQuery) {
                    $threadQuery->where('initiated_by_id', $this->crmUser()->id)
                        ->orWhere('recipient_user_id', $this->crmUser()->id)
                        ->orWhere('owner_id', $this->crmUser()->id);
                });
            })
            ->count();

        $recentActivities = RequestActivity::query()
            ->with(['user', 'request:id,title,owner_id,lead_id,customer_id', 'request.lead:id,company_name', 'request.customer:id,company_name'])
            ->whereHas('request', function ($query) {
                $this->scopeOwned($query);
            })
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $stageBreakdown = SalesStage::query()
            ->orderBy('position')
            ->get()
            ->map(function (SalesStage $stage) {
                $count = CrmRequest::query()
                    ->where('type', 'sales')
                    ->where('sales_stage_id', $stage->id)
                    ->when($this->crmUser()->isRep(), function ($query) {
                        $query->where('owner_id', $this->crmUser()->id);
                    })
                    ->count();

                return [
                    'stage' => $stage,
                    'count' => $count,
                ];
            });

        $recentRequests = $this->scopeOwned(
            CrmRequest::query()->with([
                'owner',
                'lead:id,company_name',
                'customer:id,company_name',
                'salesStage:id,name',
            ])
        )
            ->latest()
            ->limit(8)
            ->get();

        $attendanceSettings = CrmAttendanceSetting::resolve();
        $clockStatus = $attendanceSettings->show_dashboard_clock
            ? app(AttendanceClockService::class)->currentStatus($this->crmUser())
            : null;

        return view('crm.dashboard', [
            'clockStatus' => $clockStatus,
            'showDashboardClock' => $attendanceSettings->show_dashboard_clock,
            'metrics' => [
                ['label' => 'Active leads', 'value' => $activeLeads],
                ['label' => 'Live customers', 'value' => $activeCustomers],
                ['label' => 'Open sales requests', 'value' => $openSalesRequests],
                ['label' => 'Open support requests', 'value' => $openSupportRequests],
                ['label' => 'Overdue follow-ups', 'value' => $overdueFollowUps],
                ['label' => 'Due today', 'value' => $todayFollowUps],
                ['label' => 'Open dev requests', 'value' => $openDevelopmentItems],
                ['label' => 'Discussion threads', 'value' => $discussionThreads],
            ],
            'recentActivities' => $recentActivities,
            'recentRequests' => $recentRequests,
            'stageBreakdown' => $stageBreakdown,
        ]);
    }
}
