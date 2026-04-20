<?php

use App\Http\Controllers\ExternalResultsImportController;
use App\Http\Controllers\PdfToExcelConverterController;

Route::prefix('finals/import')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    Route::get('/results', [ExternalResultsImportController::class, 'showImportForm'])->name('finals.import.external-results-import');
    Route::post('/import-external-results', [ExternalResultsImportController::class, 'externalExamImport'])->name('finals.import.upload-external-results');
    Route::post('/create-performance-targets', [ExternalResultsImportController::class, 'storePerformanceTargets'])->name('finals.performance-targets.store-target');
    Route::get('/get-performance-targets', [ExternalResultsImportController::class, 'getPerformanceTargets'])->name('finals.performance-targets.get-target');
    Route::post('/subject-mappings', [ExternalResultsImportController::class, 'storeSubjectMappings'])->name('finals.import.subject-mappings.store');
    Route::post('/convert', [PdfToExcelConverterController::class, 'convertPdfToExcel'])->name('finals.import.convert-to-excel');
    Route::get('/sample', [PdfToExcelConverterController::class, 'downloadSample'])->name('finals.import.download-sample');
});
