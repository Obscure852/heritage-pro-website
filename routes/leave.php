<?php

use App\Http\Controllers\Leave\LeaveBalanceController;
use App\Http\Controllers\Leave\LeaveCalendarController;
use App\Http\Controllers\Leave\LeavePolicyController;
use App\Http\Controllers\Leave\LeaveReportController;
use App\Http\Controllers\Leave\LeaveRequestController;
use App\Http\Controllers\Leave\LeaveSettingsController;
use App\Http\Controllers\Leave\LeaveStatementController;
use App\Http\Controllers\Leave\LeaveTypeController;
use App\Http\Controllers\Leave\PublicHolidayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Leave Management Routes
|--------------------------------------------------------------------------
|
| Routes for leave management functionality including leave types,
| policies, public holidays, leave requests, and approvals.
|
*/

// Leave Types - requires manage-leave-types
Route::prefix('leave/types')->name('leave.types.')
    ->middleware(['auth', 'can:manage-leave-types'])
    ->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::get('/create', [LeaveTypeController::class, 'create'])->name('create');
        Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
        Route::get('/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
        Route::post('/{leaveType}/toggle-status', [LeaveTypeController::class, 'toggleStatus'])->name('toggle-status');
    });

// Leave Policies (nested under leave types) - requires manage-leave-types
Route::prefix('leave/types/{leaveType}/policies')->name('leave.policies.')
    ->middleware(['auth', 'can:manage-leave-types'])
    ->group(function () {
        Route::get('/', [LeavePolicyController::class, 'index'])->name('index');
        Route::get('/create', [LeavePolicyController::class, 'create'])->name('create');
        Route::post('/', [LeavePolicyController::class, 'store'])->name('store');
        Route::get('/{policy}/edit', [LeavePolicyController::class, 'edit'])->name('edit');
        Route::put('/{policy}', [LeavePolicyController::class, 'update'])->name('update');
        Route::delete('/{policy}', [LeavePolicyController::class, 'destroy'])->name('destroy');
    });

// Public Holidays - requires manage-leave-holidays
Route::prefix('leave/holidays')->name('leave.holidays.')
    ->middleware(['auth', 'can:manage-leave-holidays'])
    ->group(function () {
        Route::get('/', [PublicHolidayController::class, 'index'])->name('index');
        Route::get('/calendar', [PublicHolidayController::class, 'calendar'])->name('calendar');
        Route::get('/create', [PublicHolidayController::class, 'create'])->name('create');
        Route::post('/', [PublicHolidayController::class, 'store'])->name('store');
        Route::get('/{holiday}/edit', [PublicHolidayController::class, 'edit'])->name('edit');
        Route::put('/{holiday}', [PublicHolidayController::class, 'update'])->name('update');
        Route::delete('/{holiday}', [PublicHolidayController::class, 'destroy'])->name('destroy');
        Route::post('/{holiday}/toggle-status', [PublicHolidayController::class, 'toggleStatus'])->name('toggle-status');
    });

// Leave Balances (HR Management)
Route::prefix('leave/balances')->name('leave.balances.')
    ->middleware(['auth'])
    ->group(function () {
        // Staff self-service routes - any authenticated user
        Route::get('/dashboard', [LeaveBalanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/my-balances', [LeaveBalanceController::class, 'myBalances'])->name('my-balances');

        // Manager routes - require approve-leave-requests gate
        Route::get('/team', [LeaveBalanceController::class, 'teamBalances'])
            ->middleware('can:approve-leave-requests')->name('team');

        // HR routes - require manage-leave-balances
        Route::middleware(['can:manage-leave-balances'])->group(function () {
            Route::get('/', [LeaveBalanceController::class, 'index'])->name('index');
            Route::get('/{balance}', [LeaveBalanceController::class, 'show'])->name('show');
            Route::post('/{balance}/adjust', [LeaveBalanceController::class, 'storeAdjustment'])->name('adjust');
        });
    });

// Leave Policies (Staff View) - separate from admin policy management
Route::get('/leave/policies/view', [LeaveBalanceController::class, 'policies'])
    ->middleware(['auth'])
    ->name('leave.policies.view');

// Leave Settings - requires manage-leave-settings
Route::prefix('leave/settings')->name('leave.settings.')
    ->middleware(['auth', 'can:manage-leave-settings'])
    ->group(function () {
        Route::get('/', [LeaveSettingsController::class, 'index'])->name('index');
        Route::post('/', [LeaveSettingsController::class, 'update'])->name('update');
        Route::post('/initialize-balances', [LeaveSettingsController::class, 'initializeBalances'])->name('initialize-balances');
    });

// Leave Requests - authenticated users
Route::prefix('leave/requests')->name('leave.requests.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/pending', [LeaveRequestController::class, 'pendingApprovals'])
            ->middleware('can:approve-leave-requests')->name('pending');
        Route::get('/team-history', [LeaveRequestController::class, 'teamHistory'])
            ->middleware('can:approve-leave-requests')->name('team-history');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::post('/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
        Route::post('/calculate-days', [LeaveRequestController::class, 'calculateDays'])->name('calculate-days');
    });

// Leave Calendar - authenticated users
Route::prefix('leave/calendar')->name('leave.calendar.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/personal', [LeaveCalendarController::class, 'personal'])->name('personal');
        Route::get('/personal/events', [LeaveCalendarController::class, 'personalEvents'])->name('personal-events');

        // Team calendar - requires approve-leave-requests gate (managers only)
        Route::middleware(['can:approve-leave-requests'])->group(function () {
            Route::get('/team', [LeaveCalendarController::class, 'team'])->name('team');
            Route::get('/team/events', [LeaveCalendarController::class, 'teamEvents'])->name('team-events');
        });
    });

// Leave Statements - authenticated users
Route::prefix('leave/statements')->name('leave.statements.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [LeaveStatementController::class, 'index'])->name('index');
        Route::get('/download', [LeaveStatementController::class, 'download'])->name('download');
    });

// Leave Reports - HR and Managers
Route::prefix('leave/reports')->name('leave.reports.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/utilization', [LeaveReportController::class, 'utilization'])
            ->name('utilization')
            ->middleware('can:view-leave-reports');

        Route::get('/outstanding', [LeaveReportController::class, 'outstanding'])
            ->name('outstanding')
            ->middleware('can:view-leave-reports');

        Route::get('/carryover', [LeaveReportController::class, 'carryover'])
            ->name('carryover')
            ->middleware('can:view-leave-reports');

        Route::get('/team-summary', [LeaveReportController::class, 'teamSummary'])
            ->name('team-summary')
            ->middleware('can:approve-leave-requests');

        // Export routes
        Route::get('/utilization/export', [LeaveReportController::class, 'exportUtilization'])
            ->name('utilization.export')
            ->middleware('can:view-leave-reports');

        Route::get('/outstanding/export', [LeaveReportController::class, 'exportOutstanding'])
            ->name('outstanding.export')
            ->middleware('can:view-leave-reports');

        Route::get('/carryover/export', [LeaveReportController::class, 'exportCarryover'])
            ->name('carryover.export')
            ->middleware('can:view-leave-reports');

        Route::get('/team-summary/export', [LeaveReportController::class, 'exportTeamSummary'])
            ->name('team-summary.export')
            ->middleware('can:approve-leave-requests');

        Route::get('/personal-history/export', [LeaveReportController::class, 'exportPersonalHistory'])
            ->name('personal-history.export');
    });
