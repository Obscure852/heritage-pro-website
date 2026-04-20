<?php

use App\Http\Controllers\Activities\ActivityController;
use App\Http\Controllers\Activities\ActivityAttendanceController;
use App\Http\Controllers\Activities\ActivityEligibilityController;
use App\Http\Controllers\Activities\ActivityEventController;
use App\Http\Controllers\Activities\ActivityFeeController;
use App\Http\Controllers\Activities\ActivityResultController;
use App\Http\Controllers\Activities\ActivityRosterController;
use App\Http\Controllers\Activities\ActivityReportController;
use App\Http\Controllers\Activities\ActivityScheduleController;
use App\Http\Controllers\Activities\ActivitySessionController;
use App\Http\Controllers\Activities\ActivitySettingsController;
use App\Http\Controllers\Activities\ActivityStaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('activities')
    ->middleware(['auth', 'throttle:auth', 'block.non.african'])
    ->group(function () {
        Route::middleware('can:access-activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('activities.index');
            Route::get('/reports', [ActivityReportController::class, 'index'])->name('activities.reports.index');
            Route::get('/reports/export', [ActivityReportController::class, 'export'])->name('activities.reports.export');
        });

        Route::middleware('can:manage-activities')->group(function () {
            Route::get('/create', [ActivityController::class, 'create'])->name('activities.create');
            Route::post('/', [ActivityController::class, 'store'])->name('activities.store');
            Route::get('/{activity}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
            Route::put('/{activity}', [ActivityController::class, 'update'])->name('activities.update');
            Route::get('/{activity}/staff', [ActivityStaffController::class, 'index'])->name('activities.staff.index');
            Route::post('/{activity}/staff', [ActivityStaffController::class, 'store'])->name('activities.staff.store');
            Route::delete('/{activity}/staff/{assignment}', [ActivityStaffController::class, 'destroy'])->name('activities.staff.destroy');
            Route::get('/{activity}/eligibility', [ActivityEligibilityController::class, 'edit'])->name('activities.eligibility.edit');
            Route::put('/{activity}/eligibility', [ActivityEligibilityController::class, 'update'])->name('activities.eligibility.update');
            Route::post('/{activity}/activate', [ActivityController::class, 'activate'])->name('activities.activate');
            Route::post('/{activity}/pause', [ActivityController::class, 'pause'])->name('activities.pause');
            Route::post('/{activity}/close', [ActivityController::class, 'close'])->name('activities.close');
            Route::post('/{activity}/archive', [ActivityController::class, 'archive'])->name('activities.archive');
            Route::post('/{activity}/fees', [ActivityFeeController::class, 'store'])->name('activities.fees.store');
            Route::post('/{activity}/fees/{charge}/post', [ActivityFeeController::class, 'post'])->name('activities.fees.post');
        });

        Route::middleware('can:manage-activity-settings')->group(function () {
            Route::get('/settings', [ActivitySettingsController::class, 'index'])->name('activities.settings.index');
            Route::post('/settings', [ActivitySettingsController::class, 'update'])->name('activities.settings.update');
        });

        Route::middleware('can:manage-activity-rosters')->group(function () {
            Route::post('/{activity}/roster', [ActivityRosterController::class, 'store'])->name('activities.roster.store');
            Route::post('/{activity}/roster/bulk', [ActivityRosterController::class, 'bulkStore'])->name('activities.roster.bulk-store');
            Route::patch('/{activity}/roster/{enrollment}', [ActivityRosterController::class, 'update'])->name('activities.roster.update');
        });

        Route::middleware('can:access-activities')->group(function () {
            Route::get('/{activity}/schedules', [ActivityScheduleController::class, 'index'])->name('activities.schedules.index');
            Route::post('/{activity}/schedules', [ActivityScheduleController::class, 'store'])->name('activities.schedules.store');
            Route::patch('/{activity}/schedules/{schedule}', [ActivityScheduleController::class, 'update'])->name('activities.schedules.update');
            Route::post('/{activity}/schedules/{schedule}/generate', [ActivityScheduleController::class, 'generate'])->name('activities.schedules.generate');
            Route::post('/{activity}/sessions', [ActivitySessionController::class, 'store'])->name('activities.sessions.store');
            Route::patch('/{activity}/sessions/{session}', [ActivitySessionController::class, 'update'])->name('activities.sessions.update');
            Route::get('/{activity}/sessions/{session}/attendance', [ActivityAttendanceController::class, 'edit'])->name('activities.attendance.edit');
            Route::put('/{activity}/sessions/{session}/attendance', [ActivityAttendanceController::class, 'update'])->name('activities.attendance.update');
            Route::post('/{activity}/sessions/{session}/attendance/finalize', [ActivityAttendanceController::class, 'finalize'])->name('activities.attendance.finalize');
            Route::post('/{activity}/sessions/{session}/attendance/reopen', [ActivityAttendanceController::class, 'reopen'])->name('activities.attendance.reopen');
            Route::get('/{activity}/events', [ActivityEventController::class, 'index'])->name('activities.events.index');
            Route::post('/{activity}/events', [ActivityEventController::class, 'store'])->name('activities.events.store');
            Route::patch('/{activity}/events/{event}', [ActivityEventController::class, 'update'])->name('activities.events.update');
            Route::get('/{activity}/events/{event}/results', [ActivityResultController::class, 'edit'])->name('activities.results.edit');
            Route::put('/{activity}/events/{event}/results', [ActivityResultController::class, 'update'])->name('activities.results.update');
            Route::get('/{activity}/fees', [ActivityFeeController::class, 'index'])->name('activities.fees.index');
            Route::get('/{activity}/roster', [ActivityRosterController::class, 'index'])->name('activities.roster.index');
            Route::get('/{activity}/roster/export', [ActivityRosterController::class, 'export'])->name('activities.roster.export');
            Route::get('/{activity}', [ActivityController::class, 'show'])->name('activities.show');
        });
    });
