<?php
use App\Http\Controllers\OnlineApplicationController;
use Illuminate\Support\Facades\Route;

Route::prefix('online/application')->middleware(['throttle:public', 'block.non.african'])->group(function () {
  #http://domain.com/online/application/
  Route::get('/', [OnlineApplicationController::class, 'index'])->name('admissions.online-applications');
  Route::get('/parent/{admissionId}', [OnlineApplicationController::class, 'showParentForm'])->name('admissions.show-parent-online-applications');

  Route::get('/health/{admissionId}', [OnlineApplicationController::class, 'showHealthForm'])->name('admissions.show-health-online-applications');
  Route::get('/academic/{admissionId}', [OnlineApplicationController::class, 'showAcademicForm'])->name('admissions.show-academic-online-applications');

  Route::get('/attachments/{admissionId}', [OnlineApplicationController::class, 'showAttachmentsForm'])->name('admissions.show-attachments-online-applications');

  Route::post('/create', [OnlineApplicationController::class, 'createOnlineAdmissionRecord'])->name('admissions.create-online-application');
  Route::post('/sponsor/create', [OnlineApplicationController::class, 'createOnlineApplicatonParentRecord'])->name('admissions.create-parent-online-application');
  Route::post('/health/create', [OnlineApplicationController::class, 'createStudentHealthRecord'])->name('admissions.create-student-health-online-application');

  Route::post('/academic/create', [OnlineApplicationController::class, 'createStudentAcademicRecord'])->name('admissions.create-student-academic-online-application');
  Route::post('/attachments/create', [OnlineApplicationController::class, 'createStudentAttachmentsRecord'])->name('admissions.create-student-attachments-online-application');
  Route::get('/complete/{admissionId}', [OnlineApplicationController::class, 'onlineApplicationComplete'])->name('admissions.online-application-complete');
});
