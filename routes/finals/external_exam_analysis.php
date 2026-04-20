<?php

use App\Http\Controllers\ExternalExamAnalysisController;

Route::prefix('finals/analysis')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    Route::get('/class/overall/{classId}', [ExternalExamAnalysisController::class, 'getClassExamResults'])->name('finals.class.overall-analysis');
    Route::get('/class/subjects/summary/{classId}', [ExternalExamAnalysisController::class, 'getSubjectPerformanceReport'])->name('finals.class.subjects-summary-analyis');
    Route::get('/year/overall', [ExternalExamAnalysisController::class, 'getGraduateYearExamResults'])->name('finals.year.overall-analysis');
    Route::get('/students/transcripts', [ExternalExamAnalysisController::class, 'getStudentTranscriptsList'])->name('finals.students.transcripts-list');
});
