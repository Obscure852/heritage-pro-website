<?php
use App\Http\Controllers\SponsorController;
use Illuminate\Support\Facades\Route;


Route::prefix('sponsors')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {

  Route::get('/new', [SponsorController::class, 'create'])->name('sponsors.sponsor-new');
  Route::post('/create', [SponsorController::class, 'store'])->name('sponsors.sponsor-store');
  Route::post('/create/update/{sponsorId}', [SponsorController::class, 'storeOrUpdate'])->name('sponsors.update-or-create');

  Route::get('/edit/{id}', [SponsorController::class, 'edit'])->name('sponsors.sponsor-edit');
  Route::post('/update/{id}', [SponsorController::class, 'update'])->name('sponsors.sponsor-update');

  Route::get('/delete/{id}', [SponsorController::class, 'destroy'])->name('sponsors.delete-sponsor');
  Route::get('/contact/information', [SponsorController::class, 'sponsorsContactsDetails'])->name('sponsors.sponsors-contact-details');
  Route::get('/analysis/list', [SponsorController::class, 'sponsorsAnalyisList'])->name('sponsors.analysis-list');
  Route::get('/analysis/students', [SponsorController::class, 'sponsorsChildrenList'])->name('sponsors.sponsors-students-list');

  Route::get('/export/analysis', [SponsorController::class, 'sponsorsAnalyisListExport'])->name('sponsors.sponsors-export-list');
  Route::get('/export/contacts', [SponsorController::class, 'sponsorsContactsDetailsExport'])->name('sponsors.sponsors-export-contact-list');
  Route::get('/import/report', [SponsorController::class, 'getSponsorsByStudentTermReport'])->name('sponsors.import-list-report');


  Route::get('/settings', [SponsorController::class, 'filterList'])->name('sponsors.sponsors-settings');
  Route::post('/filters/store', [SponsorController::class, 'storeFilter'])->name('sponsors.store-filter');
  Route::put('/update/filters/{id}', [SponsorController::class, 'updateFilter'])->name('sponsors.update-filter');
  Route::delete('delete/filters/{id}', [SponsorController::class, 'destroyFilter'])->name('sponsors.destroy-filter');

  Route::get('/', [SponsorController::class, 'index'])->name('sponsors.index');
});
