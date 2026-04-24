<?php

use App\Http\Controllers\PublicWebsiteController;
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
    Route::post('/book-demo', 'bookDemo')->name('website.book-demo');
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
            require base_path('routes/crm/dev.php');
            require base_path('routes/crm/discussions.php');
            require base_path('routes/crm/integrations.php');
            require base_path('routes/crm/attendance.php');
            require base_path('routes/crm/leave.php');
            require base_path('routes/crm/users.php');
            require base_path('routes/crm/settings.php');
        });
    });
