<?php

use App\Http\Controllers\Crm\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
Route::get('/requests/{crmRequest}', [RequestController::class, 'show'])->name('requests.show');
Route::get('/requests/{crmRequest}/edit', [RequestController::class, 'edit'])->name('requests.edit');
Route::patch('/requests/{crmRequest}', [RequestController::class, 'update'])->name('requests.update');
Route::delete('/requests/{crmRequest}', [RequestController::class, 'destroy'])->name('requests.destroy');
Route::post('/requests/{crmRequest}/activities', [RequestController::class, 'storeActivity'])->name('requests.activities.store');
