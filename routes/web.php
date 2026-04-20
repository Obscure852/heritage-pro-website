<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\IdleSessionController;
use App\Http\Controllers\Api\ExternalAuthController;

Route::get('/admin/migrate', [AdminController::class, 'migrate'])->middleware('verify.migration.auth');

Route::get('/', function() {
  return redirect()->route('login');
});

Route::get('/auth/auto-login',[ExternalAuthController::class, 'autoLogin'])->name('auth.auto-login');
Auth::routes();
Route::middleware('auth')->post('/auth/activity', [IdleSessionController::class, 'touchWeb'])->name('auth.activity');

include __DIR__.'/dashboard/dashboard.php';
include __DIR__.'/students/students.php';
include __DIR__.'/admissions/admissions.php';
include __DIR__.'/staff/users.php';
include __DIR__.'/online/application.php';
include __DIR__.'/sponsor-auth/sponsor.php';
include __DIR__.'/student-auth/student.php';
include __DIR__.'/sponsors/sponsors.php';
include __DIR__.'/attendance/attendance.php';
include __DIR__.'/academic/academic.php';
include __DIR__.'/subjects/subjects.php';
include __DIR__.'/optional/optional.php';
include __DIR__.'/contacts.php';
include __DIR__.'/activities/activities.php';

include __DIR__.'/assessment/tests.php';
include __DIR__.'/assessment/assessment.php';
include __DIR__.'/assessment/senior-assessment.php';
include __DIR__.'/assessment/junior-assessment.php';
include __DIR__.'/assessment/primary-assessment.php';
include __DIR__.'/assessment/reception-assessment.php';

include __DIR__.'/finals/context.php';
include __DIR__.'/finals/senior.php';
include __DIR__.'/finals/finals.php';
include __DIR__.'/finals/classes.php';
include __DIR__.'/finals/houses.php';
include __DIR__.'/finals/core.php';
include __DIR__.'/finals/external_exam_analysis.php';
include __DIR__.'/finals/external_exam_import.php';
include __DIR__.'/finals/optionals.php';
include __DIR__.'/finals/subjects.php';
include __DIR__.'/houses/houses.php';
include __DIR__.'/invigilation/invigilation.php';
include __DIR__.'/assets/assets.php';
include __DIR__.'/assets/maintenance.php';
include __DIR__.'/assets/disposals.php';
include __DIR__.'/assets/assignments.php';
include __DIR__.'/assets/audits.php';
include __DIR__.'/welfare.php';
include __DIR__.'/notifications/notifications.php';
include __DIR__.'/settings/setup.php';
include __DIR__.'/logs/logs.php';
include __DIR__.'/license/licensing.php';
include __DIR__.'/lms/lms.php';
include __DIR__.'/fees/fees.php';
include __DIR__.'/leave.php';
include __DIR__.'/staff-attendance.php';
include __DIR__.'/library.php';
include __DIR__.'/timetable.php';
include __DIR__.'/documents/documents.php';
include __DIR__.'/schemes/schemes.php';
include __DIR__.'/staff/pdp.php';
