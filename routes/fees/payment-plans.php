<?php

use App\Http\Controllers\Fee\PaymentPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Plans Routes
|--------------------------------------------------------------------------
|
| Routes for creating and managing payment plans (installment schedules).
| Protected by 'collect-fees' gate.
|
*/

Route::prefix('fees/payment-plans')->middleware(['auth'])->group(function () {
    // ========================================
    // Payment Plans List
    // ========================================
    Route::get('/', [PaymentPlanController::class, 'index'])
        ->name('fees.payment-plans.index')
        ->middleware('can:collect-fees');

    // ========================================
    // Create Payment Plan
    // ========================================
    Route::get('/create/{invoice}', [PaymentPlanController::class, 'create'])
        ->name('fees.payment-plans.create')
        ->middleware('can:collect-fees');

    Route::post('/preview', [PaymentPlanController::class, 'preview'])
        ->name('fees.payment-plans.preview')
        ->middleware('can:collect-fees');

    Route::post('/', [PaymentPlanController::class, 'store'])
        ->name('fees.payment-plans.store')
        ->middleware('can:collect-fees');

    // ========================================
    // View Payment Plan
    // ========================================
    Route::get('/{paymentPlan}', [PaymentPlanController::class, 'show'])
        ->name('fees.payment-plans.show')
        ->middleware('can:collect-fees');

    // ========================================
    // Cancel Payment Plan
    // ========================================
    Route::post('/{paymentPlan}/cancel', [PaymentPlanController::class, 'cancel'])
        ->name('fees.payment-plans.cancel')
        ->middleware('can:manage-fee-setup');
});
