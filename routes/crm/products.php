<?php

use App\Http\Controllers\Crm\InvoiceController;
use App\Http\Controllers\Crm\ProductController;
use App\Http\Controllers\Crm\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/products/catalog', [ProductController::class, 'index'])->name('products.catalog.index');
Route::get('/products/catalog/create', [ProductController::class, 'create'])->name('products.catalog.create');
Route::post('/products/catalog', [ProductController::class, 'store'])->name('products.catalog.store');
Route::get('/products/catalog/{crmProduct}', [ProductController::class, 'show'])->name('products.catalog.show');
Route::get('/products/catalog/{crmProduct}/edit', [ProductController::class, 'edit'])->name('products.catalog.edit');
Route::patch('/products/catalog/{crmProduct}', [ProductController::class, 'update'])->name('products.catalog.update');
Route::patch('/products/catalog/{crmProduct}/status', [ProductController::class, 'updateStatus'])->name('products.catalog.status');

Route::get('/products/quotes', [QuoteController::class, 'index'])->name('products.quotes.index');
Route::get('/products/quotes/create', [QuoteController::class, 'create'])->name('products.quotes.create');
Route::post('/products/quotes', [QuoteController::class, 'store'])->name('products.quotes.store');
Route::get('/products/quotes/{crmQuote}', [QuoteController::class, 'show'])->name('products.quotes.show');
Route::get('/products/quotes/{crmQuote}/edit', [QuoteController::class, 'edit'])->name('products.quotes.edit');
Route::patch('/products/quotes/{crmQuote}', [QuoteController::class, 'update'])->name('products.quotes.update');
Route::patch('/products/quotes/{crmQuote}/status', [QuoteController::class, 'transition'])->name('products.quotes.status');
Route::get('/products/quotes/{crmQuote}/pdf/open', [QuoteController::class, 'openPdf'])->name('products.quotes.pdf.open');
Route::get('/products/quotes/{crmQuote}/pdf/download', [QuoteController::class, 'downloadPdf'])->name('products.quotes.pdf.download');
Route::get('/products/quotes/{crmQuote}/share', [QuoteController::class, 'shareCreate'])->name('products.quotes.share.create');
Route::post('/products/quotes/{crmQuote}/share', [QuoteController::class, 'shareStore'])->name('products.quotes.share.store');
Route::get('/products/invoices', [InvoiceController::class, 'index'])->name('products.invoices.index');
Route::get('/products/invoices/create', [InvoiceController::class, 'create'])->name('products.invoices.create');
Route::post('/products/invoices', [InvoiceController::class, 'store'])->name('products.invoices.store');
Route::get('/products/invoices/{crmInvoice}', [InvoiceController::class, 'show'])->name('products.invoices.show');
Route::get('/products/invoices/{crmInvoice}/edit', [InvoiceController::class, 'edit'])->name('products.invoices.edit');
Route::patch('/products/invoices/{crmInvoice}', [InvoiceController::class, 'update'])->name('products.invoices.update');
Route::patch('/products/invoices/{crmInvoice}/status', [InvoiceController::class, 'transition'])->name('products.invoices.status');
Route::get('/products/invoices/{crmInvoice}/pdf/open', [InvoiceController::class, 'openPdf'])->name('products.invoices.pdf.open');
Route::get('/products/invoices/{crmInvoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])->name('products.invoices.pdf.download');
Route::get('/products/invoices/{crmInvoice}/share', [InvoiceController::class, 'shareCreate'])->name('products.invoices.share.create');
Route::post('/products/invoices/{crmInvoice}/share', [InvoiceController::class, 'shareStore'])->name('products.invoices.share.store');
