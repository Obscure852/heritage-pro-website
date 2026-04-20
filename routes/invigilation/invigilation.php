<?php

use App\Http\Controllers\Invigilation\InvigilationController;
use App\Http\Controllers\Invigilation\InvigilationReportController;
use App\Http\Controllers\Invigilation\InvigilationSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('invigilation')
    ->group(function () {
        Route::middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-invigilation-published-roster'])->group(function () {
            Route::get('/view/teacher-roster', [InvigilationReportController::class, 'publishedTeacherRoster'])->name('invigilation.view.teacher-roster');
        });

        Route::middleware(['auth', 'throttle:auth', 'block.non.african', 'can:access-invigilation'])->group(function () {
            Route::get('/', [InvigilationController::class, 'index'])->name('invigilation.index');
            Route::get('/series/{series}', [InvigilationController::class, 'show'])->name('invigilation.show');

            Route::get('/reports/daily', [InvigilationReportController::class, 'dailyIndex'])->name('invigilation.reports.daily.index');
            Route::get('/reports/teacher', [InvigilationReportController::class, 'teacherIndex'])->name('invigilation.reports.teacher.index');
            Route::get('/reports/room', [InvigilationReportController::class, 'roomIndex'])->name('invigilation.reports.room.index');
            Route::get('/reports/conflicts', [InvigilationReportController::class, 'conflictsIndex'])->name('invigilation.reports.conflicts.index');

            Route::get('/series/{series}/reports/daily', [InvigilationReportController::class, 'daily'])->name('invigilation.reports.daily');
            Route::get('/series/{series}/reports/teacher', [InvigilationReportController::class, 'teacher'])->name('invigilation.reports.teacher');
            Route::get('/series/{series}/reports/room', [InvigilationReportController::class, 'room'])->name('invigilation.reports.room');
            Route::get('/series/{series}/reports/conflicts', [InvigilationReportController::class, 'conflicts'])->name('invigilation.reports.conflicts');

            Route::middleware('can:manage-invigilation')->group(function () {
                Route::get('/settings', [InvigilationSettingsController::class, 'index'])->name('invigilation.settings.index');
                Route::put('/settings', [InvigilationSettingsController::class, 'update'])->name('invigilation.settings.update');
                Route::post('/series', [InvigilationController::class, 'store'])->name('invigilation.store');
                Route::put('/series/{series}', [InvigilationController::class, 'update'])->name('invigilation.update');
                Route::post('/series/{series}/sessions', [InvigilationController::class, 'storeSession'])->name('invigilation.sessions.store');
                Route::put('/sessions/{session}', [InvigilationController::class, 'updateSession'])->name('invigilation.sessions.update');
                Route::delete('/sessions/{session}', [InvigilationController::class, 'destroySession'])->name('invigilation.sessions.destroy');
                Route::post('/sessions/{session}/rooms', [InvigilationController::class, 'storeRoom'])->name('invigilation.rooms.store');
                Route::put('/rooms/{room}', [InvigilationController::class, 'updateRoom'])->name('invigilation.rooms.update');
                Route::delete('/rooms/{room}', [InvigilationController::class, 'destroyRoom'])->name('invigilation.rooms.destroy');
                Route::post('/rooms/{room}/assignments', [InvigilationController::class, 'storeAssignment'])->name('invigilation.assignments.store');
                Route::put('/assignments/{assignment}', [InvigilationController::class, 'updateAssignment'])->name('invigilation.assignments.update');
                Route::delete('/assignments/{assignment}', [InvigilationController::class, 'destroyAssignment'])->name('invigilation.assignments.destroy');
                Route::post('/series/{series}/generate', [InvigilationController::class, 'generate'])->name('invigilation.generate');
                Route::post('/series/{series}/publish', [InvigilationController::class, 'publish'])->name('invigilation.publish');
                Route::post('/series/{series}/unpublish', [InvigilationController::class, 'unpublish'])->name('invigilation.unpublish');
            });
        });
    });
