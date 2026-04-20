<?php

use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\BookLookupController;
use App\Http\Controllers\Library\BorrowerController;
use App\Http\Controllers\Library\CatalogController;
use App\Http\Controllers\Library\CirculationController;
use App\Http\Controllers\Library\DashboardController;
use App\Http\Controllers\Library\LibrarySettingsController;
use App\Http\Controllers\Library\FineController;
use App\Http\Controllers\Library\OverdueController;
use App\Http\Controllers\Library\ReportController;
use App\Http\Controllers\Library\InventoryController;
use App\Http\Controllers\Library\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Library Routes
|--------------------------------------------------------------------------
|
| Routes for the Library Management module.
|
*/

// Dashboard - must be first (before any catch-all routes)
Route::get('library/dashboard', [DashboardController::class, 'index'])
    ->name('library.dashboard')
    ->middleware(['auth', 'can:manage-library']);

// Circulation - must be before catalog {book} catch-all
Route::prefix('library/circulation')->name('library.circulation.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/', [CirculationController::class, 'index'])->name('index');
        Route::post('/checkout', [CirculationController::class, 'checkout'])->name('checkout');
        Route::post('/checkin', [CirculationController::class, 'checkin'])->name('checkin');
        Route::post('/renew/{transaction}', [CirculationController::class, 'renew'])->name('renew');
        Route::post('/bulk-checkout', [CirculationController::class, 'bulkCheckout'])->name('bulk-checkout');
        Route::post('/bulk-checkin', [CirculationController::class, 'bulkCheckin'])->name('bulk-checkin');
        Route::get('/lookup-copy', [CirculationController::class, 'lookupCopy'])->name('lookup-copy');
        Route::get('/borrower-status', [CirculationController::class, 'borrowerStatus'])->name('borrower-status');
    });

// Overdue Dashboard
Route::get('library/overdue', [OverdueController::class, 'index'])
    ->name('library.overdue.index')
    ->middleware(['auth', 'can:manage-library']);

// Fines Management
Route::prefix('library/fines')->name('library.fines.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/', [FineController::class, 'index'])->name('index');
        Route::post('/{fine}/payment', [FineController::class, 'recordPayment'])->name('record-payment');
        Route::post('/{fine}/waive', [FineController::class, 'waive'])->name('waive');
        Route::get('/{fine}/receipt', [FineController::class, 'printReceipt'])->name('receipt');
    });

// Reservations Management (librarian)
Route::prefix('library/reservations')->name('library.reservations.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });

// Reports (no index route -- accessed via dashboard dropdown)
Route::prefix('library/reports')->name('library.reports.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/circulation', [ReportController::class, 'circulation'])->name('circulation');
        Route::get('/circulation/export', [ReportController::class, 'exportCirculation'])->name('circulation.export');

        Route::get('/overdue', [ReportController::class, 'overdue'])->name('overdue');
        Route::get('/overdue/export', [ReportController::class, 'exportOverdue'])->name('overdue.export');

        Route::get('/most-borrowed', [ReportController::class, 'mostBorrowed'])->name('most-borrowed');
        Route::get('/most-borrowed/export', [ReportController::class, 'exportMostBorrowed'])->name('most-borrowed.export');

        Route::get('/borrower-activity', [ReportController::class, 'borrowerActivity'])->name('borrower-activity');
        Route::get('/borrower-activity/export', [ReportController::class, 'exportBorrowerActivity'])->name('borrower-activity.export');

        Route::get('/collection-development', [ReportController::class, 'collectionDevelopment'])->name('collection-development');
        Route::get('/collection-development/export', [ReportController::class, 'exportCollectionDevelopment'])->name('collection-development.export');

        Route::get('/fine-collection', [ReportController::class, 'fineCollection'])->name('fine-collection');
        Route::get('/fine-collection/export', [ReportController::class, 'exportFineCollection'])->name('fine-collection.export');
    });

// Inventory Management
Route::prefix('library/inventory')->name('library.inventory.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{session}', [InventoryController::class, 'show'])->name('show');
        Route::post('/{session}/scan', [InventoryController::class, 'scan'])->name('scan');
        Route::post('/{session}/complete', [InventoryController::class, 'complete'])->name('complete');
        Route::post('/{session}/cancel', [InventoryController::class, 'cancel'])->name('cancel');
        Route::get('/{session}/report', [InventoryController::class, 'report'])->name('report');
        Route::post('/{session}/mark-missing', [InventoryController::class, 'markMissing'])->name('mark-missing');
        Route::get('/{session}/export', [InventoryController::class, 'export'])->name('export');
    });

// Borrower self-service reservation from catalog
Route::post('library/catalog/{book}/reserve', [ReservationController::class, 'reserve'])
    ->name('library.catalog.reserve')
    ->middleware(['auth', 'can:access-library']);

// Book lookup (AJAX) - must be before any {book} parameter routes
Route::get('library/books/lookup', [BookLookupController::class, 'lookup'])
    ->name('library.books.lookup')
    ->middleware(['auth', 'can:manage-library']);

// Book CRUD - must be before catalog {book} catch-all
Route::prefix('library/books')->name('library.books.')
    ->middleware(['auth', 'can:manage-library'])
    ->group(function () {
        Route::get('/create', [BookController::class, 'create'])->name('create');
        Route::post('/', [BookController::class, 'store'])->name('store');
        Route::get('/{book}/edit', [BookController::class, 'edit'])->name('edit');
        Route::put('/{book}', [BookController::class, 'update'])->name('update');
    });

// Catalog (accessible to anyone with library access)
Route::prefix('library/catalog')->name('library.catalog.')
    ->middleware(['auth', 'can:access-library'])
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        Route::get('/{book}', [CatalogController::class, 'show'])->name('show');
    });

// Library Settings
Route::prefix('library/settings')->name('library.settings.')
    ->middleware(['auth', 'can:manage-library-settings'])
    ->group(function () {
        Route::get('/', [LibrarySettingsController::class, 'index'])->name('index');
        Route::post('/', [LibrarySettingsController::class, 'update'])->name('update');

        // Author/Publisher management (AJAX)
        Route::post('/authors', [LibrarySettingsController::class, 'storeAuthor'])->name('store-author');
        Route::put('/authors/{author}', [LibrarySettingsController::class, 'updateAuthor'])->name('update-author');
        Route::delete('/authors/{author}', [LibrarySettingsController::class, 'destroyAuthor'])->name('destroy-author');
        Route::post('/publishers', [LibrarySettingsController::class, 'storePublisher'])->name('store-publisher');
        Route::put('/publishers/{publisher}', [LibrarySettingsController::class, 'updatePublisher'])->name('update-publisher');
        Route::delete('/publishers/{publisher}', [LibrarySettingsController::class, 'destroyPublisher'])->name('destroy-publisher');
    });

// Borrowers
Route::prefix('library/borrowers')->name('library.borrowers.')
    ->middleware(['auth', 'can:access-library'])
    ->group(function () {
        Route::get('/', [BorrowerController::class, 'index'])->name('index');
        Route::get('/search', [BorrowerController::class, 'search'])->name('search');
        Route::get('/{type}/{id}', [BorrowerController::class, 'show'])
            ->where('type', 'student|staff')
            ->where('id', '[0-9]+')
            ->name('show');
    });
