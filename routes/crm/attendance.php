<?php

use App\Http\Controllers\Crm\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')
    ->name('attendance.')
    ->group(function () {
        Route::get('/my', function () {
            return redirect()->route('crm.dashboard');
        })->name('my');
        Route::post('/clock', [AttendanceController::class, 'clock'])->name('clock');
        Route::get('/clock-status', [AttendanceController::class, 'clockStatus'])->name('clock-status');
        Route::get('/grid', [AttendanceController::class, 'grid'])->name('grid');
        Route::get('/records/{record}', [AttendanceController::class, 'recordShow'])->name('records.show');
        Route::put('/records/{record}', [AttendanceController::class, 'recordUpdate'])->name('records.update');
        Route::post('/records/{record}/correction', [AttendanceController::class, 'submitCorrection'])->name('records.correction');
        Route::put('/corrections/{correction}/review', [AttendanceController::class, 'reviewCorrection'])->name('corrections.review');
        Route::get('/corrections/pending', [AttendanceController::class, 'pendingCorrections'])->name('corrections.pending');

        Route::get('/reports', [\App\Http\Controllers\Crm\AttendanceReportController::class, 'index'])->name('reports');
        Route::get('/reports/{type}', [\App\Http\Controllers\Crm\AttendanceReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{type}/export', [\App\Http\Controllers\Crm\AttendanceReportController::class, 'export'])->name('reports.export');
    });
