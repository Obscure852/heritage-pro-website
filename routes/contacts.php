<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactTagController;
use Illuminate\Support\Facades\Route;

Route::prefix('contacts')
    ->middleware(['auth', 'throttle:auth', 'block.non.african'])
    ->group(function () {
        Route::middleware('can:access-asset-management')->group(function () {
            Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
        });

        Route::middleware('can:manage-assets')->group(function () {
            Route::get('/create', [ContactController::class, 'create'])->name('contacts.create');

            Route::get('/settings/tags', [ContactTagController::class, 'index'])->name('contacts.settings');
            Route::post('/settings/tags', [ContactTagController::class, 'store'])->name('contacts.tags.store');
            Route::put('/settings/tags/{contactTag}', [ContactTagController::class, 'update'])->name('contacts.tags.update');
            Route::delete('/settings/tags/{contactTag}', [ContactTagController::class, 'destroy'])->name('contacts.tags.destroy');

            Route::post('/', [ContactController::class, 'store'])->name('contacts.store');
            Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
            Route::put('/{contact}', [ContactController::class, 'update'])->name('contacts.update');
            Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
        });

        Route::middleware('can:access-asset-management')->group(function () {
            Route::get('/{contact}', [ContactController::class, 'show'])->name('contacts.show');
        });
    });
