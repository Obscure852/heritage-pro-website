<?php
use App\Http\Controllers\SchoolSetupController;
use App\Http\Controllers\ModuleSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('setup')->middleware(['auth', 'can:access-setup'])->withoutMiddleware(['csrf'])->group(function () {

  Route::get('/create-storage-symlink', [SchoolSetupController::class, 'createStorageSymlink'])->name('setup.storage-symlink');
  Route::get('/clear-config-cache', [SchoolSetupController::class, 'clearConfigCache'])->name('setup.config-cache');
  Route::get('/clear-caches', [SchoolSetupController::class, 'clearCaches'])->name('setup.clear-caches');
  Route::get('/logs', [SchoolSetupController::class, 'clearLogs'])->name('setup.clear-logs');

  Route::post('/term/udpate', [SchoolSetupController::class, 'updateTermDates'])->name('terms.update');
  Route::get('/terms/{year}', [SchoolSetupController::class, 'getTermsByYear'])->name('terms.byYear');
  Route::post('/close/term/{termId}', [SchoolSetupController::class, 'closeTerm'])->name('setup.close-term');
  Route::get('/rollover-error', [SchoolSetupController::class, 'rolloverErrorPage'])->name('rollover.error');

  Route::get('/backup', [SchoolSetupController::class, 'createBackup'])->name('setup.create-backup');
  Route::get('/download/backup/{file}', [SchoolSetupController::class, 'downloadBackup'])->name('setup.download-backup');

  Route::post('/data/students', [SchoolSetupController::class, 'importStudents'])->name('setup.import-students');
  Route::post('/data/sponsors', [SchoolSetupController::class, 'importSponsors'])->name('setup.import-sponsors');
  Route::post('/data/admissions', [SchoolSetupController::class, 'importAdmissions'])->name('setup.import-admissions');
  Route::post('/data/users', [SchoolSetupController::class, 'importStaff'])->name('setup.import-staff');
  Route::get('/data/import/{filename}', [SchoolSetupController::class, 'downloadImportFile'])->name('setup.download-import');
  Route::get('/data/importing', [SchoolSetupController::class, 'dataImporting'])->name('setup.data-importing');

  Route::get('/school', [SchoolSetupController::class, 'index'])->name('setup.school-setup');
  Route::post('/upload/logo', [SchoolSetupController::class, 'uploadLogo'])->name('setup.upload-logo');
  Route::post('/upload/login-image', [SchoolSetupController::class, 'uploadLoginImage'])->name('setup.upload-login-image');
  Route::post('/toggle/login-image', [SchoolSetupController::class, 'toggleLoginImage'])->name('setup.toggle-login-image');
  Route::post('/regenerate-school-id', [SchoolSetupController::class, 'regenerateSchoolId'])->name('setup.regenerate-school-id');
  Route::delete('/{userId}/role/{roleId}/remove', [SchoolSetupController::class, 'removeRole'])->name('setup.staff-role-deallocation');

  Route::post('/sms/settings', [SchoolSetupController::class, 'linkSmsUpdate'])->name('setup.link-sms-update');
  Route::post('/email/settings', [SchoolSetupController::class, 'updateSettings'])->name('setup.email-settings');
  Route::post('/notification/settings', [SchoolSetupController::class, 'updateNotificationSettings'])->name('setup.update-notification-settings');
  Route::get('/communications', [SchoolSetupController::class, 'settingsIndex'])->name('setup.communications-setup');

  Route::get('/grades/management', [SchoolSetupController::class, 'gradesSetup'])->name('setup.grades-setup');
  Route::get('/grades/management/{gradeId}', [SchoolSetupController::class, 'gradesView'])->name('setup.grades-view');
  Route::post('/grade/update/{gradeId}', [SchoolSetupController::class, 'updateGrade'])->name('setup.grade-update');

  Route::post('/check-grades', [SchoolSetupController::class, 'checkGrades'])->name('setup.check-grades');
  Route::post('/year/rollover/preview', [SchoolSetupController::class, 'previewYearRollover'])->name('setup.preview-year-rollover');
  Route::post('/term/rollover/preview', [SchoolSetupController::class, 'previewTermRollover'])->name('setup.preview-term-rollover');
  Route::post('/term/rollover', [SchoolSetupController::class, 'termRollover'])->name('setup.term-rollover');
  Route::post('/year/rollover', [SchoolSetupController::class, 'yearRollover'])->name('setup.year-rollover');
  Route::post('/year/reverse/rollover/{historyId}', [SchoolSetupController::class, 'reverseYearRollover'])->name('setup.reverse-year-rollover');
  Route::post('/term/reverse/rollover/{historyId}', [SchoolSetupController::class, 'reverseTermRollover'])->name('setup.reverse-term-rollover');

  Route::post('/store', [SchoolSetupController::class, 'store'])->name('setup.store');
  Route::get('/', [SchoolSetupController::class, 'index'])->name('setup.index');

  // Module Visibility Settings
  Route::get('/modules', [ModuleSettingsController::class, 'index'])->name('setup.module-settings');
  Route::post('/modules', [ModuleSettingsController::class, 'update'])->name('setup.module-settings-update');
});
