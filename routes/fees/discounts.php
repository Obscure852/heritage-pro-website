<?php

use App\Http\Controllers\Fee\StudentDiscountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Discount Routes
|--------------------------------------------------------------------------
|
| Routes for managing student discount assignments.
|
*/

Route::prefix('fees/discounts')->middleware(['auth', 'can:manage-fee-setup'])->group(function () {
    Route::get('/', [StudentDiscountController::class, 'index'])
        ->name('fees.discounts.index');

    Route::get('/assign', [StudentDiscountController::class, 'create'])
        ->name('fees.discounts.create');

    Route::post('/', [StudentDiscountController::class, 'store'])
        ->name('fees.discounts.store');

    Route::delete('/{studentDiscount}', [StudentDiscountController::class, 'destroy'])
        ->name('fees.discounts.destroy');

    // API endpoint for sibling lookup
    Route::get('/students/{student}/siblings', [StudentDiscountController::class, 'getSiblings'])
        ->name('fees.discounts.siblings');
});
