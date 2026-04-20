<?php
use App\Http\Controllers\OptionalSubjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('optional')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-optional'])->group(function () {

  Route::get('/grades', [OptionalSubjectController::class, 'getGradesForTermOptional'])->name('optional.grades-for-term');
  Route::post('/class/list', [OptionalSubjectController::class, 'storeOptionalSelectedGrade']);
  Route::get('/class/edit/{id}', [OptionalSubjectController::class, 'editOption'])->name('optional.edit-option');
  Route::post('/class/update/{id}', [OptionalSubjectController::class, 'updateOption'])->name('optional.update-option');

  Route::get('/students/allocated/{id}', [OptionalSubjectController::class, 'allocatedStudents'])->name('optional.allocated-options');
  Route::delete('/student/delete/{classId}/{studentId}', [OptionalSubjectController::class, 'optionDeleteStudent'])->name('optional.student-delete');

  Route::delete('/delete/multiple-students/{id}', [OptionalSubjectController::class, 'deleteMultipleStudents'])->name('optional.delete-multiple-students');
  Route::get('/list/classes-by-grade/{gradeId}', [OptionalSubjectController::class, 'getOptionalSubjectsByGrade'])->name('optional.optional-classes-by-grade');

  Route::post('/allocations/allocate-students/{id}', [OptionalSubjectController::class, 'moveStudents'])->name('optional.move-students');
  Route::post('/move-multiple-students/{class}', [OptionalSubjectController::class, 'moveMultipleStudents'])->name('optional.move-multiple-students');
  Route::get('/allocate/options/{id}', [OptionalSubjectController::class, 'allocateStudents'])->name('optional.allocate-options');
  Route::get('/delete/check/{id}', [OptionalSubjectController::class, 'checkScores'])->name('optional.check-scores');
  Route::get('/delete/{id}', [OptionalSubjectController::class, 'deleteOption'])->name('optional.delete');

  Route::post('/option/store', [OptionalSubjectController::class, 'store'])->name('optional.store');
  Route::get('/subject/new', [OptionalSubjectController::class, 'create'])->name('optional.create-new-option');
  Route::get('/subjects/{termId}/{gradeId}', [OptionalSubjectController::class, 'getOptionsByTermAndGrade'])->name('optional.grades-junior');

  Route::get('/analysis/summary/{gradeId}', [OptionalSubjectController::class, 'optionalSubjectAnalysis'])->name('optional.optional-subjects-summary');
  Route::get('/analysis/grouping/{gradeId}', [OptionalSubjectController::class, 'optionalSubjectGroupedByNameReport'])->name('optional.optional-classes-by-name');
  
  Route::get('/create/list/{gradeId}', [OptionalSubjectController::class, 'getSubjectsByGrade'])->name('optional.grade-subjects-by-grade');
  Route::get('/create/core-subjects/{gradeId}', [OptionalSubjectController::class, 'getCoreSubjectsByGrade'])->name('optional.grade-core-subjects-by-grade');
  
  Route::get('/subjects', [OptionalSubjectController::class, 'index'])->name('optional.index');
});
