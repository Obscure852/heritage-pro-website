<?php

use App\Http\Controllers\Timetable\ConstraintController;
use App\Http\Controllers\Timetable\GenerationController;
use App\Http\Controllers\Timetable\PeriodSettingsController;
use App\Http\Controllers\Timetable\TimetableController;
use App\Http\Controllers\Timetable\TimetableSlotController;
use App\Http\Controllers\Timetable\TimetableExportController;
use App\Http\Controllers\Timetable\TimetableViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Timetable Routes
|--------------------------------------------------------------------------
|
| Routes for the Timetable & Scheduling module.
|
*/

// Timetable View routes (read-only, access-timetable gate)
// Must be before the manage-timetable group which contains /{timetable} wildcard
Route::prefix('timetable/view')->name('timetable.view.')
    ->middleware(['auth', 'can:access-timetable'])
    ->group(function () {
        Route::get('/class/{timetable?}', [TimetableViewController::class, 'classView'])->name('class');
        Route::get('/teacher/{timetable?}', [TimetableViewController::class, 'teacherView'])->name('teacher');
        Route::get('/master/{timetable?}', [TimetableViewController::class, 'masterView'])->name('master');
    });

// Export routes (access-timetable gate — teachers and admins can print PDFs)
// Must be before the manage-timetable group which contains /{timetable} wildcard
Route::prefix('timetable/export')->name('timetable.export.')
    ->middleware(['auth', 'can:access-timetable'])
    ->group(function () {
        Route::get('/class/{timetable}/pdf', [TimetableExportController::class, 'classPdf'])->name('class.pdf');
        Route::get('/teacher/{timetable}/pdf', [TimetableExportController::class, 'teacherPdf'])->name('teacher.pdf');
    });

