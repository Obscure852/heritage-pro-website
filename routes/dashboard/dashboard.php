<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
  Route::get('/', [HomeController::class, 'root'])->name('dashboard');
  Route::get('/term/data', [HomeController::class, 'getDashboardTermData'])->name('dashboard.dashboard-get-data');
  Route::get('/search', [SearchController::class, 'search'])->name('search');
});
