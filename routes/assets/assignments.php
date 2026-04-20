<?php

use App\Http\Controllers\AssetManagementController;
use App\Http\Controllers\AssignmentsController;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\Route;

Route::prefix('assignments')->middleware(['auth', 'throttle:auth', 'block.non.african','can:view-system-admin'])->group(function () {
    Route::get('/list', [AssignmentsController::class, 'index'])->name('assets.assignments.index');

    Route::get('/create/{id}', [AssignmentsController::class, 'createAssetMaintenance'])->name('assets.create-assignment');
    Route::post('/asset/assign', [AssetManagementController::class, 'assignAsset'])->name('assets.store-assignment');
    Route::get('/{assignment}', [AssignmentsController::class, 'show'])->name('assets.show-assignment');

    Route::get('user/{userId}', [AssignmentsController::class, 'userAssignments'])->name('assets.show-user-assignments');

    Route::get('/edit/{asset}', [AssignmentsController::class, 'edit'])->name('assets.edit-assignment');
    Route::put('/update/{asset}', [AssignmentsController::class, 'update'])->name('assets.update-assignment');
    Route::get('/destroy/{asset}', [AssignmentsController::class, 'destroy'])->name('assets.destroy-assignment');

    Route::get('/current/report', [AssignmentsController::class, 'currentAssignmentsReport'])->name('assets.current-assignments-report');
    Route::get('/history/report', [AssignmentsController::class, 'assignmentHistoryReport'])->name('assets.assignment-history-report');
    Route::get('/users/report', [AssignmentsController::class, 'assignmentsByUserReport'])->name('assets.assignments-by-user-report');


});
