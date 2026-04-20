<?php

use App\Http\Controllers\SponsorAuth\SponsorLoginController;
use App\Http\Controllers\SponsorAuth\SponsorForgotPasswordController;
use App\Http\Controllers\SponsorAuth\SponsorResetPasswordController;
use App\Http\Controllers\Auth\IdleSessionController;
use App\Http\Controllers\SponsorPortalController;
use App\Http\Controllers\AssessmentController;

// Public-facing routes (login, password reset) - no auth required
Route::prefix('sponsor')->group(function () {
    // Login routes
    Route::get('show/login', [SponsorLoginController::class, 'showLoginForm'])->name('sponsor.login');
    Route::post('create/login', [SponsorLoginController::class, 'sponsorLogin'])->name('sponsor.login.post');

    // Password reset routes
    Route::get('password/reset', [SponsorForgotPasswordController::class, 'showLinkRequestForm'])->name('sponsor.password.request');
    Route::post('password/email', [SponsorForgotPasswordController::class, 'sendResetLinkEmail'])->name('sponsor.password.email');
    Route::get('password/reset/{token}', [SponsorResetPasswordController::class, 'showResetForm'])->name('sponsor.password.reset');
    Route::post('password/reset', [SponsorResetPasswordController::class, 'reset'])->name('sponsor.password.update');
});

// Protected sponsor portal routes - requires sponsor authentication only
Route::prefix('sponsor')->middleware(['auth:sponsor'])->group(function () {

    Route::get('/logout', [SponsorLoginController::class, 'logout'])->name('sponsor.logout');
    Route::post('/activity', [IdleSessionController::class, 'touchSponsor'])->name('sponsor.activity');

    Route::post('/term/session', [SponsorPortalController::class, 'setTermSession'])->name('sponsor.term-session');

    Route::get('/dashboard',[SponsorPortalController::class,'index'])->name('sponsor.dashboard');
    Route::get('/term/dashboard/',[SponsorPortalController::class,'getDashboardTermData'])->name('sponsor.dashboard-term');

    // Assessment module
    Route::get('/assessment',[SponsorPortalController::class,'assessmentIndex'])->name('sponsor.assessment-index');
    Route::get('/assessment/{student}',[SponsorPortalController::class,'assessmentStudentShow'])->name('sponsor.assessment.student');
    Route::get('/assessment/{student}/term-data',[SponsorPortalController::class,'getStudentTestsData'])->name('sponsor.assessment.student.term');

    Route::get('/report-card/timeline',[SponsorPortalController::class,'assessmentReportCardTimeline'])->name('sponsor.assessment-report-card-timeline');

    // Fees module
    Route::get('/fees', [SponsorPortalController::class, 'feesIndex'])->name('sponsor.fees');
    Route::get('/fees/{student}', [SponsorPortalController::class, 'feesStudentShow'])->name('sponsor.fees.student');
    Route::get('/fees/{student}/term-data', [SponsorPortalController::class, 'getStudentFeesData'])->name('sponsor.fees.student.term');
    Route::get('/statement/{student}/pdf', [SponsorPortalController::class, 'statementPdf'])->name('sponsor.statement-pdf');

    // Students module
    Route::get('/students', [SponsorPortalController::class, 'studentsIndex'])->name('sponsor.students.index');
    Route::get('/students/{student}', [SponsorPortalController::class, 'studentShow'])->name('sponsor.student.show');

    // Profile & Settings
    Route::get('/profile', [SponsorPortalController::class, 'profile'])->name('sponsor.profile');
    Route::put('/profile', [SponsorPortalController::class, 'updateProfile'])->name('sponsor.profile.update');
    Route::put('/profile/password', [SponsorPortalController::class, 'updatePassword'])->name('sponsor.profile.password');

});
