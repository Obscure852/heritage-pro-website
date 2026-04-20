<?php
use App\Http\Controllers\KlassController;
use Illuminate\Support\Facades\Route;

Route::prefix('academic')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-academic'])->group(function () {

  Route::get('/class/analysis/{classId}', [KlassController::class, 'classList'])->name('academic.class-list');
  Route::get('/classes/teachers/analysis', [KlassController::class, 'classTeachersAnalysis'])->name('academic.class-teacher-analysis');
  Route::get('/teachers/commitmenets/analysis', [KlassController::class, 'teacherCommitments'])->name('academic.teacher-commitments-analysis');

  Route::post('/class', [KlassController::class, 'storeSelectedClasssGrade'])->name('academic.store-selected-grade');
  Route::post('/subjects/list', [KlassController::class, 'storeSelectedGradeList'])->name('academic.store-selected-class');

  Route::get('/class/lists/{termId}/{gradeId}', [KlassController::class, 'getClassesByTermAndGrade'])->name('academic.class-lists');
  Route::post('/subjects/core', [KlassController::class, 'coreSubjects'])->name('academic.core-subjects');
  Route::get('/teachers/{classId}/{termId}', [KlassController::class, 'academicAllocations'])->name('academic.subjects-teachers');

  Route::get('/configurations', [KlassController::class, 'academicConfigurations'])->name('academic.configurations');
  Route::get('/master/subject/list', [KlassController::class, 'masterSubjectIndex'])->name('subjects.master-list');
  Route::get('/configurations/overall/grading', [KlassController::class, 'getOverallGradingMatrix'])->name('academic.add-overall-grading');

  Route::post('/overall/grading', [KlassController::class, 'storeOverallGradingMatrix'])->name('academic.save-overall-grading');
  Route::get('/overall/grading/edit/{gradeId}', [KlassController::class, 'editOverallGrading'])->name('academic.edit-overall-grading');
  Route::get('/overall/grading/list/{gradeId}', [KlassController::class, 'showNavigation'])->name('academic.overall-grading-list');

  Route::get('/overall/points/edit/{academicYear}', [KlassController::class, 'editOverallPoints'])->name('academic.edit-overall-points');
  Route::post('/overall/points/update', [KlassController::class, 'updateOverallPoints'])->name('academic.update-overall-points');

  Route::delete('/academic/remove-multiple-students/{klassId}', [KlassController::class, 'removeMultipleStudents'])->name('academic.remove-multiple-students');
  Route::post('/academic/move-multiple-students', [KlassController::class, 'moveMultipleStudents'])->name('academic.move-multiple-students');
  
  Route::post('/allocations/allocate-students/{id}', [KlassController::class, 'moveStudents'])->name('academic.move-students');
  Route::get('/allocations/{id}/{termId}', [KlassController::class, 'allocateStudents'])->name('academic.allocate-students');

  Route::post('/subjects/new', [KlassController::class, 'newSubjectKlass'])->name('academic.new-class');
  Route::get('/subjects/list', [KlassController::class, 'klassSubjectList'])->name('academic.show-new');
  Route::get('/subjects/show', [KlassController::class, 'showNewKlassSubject'])->name('academic.show-new-class');

  Route::get('/grades', [KlassController::class, 'getGradesForTerm'])->name('klasses.get-grades-for-term');
  Route::get('/grades/junior', [KlassController::class, 'getGradesForTermJunior'])->name('academic.grades-junior');
  Route::get('/grades/classes', [KlassController::class, 'getGradesAndKlassesForTerm'])->name('klasses.get-grades-list');

  Route::post('/update/class/{id}', [KlassController::class, 'update'])->name('academic.class-edit');
  Route::get('/edit/class/{id}', [KlassController::class, 'edit'])->name('academic.edit');
  Route::get('/delete/class/{klassId}', [KlassController::class, 'deleteKlass'])->name('academic.delete-class');
  Route::get('/create', [KlassController::class, 'create'])->name('academic.create');
  Route::post('/store', [KlassController::class, 'store'])->name('academic.store');
  Route::get('/show/{id}', [KlassController::class, 'show'])->name('academic.show');

  Route::get('/', [KlassController::class, 'index'])->name('academic.index');
});
