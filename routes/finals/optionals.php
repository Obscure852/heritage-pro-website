<?php

use App\Http\Controllers\FinalOptionalSubjectController;

Route::prefix('finals/optionals')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    // Static routes
    Route::get('/', [FinalOptionalSubjectController::class, 'index'])->name('finals.optionals.index');
    Route::get('/data', [FinalOptionalSubjectController::class, 'getData'])->name('finals.optionals.data');
    Route::get('/badge-data', [FinalOptionalSubjectController::class, 'getBadgeData'])->name('finals.optionals.badge-data');

    // Specific report routes (must come BEFORE wildcard routes)
    Route::get('/subjects/analysis', [FinalOptionalSubjectController::class, 'optionalSubjectsAnalysis'])->name('finals.optionals.subjects-analysis');
    Route::get('/department/analysis', [FinalOptionalSubjectController::class, 'optionalSubjectsDepartmentAnalysis'])->name('finals.optionals.department-analysis');
    Route::get('/teachers/analysis', [FinalOptionalSubjectController::class, 'optionalSubjectsTeacherAnalysis'])->name('finals.optionals.teachers-analysis');
    // Commented out - PSLE subject grades data not available
    // Route::get('/x/class-lists/analysis', [FinalOptionalSubjectController::class, 'optionalSubjectsClassListsAnalysis'])->name('finals.optionals.class-lists-analysis');

    // Wildcard routes (must come LAST)
    Route::get('/{optionalSubject}', [FinalOptionalSubjectController::class, 'show'])->name('finals.optionals.show');
    Route::get('/{id}/students', [FinalOptionalSubjectController::class, 'showStudents'])->name('finals.optionals.show-students');
    Route::get('/{optionalSubject}/report', [FinalOptionalSubjectController::class, 'generateReport'])->name('finals.optionals.report');
    Route::get('/{optionalSubject}/export', [FinalOptionalSubjectController::class, 'export'])->name('finals.optionals.export');
    Route::get('/{optionalSubject}/manage-students', [FinalOptionalSubjectController::class, 'manageStudents'])->name('finals.optionals.manage-students');
    Route::post('/{optionalSubject}/add-students', [FinalOptionalSubjectController::class, 'addStudents'])->name('finals.optionals.add-students');
    Route::delete('/{optionalSubject}/students/{student}', [FinalOptionalSubjectController::class, 'removeStudent'])->name('finals.optionals.remove-student');

});
