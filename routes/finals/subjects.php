<?php

use App\Http\Controllers\FinalGradeSubjectController;

Route::prefix('finals/subjects')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    Route::get('/', [FinalGradeSubjectController::class, 'index'])->name('finals.subjects.index');
    Route::get('/data', [FinalGradeSubjectController::class, 'getData'])->name('finals.subjects.data');
    Route::get('/badge-data', [FinalGradeSubjectController::class, 'getBadgeData'])->name('finals.subjects.badge-data');
    Route::get('/report-classes', [FinalGradeSubjectController::class, 'getReportClasses'])->name('finals.subjects.report-classes');
    Route::get('/{finalGradeSubject}/edit', [FinalGradeSubjectController::class, 'edit'])->name('finals.subjects.edit');
    Route::put('/{finalGradeSubject}', [FinalGradeSubjectController::class, 'update'])->name('finals.subjects.update');
    Route::get('/subjects/analysis', [FinalGradeSubjectController::class, 'subjectGenderGradesReport'])->name('finals.subjects.subject-gender-grades-report');
    Route::get('/subjects/psle-jce-comparison', [FinalGradeSubjectController::class, 'subjectPsleJceComparisonReport'])->name('finals.subjects.subject-psle-jce-comparison');
    Route::get('/subjects/overall-teacher-performance', [FinalGradeSubjectController::class, 'overallTeacherPerformanceReport'])->name('finals.subjects.overall-teacher-performance');
    Route::get('/teachers/cjss/overall/subjects/analysis/{classId}/{type}/{sequence}', [FinalGradeSubjectController::class, 'overallTeacherPerformanceByGrade'])
        ->name('finals.subjects.overall-teachers-analysis');

});
