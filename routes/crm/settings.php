<?php

use App\Http\Controllers\Crm\CommercialSettingController;
use App\Http\Controllers\Crm\ImportController;
use App\Http\Controllers\Crm\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::get('/settings/sales-stages', [SettingController::class, 'salesStages'])->name('settings.sales-stages');
Route::get('/settings/sales-stages/create', [SettingController::class, 'createSalesStage'])->name('settings.sales-stages.create');
Route::post('/settings/sales-stages', [SettingController::class, 'storeSalesStage'])->name('settings.sales-stages.store');
Route::get('/settings/sales-stages/{salesStage}/edit', [SettingController::class, 'editSalesStage'])->name('settings.sales-stages.edit');
Route::patch('/settings/sales-stages/{salesStage}', [SettingController::class, 'updateSalesStage'])->name('settings.sales-stages.update');
Route::delete('/settings/sales-stages/{salesStage}', [SettingController::class, 'destroySalesStage'])->name('settings.sales-stages.destroy');
Route::get('/settings/commercial', [CommercialSettingController::class, 'index'])->name('settings.commercial');
Route::patch('/settings/commercial', [CommercialSettingController::class, 'update'])->name('settings.commercial.update');
Route::get('/settings/company-information', [CommercialSettingController::class, 'companyInformation'])->name('settings.company-information');
Route::patch('/settings/company-information', [CommercialSettingController::class, 'updateCompanyInformation'])->name('settings.company-information.update');
Route::get('/settings/branding', [CommercialSettingController::class, 'branding'])->name('settings.branding');
Route::patch('/settings/branding', [CommercialSettingController::class, 'updateBranding'])->name('settings.branding.update');
Route::post('/settings/commercial/currencies', [CommercialSettingController::class, 'storeCurrency'])->name('settings.commercial.currencies.store');
Route::get('/settings/commercial/currencies/{currency}/edit', [CommercialSettingController::class, 'editCurrency'])->name('settings.commercial.edit-currency');
Route::patch('/settings/commercial/currencies/{currency}', [CommercialSettingController::class, 'updateCurrency'])->name('settings.commercial.currencies.update');
Route::get('/settings/imports', [ImportController::class, 'index'])->name('settings.imports');
Route::get('/settings/imports/users', [ImportController::class, 'users'])->name('settings.imports.users');
Route::get('/settings/imports/leads', [ImportController::class, 'leads'])->name('settings.imports.leads');
Route::get('/settings/imports/contacts', [ImportController::class, 'contacts'])->name('settings.imports.contacts');
Route::get('/settings/imports/templates/{entity}', [ImportController::class, 'downloadTemplate'])->name('settings.imports.templates.download');
Route::post('/settings/imports/preview', [ImportController::class, 'preview'])->name('settings.imports.preview');
Route::post('/settings/imports/{crmImportRun}/confirm', [ImportController::class, 'confirm'])->name('settings.imports.confirm');
Route::get('/settings/imports/runs/{crmImportRun}', [ImportController::class, 'showRun'])->name('settings.imports.runs.show');
Route::get('/settings/imports/runs/{crmImportRun}/failures', [ImportController::class, 'downloadFailures'])->name('settings.imports.runs.failures.download');
Route::get('/settings/imports/runs/{crmImportRun}/passwords', [ImportController::class, 'downloadPasswords'])->name('settings.imports.runs.passwords.download');
