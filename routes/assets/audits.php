<?php 
use App\Http\Controllers\AssetAuditController; 
use Illuminate\Support\Facades\Route;  

Route::prefix('audits')->middleware(['auth', 'throttle:auth', 'block.non.african','can:view-system-admin'])->group(function () {     
    
    Route::get('/', [AssetAuditController::class, 'index'])->name('audits.index');
    Route::get('/create', [AssetAuditController::class, 'create'])->name('audits.create');
    Route::post('/store', [AssetAuditController::class, 'store'])->name('audits.store');
    
    Route::get('/trend-analysis', [AssetAuditController::class, 'trendAnalysis'])->name('audits.trend-analysis');
    Route::get('/comparison-report', [AssetAuditController::class, 'comparisonReport'])->name('audits.comparison-report');
    Route::get('/performance-dashboard', [AssetAuditController::class, 'performanceDashboard'])->name('audits.performance-dashboard');
    
    Route::get('show/{id}', [AssetAuditController::class, 'show'])->name('audits.show');
    Route::get('edit/{id}', [AssetAuditController::class, 'edit'])->name('audits.edit');
    Route::put('update/{id}', [AssetAuditController::class, 'update'])->name('audits.update');
    Route::delete('destroy/{id}', [AssetAuditController::class, 'destroy'])->name('audits.destroy');
    
    Route::get('start/{id}', [AssetAuditController::class, 'start'])->name('audits.start');
    Route::get('/{id}/conduct', [AssetAuditController::class, 'conduct'])->name('audits.conduct');
    Route::post('/{auditId}/verify/{itemId}', [AssetAuditController::class, 'verifyAsset'])->name('audits.verify-asset');
    Route::post('/{id}/complete', [AssetAuditController::class, 'complete'])->name('audits.complete');
    
    Route::get('/{id}/missing-report', [AssetAuditController::class, 'missingAssetsReport'])->name('audits.missing-report');
    Route::get('/{id}/maintenance-report', [AssetAuditController::class, 'maintenanceReport'])->name('audits.maintenance-report');
    Route::get('/{id}/condition-report', [AssetAuditController::class, 'conditionReport'])->name('audits.condition-report');
    Route::get('/{id}/location-report', [AssetAuditController::class, 'locationReport'])->name('audits.location-report');
    Route::get('/{id}/financial-report', [AssetAuditController::class, 'financialReport'])->name('audits.financial-report');
    
    Route::get('/{id}/summary', [AssetAuditController::class, 'showAuditSummary'])->name('audits.summary');
    Route::get('/{id}/export', [AssetAuditController::class, 'export'])->name('audits.export');
    
    Route::post('/{id}/add-assets', [AssetAuditController::class, 'addAssets'])->name('audits.add-assets');
    Route::delete('/{auditId}/remove-asset/{assetId}', [AssetAuditController::class, 'removeAsset'])->name('audits.remove-asset');
    
    Route::put('/audit-items/{auditItemId}/update', [AssetAuditController::class, 'updateAuditItem'])->name('audits.update-audit-item');
    Route::get('/{auditId}/progress', [AssetAuditController::class, 'getProgress'])->name('audits.get-progress');
    Route::get('/{auditId}/cancel', [AssetAuditController::class, 'cancelAuditController'])->name('audits.cancel');
    Route::get('/{auditId}/export-report', [AssetAuditController::class, 'exportReport'])->name('audits.export-report');
    Route::get('/{auditId}/print-report', [AssetAuditController::class, 'printReport'])->name('audits.print-report');
});