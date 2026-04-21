<?php

use App\Http\Controllers\Crm\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::get('/settings/sales-stages', [SettingController::class, 'salesStages'])->name('settings.sales-stages');
Route::get('/settings/sales-stages/create', [SettingController::class, 'createSalesStage'])->name('settings.sales-stages.create');
Route::post('/settings/sales-stages', [SettingController::class, 'storeSalesStage'])->name('settings.sales-stages.store');
Route::get('/settings/sales-stages/{salesStage}/edit', [SettingController::class, 'editSalesStage'])->name('settings.sales-stages.edit');
Route::patch('/settings/sales-stages/{salesStage}', [SettingController::class, 'updateSalesStage'])->name('settings.sales-stages.update');
Route::delete('/settings/sales-stages/{salesStage}', [SettingController::class, 'destroySalesStage'])->name('settings.sales-stages.destroy');
