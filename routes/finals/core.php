<?php

use App\Http\Controllers\FinalKlassSubjectController;

Route::prefix('finals/core')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    // Static routes
    Route::get('/', [FinalKlassSubjectController::class, 'index'])->name('finals.core.index');
    Route::get('/data', [FinalKlassSubjectController::class, 'getData'])->name('finals.core.data');
    Route::get('/badge-data', [FinalKlassSubjectController::class, 'getBadgeData'])->name('finals.core.badge-data');

    // Specific report routes (must come BEFORE wildcard routes)
    Route::get('/subjects/analysis', [FinalKlassSubjectController::class, 'coreSubjectsAnalysis'])->name('finals.core.subjects-analysis');
    Route::get('/department/analysis', [FinalKlassSubjectController::class, 'departmentSubjectsAnalysis'])->name('finals.core.department-subjects-analysis');
    Route::get('/teacher/analysis', [FinalKlassSubjectController::class, 'teacherSubjectsAnalysis'])->name('finals.core.teacher-subjects-analysis');
    Route::get('/x/class-lists', [FinalKlassSubjectController::class, 'coreSubjectsClassListsAnalysis'])->name('finals.core.core-subjects-class-lists');

    // Wildcard routes (must come LAST)
    Route::get('/{finalKlassSubject}', [FinalKlassSubjectController::class, 'show'])->name('finals.core.show');
});
