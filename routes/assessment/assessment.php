<?php

use App\Http\Controllers\Assessment\TestController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ThresholdSettingsController;
use Illuminate\Support\Facades\Route;

/**
 * Shared Assessment Routes
 *
 * Routes used by all school types (Primary, Junior, Senior).
 * School-specific routes are in separate files.
 */
Route::prefix('assessment')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    Route::post('/update-term', [AssessmentController::class, 'updateTerm'])->name('assessment.update-term');

    Route::middleware('can:access-assessment')->group(function () {
        // Index & Main Views
        Route::get('/', [AssessmentController::class, 'index'])->name('assessment.index');
        Route::get('/gradebook/primary', [AssessmentController::class, 'primaryGradebook'])->name('assessment.gradebook.primary');
        Route::get('/gradebook/junior', [AssessmentController::class, 'juniorGradebook'])->name('assessment.gradebook.junior');
        Route::get('/gradebook/senior', [AssessmentController::class, 'seniorGradebook'])->name('assessment.gradebook.senior');
        Route::get('/create', [TestController::class, 'create'])->name('assessment.create');
        Route::post('/store', [TestController::class, 'store'])->name('assessment.store');
        Route::put('/update/{id}', [AssessmentController::class, 'update'])->name('assessment.update');
        Route::delete('delete/{id}', [AssessmentController::class, 'destroy'])->name('assessment.destroy');

        // Department Analysis (shared)
        Route::get('/departments/subjects/{classId}', [AssessmentController::class, 'analyzePerformanceByDepartment'])->name('assessment.analysis-by-department');
        Route::get('assessment/export-subject-analysis/{classId}/{sequence}', [AssessmentController::class, 'exportSubjectAnalysis'])->name('assessment.export-subject-analysis');

        Route::get('/subjects/exam/grade/{classId}/{type}/{sequence}', [AssessmentController::class, 'generateSubjectExamPerformanceReport'])->name('assessment.all-subjects-exam');

        // Generic Report Card (shared)
        Route::get('/report-card/html/{id}', [AssessmentController::class, 'htmlReportCard'])->name('assessment.html-report-card');

        // Recalculation
        Route::post('/recalculate/grade/{id}', [AssessmentController::class, 'recalculateGradesForGrade'])->name('assessment.recalculate-scores');
        Route::get('/recalculate/progress/{id}', [AssessmentController::class, 'checkRecalculationProgress'])->name('assessment.recalculate-progress');

        // Email & Bulk Operations
        Route::post('/email-report-card', [AssessmentController::class, 'emailReportCard'])->name('assessment.email-report-card');
        Route::post('/bulk-email-report-cards', [AssessmentController::class, 'bulkEmailReportCards'])->name('assessment.bulk-email-report-cards');
        Route::post('/archive-email-report-cards', [AssessmentController::class, 'archiveEmailReportCards'])->name('assessment.archive-email-report-cards');

        // Grade-Wide Assessment
        Route::get('/subject/all/{id}', [AssessmentController::class, 'showGradeWideAssessmentByTeacher'])->name('assessment.grade-wide-assessment');
        Route::get('/grade/subject/{id}', [AssessmentController::class, 'showGradeSubjectWideAssessmentByTeacher'])->name('assessment.grade-subject-wide-assessment');

        // Subject & Report Views
        Route::get('/subjects/{id}', [AssessmentController::class, 'showAssessment'])->name('assessment.testing');
        Route::get('/reports/{id}', [AssessmentController::class, 'showReports'])->name('assessment.reports');

        // Class & Student Management
        Route::get('/students/{classId}/{termId}', [AssessmentController::class, 'classAssessmentList'])->name('assessment.class-lists');
        Route::get('/klasses', [AssessmentController::class, 'getKlassesForTerm'])->name('assessment.klasses-for-term');
        Route::post('/save-selected-subject', [AssessmentController::class, 'saveSelectedSubject'])->name('assessment.save-selected-subject');

        // Subject Remarks
        Route::post('/subject/remarks', [AssessmentController::class, 'newSubjectRemark'])->name('assessment.new-remark');
        Route::post('/optional/remarks', [AssessmentController::class, 'newOptionalSubjectRemark'])->name('assessment.new-remark-optional');
        Route::get('/optional/remarks/{studentId}/{id}/{studentIds}/{index}', [AssessmentController::class, 'optionalSubjectRemarks'])->name('assessment.optional-subject-remarks');
        Route::get('/core/remarks/{studentId}/{id}/{studentIds}/{index}', [AssessmentController::class, 'coreSubjectRemarks'])->name('assessment.core-subject-remarks');

        // Comment Bank
        Route::get('/comment/bank/', [AssessmentController::class, 'getCommentsBank'])->name('assessment.comment-bank');
        Route::get('/comment/create/', [AssessmentController::class, 'createComment'])->name('assessment.create-comment');
        Route::post('/comment', [AssessmentController::class, 'storeOverallComment'])->name('assessment.store-comment');
        Route::get('/comment/edit/{id}/', [AssessmentController::class, 'editComment'])->name('assessment.edit-comment');
        Route::put('/comment/{id}', [AssessmentController::class, 'update'])->name('assessment.update-comment');
        Route::delete('/comment/{id}', [AssessmentController::class, 'destroy'])->name('assessment.delete-comment');

        // Subject Comment Bank
        Route::get('/subjects/comment/create/', [AssessmentController::class, 'createSubjectCommentBank'])->name('assessment.create-subject-comment');
        Route::post('/subject/comment/store/', [AssessmentController::class, 'storeSubjectComment'])->name('assessment.store-subject-comment');
        Route::get('/subject/comment/edit/{id}', [AssessmentController::class, 'editSubjectComment'])->name('assessment.edit-subject-comment');
        Route::put('/subject/comment/update/{id}', [AssessmentController::class, 'updateSubjectComment'])->name('assessment.update-subject-comment');
        Route::delete('/subject/comment/{id}', [AssessmentController::class, 'destroySubjectComment'])->name('assessment.delete-subject-comment');

        // Venue Management
        Route::post('/venue/store/', [AssessmentController::class, 'storeVenue'])->name('assessment.create-venue');
        Route::put('/venue/edit/{id}/', [AssessmentController::class, 'updateVenue'])->name('assessment.update-venue');
        Route::delete('/venue/{id}', [AssessmentController::class, 'destroyVenue'])->name('assessment.destroy-venue');

        // Overall Comments
        Route::post('/overall/comments/{id}', [AssessmentController::class, 'updateComment'])->name('assessment.new-comment');
        Route::get('/overall/comments/update/{id}', [AssessmentController::class, 'overallComments'])->name('assessment.comments');

        // Grade Analysis (shared)
        Route::get('/grade/{classId}', [AssessmentController::class, 'showGradeTermAnalysisReport'])->name('assessment.overall-class-analysis');

        // Threshold Settings Routes
        Route::prefix('threshold')->group(function () {
            // Get effective threshold for current context (AJAX)
            Route::get('/effective', [ThresholdSettingsController::class, 'getEffectiveThreshold'])
                ->name('threshold.effective');

            // Teacher preference management (AJAX)
            Route::post('/teacher-preference', [ThresholdSettingsController::class, 'updateTeacherPreference'])
                ->name('threshold.update-teacher-preference');
            Route::post('/teacher-preference/reset', [ThresholdSettingsController::class, 'resetTeacherPreference'])
                ->name('threshold.reset-teacher-preference');
        });

        // Admin-only routes for system threshold settings
        Route::middleware(['can:manage-academic'])->prefix('threshold/system')->group(function () {
            Route::get('/', [ThresholdSettingsController::class, 'systemSettings'])
                ->name('threshold.system-settings');
            Route::post('/store', [ThresholdSettingsController::class, 'storeSystemSetting'])
                ->name('threshold.store-system-setting');
            Route::delete('/{id}', [ThresholdSettingsController::class, 'deleteSystemSetting'])
                ->name('threshold.delete-system-setting');
            Route::post('/{id}/toggle', [ThresholdSettingsController::class, 'toggleSystemSetting'])
                ->name('threshold.toggle-system-setting');
        });
    });

    Route::middleware('can:access-markbook')->group(function () {
        // Markbook
        Route::get('/markbook/primary', [AssessmentController::class, 'primaryMarkbook'])->name('assessment.markbook.primary');
        Route::get('/markbook/junior', [AssessmentController::class, 'juniorMarkbook'])->name('assessment.markbook.junior');
        Route::get('/markbook/senior', [AssessmentController::class, 'seniorMarkbook'])->name('assessment.markbook.senior');
        Route::get('/markbook/options/{subjectId}', [AssessmentController::class, 'optionalStudents'])->name('assessment.option-markbook');
        Route::get('/markbook/{subjectId}', [AssessmentController::class, 'testStudents'])->name('assessment.selected-subject');
        Route::get('/markbook/{term_id?}', [AssessmentController::class, 'assessmentMarkbook'])->name('assessment.markbook');
        Route::post('/markbook/marks', [AssessmentController::class, 'updateMarks'])->name('assessment.update-marks');
        Route::get('/class/subjects/', [AssessmentController::class, 'fetchClassSubjects'])->name('assessment.fetch-classes');
    });
});
