<?php

use App\Http\Controllers\Assessment\JuniorAssessmentController;
use Illuminate\Support\Facades\Route;

/**
 * Junior School (CJSS) Assessment Routes
 *
 * Routes specific to Junior Secondary Schools.
 * All route names preserved for backward compatibility with views.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-assessment'])->group(function () {

    // Analysis Reports
    Route::get('/departments/year/{classId}/{sequenceId}/{type}', [JuniorAssessmentController::class, 'generateAnalysisByDepartment'])
        ->name('assessment.department-by-year-analysis');

    Route::get('/class/grade/{classId}/{sequence}/{type}', [JuniorAssessmentController::class, 'generateClassAnalysisReport'])
        ->name('assessment.grade-class-by-class');

    Route::get('/class/grade/distribution/{classId}/{sequence}/{type}', [JuniorAssessmentController::class, 'generateGradeDistributionReport'])
        ->name('assessment.grade-distribution-by-gender');

    Route::get('/house/subjects/{classId}/{sequence}/{type}', [JuniorAssessmentController::class, 'generateHouseAnalysisReport'])
        ->name('assessment.house-analysis-by-class');

    Route::get('/ca/subjects/houses/{sequenceId}', [JuniorAssessmentController::class, 'generateCAJuniorHousePerformanceReport'])
        ->name('assessment.ca-house-junior-analysis');

    Route::get('/exam/overall/houses/', [JuniorAssessmentController::class, 'generateOverallExamHousePerformanceReport'])
        ->name('assessment.exam-houses-overall-analysis');

    Route::get('/exam/overall/houses/simple/{type}/{sequenceId}', [JuniorAssessmentController::class, 'generateOverallExamHousePerformanceReportSimple'])
        ->name('assessment.exam-houses-overall-analysis-simple');

    Route::get('/overall/grade/houses/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'generateOverallGradeHouseExamPerformanceReport'])
        ->name('assessment.grade-houses-overall-analysis');

    Route::get('/overall/grade/houses/simple/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'generateOverallGradeHouseExamPerformanceReportSimple'])
        ->name('assessment.grade-houses-overall-analysis-simple');

    Route::get('/ca/overall/houses/{sequence}', [JuniorAssessmentController::class, 'generateOverallCAHousePerformanceReport'])
        ->name('assessment.ca-houses-overall-analysis');

    Route::get('/ca/overall/classes/{classId}/{sequence}', [JuniorAssessmentController::class, 'generateOverallCAClassPerformanceReport'])
        ->name('assessment.ca-classes-overall-analysis');

    Route::get('/exam/overall/classes/{classId}', [JuniorAssessmentController::class, 'generateOverallExamClassPerformanceReport'])
        ->name('assessment.exam-classes-overall-analysis');

    // Overall Classes Analysis II (No gender breakdown, separate tables per class)
    Route::get('/ca/overall/classes-ii/{classId}/{sequence}', [JuniorAssessmentController::class, 'generateOverallCAClassPerformanceReportII'])
        ->name('assessment.ca-classes-overall-analysis-ii');

    Route::get('/exam/overall/classes-ii/{classId}', [JuniorAssessmentController::class, 'generateOverallExamClassPerformanceReportII'])
        ->name('assessment.exam-classes-overall-analysis-ii');

    // Teacher Analysis
    Route::get('/teachers/cjss/subjects/analysis/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'generateOverallCATeacherPerformanceReport'])
        ->name('assessment.subjects-ca-teachers-analysis');

    Route::get('/teachers/cjss/overall/subjects/analysis/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'generateOverallTeacherPerformanceByGrade'])
        ->name('assessment.subjects-overall-teachers-analysis');

    Route::get('/exam/subjects/houses', [JuniorAssessmentController::class, 'generateExamHousePerformanceReport'])
        ->name('assessment.exam-house-analysis');

    // Grade Analysis
    Route::get('/grade/ca/{classId}/{sequenceId}', [JuniorAssessmentController::class, 'generateCAByGradeAnalysis'])
        ->name('assessment.overall-ca-grade-analysis');

    Route::get('/grade/exam/{classId}', [JuniorAssessmentController::class, 'generateExamByGradeAnalysis'])
        ->name('assessment.overall-exam-grade-analysis');

    Route::get('/class/subjects/distribution/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'subjectGradeDistributionByClass'])
        ->name('assessment.subject-grade-distribution-by-class');

    Route::get('/grade/stream/year/{classId}/{sequenceId}/{type}', [JuniorAssessmentController::class, 'generateGradeStreamPSLEAnalysis'])
        ->name('assessment.grade-stream-psle-analysis');

    Route::get('/grade/special-needs/{classId}/{sequenceId}/{type}', [JuniorAssessmentController::class, 'generateSpecialNeedsAnalysis'])
        ->name('assessment.special-needs-analysis');

    Route::get('/class/analysis/exam/{classId}/{type}/{sequence}', [JuniorAssessmentController::class, 'generateExamAnalysis'])
        ->name('assessment.generate-exam-analysis');

    // Value Addition Analysis
    Route::get('/class/value/add/ca/{classId}/{type}/{sequenceId}', [JuniorAssessmentController::class, 'generateValueAdditionAnalysis'])
        ->name('assessment.generateValueAdditionAnalysis');

    Route::get('/class/value/compare/{classId}/{type}/{sequenceId}', [JuniorAssessmentController::class, 'generateTestComparisonAnalysis'])
        ->name('assessment.generateTestComparisonAnalysis');

    Route::get('/overall/value/add/{classId}/{type}/{sequenceId}', [JuniorAssessmentController::class, 'generateValueAdditionAnalysisForGrade'])
        ->name('assessment.generateValueAdditionAnalysisForGrade');

    Route::get('/overall/comparison/value/{classId}/{type}/{sequenceId}', [JuniorAssessmentController::class, 'generateTestComparisonAnalysisForGrade'])
        ->name('assessment.generateTestComparisonAnalysisForGrade');

    // Report Cards
    Route::get('/junior/class/report-cards/pdf/{classId}', [JuniorAssessmentController::class, 'generateListClassReportCards'])
        ->name('assessment.generate-grades-report-cards');

    Route::get('/report-card/pdf/junior/{id}', [JuniorAssessmentController::class, 'pdfReportCardJunior3'])
        ->name('assessment.junior-pdf-report-card');

    Route::get('/report-card/html/junior/{id}', [JuniorAssessmentController::class, 'htmlReportCardJunior3'])
        ->name('assessment.html-report-card-junior');

    // Class Analysis
    Route::get('/classes/{classId}', [JuniorAssessmentController::class, 'showClassTermAnalysisReport'])
        ->name('assessment.analyis-term-report');

    Route::get('/class/grade/{classId}/{sequenceId?}', [JuniorAssessmentController::class, 'showClassGradeAnalysisReport']);
});
