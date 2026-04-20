<?php

use App\Http\Controllers\Fee\FeeCollectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fee Collection Routes
|--------------------------------------------------------------------------
|
| Routes for invoice generation, viewing, and student fee account management.
| Protected by 'collect-fees' gate.
|
*/

Route::prefix('fees/collection')->middleware(['auth'])->group(function () {
    // ========================================
    // Student Search (AJAX)
    // ========================================
    Route::get('students/search', [FeeCollectionController::class, 'searchStudent'])
        ->name('fees.collection.students.search')
        ->middleware('can:collect-fees');

    // ========================================
    // Student Account
    // ========================================
    Route::get('students/{student}/account', [FeeCollectionController::class, 'studentAccount'])
        ->name('fees.collection.students.account')
        ->middleware('can:collect-fees');

    // ========================================
    // Invoices
    // ========================================
    Route::get('invoices', [FeeCollectionController::class, 'indexInvoices'])
        ->name('fees.collection.invoices.index');

    Route::get('invoices/create', [FeeCollectionController::class, 'createInvoice'])
        ->name('fees.collection.invoices.create');

    Route::post('invoices', [FeeCollectionController::class, 'storeInvoice'])
        ->name('fees.collection.invoices.store');

    Route::get('invoices/bulk', [FeeCollectionController::class, 'bulkInvoices'])
        ->name('fees.collection.invoices.bulk');

    Route::post('invoices/bulk', [FeeCollectionController::class, 'storeBulkInvoices'])
        ->name('fees.collection.invoices.bulk.store');

    Route::get('invoices/bulk/progress', [FeeCollectionController::class, 'bulkInvoiceProgress'])
        ->name('fees.collection.invoices.bulk.progress');

    Route::post('invoices/bulk/cancel', [FeeCollectionController::class, 'cancelBulkInvoices'])
        ->name('fees.collection.invoices.bulk.cancel');

    Route::get('invoices/{invoice}', [FeeCollectionController::class, 'showInvoice'])
        ->name('fees.collection.invoices.show');

    Route::post('invoices/{invoice}/cancel', [FeeCollectionController::class, 'cancelInvoice'])
        ->name('fees.collection.invoices.cancel')
        ->middleware(['can:manage-fee-setup', 'fee.historical-lock']);

    Route::post('invoices/{invoice}/recalculate', [FeeCollectionController::class, 'recalculateInvoice'])
        ->name('fees.collection.invoices.recalculate')
        ->middleware('can:collect-fees');

    // PDF route
    Route::get('invoices/{invoice}/pdf', [FeeCollectionController::class, 'printInvoice'])
        ->name('fees.collection.invoices.pdf');

    // ========================================
    // Payments
    // ========================================
    Route::get('payments/{invoice}/create', [FeeCollectionController::class, 'createPayment'])
        ->name('fees.collection.payments.create');

    Route::post('payments', [FeeCollectionController::class, 'storePayment'])
        ->name('fees.collection.payments.store');

    Route::get('payments/{payment}', [FeeCollectionController::class, 'showPayment'])
        ->name('fees.collection.payments.show');

    Route::post('payments/{payment}/void', [FeeCollectionController::class, 'voidPayment'])
        ->name('fees.collection.payments.void')
        ->middleware(['can:void-payments', 'fee.historical-lock']);

    Route::get('payments/{payment}/receipt', [FeeCollectionController::class, 'printReceipt'])
        ->name('fees.collection.payments.receipt');
});
