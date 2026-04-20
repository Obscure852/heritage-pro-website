<?php

use App\Http\Controllers\FinalsClassController;
use App\Http\Controllers\FinalsStudentController;

Route::prefix('finals/students')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
        Route::get('/', [FinalsStudentController::class, 'index'])->name('finals.students.index');
        Route::get('/get-data', [FinalsStudentController::class, 'getData'])->name('finals.students.get-data');
        Route::get('/badge-data', [FinalsStudentController::class, 'getBadgeData'])->name('finals.students.badge-data');
        Route::get('/eligible-students', [FinalsStudentController::class, 'eligibleStudents'])->name('finals.students.eligible');
        Route::post('/add', [FinalsStudentController::class, 'addFromStudentsModule'])->name('finals.students.add');
        Route::get('/no-candidate', [FinalsStudentController::class, 'noCandidateNumber'])->name('finals.students.no-candidate');
        Route::delete('/bulk-delete', [FinalsStudentController::class, 'bulkDestroy'])->name('finals.students.bulk-destroy');
        Route::get('/{student}', [FinalsStudentController::class, 'show'])->name('finals.students.show');
        Route::post('/{student}/update-exam-number', [FinalsStudentController::class, 'updateExamNumber'])->name('finals.students.update-exam-number');
        Route::post('/{student}/add-subject-result', [FinalsStudentController::class, 'addSubjectResult'])->name('finals.students.add-subject-result');
        Route::post('/{student}/update-overall-result', [FinalsStudentController::class, 'updateOverallResult'])->name('finals.students.update-overall-result');

        Route::get('/transcript/{studentId}', [FinalsStudentController::class, 'getStudentTranscript'])->name('finals.students.transcript');
        Route::get('/{student}/edit', [FinalsStudentController::class, 'edit'])->name('finals.students.edit');
        Route::put('/{student}', [FinalsStudentController::class, 'update'])->name('finals.students.update');
        Route::delete('/{student}', [FinalsStudentController::class, 'destroy'])->name('finals.students.destroy');
});
