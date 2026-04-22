<?php

use App\Http\Controllers\Crm\UserSettingController;
use App\Http\Controllers\Crm\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::patch('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
Route::post('/users/{user}/qualifications', [UserController::class, 'storeQualification'])->name('users.qualifications.store');
Route::patch('/users/{user}/qualifications/{qualification}', [UserController::class, 'updateQualification'])->name('users.qualifications.update');
Route::delete('/users/{user}/qualifications/{qualification}', [UserController::class, 'destroyQualification'])->name('users.qualifications.destroy');
Route::get('/users/{user}/qualifications/{qualification}/attachments/{attachment}/open', [UserController::class, 'openQualificationAttachment'])->name('users.qualifications.attachments.open');
Route::get('/users/{user}/qualifications/{qualification}/attachments/{attachment}/download', [UserController::class, 'downloadQualificationAttachment'])->name('users.qualifications.attachments.download');
Route::delete('/users/{user}/qualifications/{qualification}/attachments/{attachment}', [UserController::class, 'destroyQualificationAttachment'])->name('users.qualifications.attachments.destroy');
Route::post('/users/{user}/signatures', [UserController::class, 'storeSignature'])->name('users.signatures.store');
Route::patch('/users/{user}/signatures/{signature}/default', [UserController::class, 'setDefaultSignature'])->name('users.signatures.default');
Route::get('/users/{user}/signatures/{signature}/open', [UserController::class, 'openSignature'])->name('users.signatures.open');
Route::get('/users/{user}/signatures/{signature}/download', [UserController::class, 'downloadSignature'])->name('users.signatures.download');
Route::delete('/users/{user}/signatures/{signature}', [UserController::class, 'destroySignature'])->name('users.signatures.destroy');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::get('/users/settings', [UserSettingController::class, 'index'])->name('users.settings.index');
Route::get('/users/settings/departments', [UserSettingController::class, 'departments'])->name('users.settings.departments');
Route::post('/users/settings/departments', [UserSettingController::class, 'storeDepartment'])->name('users.settings.departments.store');
Route::patch('/users/settings/departments/{crmUserDepartment}', [UserSettingController::class, 'updateDepartment'])->name('users.settings.departments.update');
Route::delete('/users/settings/departments/{crmUserDepartment}', [UserSettingController::class, 'destroyDepartment'])->name('users.settings.departments.destroy');
Route::get('/users/settings/positions', [UserSettingController::class, 'positions'])->name('users.settings.positions');
Route::post('/users/settings/positions', [UserSettingController::class, 'storePosition'])->name('users.settings.positions.store');
Route::patch('/users/settings/positions/{crmUserPosition}', [UserSettingController::class, 'updatePosition'])->name('users.settings.positions.update');
Route::delete('/users/settings/positions/{crmUserPosition}', [UserSettingController::class, 'destroyPosition'])->name('users.settings.positions.destroy');
Route::get('/users/settings/custom-filters', [UserSettingController::class, 'customFilters'])->name('users.settings.filters');
Route::post('/users/settings/custom-filters', [UserSettingController::class, 'storeFilter'])->name('users.settings.filters.store');
Route::patch('/users/settings/custom-filters/{crmUserFilter}', [UserSettingController::class, 'updateFilter'])->name('users.settings.filters.update');
Route::delete('/users/settings/custom-filters/{crmUserFilter}', [UserSettingController::class, 'destroyFilter'])->name('users.settings.filters.destroy');
Route::get('/users/settings/company-information', fn () => redirect()->route('crm.settings.company-information'));
Route::get('/users/settings/branding', fn () => redirect()->route('crm.settings.branding'));
