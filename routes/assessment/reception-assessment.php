<?php

use App\Http\Controllers\CriteriaBasedTestController;
use Illuminate\Support\Facades\Route;

/**
 * Reception/Pre-School Assessment Routes
 *
 * Routes specific to Reception and Pre-School (Criteria-Based Tests).
 * All route names preserved for backward compatibility with views.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-assessment'])->group(function () {

    // Criteria-Based Test Management
    Route::post('/criteria-based-tests/new', [CriteriaBasedTestController::class, 'addReceptionTest'])
        ->name('reception.add-reception-test');

    Route::post('/criteria-based-tests/enter', [CriteriaBasedTestController::class, 'storeCriteriaTestAssessment'])
        ->name('reception.store-criteria-test-assessment');

    Route::get('/criteria-based-tests/create', [CriteriaBasedTestController::class, 'createTest'])
        ->name('reception.create-test');

    Route::get('/criteria-based-tests/get/{testId}', [CriteriaBasedTestController::class, 'getCriteriaBaseTest'])
        ->name('reception.get-test-update');

    Route::post('/criteria-based-tests/update/{testId}', [CriteriaBasedTestController::class, 'updatedCriteriaBaseTest'])
        ->name('reception.update-criteria-test');

    Route::get('/delete/test/{testId}', [CriteriaBasedTestController::class, 'deleteCriteriaBaseTest'])
        ->name('reception.delete-criteria-based-test');

    Route::post('/criteria-based-tests/copy', [CriteriaBasedTestController::class, 'copy'])
        ->name('reception.copy-criteria-based-test');

    // Report Cards
    Route::get('/criteria-based/pdf/report-card/{id}', [CriteriaBasedTestController::class, 'showPrePDFReportCard'])
        ->name('reception.pre-pdf-report-card');

    Route::get('/criteria-based/html/report-card/{id}', [CriteriaBasedTestController::class, 'showPreHTMLReportCard'])
        ->name('reception.pre-html-report-card');

    Route::get('/criteria-based/class/pdf/report-card/{classId}', [CriteriaBasedTestController::class, 'generateRECClassReportCardsPDF'])
        ->name('reception.pre-list-pdf-report-card');

    // Test Listings
    Route::get('/criteria-based/tests/', [CriteriaBasedTestController::class, 'index'])
        ->name('reception.criteria-tests');

    Route::get('/criteria/based/tests/{gradeId}', [CriteriaBasedTestController::class, 'getCriteriaBasedTestsByTermAndGrade'])
        ->name('reception.criteria-tests-list');
});
