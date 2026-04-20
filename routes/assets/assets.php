<?php
use App\Http\Controllers\AssetManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('assets')->middleware(['auth', 'throttle:auth', 'block.non.african','can:view-system-admin'])->group(function () {
    Route::get('/list', [AssetManagementController::class, 'index'])->name('assets.index');

    Route::get('/asset-management/create', [AssetManagementController::class, 'createAsset'])->name('assets.create');
    Route::post('/asset-management', [AssetManagementController::class, 'store'])->name('assets.store');
    Route::get('/show-management/{asset}', [AssetManagementController::class, 'show'])->name('assets.show');
    Route::get('/asset-management/edit/{asset}', [AssetManagementController::class, 'edit'])->name('assets.edit');
    Route::put('/update-management/{asset}', [AssetManagementController::class, 'update'])->name('assets.update');
    Route::delete('/destroy-asset/{asset}', [AssetManagementController::class, 'destroy'])->name('assets.destroy');

    Route::get('/destroy-image/{asset}', [AssetManagementController::class, 'deleteImage'])->name('assets.destroy-image');
    Route::get('/destroy-document/{asset}', [AssetManagementController::class, 'deleteDocument'])->name('assets.destroy-document');

    Route::post('/asset-assignment', [AssetManagementController::class, 'assignAsset'])->name('assets.assign-asset');
    Route::get('/return-assignment/{id}', [AssetManagementController::class, 'returnForm'])->name('assets.return-asset');
    Route::post('/process-return', [AssetManagementController::class, 'processReturn'])->name('assets.process-return');

    Route::get('/assets-settings', [AssetManagementController::class, 'assetSettings'])->name('assets.settings');
    Route::post('/asset-categories', [AssetManagementController::class, 'storeCategory'])->name('asset-categories.store');
    Route::put('/asset-categories/{asset_category}', [AssetManagementController::class, 'updateCategory'])->name('asset-categories.update');
    Route::delete('/asset-categories/{asset_category}', [AssetManagementController::class, 'destroyCategory'])->name('asset-categories.destroy');

    Route::get('/reports/category', [AssetManagementController::class, 'assetCategoryReport'])->name('assets.category-report');
    Route::get('/reports/value', [AssetManagementController::class, 'assetValueReport'])->name('assets.value-report');
    Route::get('/reports/location', [AssetManagementController::class, 'assetLocationReport'])->name('assets.location-report');
    Route::get('/reports/status', [AssetManagementController::class, 'assetStatusReport'])->name('assets.status-report');
    Route::get('/reports/assignments', [AssetManagementController::class, 'assetAssignmentReport'])->name('assets.assignment-report');
    Route::get('/reports/maintenance', [AssetManagementController::class, 'assetMaintenanceReport'])->name('assets.maintenance-report');
    Route::get('/reports/utilization', [AssetManagementController::class, 'assetUtilizationReport'])->name('assets.utilization-report');

    Route::get('/template', [AssetManagementController::class, 'downloadTemplate'])->name('assets.import-download-template');
    Route::post('/process', [AssetManagementController::class, 'importAssets'])->name('assets.import-process');
});
