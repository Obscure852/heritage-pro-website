<?php

use App\Http\Controllers\StaffAttendance\AttendanceDashboardController;
use App\Http\Controllers\StaffAttendance\DeviceConfigController;
use App\Http\Controllers\StaffAttendance\ManualRegisterController;
use App\Http\Controllers\StaffAttendance\ReportController;
use App\Http\Controllers\StaffAttendance\SelfServiceClockController;
use App\Http\Controllers\StaffAttendance\StaffAttendanceCodeController;
use App\Http\Controllers\StaffAttendance\StaffAttendanceSettingsController;
use App\Http\Controllers\StaffAttendance\StaffMappingController;
use App\Http\Controllers\StaffAttendance\SyncHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Attendance Routes
|--------------------------------------------------------------------------
|
| Routes for the staff attendance module including device configuration,
| attendance records, and sync management.
|
*/

/*
|--------------------------------------------------------------------------
| Staff Attendance Routes - Authorization Summary
|--------------------------------------------------------------------------
|
| All routes require authentication via 'auth' middleware.
|
| Protected by Gates:
| - /codes (mutating) -> manage-staff-attendance-codes
| - /manual-register -> manage-staff-attendance-register (via controller)
| - /reports -> view-attendance-reports
| - /settings -> manage-staff-attendance-settings
|
| Controller-level authorization:
| - /manager/dashboard -> subordinates()->exists() check
|
| Open to authenticated users:
| - /self-service -> all authenticated staff
| - /devices, /sync-history, /mapping -> admin convention (no formal gate)
|
*/

Route::prefix('staff-attendance')->middleware(['auth', 'throttle:auth'])->group(function () {
    // Device Configuration
    Route::prefix('devices')->group(function () {
        Route::get('/', [DeviceConfigController::class, 'index'])->name('staff-attendance.devices.index');
        Route::get('/create', [DeviceConfigController::class, 'create'])->name('staff-attendance.devices.create');
        Route::post('/', [DeviceConfigController::class, 'store'])->name('staff-attendance.devices.store');
        Route::get('/{device}/edit', [DeviceConfigController::class, 'edit'])->name('staff-attendance.devices.edit');
        Route::put('/{device}', [DeviceConfigController::class, 'update'])->name('staff-attendance.devices.update');
        Route::delete('/{device}', [DeviceConfigController::class, 'destroy'])->name('staff-attendance.devices.destroy');
        Route::post('/{device}/test', [DeviceConfigController::class, 'testConnection'])->name('staff-attendance.devices.test');
        Route::post('/{device}/toggle', [DeviceConfigController::class, 'toggleActive'])->name('staff-attendance.devices.toggle');
    });

    // Sync History
    Route::prefix('sync-history')->group(function () {
        Route::get('/', [SyncHistoryController::class, 'index'])->name('staff-attendance.sync-history.index');
    });

    // Biometric ID Mapping
    Route::prefix('mapping')->group(function () {
        Route::get('/', [StaffMappingController::class, 'index'])->name('staff-attendance.mapping.index');
        Route::post('/', [StaffMappingController::class, 'store'])->name('staff-attendance.mapping.store');
        Route::delete('/{mapping}', [StaffMappingController::class, 'destroy'])->name('staff-attendance.mapping.destroy');
        Route::get('/unmapped-staff', [StaffMappingController::class, 'unmappedStaff'])->name('staff-attendance.mapping.unmapped-staff');
    });

    // Attendance Codes
    Route::prefix('codes')->group(function () {
        Route::get('/', [StaffAttendanceCodeController::class, 'index'])->name('staff-attendance.codes.index');
        Route::get('/list', [StaffAttendanceCodeController::class, 'list'])->name('staff-attendance.codes.list');
        Route::post('/', [StaffAttendanceCodeController::class, 'store'])->name('staff-attendance.codes.store');
        Route::put('/{code}', [StaffAttendanceCodeController::class, 'update'])->name('staff-attendance.codes.update');
        Route::post('/{code}/toggle', [StaffAttendanceCodeController::class, 'toggleActive'])->name('staff-attendance.codes.toggle');
        Route::delete('/{code}', [StaffAttendanceCodeController::class, 'destroy'])->name('staff-attendance.codes.destroy');
    });

    // Manual Attendance Register
    Route::prefix('manual-register')->group(function () {
        Route::get('/', [ManualRegisterController::class, 'index'])->name('staff-attendance.manual-register.index');
        Route::post('/update', [ManualRegisterController::class, 'batchUpdate'])->name('staff-attendance.manual-register.update');
    });

    // Self-Service Clock In/Out
    Route::prefix('self-service')->group(function () {
        Route::get('/', [SelfServiceClockController::class, 'index'])
            ->name('staff-attendance.self-service.index');
        Route::get('/status', [SelfServiceClockController::class, 'status'])
            ->name('staff-attendance.self-service.status');
        Route::post('/clock-in', [SelfServiceClockController::class, 'clockIn'])
            ->name('staff-attendance.self-service.clock-in');
        Route::post('/clock-out', [SelfServiceClockController::class, 'clockOut'])
            ->name('staff-attendance.self-service.clock-out');
    });

    // Manager Dashboard
    Route::prefix('manager')->group(function () {
        Route::get('/dashboard', [AttendanceDashboardController::class, 'index'])
            ->name('staff-attendance.manager.dashboard');
    });

    // Settings
    Route::prefix('settings')->middleware('can:manage-staff-attendance-settings')->group(function () {
        Route::get('/', [StaffAttendanceSettingsController::class, 'index'])
            ->name('staff-attendance.settings.index');
        Route::post('/', [StaffAttendanceSettingsController::class, 'update'])
            ->name('staff-attendance.settings.update');
        Route::post('/trigger-sync', [StaffAttendanceSettingsController::class, 'triggerSync'])
            ->name('staff-attendance.settings.trigger-sync');
    });

    // Attendance Reports
    Route::prefix('reports')->middleware('can:view-attendance-reports')->group(function () {
        // Redirect reports index to daily report
        Route::get('/', function () {
            return redirect()->route('staff-attendance.reports.daily');
        })->name('staff-attendance.reports.index');

        Route::get('/daily', [ReportController::class, 'daily'])->name('staff-attendance.reports.daily');
        Route::get('/daily/export', [ReportController::class, 'exportDaily'])->name('staff-attendance.reports.daily.export');

        Route::get('/monthly', [ReportController::class, 'monthly'])->name('staff-attendance.reports.monthly');
        Route::get('/monthly/export', [ReportController::class, 'exportMonthly'])->name('staff-attendance.reports.monthly.export');

        Route::get('/department', [ReportController::class, 'department'])->name('staff-attendance.reports.department');
        Route::get('/department/export', [ReportController::class, 'exportDepartment'])->name('staff-attendance.reports.department.export');

        Route::get('/punctuality', [ReportController::class, 'punctuality'])->name('staff-attendance.reports.punctuality');
        Route::get('/punctuality/export', [ReportController::class, 'exportPunctuality'])->name('staff-attendance.reports.punctuality.export');

        Route::get('/absenteeism', [ReportController::class, 'absenteeism'])->name('staff-attendance.reports.absenteeism');
        Route::get('/absenteeism/export', [ReportController::class, 'exportAbsenteeism'])->name('staff-attendance.reports.absenteeism.export');

        Route::get('/hours-worked', [ReportController::class, 'hoursWorked'])->name('staff-attendance.reports.hours-worked');
        Route::get('/hours-worked/export', [ReportController::class, 'exportHoursWorked'])->name('staff-attendance.reports.hours-worked.export');
    });
});
