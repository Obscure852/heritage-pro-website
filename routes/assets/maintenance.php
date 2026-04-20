<?php
use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('maintenance')->middleware(['auth', 'throttle:auth', 'block.non.african','can:view-system-admin'])->group(function () {
    Route::get('/list', [MaintenanceController::class, 'index'])->name('assets.maintenance.index');

    Route::get('/create/{id}', [MaintenanceController::class, 'createAssetMaintenance'])->name('assets.create-maintenance');
    Route::post('/store', [MaintenanceController::class, 'store'])->name('assets.store-asset-maintenance');
    Route::get('/edit/{asset}', [MaintenanceController::class, 'edit'])->name('assets.edit-maintenance');
    Route::put('/update/{asset}', [MaintenanceController::class, 'update'])->name('assets.update-maintenance');
    Route::delete('/destroy/{asset}', [MaintenanceController::class, 'destroy'])->name('assets.destroy-maintenance');

    Route::get('/new', [MaintenanceController::class, 'createWithSelect'])->name('assets.maintenance.create-with-select');

    Route::get('/complete/{id}', [MaintenanceController::class, 'completeForm'])->name('assets.maintenance-complete');
    Route::post('/complete/process/{id}', [MaintenanceController::class, 'complete'])->name('assets.maintenance-complete-process');
    Route::get('/terminate/{id}', [MaintenanceController::class, 'cancel'])->name('assets.maintenance-cancel');

    Route::get('/reports/scheduled', [MaintenanceController::class, 'scheduledMaintenanceReport'])->name('assets.scheduled-maintenance-report');
    Route::get('/reports/cost-analysis', [MaintenanceController::class, 'maintenanceCostAnalysis'])->name('assets.maintenance-cost-analysis');
    
});
