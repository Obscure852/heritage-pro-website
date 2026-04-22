<?php

use App\Http\Controllers\Crm\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/calendar/feed', [CalendarController::class, 'feed'])->name('calendar.feed');
Route::post('/calendar/calendars', [CalendarController::class, 'storeCalendar'])->name('calendar.calendars.store');
Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
Route::patch('/calendar/events/{crmCalendarEvent}', [CalendarController::class, 'update'])->name('calendar.events.update');
Route::delete('/calendar/events/{crmCalendarEvent}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');
Route::post('/calendar/events/{crmCalendarEvent}/status', [CalendarController::class, 'updateStatus'])->name('calendar.events.status');