Route::prefix('timetable')->name('timetable.')
    ->middleware(['auth', 'can:manage-timetable'])
    ->group(function () {
        // Period Settings routes (must be before /{timetable} wildcard)
        Route::prefix('period-settings')->name('period-settings.')->group(function () {
            Route::get('/', [PeriodSettingsController::class, 'index'])->name('index');
            Route::post('/periods', [PeriodSettingsController::class, 'updatePeriods'])->name('update-periods');
            Route::post('/breaks', [PeriodSettingsController::class, 'updateBreaks'])->name('update-breaks');
            Route::post('/block-allocations', [PeriodSettingsController::class, 'updateBlockAllocations'])->name('update-block-allocations');
            Route::get('/block-allocations', [PeriodSettingsController::class, 'getBlockAllocations'])->name('get-block-allocations');
            Route::post('/coupling-groups', [PeriodSettingsController::class, 'updateCouplingGroups'])->name('update-coupling-groups');
        });

        // Slot Management routes (must be before /{timetable} wildcard)
        Route::prefix('slots')->name('slots.')->group(function () {
            Route::get('/grid/{timetable}', [TimetableSlotController::class, 'index'])->name('grid');
            Route::get('/grid-data/{timetable}', [TimetableSlotController::class, 'gridData'])->name('grid-data');
            Route::post('/assign', [TimetableSlotController::class, 'assign'])->name('assign');
            Route::delete('/delete/{slot}', [TimetableSlotController::class, 'delete'])->name('delete');
            Route::post('/check-conflicts', [TimetableSlotController::class, 'checkConflicts'])->name('check-conflicts');
            Route::post('/move', [TimetableSlotController::class, 'move'])->name('move');
            Route::post('/swap', [TimetableSlotController::class, 'swap'])->name('swap');
            Route::post('/toggle-lock', [TimetableSlotController::class, 'toggleLock'])->name('toggle-lock');
            Route::post('/get-warnings', [TimetableSlotController::class, 'getWarnings'])->name('get-warnings');
            Route::get('/allocation-status', [TimetableSlotController::class, 'allocationStatus'])->name('allocation-status');
            Route::get('/teachers', [TimetableSlotController::class, 'teachers'])->name('teachers');
            Route::get('/subjects', [TimetableSlotController::class, 'subjects'])->name('subjects');
        });

        // Constraint Configuration routes (must be before /{timetable} wildcard)
        Route::prefix('constraints')->name('constraints.')->group(function () {
            Route::get('/{timetable}', [ConstraintController::class, 'index'])->name('index');
            Route::post('/teacher-availability', [ConstraintController::class, 'saveTeacherAvailability'])->name('save-teacher-availability');
            Route::post('/teacher-preference', [ConstraintController::class, 'saveTeacherPreference'])->name('save-teacher-preference');
            Route::post('/room-requirement', [ConstraintController::class, 'saveRoomRequirement'])->name('save-room-requirement');
            Route::post('/room-capacity', [ConstraintController::class, 'saveRoomCapacity'])->name('save-room-capacity');
            Route::post('/subject-spread', [ConstraintController::class, 'saveSubjectSpread'])->name('save-subject-spread');
            Route::post('/consecutive-limit', [ConstraintController::class, 'saveConsecutiveLimit'])->name('save-consecutive-limit');
            Route::post('/subject-pair', [ConstraintController::class, 'saveSubjectPair'])->name('save-subject-pair');
            Route::post('/period-restriction', [ConstraintController::class, 'savePeriodRestriction'])->name('save-period-restriction');
            Route::post('/teacher-room-assignment', [ConstraintController::class, 'saveTeacherRoomAssignment'])->name('save-teacher-room-assignment');
            Route::delete('/delete/{constraint}', [ConstraintController::class, 'deleteConstraint'])->name('delete');
        });

        // Generation routes (must be before /{timetable} wildcard)
        Route::prefix('generation')->name('generation.')->group(function () {
            Route::get('/settings', [GenerationController::class, 'settings'])->name('settings');
            Route::get('/documentation', [GenerationController::class, 'documentation'])->name('documentation')->middleware('can:view-system-admin');
            Route::get('/{timetable}', [GenerationController::class, 'index'])->name('index');
            Route::post('/generate', [GenerationController::class, 'generate'])->name('generate');
            Route::post('/parameters', [GenerationController::class, 'saveParameters'])->name('save-parameters');
            Route::post('/apply-profile', [GenerationController::class, 'applyProfile'])->name('apply-profile');
            Route::get('/{timetable}/status', [GenerationController::class, 'status'])->name('status');
            Route::post('/{timetable}/cancel', [GenerationController::class, 'cancel'])->name('cancel');
        });

        // Publishing workflow routes (must be before /{timetable} wildcard)
        Route::prefix('publishing')->name('publishing.')->group(function () {
            Route::post('/{timetable}/publish', [TimetableController::class, 'publish'])->name('publish');
            Route::post('/{timetable}/unpublish', [TimetableController::class, 'unpublish'])->name('unpublish');
            Route::get('/{timetable}/versions', [TimetableController::class, 'versions'])->name('versions');
            Route::post('/{timetable}/rollback/{version}', [TimetableController::class, 'rollback'])->name('rollback');
        });

        // Export routes (admin-only, must be before /{timetable} wildcard)
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/master/{timetable}/pdf', [TimetableExportController::class, 'masterPdf'])->name('master.pdf');
            Route::get('/master/{timetable}/excel', [TimetableExportController::class, 'masterExcel'])->name('master.excel');
            // Class and teacher Excel exports (admin-only)
            Route::get('/class/{timetable}/excel', [TimetableExportController::class, 'classExcel'])->name('class.excel');
            Route::get('/teacher/{timetable}/excel', [TimetableExportController::class, 'teacherExcel'])->name('teacher.excel');
        });

        // Timetable CRUD routes
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::post('/', [TimetableController::class, 'store'])->name('store');
        Route::get('/{timetable}', [TimetableController::class, 'show'])->name('show');
        Route::put('/{timetable}', [TimetableController::class, 'update'])->name('update');
        Route::delete('/{timetable}', [TimetableController::class, 'destroy'])->name('destroy');
    });
