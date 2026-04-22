<?php

use App\Http\Controllers\Crm\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
Route::get('/requests/sales', [RequestController::class, 'salesIndex'])->name('requests.sales.index');
Route::get('/requests/support', [RequestController::class, 'supportIndex'])->name('requests.support.index');
Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
Route::get('/requests/create/sales', [RequestController::class, 'createSales'])->name('requests.sales.create');
Route::get('/requests/create/support', [RequestController::class, 'createSupport'])->name('requests.support.create');
Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
Route::post('/requests/sales', [RequestController::class, 'storeSales'])->name('requests.sales.store');
Route::post('/requests/support', [RequestController::class, 'storeSupport'])->name('requests.support.store');
Route::get('/requests/{crmRequest}', [RequestController::class, 'show'])->name('requests.show');
Route::get('/requests/{crmRequest}/edit', [RequestController::class, 'edit'])->name('requests.edit');
Route::patch('/requests/{crmRequest}', [RequestController::class, 'update'])->name('requests.update');
Route::delete('/requests/{crmRequest}', [RequestController::class, 'destroy'])->name('requests.destroy');
Route::get('/requests/{crmRequest}/attachments/{requestAttachment}/open', [RequestController::class, 'openAttachment'])->name('requests.attachments.open');
Route::get('/requests/{crmRequest}/attachments/{requestAttachment}/download', [RequestController::class, 'downloadAttachment'])->name('requests.attachments.download');
Route::delete('/requests/{crmRequest}/attachments/{requestAttachment}', [RequestController::class, 'destroyAttachment'])->name('requests.attachments.destroy');
Route::post('/requests/{crmRequest}/activities', [RequestController::class, 'storeActivity'])->name('requests.activities.store');
