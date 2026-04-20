<?php

use App\Http\Controllers\Fee\FeeRefundController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fee Refund Routes
|--------------------------------------------------------------------------
|
| Routes for managing fee refunds and credit notes.
| Protected by appropriate permissions.
|
*/

Route::prefix('fees/refunds')->middleware(['auth'])->group(function () {
    // ========================================
    // Refund Listing
    // ========================================
    Route::get('/', [FeeRefundController::class, 'index'])
        ->name('fees.refunds.index')
        ->middleware('can:view-refunds');

    Route::get('/pending', [FeeRefundController::class, 'pending'])
        ->name('fees.refunds.pending')
        ->middleware('can:approve-refunds');

    // ========================================
    // Create Refund/Credit Note
    // ========================================
    Route::get('/payment/{payment}/create', [FeeRefundController::class, 'createFromPayment'])
        ->name('fees.refunds.create')
        ->middleware('can:request-refunds');

    Route::get('/invoice/{invoice}/credit-note', [FeeRefundController::class, 'createCreditNote'])
        ->name('fees.refunds.credit-note.create')
        ->middleware('can:request-refunds');

    Route::post('/', [FeeRefundController::class, 'store'])
        ->name('fees.refunds.store')
        ->middleware(['can:request-refunds', 'fee.historical-lock']);

    Route::post('/credit-note', [FeeRefundController::class, 'storeCreditNote'])
        ->name('fees.refunds.credit-note.store')
        ->middleware(['can:request-refunds', 'fee.historical-lock']);

    // ========================================
    // View Refund
    // ========================================
    Route::get('/{refund}', [FeeRefundController::class, 'show'])
        ->name('fees.refunds.show')
        ->middleware('can:view-refunds');

    Route::get('/{refund}/print', [FeeRefundController::class, 'print'])
        ->name('fees.refunds.print')
        ->middleware('can:view-refunds');

    // ========================================
    // Refund Approval Workflow
    // ========================================
    Route::post('/{refund}/approve', [FeeRefundController::class, 'approve'])
        ->name('fees.refunds.approve')
        ->middleware(['can:approve-refunds', 'fee.historical-lock']);

    Route::post('/{refund}/reject', [FeeRefundController::class, 'reject'])
        ->name('fees.refunds.reject')
        ->middleware(['can:approve-refunds', 'fee.historical-lock']);

    Route::post('/{refund}/process', [FeeRefundController::class, 'process'])
        ->name('fees.refunds.process')
        ->middleware(['can:process-refunds', 'fee.historical-lock']);
});
