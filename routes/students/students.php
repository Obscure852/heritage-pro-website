<?php
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('students')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-students'])->group(function () {

  Route::get('/houses', [StudentController::class, 'studentsHouseReport'])->name('students.house-students');
  Route::get('/without-houses', [StudentController::class, 'studentsWithoutHouses'])->name('students.without-houses');
  Route::post('/allocate-to-house', [StudentController::class, 'allocateStudentToHouse'])->name('students.allocate-to-house');
  Route::post('/allocate-all-to-house', [StudentController::class, 'allocateAllStudentsToHouse'])->name('students.allocate-all-to-house');
  Route::get('/search-names', [StudentController::class, 'searchNames'])->name('students.search-student-names');
  Route::get('/edit/behaviour/{studentId}/{id}', [StudentController::class, 'editStudentBehaviour'])->name('students.edit-student-behaviour');
  Route::get('/remove/behaviour/{id}', [StudentController::class, 'removeStudentBehaviour'])->name('students.remove-student-behaviour');
  Route::post('/update/student/behaviour/{id}', [StudentController::class, 'updateStudentBehaviour'])->name('students.update-student-behaviour');
  Route::get('/behaviour/{id}', [StudentController::class, 'studentBehaviour'])->name('students.add-student-behaviour');
  
  Route::post('/medicals', [StudentController::class, 'insertOrUpdateStudentMedicals'])->name('students.create-student-medicals');
  Route::post('/departures', [StudentController::class, 'storeOrUpdateDepartures'])->name('students.create-student-departures');
  
  Route::get('/settings', [StudentController::class, 'getStudentsSettings'])->name('students.students-settings');
  Route::get('/textbooks', [StudentController::class, 'getCurriculumMaterials'])->name('students.curriculum-materials');
  Route::get('/textbooks/clearance/{id}', [StudentController::class, 'getClearanceForm'])->name('students.clearance-form');
  
  Route::delete('/authors/delete/{id}', [StudentController::class, 'deleteAuthor'])->name('students.delete-author');
  Route::get('/books/new', [StudentController::class, 'addBook'])->name('students.add-book');
  Route::get('/books/edit/{id}', [StudentController::class, 'editBook'])->name('students.edit-book');
  Route::delete('/book/delete/{id}', [StudentController::class, 'deleteBook'])->name('students.delete-book');
  
  Route::post('/publisher/save', [StudentController::class, 'addPublisher'])->name('students.store-publisher');
  Route::put('/update/publisher/{id}', [StudentController::class, 'updatePublisher'])->name('students.update-publisher');
  
  Route::delete('/publisher/delete/{id}', [StudentController::class, 'deletePublisher'])->name('students.delete-publisher');
  Route::post('/books/save', [StudentController::class, 'storeBook'])->name('students.store-book');
  Route::put('/update/books/{id}', [StudentController::class, 'updateBook'])->name('students.update-book');
  
  Route::post('/create/author', [StudentController::class, 'createAuthor'])->name('students.create-author');
  Route::match(['PUT', 'POST'],'/update/author/{id}', [StudentController::class, 'updateAuthor'])->name('students.update-author');
  Route::post('/books/import', [StudentController::class, 'importBooks'])->name('students.import-books');
  
  Route::post('/filters/store', [StudentController::class, 'saveStudentFilter'])->name('students.store-students-settings');
  Route::post('/types/store', [StudentController::class, 'saveStudentType'])->name('students.store-students-type');
  
  Route::put('/update/filter/{filter}', [StudentController::class, 'updateStudentFilter'])->name('students.update-student-filter');
  Route::put('/update/type/{id}', [StudentController::class, 'updateStudentType'])->name('students.update-student-type');
  
  Route::delete('/filters/{id}', [StudentController::class, 'destroyStudentFilter'])->name('students.destroy-student-filter');
  Route::delete('/types/{id}', [StudentController::class, 'destroyStudentType'])->name('students.destroy-student-type');
  
  Route::post('/new/behaviour', [StudentController::class, 'addStudentBehaviour'])->name('students.store-student-behaviour');
  Route::get('/term/data', [StudentController::class, 'getTermData'])->name('students.student-get-data');
  
  Route::post('/update/{id}', [StudentController::class, 'update'])->name('students.update');
  Route::get('/create', [StudentController::class, 'create'])->name('students.create');
  Route::post('/store', [StudentController::class, 'store'])->name('students.store');
  Route::get('/show/{id}', [StudentController::class, 'show'])->name('students.show');
  Route::get('/progress-report/{id}/export', [StudentController::class, 'exportProgressReport'])->name('students.export-progress-report');

  Route::get('/analysis/export/{termId}/{year}', [StudentController::class, 'studentsExport']);
  Route::get('/analysis/classes/lists/', [StudentController::class, 'getKlassesWithStats'])->name('students.klasses-with-stats');
  Route::get('/analysis/classes/{termId}/{year}', [StudentController::class, 'getKlassesWithStudentCounts']);
  Route::get('/analysis/list/', [StudentController::class, 'getStudentListAnalysis'])->name('students.analysis-term');
  Route::get('/analysis/statistics/', [StudentController::class, 'getStudentStatistics'])->name('students.students-statistics');
  Route::get('/analysis/boarding/', [StudentController::class, 'getBoardingAnalysis'])->name('students.boarding-analysis');
  Route::get('/analysis/boarding/export', [StudentController::class, 'getBoardingAnalysisExport'])->name('students.boarding-analysis-export');
  Route::get('/analysis/statistics/types/', [StudentController::class, 'getStudentTypesStatistics'])->name('students.students-statistics-type');
  Route::get('/analysis/statistics/filters/', [StudentController::class, 'getFilteredStudentsReport'])->name('students.students-statistics-filter');
  
  Route::get('/analysis/leaving/', [StudentController::class, 'getDeparturesReport'])->name('students.students-leaving-analysis-year');
  Route::get('/analysis/textbook/allocations', [StudentController::class, 'getBookAllocationsReport'])->name('students.students-book-allocations');
  
  Route::get('/analysis/textbook/status', [StudentController::class, 'getBooksWithCopiesReport'])->name('students.students-textbooks-status');
  Route::get('/analysis/textbook/allocations/query', [StudentController::class, 'getBookAllocationsReport'])->name('students.students-book-query');
  
  Route::get('/analysis/classes/', [StudentController::class, 'getKlassTeachersList'])->name('students.classes-analysis');
  Route::post('/term/session', [StudentController::class, 'setTermSession'])->name('students.term-session');
  
  Route::get('/delete/{studentId}', [StudentController::class, 'deleteStudent'])->name('students.delete-student');
  Route::post('/students/delete-multiple', [StudentController::class, 'deleteMultiple'])->name('students.delete-multiple');

  Route::post('/psle/{studentId}', [StudentController::class, 'createOrUpdatePSLE'])->name('students.create-psle');
  Route::post('/jce/{studentId}', [StudentController::class, 'createOrUpdateJCE'])->name('students.create-jce');
  
  Route::get('/custom/analysis/', [StudentController::class, 'studentsCustomAnalysis'])->name('students.students-custom-analysis');
  Route::post('/custom/classes/', [StudentController::class, 'getClasses'])->name('students.students-get-classes');
  Route::post('/custom/fields/', [StudentController::class, 'getFields'])->name('students.students-get-fields');
  Route::post('/custom/report', [StudentController::class, 'generateReport'])->name('students.generate-custom-report');

  Route::get('/class-list/report', [StudentController::class, 'classListReport'])->name('students.class-list-report');
  Route::post('/class-list/options', [StudentController::class, 'getClassListOptions'])->name('students.class-list-options');
  Route::post('/class-list/preview', [StudentController::class, 'classListPreview'])->name('students.class-list-preview');
  Route::post('/class-list/generate', [StudentController::class, 'generateClassListReport'])->name('students.generate-class-list-report');

  Route::get('/id-cards', [StudentController::class, 'studentIdCards'])->name('students.id-cards');
  Route::post('/id-cards/preview', [StudentController::class, 'previewIdCards'])->name('students.preview-id-cards');
  Route::post('/id-cards/generate', [StudentController::class, 'generateIdCards'])->name('students.generate-id-cards');
  
  Route::get('/class/statistical-analysis', [StudentController::class, 'getStudentStatisticsExport'])->name('students.class-statistical-analysis');
  
  Route::post('/allocation/book', [StudentController::class, 'studentBookAllocation'])->name('students.student-book-allocation');
  Route::get('/allocations/edit/{id}/{allocationId}', [StudentController::class, 'editBookAllocation'])->name('students.edit-book-allocation');
  Route::get('/show/allocations/{studentId}', [StudentController::class, 'getBookAllocation'])->name('students.get-book-allocation');
  
  Route::post('/book/allocation', [StudentController::class, 'storeBookAllocation'])->name('students.allocate-book');
  Route::put('/book/allocation/update/{id}', [StudentController::class, 'updateBookAllocation'])->name('students.update-allocation');

  Route::get('/term/import/list', [StudentController::class, 'getTermImportList'])->name('students.term-import-list');
  
  Route::get('/get-available-copies/{bookId}', [StudentController::class, 'getAvailableCopies'])->name('books.getAvailableCopies');
  Route::get('/classes-by-grade/{gradeId}', [StudentController::class, 'getClassesByGrade'])->name('students.classes-by-grade');

  Route::get('/duplicates', [StudentController::class, 'duplicates'])->name('students.duplicates');
  Route::get('/unallocated', [StudentController::class, 'unallocated'])->name('students.unallocated');

  Route::get('/students/badge-data', [StudentController::class, 'getBadgeData'])->name('students.badge-data');
  Route::get('/', [StudentController::class, 'index'])->name('students.index');
  });
