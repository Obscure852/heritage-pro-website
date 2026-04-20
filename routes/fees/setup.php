<?php

use App\Http\Controllers\Fee\FeeSetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fee Setup Routes
|--------------------------------------------------------------------------
|
| Routes for managing fee types and fee structures.
| These routes are used by the accountant for fee configuration.
|
*/

Route::prefix('fees/setup')->middleware(['auth', 'can:manage-fee-setup'])->group(function () {
    // ========================================
    // Main Setup Page (Tabbed)
    // ========================================
    Route::get('/', [FeeSetupController::class, 'index'])
        ->name('fees.setup.index');

    // ========================================
    // Fee Types
    // ========================================
    Route::get('fee-types', [FeeSetupController::class, 'indexTypes'])
        ->name('fees.setup.types.index');

    Route::get('fee-types/create', [FeeSetupController::class, 'createType'])
        ->name('fees.setup.types.create');

    Route::post('fee-types', [FeeSetupController::class, 'storeType'])
        ->name('fees.setup.types.store');

    Route::get('fee-types/{feeType}/edit', [FeeSetupController::class, 'editType'])
        ->name('fees.setup.types.edit');

    Route::put('fee-types/{feeType}', [FeeSetupController::class, 'updateType'])
        ->name('fees.setup.types.update');

    Route::delete('fee-types/{feeType}', [FeeSetupController::class, 'destroyType'])
        ->name('fees.setup.types.destroy');

    // ========================================
    // Fee Structures
    // ========================================
    Route::get('fee-structures', [FeeSetupController::class, 'indexStructures'])
        ->name('fees.setup.structures.index');

    Route::get('fee-structures/create', [FeeSetupController::class, 'createStructure'])
        ->name('fees.setup.structures.create');

    Route::post('fee-structures', [FeeSetupController::class, 'storeStructure'])
        ->name('fees.setup.structures.store');

    Route::get('fee-structures/{feeStructure}/edit', [FeeSetupController::class, 'editStructure'])
        ->name('fees.setup.structures.edit');

    Route::put('fee-structures/{feeStructure}', [FeeSetupController::class, 'updateStructure'])
        ->name('fees.setup.structures.update');

    Route::delete('fee-structures/{feeStructure}', [FeeSetupController::class, 'destroyStructure'])
        ->name('fees.setup.structures.destroy');

    Route::post('fee-structures/copy', [FeeSetupController::class, 'copyStructures'])
        ->name('fees.setup.structures.copy');

    // ========================================
    // Discount Types
    // ========================================
    Route::get('discount-types', [FeeSetupController::class, 'indexDiscountTypes'])
        ->name('fees.setup.discount-types.index');

    Route::get('discount-types/create', [FeeSetupController::class, 'createDiscountType'])
        ->name('fees.setup.discount-types.create');

    Route::post('discount-types', [FeeSetupController::class, 'storeDiscountType'])
        ->name('fees.setup.discount-types.store');

    Route::get('discount-types/{discountType}/edit', [FeeSetupController::class, 'editDiscountType'])
        ->name('fees.setup.discount-types.edit');

    Route::put('discount-types/{discountType}', [FeeSetupController::class, 'updateDiscountType'])
        ->name('fees.setup.discount-types.update');

    Route::delete('discount-types/{discountType}', [FeeSetupController::class, 'destroyDiscountType'])
        ->name('fees.setup.discount-types.destroy');

    // ========================================
    // Payment Methods
    // ========================================
    Route::post('payment-methods', [FeeSetupController::class, 'updatePaymentMethod'])
        ->name('fees.setup.payment-methods.update');

    // ========================================
    // General Settings
    // ========================================
    Route::post('settings', [FeeSetupController::class, 'updateSettings'])
        ->name('fees.setup.settings.update');

    // ========================================
    // Audit Trail (AJAX)
    // ========================================
    Route::get('audit-logs', [FeeSetupController::class, 'getAuditLogs'])
        ->name('fees.setup.audit-logs');
});
