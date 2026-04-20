<?php
use App\Http\Controllers\LoggingController;
use Illuminate\Support\Facades\Route;

Route::prefix('logs')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-setup'])->group(function () {
  Route::post('/clear', [LoggingController::class, 'clearOldLogs'])->name('logs.clear');
  Route::get('/tutorials', [LoggingController::class, 'tutorials'])->name('logs.tutorials');
  Route::post('/api-tokens', [LoggingController::class, 'store'])->name('logs.api-tokens.store');
  Route::delete('/api-tokens/{token}', [LoggingController::class, 'destroy'])->name('logs.api-tokens.destroy');
  Route::get('/', [LoggingController::class, 'index'])->name('logs.index');
});
