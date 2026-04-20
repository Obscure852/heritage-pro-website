<?php

use App\Http\Controllers\Fee\BalanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Balance Management Routes
|--------------------------------------------------------------------------
|
| Routes for balance management, clearance status, and override functionality.
| Protected by appropriate fee gates.
|
*/

Route::prefix('fees/balance')->middleware(['auth'])->group(function () {
    // Outstanding students list
    Route::get('outstanding', [BalanceController::class, 'outstandingStudents'])
        ->name('fees.balance.outstanding');

    // Student clearance status
    Route::get('clearance/{student}', [BalanceController::class, 'clearanceStatus'])
        ->name('fees.balance.clearance');

    // Grant clearance override (POST)
    Route::post('override/grant', [BalanceController::class, 'grantOverride'])
        ->name('fees.balance.override.grant')
        ->middleware('can:manage-fee-setup');

    // Revoke clearance override (POST)
    Route::post('override/revoke/{student}/{year}', [BalanceController::class, 'revokeOverride'])
        ->name('fees.balance.override.revoke')
        ->middleware('can:manage-fee-setup');
});
