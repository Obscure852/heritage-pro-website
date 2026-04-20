<?php
use App\Http\Controllers\AdmissionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admissions')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-admissions'])->group(function () {
  Route::middleware('can:manage-admissions')->group(function () {
    Route::get('/settings', [AdmissionsController::class, 'settings'])->name('admissions.settings');
    Route::get('/placement', [AdmissionsController::class, 'placement'])->name('admissions.placement');
    Route::post('/settings/import-senior', [AdmissionsController::class, 'importSeniorAdmissions'])->name('admissions.import-senior');
    Route::post('/settings/placement-criteria', [AdmissionsController::class, 'storePlacementCriteria'])->name('admissions.store-placement-criteria');
    Route::post('/settings/placement-criteria/reset', [AdmissionsController::class, 'resetPlacementCriteria'])->name('admissions.reset-placement-criteria');
    Route::post('/placement/allocate', [AdmissionsController::class, 'allocatePlacementRecommendations'])->name('admissions.allocate-placement');
    Route::post('/placement/update-class-capacity', [AdmissionsController::class, 'updateClassCapacity'])->name('admissions.update-class-capacity');
  });

  Route::post('/academic', [AdmissionsController::class, 'insertOrUpdateAcademics'])->name('admissions.create-admission-academics');
  Route::post('/academic/senior', [AdmissionsController::class, 'insertOrUpdateSeniorAcademics'])->name('admissions.create-senior-admission-academics');
  Route::get('/delete/{id}', [AdmissionsController::class, 'deleteAdmission'])->name('admissions.delete-admission-academics');

  Route::post('/medicals', [AdmissionsController::class, 'insertOrUpdateMedicals'])->name('admissions.create-admission-medicals');
  Route::get('/add/academic/{id}', [AdmissionsController::class, 'addAcademic'])->name('admissions.add-academic');

  Route::get('/new', [AdmissionsController::class, 'create'])->name('admissions.admissions-new');
  Route::post('/create/admission', [AdmissionsController::class, 'store'])->name('admissions.admissions-create');

  Route::post('/update/{id}', [AdmissionsController::class, 'update'])->name('admissions.admissions-update');
  Route::get('/view/{id}', [AdmissionsController::class, 'show'])->name('admissions.admissions-view');

  Route::get('/status/report', [AdmissionsController::class, 'admissionsByStatus'])->name('admissions.status-report');
  Route::get('/names/report', [AdmissionsController::class, 'statusAndNames'])->name('admissions.status-names-report');

  Route::get('/analysis/grade', [AdmissionsController::class, 'getGradeAnalysisByGender'])->name('admissions.analysis-by-grade');
  Route::post('/enroll/{id}', [AdmissionsController::class, 'enrollAdmission'])->name('admissions.enrol-admission');
  Route::get('/analysis/export', [AdmissionsController::class, 'statusAndNamesExport'])->name('admissions.admission-export');
  Route::get('/analysis/export', [AdmissionsController::class, 'statusAndNamesExport'])->name('admissions.admission-export');

  Route::get('/create', [AdmissionsController::class, 'create'])->name('admissions.create');
  Route::post('/store', [AdmissionsController::class, 'store'])->name('admissions.store');

  Route::put('/update/{id}', [AdmissionsController::class, 'update'])->name('admissions.update');
  Route::get('/', [AdmissionsController::class, 'index'])->name('admissions.index');
});
