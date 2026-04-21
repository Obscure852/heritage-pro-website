<?php

use App\Http\Controllers\Crm\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
Route::get('/integrations/create', [IntegrationController::class, 'create'])->name('integrations.create');
Route::post('/integrations', [IntegrationController::class, 'store'])->name('integrations.store');
Route::get('/integrations/{integration}', [IntegrationController::class, 'show'])->name('integrations.show');
Route::get('/integrations/{integration}/edit', [IntegrationController::class, 'edit'])->name('integrations.edit');
Route::patch('/integrations/{integration}', [IntegrationController::class, 'update'])->name('integrations.update');
Route::delete('/integrations/{integration}', [IntegrationController::class, 'destroy'])->name('integrations.destroy');
