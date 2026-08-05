<?php

namespace App\Http\Controllers\Crm;

use App\Models\CrmAttendanceSetting;
use App\Models\User;
use App\Services\Crm\AttendanceClockService;
use App\Services\Crm\AttendanceReportService;
use App\Services\Crm\DashboardMetricsService;
use App\Services\Crm\DashboardWidgetRegistry;
use App\Services\Crm\LeaveBalanceService;
use App\Support\Crm\DashboardPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends CrmController
{
    public function __construct(
        private readonly DashboardMetricsService $metrics,
        private readonly DashboardWidgetRegistry $widgets,
        private readonly AttendanceClockService $clock,
        private readonly AttendanceReportService $attendanceReports,
        private readonly LeaveBalanceService $leaveBalances
    ) {
    }

    public function index(Request $request): View {
        $user = $this->crmUser();
        $period = DashboardPeriod::fromKey($request->query('period'));

        $attendanceSettings = CrmAttendanceSetting::resolve();
        $settings = ['show_dashboard_clock' => (bool) $attendanceSettings->show_dashboard_clock];

        $rows = $this->widgets->rowsFor($user, $settings);
        $visibleKeys = collect($rows)->flatten(1)->pluck('key')->all();

        return view('crm.dashboard', array_merge([
            'period' => $period,
            'periodOptions' => DashboardPeriod::options(),
            'widgetRows' => $rows,
            'visibleWidgets' => $visibleKeys,
            'metrics' => $this->metrics->headlineMetrics($user),
        ], $this->widgetData($visibleKeys, $user, $period)));
    }

    /**
     * Build view data for the visible widgets only, so a hidden widget costs no queries.
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    private function widgetData(array $keys, User $user, DashboardPeriod $period): array {
        $data = [];
        $has = fn (string $key) => in_array($key, $keys, true);
        $showCommercial = $has('commercial');

        if ($has('movement')) {
            $data['flowMetrics'] = $this->metrics->flowMetrics($user, $period);
        }

        if ($has('pressure')) {
            $data['pressureMetrics'] = $this->metrics->pressureMetrics($user);
        }

        if ($showCommercial) {
            $data['commercialMetrics'] = $this->metrics->commercialMetrics($user, $period);
            $data['mixedCurrencies'] = $this->metrics->mixedCurrencies($user);
        }

        if ($has('revenue_trend') || $has('quote_conversion')) {
            $data['currencyCode'] = $this->metrics->currencyCode();
        }

        if ($has('revenue_trend')) {
            $data['revenueTrend'] = $this->metrics->revenueTrend($user);
        }

        if ($has('quote_conversion')) {
            $data['quoteConversion'] = $this->metrics->quoteConversion($user, $period);
        }

        if ($has('my_day')) {
            $data['myDay'] = $this->metrics->myDay($user);
        }

        if ($has('clock')) {
            $data['clockStatus'] = $this->clock->currentStatus($user);
        }

        if ($has('going_cold')) {
            $data['goingCold'] = $this->metrics->goingCold($user);
            $data['goingColdDays'] = (int) config('heritage_crm.dashboard.going_cold_days', 14);
        }

        if ($has('needs_attention')) {
            $data['needsAttention'] = $this->metrics->needsAttention($user, $showCommercial);
        }

        if ($has('team_today')) {
            $data['teamToday'] = $this->metrics->teamToday($user, $this->attendanceReports);
        }

        if ($has('leave')) {
            $leaveYear = $this->leaveBalances->currentLeaveYear();
            $data['leaveApprovals'] = $this->metrics->leaveAwaitingApproval($user);
            $data['myLeaveBalances'] = $this->metrics->myLeaveBalances($user, $leaveYear);
            $data['leaveYear'] = $leaveYear;
        }

        return $data;
    }
}
