<?php

use App\Http\Controllers\Fee\FeeReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fee Reports Routes
|--------------------------------------------------------------------------
|
| Routes for fee reports and analytics dashboard.
| Protected by 'view-fee-reports' gate.
|
| Route naming convention: fees.reports.*
|
*/

Route::prefix('fees/reports')->middleware(['auth', 'can:view-fee-reports'])->group(function () {
    // ========================================
    // Dashboard
    // ========================================
    Route::get('/', [FeeReportController::class, 'dashboard'])
        ->name('fees.reports.dashboard');

    // ========================================
    // Collection Reports
    // ========================================
    Route::get('collection-summary', [FeeReportController::class, 'collectionSummary'])
        ->name('fees.reports.collection-summary');

    Route::get('collector-performance', [FeeReportController::class, 'collectorPerformance'])
        ->name('fees.reports.collector-performance');

    // ========================================
    // Student Statement
    // ========================================
    Route::get('student-statement', [FeeReportController::class, 'studentStatement'])
        ->name('fees.reports.student-statement');

    Route::get('student-statement/{student}/pdf', [FeeReportController::class, 'studentStatementPdf'])
        ->name('fees.reports.student-statement.pdf');

    Route::get('search-student', [FeeReportController::class, 'searchStudent'])
        ->name('fees.reports.search-student');

    // ========================================
    // Outstanding Balances Reports
    // ========================================
    Route::get('outstanding-by-grade', [FeeReportController::class, 'outstandingByGrade'])
        ->name('fees.reports.outstanding-by-grade');

    Route::get('aging-report', [FeeReportController::class, 'agingReport'])
        ->name('fees.reports.aging-report');

    Route::get('debtors-list', [FeeReportController::class, 'debtorsList'])
        ->name('fees.reports.debtors-list');

    // ========================================
    // Analytics Reports
    // ========================================
    Route::get('payment-trends', [FeeReportController::class, 'paymentTrends'])
        ->name('fees.reports.payment-trends');

    // ========================================
    // Daily Operations Reports
    // ========================================
    Route::get('daily-collections', [FeeReportController::class, 'dailyCollections'])
        ->name('fees.reports.daily-collections');

    Route::get('end-of-day', [FeeReportController::class, 'endOfDayReport'])
        ->name('fees.reports.end-of-day');

    Route::get('end-of-day/pdf', [FeeReportController::class, 'endOfDayReportPdf'])
        ->name('fees.reports.end-of-day.pdf');
});

/*
|--------------------------------------------------------------------------
| Excel Export Routes
|--------------------------------------------------------------------------
|
| Routes for exporting fee reports to Excel.
| Protected by 'export-fee-reports' gate.
|
| Route naming convention: fees.reports.export.*
|
*/

Route::prefix('fees/reports/export')->middleware(['auth', 'can:export-fee-reports'])->group(function () {
    Route::get('collection-summary', [FeeReportController::class, 'exportCollectionSummary'])
        ->name('fees.reports.export.collection-summary');

    Route::get('outstanding-by-grade', [FeeReportController::class, 'exportOutstandingByGrade'])
        ->name('fees.reports.export.outstanding-by-grade');

    Route::get('aging-report', [FeeReportController::class, 'exportAgingReport'])
        ->name('fees.reports.export.aging-report');

    Route::get('debtors-list', [FeeReportController::class, 'exportDebtorsList'])
        ->name('fees.reports.export.debtors-list');

    Route::get('collector-performance', [FeeReportController::class, 'exportCollectorPerformance'])
        ->name('fees.reports.export.collector-performance');

    Route::get('student-statement/{student}', [FeeReportController::class, 'exportStudentStatement'])
        ->name('fees.reports.export.student-statement');

    Route::get('daily-collections', [FeeReportController::class, 'exportDailyCollections'])
        ->name('fees.reports.export.daily-collections');
});
