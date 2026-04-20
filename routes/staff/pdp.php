<?php

use App\Http\Controllers\Pdp\MyPdpController;
use App\Http\Controllers\Pdp\PdpPlanController;
use App\Http\Controllers\Pdp\PdpPdfController;
use App\Http\Controllers\Pdp\PdpReportController;
use App\Http\Controllers\Pdp\PdpReviewController;
use App\Http\Controllers\Pdp\PdpRolloutController;
use App\Http\Controllers\Pdp\PdpSectionEntryController;
use App\Http\Controllers\Pdp\PdpSettingsController;
use App\Http\Controllers\Pdp\PdpSignatureController;
use App\Http\Controllers\Pdp\PdpTemplateController;
use App\Http\Controllers\Pdp\PdpTemplateSectionRowController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff/pdp')->name('staff.pdp.')->middleware(['auth'])->group(function (): void {
    Route::get('/my', [MyPdpController::class, 'index'])->name('my');
    Route::prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/', [PdpSettingsController::class, 'index'])->name('index');
        Route::post('/{scope}', [PdpSettingsController::class, 'update'])->name('update');
    });

    Route::prefix('plans')->name('plans.')->group(function (): void {
        Route::get('/', [PdpPlanController::class, 'index'])->name('index');
        Route::get('/create', [PdpPlanController::class, 'create'])->name('create');
        Route::post('/', [PdpPlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PdpPlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PdpPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [PdpPlanController::class, 'update'])->name('update');
        Route::get('/{plan}/print', [PdpPdfController::class, 'preview'])->name('print');
        Route::get('/{plan}/pdf', [PdpPdfController::class, 'download'])->name('pdf');

        Route::post('/{plan}/sections/{sectionKey}/entries', [PdpSectionEntryController::class, 'store'])
            ->name('sections.entries.store');
        Route::put('/{plan}/sections/{sectionKey}/entries/{entry}', [PdpSectionEntryController::class, 'update'])
            ->name('sections.entries.update');
        Route::delete('/{plan}/sections/{sectionKey}/entries/{entry}', [PdpSectionEntryController::class, 'destroy'])
            ->name('sections.entries.destroy');

        Route::post('/{plan}/reviews/{periodKey}/open', [PdpReviewController::class, 'open'])
            ->name('reviews.open');
        Route::post('/{plan}/reviews/{periodKey}/close', [PdpReviewController::class, 'close'])
            ->name('reviews.close');
        Route::post('/{plan}/signatures/{signature}/sign', [PdpSignatureController::class, 'sign'])
            ->name('signatures.sign');
    });

    Route::prefix('templates')->name('templates.')->group(function (): void {
        Route::get('/', [PdpTemplateController::class, 'index'])->name('index');
        Route::get('/create', [PdpTemplateController::class, 'create'])->name('create');
        Route::post('/', [PdpTemplateController::class, 'store'])->name('store');
        Route::get('/{template}', [PdpTemplateController::class, 'show'])->name('show');
        Route::put('/{template}', [PdpTemplateController::class, 'update'])->name('update');
        Route::put('/{template}/sections', [PdpTemplateController::class, 'updateSections'])->name('sections.update');
        Route::put('/{template}/employee-information', [PdpTemplateController::class, 'updateEmployeeInformation'])->name('employee-information.update');
        Route::put('/{template}/sections/{section}/builder', [PdpTemplateController::class, 'updateSectionBuilder'])->name('sections.builder.update');
        Route::put('/{template}/periods', [PdpTemplateController::class, 'updatePeriods'])->name('periods.update');
        Route::put('/{template}/ratings', [PdpTemplateController::class, 'updateRatings'])->name('ratings.update');
        Route::put('/{template}/approvals', [PdpTemplateController::class, 'updateApprovals'])->name('approvals.update');
        Route::post('/{template}/sections/{section}/rows', [PdpTemplateSectionRowController::class, 'store'])->name('sections.rows.store');
        Route::put('/{template}/sections/{section}/rows/{row}', [PdpTemplateSectionRowController::class, 'update'])->name('sections.rows.update');
        Route::delete('/{template}/sections/{section}/rows/{row}', [PdpTemplateSectionRowController::class, 'destroy'])->name('sections.rows.destroy');
        Route::post('/{template}/clone', [PdpTemplateController::class, 'clone'])->name('clone');
        Route::post('/{template}/publish', [PdpTemplateController::class, 'publish'])->name('publish');
        Route::post('/{template}/activate', [PdpTemplateController::class, 'activate'])->name('activate');
        Route::post('/{template}/archive', [PdpTemplateController::class, 'archive'])->name('archive');
        Route::delete('/{template}', [PdpTemplateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('rollouts')->name('rollouts.')->group(function (): void {
        Route::post('/', [PdpRolloutController::class, 'store'])->name('store');
        Route::get('/{rollout}', [PdpRolloutController::class, 'show'])->name('show');
    });

    Route::prefix('reports')->name('reports.')->group(function (): void {
        Route::get('/', [PdpReportController::class, 'index'])->name('index');
    });
});
