<?php

use App\Http\Controllers\FinalsHouseController;

Route::prefix('finals/houses')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    // Static routes
    Route::get('/', [FinalsHouseController::class, 'index'])->name('finals.houses.index');
    Route::get('/partial', [FinalsHouseController::class, 'getData'])->name('finals.houses.partial');
    Route::get('/badge-data', [FinalsHouseController::class, 'getBadgeData'])->name('finals.houses.badge-data');
    
    // Report routes (must come before wildcard routes if any)
    Route::get('/houses/class-analysis', [FinalsHouseController::class, 'overallHouseClassAnalysis'])->name('finals.houses.houses-class-analysis');
    Route::get('/houses/performance-analysis', [FinalsHouseController::class, 'housePerformanceAnalysis'])->name('finals.houses.performance-analysis');
    Route::get('/exam/overall/houses', [FinalsHouseController::class, 'generateOverallExamHousePerformanceReport'])
        ->name('finals.houses.exam-houses-overall-analysis');
});
