<?php

use App\Http\Controllers\AssetDisposalController;
use Illuminate\Support\Facades\Route;

Route::prefix('disposals')->middleware(['auth', 'throttle:auth', 'block.non.african','can:view-system-admin'])->group(function () {
    Route::get('/list', [AssetDisposalController::class, 'index'])->name('disposals.index');

    Route::get('/create/{assetId?}', [AssetDisposalController::class, 'create'])->name('disposals.create');
    Route::post('/asset/dispose', [AssetDisposalController::class, 'store'])->name('disposals.store');

    Route::get('/edit/{disposal}', [AssetDisposalController::class, 'edit'])->name('disposals.edit');
    Route::put('/update/{disposal}', [AssetDisposalController::class, 'update'])->name('disposals.update');
    Route::delete('/destroy/{disposal}', [AssetDisposalController::class, 'destroy'])->name('disposals.destroy');

    Route::get('/summary', [AssetDisposalController::class, 'disposalSummaryReport'])->name('disposals.summary-report');
    Route::get('/by-date-status', [AssetDisposalController::class, 'disposalByDateAndStatusReport'])->name('disposals.by-date-report');
});
