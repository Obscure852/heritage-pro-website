<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller {
    /**
     * Display the catalog listing page.
     */
    public function index(Request $request): View {
        $books = Book::with(['author', 'authors', 'publisher', 'grade'])
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn($q) => $q->where('status', 'available'),
                'copies as checked_out_copies_count' => fn($q) => $q->where('status', 'checked_out'),
            ])
            ->orderBy('title')
            ->get();

        // Filter values: prefer settings, fall back to distinct DB values
        $categories = LibrarySetting::get('catalog_categories')
            ?? Book::whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre')->toArray();
        $grades = Grade::where('active', 1)->orderBy('sequence')->get();
        $languages = Book::whereNotNull('language')->distinct()->orderBy('language')->pluck('language');
        $itemTypesSetting = LibrarySetting::get('catalog_item_types', []);
        $itemTypes = !empty($itemTypesSetting)
            ? collect($itemTypesSetting)->pluck('name')
            : Book::whereNotNull('format')->distinct()->orderBy('format')->pluck('format');
        $readingLevels = LibrarySetting::get('catalog_reading_levels')
            ?? Book::whereNotNull('reading_level')->distinct()->orderBy('reading_level')->pluck('reading_level')->toArray();

        // New arrivals - configurable period
        $newArrivalsDays = LibrarySetting::get('new_arrivals_period', ['days' => 30])['days'];
        $newArrivals = Book::with(['author', 'authors'])
            ->where('created_at', '>=', now()->subDays($newArrivalsDays))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Header stats
        $stats = [
            'total_books' => $books->count(),
            'available_copies' => $books->sum('available_copies_count'),
            'checked_out_copies' => $books->sum('checked_out_copies_count'),
        ];

        return view('library.catalog.index', compact(
            'books',
            'categories',
            'grades',
            'languages',
            'itemTypes',
            'readingLevels',
            'newArrivals',
            'stats'
        ));
    }

    /**
     * Display the book detail page.
     */
    public function show(Book $book): View {
        $book->load(['author', 'authors', 'publisher', 'grade', 'copies.currentTransaction.borrower']);

        $circulationHistory = LibraryTransaction::whereHas('copy', fn($q) => $q->where('book_id', $book->id))
            ->with(['borrower', 'copy'])
            ->orderByDesc('checkout_date')
            ->paginate(20);

        return view('library.catalog.show', compact('book', 'circulationHistory'));
    }
}
