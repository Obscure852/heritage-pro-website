<?php
use App\Http\Controllers\HouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('houses')->middleware(['auth', 'throttle:auth', 'block.non.african','can:houses-access'])->group(function () {
  Route::get('/students', [HouseController::class, 'studentsHouseAnalysis'])->name('house.students-house-list');
  Route::get('/list', [HouseController::class, 'houseReport'])->name('house.house-list');
  Route::get('/new', [HouseController::class, 'show'])->name('house.show');

  Route::post('/store', [HouseController::class, 'store'])->name('house.store');
  Route::post('/allocations/move/{id}', [HouseController::class, 'moveStudents'])->name('house.move-students');
  Route::get('/allocations/users/{id}', [HouseController::class, 'allocateUsers'])->name('house.open-house-users');
  Route::post('/allocations/users/move/{id}', [HouseController::class, 'moveUsers'])->name('house.move-users');
  Route::delete('/delete-multiple-students/{id}', [HouseController::class, 'deleteMultipleStudents'])->name('house.delete-multiple-students');
  Route::delete('/delete-multiple-users/{id}', [HouseController::class, 'deleteMultipleUsers'])->name('house.delete-multiple-users');
  Route::get('/grade/{classId}/{sequenceId}/{type}', [HouseController::class, 'showClassGrouping'])->name('assessment.house-analysis-senior-grade-analysis');
  Route::get('/class/{classId}/{sequenceId}/{type}', [HouseController::class, 'showClassGroupingBest5'])->name('assessment.house-analysis-senior-class-analysis');

  Route::get('/allocations/{id}', [HouseController::class, 'allocateStudents'])->name('house.open-house');
  Route::get('/term/data', [HouseController::class, 'getTermData'])->name('houses.get-term-data');
  Route::get('/term/view/{houseId}', [HouseController::class, 'getHouseData'])->name('house.house-view');

  Route::delete('/student/delete/{houseId}/{studentId}', [HouseController::class, 'deleteStudent'])->name('house.delete-student');
  Route::delete('/user/delete/{houseId}/{userId}', [HouseController::class, 'deleteUser'])->name('house.delete-user');
  Route::get('/edit/{houseId}', [HouseController::class, 'editHouse'])->name('house.edit-house');

  Route::post('/update/{houseId}', [HouseController::class, 'updateHouse'])->name('house.update-house');
  Route::get('/contacts', [HouseController::class, 'getContacts'])->name('house.contacts-house');

  Route::get('/delete/{houseId}', [HouseController::class, 'deleteHouse'])->name('house.delete-house');
  Route::get('/export/students', [HouseController::class, 'studentsHouseAnalysisExport'])->name('house.students-house-export');
  Route::get('/', [HouseController::class, 'index'])->name('house.index');
});
