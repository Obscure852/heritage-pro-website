<?php
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCodeController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-attendance'])->group(function () {
  Route::post('/store', [AttendanceController::class, 'store'])->name('attendance.store');
  Route::post('/class', [AttendanceController::class, 'storeSelectedClass']);

  Route::get('/holiday/list/{termId}', [AttendanceController::class, 'holidayList'])->name('attendance.holiday-list');
  Route::get('/holiday/list-by-year/{year}', [AttendanceController::class, 'holidayListByYear'])->name('attendance.holiday-list-by-year');
  Route::put('/holidays/update/{id}', [AttendanceController::class, 'updateHoliday'])->name('holidays.update-holiday');
  Route::delete('/holidays/{id}', [AttendanceController::class, 'deleteHoliday'])->name('holidays.delete-holiday');
  Route::get('/holidays', [AttendanceController::class, 'holidays'])->name('attendance.holidays');
  Route::post('/new', [AttendanceController::class, 'addDays'])->name('attendance.add-day');

  Route::get('/class/{classId}/{termId}/{weekStart?}', [AttendanceController::class, 'showClassList'])->name('attendance.class-list');
  Route::post('/navigate-week', [AttendanceController::class, 'navigateWeek'])->name('attendance.navigate-week');
  Route::get('/grades/classes', [AttendanceController::class, 'getGradesAndKlassesForTermWithTeacher'])->name('attendance.get-grades-list');

  Route::get('/summary/{classId}', [AttendanceController::class, 'generateAttendanceSummary'])->name('attendance.class-summary');

  Route::post('/manual-entry', [AttendanceController::class, 'storeManualEntry'])->name('attendance.manual-entry');
  Route::get('/manual-entry/{studentId}/{studentIds}/{index}', [AttendanceController::class, 'showManualEntryForm'])->name('attendance.get-manual-entry-form');

  Route::get('/class/{classId}', [AttendanceController::class, 'classAttendanceReport'])->name('attendance.class-attendance-report');
  Route::get('/class/term/{classId}', [AttendanceController::class, 'showMonthlyAttendance'])->name('attendance.class-termly-attendance-report');
  Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');

  // Attendance Settings (Codes)
  Route::get('/settings', [AttendanceCodeController::class, 'index'])->name('attendance.settings');
  Route::get('/settings/codes/create', [AttendanceCodeController::class, 'create'])->name('attendance.codes.create');
  Route::post('/settings/codes', [AttendanceCodeController::class, 'store'])->name('attendance.codes.store');
  Route::put('/settings/codes/{id}', [AttendanceCodeController::class, 'update'])->name('attendance.codes.update');
  Route::delete('/settings/codes/{id}', [AttendanceCodeController::class, 'destroy'])->name('attendance.codes.destroy');
  Route::post('/settings/codes/order', [AttendanceCodeController::class, 'updateOrder'])->name('attendance.codes.update-order');
  Route::post('/settings/codes/{id}/toggle', [AttendanceCodeController::class, 'toggleActive'])->name('attendance.codes.toggle');
});
