<?php

use App\Http\Controllers\Crm\LeaveApprovalController;
use App\Http\Controllers\Crm\LeaveBalanceController;
use App\Http\Controllers\Crm\LeaveController;
use App\Http\Controllers\Crm\LeaveReportController;
use App\Http\Controllers\Crm\LeaveSettingController;
use App\Http\Controllers\Crm\LeaveTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('leave')
    ->name('leave.')
    ->group(function () {
        // Employee routes
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/apply', [LeaveController::class, 'create'])->name('apply');
        Route::post('/apply', [LeaveController::class, 'store'])->name('store');
        Route::get('/history', [LeaveController::class, 'history'])->name('history');
        Route::get('/balances', [LeaveController::class, 'balances'])->name('balances');
        Route::post('/calculate-days', [LeaveController::class, 'calculateDays'])->name('calculate-days');

        // Manager approval routes
        Route::get('/approvals/list', [LeaveApprovalController::class, 'index'])->name('approvals');
        Route::put('/approvals/{leaveRequest}', [LeaveApprovalController::class, 'review'])->name('approvals.review');

        // Team views
        Route::get('/team/calendar', [LeaveController::class, 'teamCalendar'])->name('team-calendar');
        Route::get('/team/balances', [LeaveController::class, 'teamBalances'])->name('team-balances');

        // Admin: Leave types
        Route::resource('types', LeaveTypeController::class)
            ->except(['show'])
            ->parameters(['types' => 'leaveType']);

        // Admin: Settings
        Route::get('/settings/general', [LeaveSettingController::class, 'edit'])->name('settings');
        Route::put('/settings/general', [LeaveSettingController::class, 'update'])->name('settings.update');

        // Admin: Balance management
        Route::get('/settings/balances', [LeaveBalanceController::class, 'index'])->name('balance-management');
        Route::put('/settings/balances/{balance}', [LeaveBalanceController::class, 'adjust'])->name('balance-management.adjust');

        // Admin: Reports
        Route::get('/reports/overview', [LeaveReportController::class, 'index'])->name('reports');

        // Wildcard routes LAST — these catch /{leaveRequest} so must come after all static segments
        Route::get('/{leaveRequest}', [LeaveController::class, 'show'])->name('show');
        Route::put('/{leaveRequest}/cancel', [LeaveController::class, 'cancel'])->name('cancel');
    });
