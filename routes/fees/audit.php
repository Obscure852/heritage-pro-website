<?php

use App\Http\Controllers\Fee\FeeAuditController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fee Audit Routes
|--------------------------------------------------------------------------
|
| Routes for viewing audit trails of fee operations.
| Protected by 'view-fee-reports' gate.
|
| Route naming convention: fees.audit.*
|
*/

Route::prefix('fees/audit')->middleware(['auth', 'can:view-fee-reports'])->group(function () {
    // Invoice audit history
    Route::get('invoices/{invoice}/history', [FeeAuditController::class, 'invoiceHistory'])
        ->name('fees.audit.invoice-history');

    // Payment audit history
    Route::get('payments/{payment}/history', [FeeAuditController::class, 'paymentHistory'])
        ->name('fees.audit.payment-history');
});
