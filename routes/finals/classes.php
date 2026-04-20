<?php

use App\Http\Controllers\FinalsClassController;

Route::prefix('finals/classes')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    // Static routes
    Route::get('/', [FinalsClassController::class, 'index'])->name('finals.classes.index');
    Route::get('/data', [FinalsClassController::class, 'getData'])->name('finals.classes.data');
    Route::get('/badge-data', [FinalsClassController::class, 'getBadgeData'])->name('finals.classes.badge-data');

    // Specific report routes (must come BEFORE wildcard routes)
    Route::get('/overall/analysis', [FinalsClassController::class, 'overallAnalysis'])->name('finals.classes.overall-analysis');
    Route::get('/overall/summary/analysis', [FinalsClassController::class, 'overallPerformanceAnalysis'])->name('finals.classes.overall-performance-analysis');
    Route::get('/grade/jce-psle-comparison', [FinalsClassController::class, 'gradeJcePsleComparison'])->name('finals.classes.jce-psle-grade-comparison');
    Route::get('/jce-psle-comparison/{classId}', [FinalsClassController::class, 'jcePsleComparison'])->name('finals.classes.jce-psle-comparison');
    Route::delete('/remove/students/{klassId}/{studentId}', [FinalsClassController::class, 'removeStudent'])->name('finals.classes.students.remove');

    // Wildcard routes (must come LAST)
    Route::get('/{klass}', [FinalsClassController::class, 'show'])->name('finals.classes.show');
    Route::get('/{klass}/report', [FinalsClassController::class, 'generateReport'])->name('finals.classes.report');

});
