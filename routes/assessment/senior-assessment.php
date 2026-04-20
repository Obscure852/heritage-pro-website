<?php

use App\Http\Controllers\Assessment\SeniorAssessmentController;
use Illuminate\Support\Facades\Route;

/**
 * Senior School Assessment Routes
 *
 * Routes specific to Senior Secondary Schools.
 * All route names preserved for backward compatibility with views.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-assessment'])->group(function () {

    // Analysis Reports
    Route::get('/grade/subjects/all/{classId}/{sequenceId}/{type}', [SeniorAssessmentController::class, 'generateGradeAnalysisSenior'])
        ->name('assessment.grade-subjects-all-senior');

    Route::get('/subjects/senior/grade/{classId}/{type}/{sequence}', [SeniorAssessmentController::class, 'generateSubjectSeniorABCPerformanceReport'])
        ->name('assessment.grade-subjects-analysis-senior');

    Route::get('/classes/senior/grade/{classId}/{type}/{sequenceId}', [SeniorAssessmentController::class, 'generateClassCreditsSummary'])
        ->name('assessment.credits-subjects-analysis-senior');

    Route::get('/ca/subjects/houses/{sequenceId}/{type}', [SeniorAssessmentController::class, 'generateCASeniorHousePerformanceReport'])
        ->name('assessment.ca-house-senior-analysis');

    Route::get('/teachers/senior/subjects/analysis/{classId}/{type}/{sequence}', [SeniorAssessmentController::class, 'generateOverallTeacherPerformanceReportSenior'])
        ->name('assessment.subjects-senior-teachers-analysis');

    Route::get('/senior/grade/{classId}/{type}/{sequenceId}', [SeniorAssessmentController::class, 'generateSeniorGradeStudentList'])
        ->name('assessment.overall-ca-senior-grade-analysis');

    Route::get('/senior/teachers/grade/{classId}/{sequenceId}/{type}', [SeniorAssessmentController::class, 'showSubjectTeacherGradeAnalysis'])
        ->name('assessment.subject-grade-teachers-analysis');

    Route::get('/senior/subjects/grade/{classId}/{sequenceId}/{type}', [SeniorAssessmentController::class, 'showSubjectGradeAnalysis'])
        ->name('assessment.subject-grade-analysis');

    Route::get('/class/analytics/ca/{classId}/{sequenceId?}/{type}', [SeniorAssessmentController::class, 'generateCAAnalysisSenior'])
        ->name('assessment.generateCAAnalysisSenior');

    Route::get('/class/credits/analytics/{classId}/{sequenceId?}/{type}', [SeniorAssessmentController::class, 'generateSeniorCreditsReport'])
        ->name('assessment.generate-class-credits-analysis');

    Route::get('/house/credits/report/{sequenceId}/{type}', [SeniorAssessmentController::class, 'generateHouseCreditsReport'])
        ->name('assessment.house-credits-report');

    Route::get('/jce/house/grade-distribution/{classId}', [SeniorAssessmentController::class, 'generateJCEHouseGradeDistribution'])
        ->name('assessment.jce-house-grade-distribution');

    Route::get('/value-addition/report/{classId}', [SeniorAssessmentController::class, 'generateValueAdditionReport'])
        ->name('assessment.value-addition-report');

    Route::get('/value-addition/export/{classId}', [SeniorAssessmentController::class, 'exportValueAdditionReport'])
        ->name('assessment.value-addition-export');

    Route::get('/award-type-analysis/{classId}/{sequenceId}/{type}/{awardType}', [SeniorAssessmentController::class, 'generateAwardTypeAnalysis'])
        ->name('assessment.award-type-analysis')
        ->where('awardType', 'triple|double|single');

    Route::get('/house-award-analysis/{classId}/{sequenceId}/{type}', [SeniorAssessmentController::class, 'generateHouseAwardAnalysis'])
        ->name('assessment.house-award-analysis');

    Route::get('/house-6c-tracking/{classId}', [SeniorAssessmentController::class, 'generateHouse6CTrackingReport'])
        ->name('assessment.house-6c-tracking');

    Route::get('/teacher-value-addition/{classId}/{sequenceId}/{type}', [SeniorAssessmentController::class, 'showTeacherValueAdditionAnalysis'])
        ->name('assessment.teacher-value-addition-analysis');

    Route::get('/class/subjects/analytics/ca/{classId}/{sequenceId?}/{type}', [SeniorAssessmentController::class, 'generateSubjectAnalysisReportSenior'])
        ->name('assessment.generate-subjects-ca-analysis-senior');

    Route::get('/grade/subjects/analytics/ca/{classId}/{sequenceId?}/{type}', [SeniorAssessmentController::class, 'generateGradeSubjectAnalysisReportSenior'])
        ->name('assessment.generate-grade-subjects-ca-analysis-senior');

    // Report Cards
    Route::get('/senior/class/report-cards/pdf/{classId}', [SeniorAssessmentController::class, 'generateSeniorClassListReportCards'])
        ->name('assessment.generate-class-report-cards');

    Route::get('/report-card/html/senior/{id}', [SeniorAssessmentController::class, 'htmlReportCardSenior'])
        ->name('assessment.html-report-card-senior');

    Route::get('/report-card/pdf/senior/{id}', [SeniorAssessmentController::class, 'pdfReportCardSenior'])
        ->name('assessment.pdf-report-card-senior');
});
