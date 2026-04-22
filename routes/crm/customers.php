<?php

use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/onboarding/create', [CustomerController::class, 'onboardingCreate'])->name('customers.onboarding.create');
Route::post('/customers/onboarding', [CustomerController::class, 'onboardingStore'])->name('customers.onboarding.store');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
