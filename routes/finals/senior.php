<?php

use App\Http\Controllers\Finals\FinalsSeniorStudentController;

Route::prefix('finals/senior')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

    Route::prefix('students')->group(function () {
        Route::get('/top-performers', [FinalsSeniorStudentController::class, 'topPerformers'])
            ->name('finals.senior.students.top-performers');

        Route::get('/transcripts', [FinalsSeniorStudentController::class, 'transcriptsList'])
            ->name('finals.senior.students.transcripts-list');
    });

});
