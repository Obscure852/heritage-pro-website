<?php

use App\Http\Controllers\Welfare\CounselingController;
use App\Http\Controllers\Welfare\DisciplinaryController;
use App\Http\Controllers\Welfare\HealthIncidentController;
use App\Http\Controllers\Welfare\InterventionPlanController;
use App\Http\Controllers\Welfare\ParentCommunicationController;
use App\Http\Controllers\Welfare\SafeguardingController;
use App\Http\Controllers\Welfare\WelfareCaseController;
use App\Http\Controllers\Welfare\WelfareDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Welfare Routes
|--------------------------------------------------------------------------
|
| Routes for the Student Welfare module.
| All routes require authentication and welfare access.
|
*/

Route::middleware(['auth'])->prefix('welfare')->name('welfare.')->group(function () {

    // Dashboard
    Route::get('/', [WelfareDashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [WelfareDashboardController::class, 'statistics'])->name('statistics');
    Route::get('/reports', [WelfareDashboardController::class, 'reports'])->name('reports');
    Route::get('/export', [WelfareDashboardController::class, 'export'])->name('export');

    // Welfare Cases
    Route::resource('cases', WelfareCaseController::class)
        ->except(['show'])
        ->parameter('cases', 'case');
    Route::prefix('cases/{case}')->name('cases.')->group(function () {
        Route::post('/assign', [WelfareCaseController::class, 'assign'])->name('assign');
        Route::post('/escalate', [WelfareCaseController::class, 'escalate'])->name('escalate');
        Route::post('/approve', [WelfareCaseController::class, 'approve'])->name('approve');
        Route::post('/reject', [WelfareCaseController::class, 'reject'])->name('reject');
        Route::post('/close', [WelfareCaseController::class, 'close'])->name('close');
        Route::post('/reopen', [WelfareCaseController::class, 'reopen'])->name('reopen');
        Route::get('/audit', [WelfareCaseController::class, 'audit'])->name('audit');
        Route::post('/notes', [WelfareCaseController::class, 'addNote'])->name('notes.store');
        Route::post('/attachments', [WelfareCaseController::class, 'addAttachment'])->name('attachments.store');
    });

    // Counseling Sessions (Level 4 Confidential)
    Route::middleware(['can:access-counseling'])->group(function () {
        Route::resource('counseling', CounselingController::class)
            ->except(['show'])
            ->parameter('counseling', 'session');
        Route::prefix('counseling/{session}')->name('counseling.')->group(function () {
            Route::post('/complete', [CounselingController::class, 'complete'])->name('complete');
            Route::post('/cancel', [CounselingController::class, 'cancel'])->name('cancel');
            Route::post('/no-show', [CounselingController::class, 'noShow'])->name('no-show');
        });
        Route::get('/counseling-calendar', [CounselingController::class, 'calendar'])->name('counseling.calendar');
        Route::get('/counseling-upcoming', [CounselingController::class, 'upcoming'])->name('counseling.upcoming');
    });

    // Disciplinary Records
    Route::middleware(['can:access-disciplinary'])->group(function () {
        Route::resource('disciplinary', DisciplinaryController::class)
            ->except(['show'])
            ->parameter('disciplinary', 'record');
        Route::prefix('disciplinary/{record}')->name('disciplinary.')->group(function () {
            Route::post('/apply-action', [DisciplinaryController::class, 'applyAction'])->name('apply-action');
            Route::post('/resolve', [DisciplinaryController::class, 'resolve'])->name('resolve');
            Route::post('/notify-parent', [DisciplinaryController::class, 'notifyParent'])->name('notify-parent');
        });
        Route::get('/disciplinary-actions', [DisciplinaryController::class, 'actions'])->name('disciplinary.actions');
    });

    // Safeguarding Concerns (Level 4 Highly Confidential)
    Route::middleware(['can:access-safeguarding'])->group(function () {
        Route::resource('safeguarding', SafeguardingController::class)
            ->except(['show'])
            ->parameter('safeguarding', 'concern');
        Route::prefix('safeguarding/{concern}')->name('safeguarding.')->group(function () {
            Route::post('/immediate-action', [SafeguardingController::class, 'recordImmediateAction'])->name('immediate-action');
            Route::post('/notify-authorities', [SafeguardingController::class, 'notifyAuthorities'])->name('notify-authorities');
            Route::post('/notify-parents', [SafeguardingController::class, 'notifyParents'])->name('notify-parents');
            Route::post('/close', [SafeguardingController::class, 'close'])->name('close');
        });
    });

    // Health Incidents
    Route::middleware(['can:access-health-incidents'])->group(function () {
        Route::resource('health', HealthIncidentController::class)
            ->except(['show'])
            ->parameter('health', 'incident');
        Route::prefix('health/{incident}')->name('health.')->group(function () {
            Route::post('/treatment', [HealthIncidentController::class, 'recordTreatment'])->name('treatment');
            Route::post('/notify-parent', [HealthIncidentController::class, 'notifyParent'])->name('notify-parent');
            Route::post('/sent-home', [HealthIncidentController::class, 'sentHome'])->name('sent-home');
            Route::post('/hospital', [HealthIncidentController::class, 'hospital'])->name('hospital');
            Route::post('/resolve', [HealthIncidentController::class, 'resolve'])->name('resolve');
        });
        Route::get('/health-today', [HealthIncidentController::class, 'today'])->name('health.today');
    });

    // Intervention Plans
    Route::middleware(['can:access-intervention-plans'])->group(function () {
        Route::resource('intervention-plans', InterventionPlanController::class)
            ->except(['show'])
            ->parameter('intervention-plans', 'interventionPlan');
        Route::prefix('intervention-plans/{interventionPlan}')->name('intervention-plans.')->group(function () {
            Route::post('/activate', [InterventionPlanController::class, 'activate'])->name('activate');
            Route::post('/hold', [InterventionPlanController::class, 'hold'])->name('hold');
            Route::post('/resume', [InterventionPlanController::class, 'resume'])->name('resume');
            Route::post('/complete', [InterventionPlanController::class, 'complete'])->name('complete');
            Route::post('/consent', [InterventionPlanController::class, 'recordConsent'])->name('consent');
            Route::post('/reviews', [InterventionPlanController::class, 'addReview'])->name('reviews.store');
        });
    });

    // Parent Communications
    Route::resource('communications', ParentCommunicationController::class)->except(['show']);
    Route::post('/communications/{communication}/follow-up', [ParentCommunicationController::class, 'completeFollowUp'])
        ->name('communications.follow-up');

    // Student Welfare Profile
    Route::get('/student/{student}', [WelfareDashboardController::class, 'studentProfile'])->name('student.profile');
    Route::get('/student/{student}/history', [WelfareDashboardController::class, 'studentHistory'])->name('student.history');

    // Attachment download
    Route::get('/attachments/{attachment}/download', [WelfareCaseController::class, 'downloadAttachment'])
        ->name('attachments.download');

    // Audit Log
    Route::middleware(['can:view-welfare-audit'])->group(function () {
        Route::get('/audit-log', [WelfareDashboardController::class, 'auditLog'])->name('audit-log');
    });
});
