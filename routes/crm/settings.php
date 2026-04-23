<?php

use App\Http\Controllers\Crm\AttendanceSettingController;
use App\Http\Controllers\Crm\CommercialSettingController;
use App\Http\Controllers\Crm\ImportController;
use App\Http\Controllers\Crm\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::get('/settings/sales-stages', [SettingController::class, 'salesStages'])->name('settings.sales-stages');
Route::get('/settings/sales-stages/create', [SettingController::class, 'createSalesStage'])->name('settings.sales-stages.create');
Route::post('/settings/sales-stages', [SettingController::class, 'storeSalesStage'])->name('settings.sales-stages.store');
Route::get('/settings/sales-stages/{salesStage}/edit', [SettingController::class, 'editSalesStage'])->name('settings.sales-stages.edit');
Route::patch('/settings/sales-stages/{salesStage}', [SettingController::class, 'updateSalesStage'])->name('settings.sales-stages.update');
Route::delete('/settings/sales-stages/{salesStage}', [SettingController::class, 'destroySalesStage'])->name('settings.sales-stages.destroy');
Route::get('/settings/commercial', [CommercialSettingController::class, 'index'])->name('settings.commercial');
Route::patch('/settings/commercial', [CommercialSettingController::class, 'update'])->name('settings.commercial.update');
Route::get('/settings/company-information', [CommercialSettingController::class, 'companyInformation'])->name('settings.company-information');
Route::patch('/settings/company-information', [CommercialSettingController::class, 'updateCompanyInformation'])->name('settings.company-information.update');
Route::get('/settings/branding', [CommercialSettingController::class, 'branding'])->name('settings.branding');
Route::patch('/settings/branding', [CommercialSettingController::class, 'updateBranding'])->name('settings.branding.update');
Route::post('/settings/commercial/currencies', [CommercialSettingController::class, 'storeCurrency'])->name('settings.commercial.currencies.store');
Route::get('/settings/commercial/currencies/{currency}/edit', [CommercialSettingController::class, 'editCurrency'])->name('settings.commercial.edit-currency');
Route::patch('/settings/commercial/currencies/{currency}', [CommercialSettingController::class, 'updateCurrency'])->name('settings.commercial.currencies.update');
Route::get('/settings/imports', [ImportController::class, 'index'])->name('settings.imports');
Route::get('/settings/imports/users', [ImportController::class, 'users'])->name('settings.imports.users');
Route::get('/settings/imports/leads', [ImportController::class, 'leads'])->name('settings.imports.leads');
Route::get('/settings/imports/contacts', [ImportController::class, 'contacts'])->name('settings.imports.contacts');
Route::get('/settings/imports/templates/{entity}', [ImportController::class, 'downloadTemplate'])->name('settings.imports.templates.download');
Route::post('/settings/imports/preview', [ImportController::class, 'preview'])->name('settings.imports.preview');
Route::post('/settings/imports/{crmImportRun}/confirm', [ImportController::class, 'confirm'])->name('settings.imports.confirm');
Route::get('/settings/imports/runs/{crmImportRun}', [ImportController::class, 'showRun'])->name('settings.imports.runs.show');
Route::get('/settings/imports/runs/{crmImportRun}/failures', [ImportController::class, 'downloadFailures'])->name('settings.imports.runs.failures.download');
Route::get('/settings/imports/runs/{crmImportRun}/passwords', [ImportController::class, 'downloadPasswords'])->name('settings.imports.runs.passwords.download');

// Attendance settings (single page with modal forms)
Route::get('/settings/attendance', [AttendanceSettingController::class, 'index'])->name('settings.attendance.index');
Route::patch('/settings/attendance/widget', [AttendanceSettingController::class, 'updateWidgetSettings'])->name('settings.attendance.widget.update');
Route::post('/settings/attendance/codes', [AttendanceSettingController::class, 'storeCode'])->name('settings.attendance.codes.store');
Route::put('/settings/attendance/codes/{attendanceCode}', [AttendanceSettingController::class, 'updateCode'])->name('settings.attendance.codes.update');
Route::delete('/settings/attendance/codes/{attendanceCode}', [AttendanceSettingController::class, 'destroyCode'])->name('settings.attendance.codes.destroy');
Route::post('/settings/attendance/shifts', [AttendanceSettingController::class, 'storeShift'])->name('settings.attendance.shifts.store');
Route::put('/settings/attendance/shifts/{shift}', [AttendanceSettingController::class, 'updateShift'])->name('settings.attendance.shifts.update');
Route::delete('/settings/attendance/shifts/{shift}', [AttendanceSettingController::class, 'destroyShift'])->name('settings.attendance.shifts.destroy');
Route::post('/settings/attendance/shifts/bulk-assign', [AttendanceSettingController::class, 'bulkAssignShift'])->name('settings.attendance.shifts.bulk-assign');
Route::post('/settings/attendance/holidays', [AttendanceSettingController::class, 'storeHoliday'])->name('settings.attendance.holidays.store');
Route::put('/settings/attendance/holidays/{holiday}', [AttendanceSettingController::class, 'updateHoliday'])->name('settings.attendance.holidays.update');
Route::delete('/settings/attendance/holidays/{holiday}', [AttendanceSettingController::class, 'destroyHoliday'])->name('settings.attendance.holidays.destroy');
Route::post('/settings/attendance/devices', [AttendanceSettingController::class, 'storeDevice'])->name('settings.attendance.devices.store');
Route::put('/settings/attendance/devices/{device}', [AttendanceSettingController::class, 'updateDevice'])->name('settings.attendance.devices.update');
Route::delete('/settings/attendance/devices/{device}', [AttendanceSettingController::class, 'destroyDevice'])->name('settings.attendance.devices.destroy');
Route::post('/settings/attendance/devices/{device}/regenerate-token', [AttendanceSettingController::class, 'regenerateDeviceToken'])->name('settings.attendance.devices.regenerate-token');
