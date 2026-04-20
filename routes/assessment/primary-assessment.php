<?php

use App\Http\Controllers\Assessment\PrimaryAssessmentController;
use Illuminate\Support\Facades\Route;

/**
 * Primary School Assessment Routes
 *
 * Routes specific to Primary Schools.
 * All route names preserved for backward compatibility with views.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-assessment'])->group(function () {

    // Analysis Reports
    Route::get('/primary/analysis/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'htmlClassPerformanceAnalysis'])
        ->name('assessment.primary-tests-class-analysis');

    Route::get('/primary/grade/analysis/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'htmlGradePerformanceAnalysis'])
        ->name('assessment.primary-tests-grade-analysis');

    Route::get('/primary/grade/subjects/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'generateSubjectGradePerformanceReport'])
        ->name('assessment.test-primary-grade-subject-analysis');

    Route::get('/primary/region/grade/subjects/{classId}', [PrimaryAssessmentController::class, 'generateRegionalGradePerformanceReport'])
        ->name('assessment.regional-test-primary-grade-subject-analysis');

    Route::get('/primary/grades/overall/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'generateGradePerformanceReport'])
        ->name('assessment.assessment-overall-grade-subject-analysis');

    // Export Routes
    Route::get('/export/class/analysis/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'htmlClassPerformanceAnalysisExport'])
        ->name('assessment.export-class-analysis');

    Route::get('/export/grade/analysis/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'htmlGradePerformanceAnalysisExport'])
        ->name('assessment.export-grade-analysis');

    Route::get('/export/class/region/analysis/{classId}', [PrimaryAssessmentController::class, 'regionalGradePerformanceReportExport'])
        ->name('assessment.export-class-region-analysis');

    Route::get('/export/class/subjects/{classId}/{type}/{sequenceId}', [PrimaryAssessmentController::class, 'generateSubjectGradePerformanceReportExport'])
        ->name('assessment.export-grade-subjects-analysis');

    // Report Cards
    Route::get('/primary/pdf/cards/{classId}', [PrimaryAssessmentController::class, 'pdfReportCardsForClassPrimary'])
        ->name('assessment.all-students-primary-reports-pdf');

    Route::get('/primary/report-card/pdf/{id}', [PrimaryAssessmentController::class, 'primaryPDFReportCard'])
        ->name('assessment.primary-pdf-report-card');

    Route::get('/primary/report-card/html/{id}', [PrimaryAssessmentController::class, 'primaryHTMLReportCard'])
        ->name('assessment.primary-html-report-card');
});
