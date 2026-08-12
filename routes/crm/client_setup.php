<?php

use App\Http\Controllers\Crm\ClientSetupController;
use Illuminate\Support\Facades\Route;

Route::get('/client-setup', [ClientSetupController::class, 'index'])->name('client-setup.index');
Route::get('/client-setup/create', [ClientSetupController::class, 'create'])->name('client-setup.create');
Route::post('/client-setup', [ClientSetupController::class, 'store'])->name('client-setup.store');
Route::delete('/client-setup/{submission}', [ClientSetupController::class, 'destroy'])->name('client-setup.destroy');
Route::get('/client-setup/{submission}', [ClientSetupController::class, 'show'])->name('client-setup.show');
Route::post('/client-setup/{submission}/resend', [ClientSetupController::class, 'resend'])->name('client-setup.resend');
Route::patch('/client-setup/{submission}/assignment', [ClientSetupController::class, 'assignment'])->name('client-setup.assignment');
Route::patch('/client-setup/{submission}/status', [ClientSetupController::class, 'status'])->name('client-setup.status');
Route::post('/client-setup/{submission}/notes', [ClientSetupController::class, 'storeNote'])->name('client-setup.notes.store');
Route::post('/client-setup/{submission}/change-requests', [ClientSetupController::class, 'storeChangeRequest'])->name('client-setup.change-requests.store');
Route::patch('/client-setup/{submission}/change-requests/{changeRequest}', [ClientSetupController::class, 'resolveChangeRequest'])->name('client-setup.change-requests.resolve');
Route::patch('/client-setup/{submission}/migration-uploads/{migrationUpload}/approve', [ClientSetupController::class, 'approveMigrationUpload'])->name('client-setup.migration-uploads.approve');
Route::get('/client-setup/{submission}/migration-uploads/{migrationUpload}/validation-report', [ClientSetupController::class, 'downloadMigrationValidationReport'])->name('client-setup.migration-uploads.validation-report');
Route::post('/client-setup/{submission}/migration-uploads/{migrationUpload}/import', [ClientSetupController::class, 'importMigrationUpload'])->name('client-setup.migration-uploads.import');
Route::get('/client-setup/{submission}/attachments/{attachment}/download', [ClientSetupController::class, 'downloadAttachment'])->name('client-setup.attachment.download');
Route::get('/client-setup/{submission}/revisions/compare', [ClientSetupController::class, 'compareRevisions'])->name('client-setup.revisions.compare');
Route::get('/client-setup/{submission}/print', [ClientSetupController::class, 'print'])->name('client-setup.print');
