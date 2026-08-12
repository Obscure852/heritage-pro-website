<?php

use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\ClientSetupController;
use App\Http\Controllers\Crm\CalendarAvailabilityController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::controller(PublicWebsiteController::class)->group(function () {
    Route::get('/', 'home')->name('website.home');
    Route::get('/products', 'page')->defaults('page', 'products')->name('website.products');
    Route::get('/features', 'page')->defaults('page', 'features')->name('website.features');
    Route::get('/customers', 'page')->defaults('page', 'customers')->name('website.customers');
    Route::get('/pricing', 'page')->defaults('page', 'pricing')->name('website.pricing');
    Route::get('/about', 'page')->defaults('page', 'about')->name('website.about');
    Route::get('/team', 'page')->defaults('page', 'team')->name('website.team');
    Route::get('/faq', 'page')->defaults('page', 'faq')->name('website.faq');
    Route::get('/prospectus', 'prospectus')->name('website.prospectus');
    Route::get('/journal', 'journal')->name('website.journal');
    Route::get('/journal/{slug}', 'journalArticle')->name('website.journal.article');
    Route::post('/book-demo', 'bookDemo')->name('website.book-demo');
});

Route::controller(ClientSetupController::class)
    ->prefix('setup')
    ->name('client-setup.')
    ->group(function () {
        Route::get('/resume', 'resume')->name('resume');
        Route::post('/resume', 'requestResumeLink')
            ->middleware('throttle:client-setup-resume')
            ->name('resume.request');
        Route::get('/{token}', 'entry')
            ->middleware('throttle:client-setup-entry')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('entry');
        Route::get('/{token}/exit', 'exit')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('exit');
        Route::post('/{token}/submit-academic', 'submitAcademic')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('academic-submit');
        Route::get('/{token}/academic-submitted', 'academicSubmitted')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('academic-submitted');
        Route::post('/{token}/supplemental-complete', 'completeSupplemental')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('supplemental-complete');
        Route::post('/{token}/attachments', 'uploadAttachment')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('attachment-upload');
        Route::get('/{token}/migration/template/{kind}', 'downloadMigrationTemplate')
            ->where(['token' => '[A-Fa-f0-9]{64}', 'kind' => 'staff|students'])
            ->name('migration-template.download');
        Route::post('/{token}/migration/upload', 'uploadMigrationTemplate')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('migration-upload');
        Route::post('/{token}/change-requests/{changeRequest}/respond', 'respondToChangeRequest')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('change-request.respond');
        Route::post('/{token}/verification-code', 'requestCode')
            ->middleware('throttle:client-setup-code')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('verification-code');
        Route::post('/{token}/verify', 'verify')
            ->middleware('throttle:client-setup-verify')
            ->where('token', '[A-Fa-f0-9]{64}')
            ->name('verify');
        Route::get('/{token}/stage/{stage}', 'stage')
            ->where(['token' => '[A-Fa-f0-9]{64}', 'stage' => '[a-z0-9][a-z0-9_-]{0,79}'])
            ->name('stage');
        Route::patch('/{token}/stage/{stage}', 'saveStage')
            ->where(['token' => '[A-Fa-f0-9]{64}', 'stage' => '[a-z0-9][a-z0-9_-]{0,79}'])
            ->name('stage.save');
    });

Route::get('/sign-in', fn () => redirect()->route('login'))->name('website.sign-in');
Route::get('/crm/calendar/availability/{crmCalendarEventAttendee}/{response}', CalendarAvailabilityController::class)
    ->middleware('signed')
    ->name('crm.calendar.attendees.availability');

Auth::routes(['register' => false]);
//Users/thatoobuseng/Sites/Heritage Website

Route::middleware(['auth', 'crm.access', 'crm.onboarding'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('crm.dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return redirect()->route('crm.dashboard');
    })->name('home');
});

Route::prefix('crm')->middleware(['auth', 'crm.access'])->name('crm.')->group(function () {
        require base_path('routes/crm/onboarding.php');

        Route::middleware('crm.onboarding')->group(function () {
            require base_path('routes/crm/dashboard.php');
            require base_path('routes/crm/workspace.php');
            require base_path('routes/crm/customers.php');
            require base_path('routes/crm/contacts.php');
            require base_path('routes/crm/calendar.php');
            require base_path('routes/crm/products.php');
            require base_path('routes/crm/requests.php');
            require base_path('routes/crm/client_setup.php');
            require base_path('routes/crm/dev.php');
            require base_path('routes/crm/discussions.php');
            require base_path('routes/crm/integrations.php');
            require base_path('routes/crm/attendance.php');
            require base_path('routes/crm/leave.php');
            require base_path('routes/crm/users.php');
            require base_path('routes/crm/settings.php');
        });
    });
