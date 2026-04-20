<?php
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('subjects')->middleware(['auth', 'throttle:auth', 'block.non.african','can:manage-academic'])->group(function () {
  Route::post('/selected/subjects', [SubjectController::class, 'storeSelectedSubjectGrade'])->name('subject.store-selected-subject');
  Route::post('/update/{id}', [SubjectController::class, 'updateGradeSubject'])->name('subject.update-subject');

  Route::get('/master/new', [SubjectController::class, 'storeMasterSubject'])->name('subjects.add-master-list');
  Route::post('/master/add', [SubjectController::class, 'addSubject'])->name('subjects.add-subject');
  Route::post('/master/update/{subjectId}', [SubjectController::class, 'updateMasterSubject'])->name('subjects.update-master-subject');
  Route::get('/master/edit/{subjectId}', [SubjectController::class, 'editMasterSubject'])->name('subjects.edit-master-subject');

  Route::post('/grading/copy/{fromSubjectId}', [SubjectController::class, 'copyGradingScale'])->name('subjects.copy-grading-scale');
  Route::post('/grading/new', [SubjectController::class, 'saveGradingScale'])->name('subjects.save-grading-scale');
  Route::post('/grading/subject/{id}', [SubjectController::class, 'updateGradingScale'])->name('subjects.update-grading-scale');
  Route::get('/grading/edit/{id}', [SubjectController::class, 'editGradingScale'])->name('subjects.edit-grading-scale');
  Route::get('/grading/{id}', [SubjectController::class, 'getGradingScale'])->name('subjects.grading-scale');

  Route::get('/syllabus-preview/{gradeSubject}', [SubjectController::class, 'syllabusPreview'])->name('subjects.syllabus-preview');
  Route::get('/list/{gradeId}', [SubjectController::class, 'subjectByGrade'])->name('subjects.subject-by-grade');
  Route::get('/delete/{id}', [SubjectController::class, 'deleteSubject'])->name('subject.delete');
  Route::get('/edit/{id}', [SubjectController::class, 'show'])->name('subject.edit-subject');

  Route::get('/view/component/{subjectId}', [SubjectController::class, 'viewComponents'])->name('subject.view-component');
  Route::get('/create/component/{subjectId}', [SubjectController::class, 'createComponent'])->name('subject.create-component');
  Route::post('/add-component', [SubjectController::class, 'addComponent'])->name('subject.add-component');
  Route::get('/edit-component/{subjectId}/{componentId}', [SubjectController::class, 'editComponent'])->name('subject.edit-component');
  Route::get('/delete/component/{componetId}', [SubjectController::class, 'deleteSubjectComponent'])->name('subject.delete-component');
  Route::post('/update-component/{componentId}', [SubjectController::class, 'updateComponent'])->name('subject.update-component');

  Route::get('/create/grade/option', [SubjectController::class, 'createGradeOption'])->name('subject.create-grade-option');
  Route::post('/add/grade/option', [SubjectController::class, 'addGradeOption'])->name('subject.add-grade-option');

  Route::get('/link/grade/option/{subjectId}', [SubjectController::class, 'linkToSubject'])->name('subject.link-grade-option');
  Route::post('/add/link/option', [SubjectController::class, 'addLinkToSubject'])->name('subject.link-to-subject-option');

  Route::post('/unlink-option-set', [SubjectController::class, 'unlinkOptionSet'])->name('subject.unlink-option-set');
  Route::get('/edit/gradeoption/{id}', [SubjectController::class, 'editGradeOption'])->name('subject.edit-grade-option');
  Route::post('/update/grade/option', [SubjectController::class, 'updateGradeOptions'])->name('subject.update-grade-option');

  Route::get('/create', [SubjectController::class, 'create'])->name('subjects.create');
  Route::post('/store', [SubjectController::class, 'store'])->name('subjects.store');
  Route::get('/show/{id}', [SubjectController::class, 'show'])->name('subjects.show');

  Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
});
