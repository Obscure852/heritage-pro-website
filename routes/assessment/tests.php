<?php

use App\Http\Controllers\Assessment\TestController;
use Illuminate\Support\Facades\Route;

/**
 * Test Management Routes
 *
 * Routes for test/exam CRUD operations.
 * All route names preserved for backward compatibility.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-assessment'])->group(function () {

    // Test List & Index
    Route::get('/exam/list', [TestController::class, 'index'])->name('assessment.test-list');
    Route::get('/tests/{termId}/{gradeId}', [TestController::class, 'listByTermAndGrade'])->name('assessment.tests-lists');

    // Test Creation
    Route::get('/exam/create', [TestController::class, 'create'])->name('assessment.create-test');
    Route::post('/test/store', [TestController::class, 'store'])->name('assessment.test-store');
    Route::post('/optional/create', [TestController::class, 'storeOptional'])->name('assessment.optional-store');

    // Test Edit/Update
    Route::get('/grade/test/edit/{id}', [TestController::class, 'edit'])->name('assessment.ca-exam-edit');
    Route::post('/grade/test/update/{id}', [TestController::class, 'update'])->name('assessment.ca-exam-update');

    // Test Delete
    Route::delete('/grade/test/delete/{id}', [TestController::class, 'destroy'])->name('assessment.ca-exam-delete');

    // Test Utilities
    Route::post('/grade/test/copy', [TestController::class, 'copy'])->name('assessment.copy-test');
    Route::get('/grade/test/has-marks/{id}', [TestController::class, 'hasMarks'])->name('assessment.test-has-marks');

    // Session Storage
    Route::post('/tests/list', [TestController::class, 'storeSelectedTestGrade'])->name('assessment.store-selected-test');
    Route::post('/classes/list', [TestController::class, 'storeSelectedClass'])->name('assessment.store-selected-class');

    // Value Addition Subject Mapping
    Route::get('/value-addition-mappings', [TestController::class, 'getValueAdditionMappings'])
        ->name('assessment.value-addition-mappings.index');
    Route::post('/value-addition-mappings', [TestController::class, 'storeValueAdditionMappings'])
        ->name('assessment.value-addition-mappings.store');
});
