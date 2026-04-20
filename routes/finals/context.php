<?php

use App\Http\Controllers\FinalsContextController;

Route::prefix('finals/context')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    Route::get('/switch/{context}', [FinalsContextController::class, 'switch'])->name('finals.context.switch');
});
