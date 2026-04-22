<?php

use App\Http\Controllers\Crm\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/onboarding/profile', [OnboardingController::class, 'editProfile'])->name('onboarding.profile');
Route::patch('/onboarding/profile', [OnboardingController::class, 'updateProfile'])->name('onboarding.profile.update');
Route::post('/onboarding/profile/skip', [OnboardingController::class, 'skipProfile'])->name('onboarding.profile.skip');
Route::get('/onboarding/work', [OnboardingController::class, 'editWork'])->name('onboarding.work');
Route::patch('/onboarding/work', [OnboardingController::class, 'updateWork'])->name('onboarding.work.update');
Route::post('/onboarding/work/skip', [OnboardingController::class, 'skipWork'])->name('onboarding.work.skip');
