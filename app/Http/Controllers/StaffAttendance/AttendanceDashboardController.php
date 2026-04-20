<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Services\StaffAttendance\AttendanceDashboardService;
use Illuminate\Http\Request;

class AttendanceDashboardController extends Controller {
    protected AttendanceDashboardService $dashboardService;

    /**
     * Admin roles that can view all staff attendance.
     */
    protected array $adminRoles = ['Administrator', 'Leave Admin', 'HR Admin'];

    public function __construct(AttendanceDashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }

    public function index() {
        $user = auth()->user();

        // Check if user is admin (can view all staff) or has subordinates (manager view)
        $isAdmin = $user->hasAnyRoles($this->adminRoles);

        // Authorization: Must be admin OR have subordinates to see dashboard
        if (!$isAdmin && !$user->subordinates()->exists()) {
            abort(403, 'You do not have team members to view.');
        }

        $dashboardData = $this->dashboardService->getDashboardData($user, $isAdmin);

        return view('staff-attendance.manager.dashboard', compact('dashboardData', 'isAdmin'));
    }
}
