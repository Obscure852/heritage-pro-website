<?php

use App\Http\Controllers\Crm\DevController;
use Illuminate\Support\Facades\Route;

Route::get('/dev', [DevController::class, 'index'])->name('dev.index');
Route::get('/dev/create', [DevController::class, 'create'])->name('dev.create');
Route::post('/dev', [DevController::class, 'store'])->name('dev.store');
Route::get('/dev/{developmentRequest}', [DevController::class, 'show'])->name('dev.show');
Route::get('/dev/{developmentRequest}/edit', [DevController::class, 'edit'])->name('dev.edit');
Route::patch('/dev/{developmentRequest}', [DevController::class, 'update'])->name('dev.update');
Route::delete('/dev/{developmentRequest}', [DevController::class, 'destroy'])->name('dev.destroy');
