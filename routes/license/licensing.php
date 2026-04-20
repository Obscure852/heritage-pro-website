<?php
use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('license')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-setup'])->group(function () {
  Route::get('/license-expired', [LicenseController::class, 'expired'])->name('setup.license-expired');
  Route::post('/license/create', [LicenseController::class, 'createSchoolLicense'])->name('setup.create-school-license');
  Route::get('/license/tutorials', [LicenseController::class, 'tutorials'])->name('setup.video-tutorials');
});