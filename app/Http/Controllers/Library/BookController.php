<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StoreBookRequest;
use App\Http\Requests\Library\UpdateBookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Library\LibrarySetting;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookController extends Controller {
    /**
     * Show the book creation form.
     */
    public function create(): View {
        $grades = Grade::where('active', true)->orderBy('sequence')->get();
        $authors = Author::orderBy('last_name')->orderBy('first_name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $locations     = LibrarySetting::get('catalog_locations', []);
        $itemTypes     = LibrarySetting::get('catalog_item_types', [['name' => 'Book']]);
        $categories    = LibrarySetting::get('catalog_categories', ['Fiction', 'Non-Fiction']);
        $readingLevels = LibrarySetting::get('catalog_reading_levels', []);
        $itemTypeNames = collect($itemTypes)->pluck('name');
        $currencyData  = LibrarySetting::get('library_currency', ['code' => 'BWP']);
        $defaultCurrency = $currencyData['code'] ?? 'BWP';

        return view('library.books.create', compact('grades', 'authors', 'publishers', 'locations', 'itemTypeNames', 'categories', 'readingLevels', 'defaultCurrency'));
    }

    /**
     * Store a new book.
     *
     * Creates publisher (if new), book record, and author associations
     * within a single database transaction.
     */
    public function store(StoreBookRequest $request): RedirectResponse {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            // Create or find Publisher
            $publisherId = null;
            if (!empty($validated['publisher_name'])) {
                $publisher = Publisher::firstOrCreate(
                    ['name' => trim($validated['publisher_name'])]
                );
                $publisherId = $publisher->id;
            }

            // Create the Book
            $book = Book::create([
                'isbn'            => preg_replace('/[\s\-]/', '', $validated['isbn']),
                'title'           => $validated['title'],
                'publisher_id'    => $publisherId,
                'grade_id'        => $validated['grade_id'] ?? null,
                'publication_year' => $validated['publication_year'] ?? null,
                'edition'         => $validated['edition'] ?? null,
                'genre'           => $validated['genre'] ?? null,
                'language'        => $validated['language'] ?? null,
                'format'          => $validated['format'] ?? null,
                'pages'           => $validated['pages'] ?? null,
                'description'     => $validated['description'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'dewey_decimal'   => $validated['dewey_decimal'] ?? null,
                'reading_level'   => $validated['reading_level'] ?? null,
                'condition'       => $validated['condition'] ?? null,
                'keywords'        => $validated['keywords'] ?? null,
                'price'           => $validated['price'] ?? null,
                'currency'        => $validated['currency'] ?? null,
                'location'        => $validated['location'] ?? null,
                'date_added'      => now(),
                'status'          => 'active',
            ]);

            // Handle authors
            $authorIds = $this->parseAndCreateAuthors($validated['author_names'] ?? null);
            if (!empty($authorIds)) {
                $book->authors()->attach($authorIds);
                // Set author_id to first author for backward compatibility
                $book->update(['author_id' => $authorIds[0]]);
            }

            return $book;
        });

        return redirect()
            ->route('library.catalog.show', $book)
            ->with('message', 'Book added to catalog successfully.');
    }

    /**
     * Show the book edit form.
     */
    public function edit(Book $book): View {
        $book->load(['authors', 'publisher', 'grade']);

        $grades = Grade::where('active', true)->orderBy('sequence')->get();
        $authors = Author::orderBy('last_name')->orderBy('first_name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $authorNames = $book->authors->pluck('full_name')->join(', ');
        $locations     = LibrarySetting::get('catalog_locations', []);
        $itemTypes     = LibrarySetting::get('catalog_item_types', [['name' => 'Book']]);
        $categories    = LibrarySetting::get('catalog_categories', ['Fiction', 'Non-Fiction']);
        $readingLevels = LibrarySetting::get('catalog_reading_levels', []);
        $itemTypeNames = collect($itemTypes)->pluck('name');
        $currencyData  = LibrarySetting::get('library_currency', ['code' => 'BWP']);
        $defaultCurrency = $currencyData['code'] ?? 'BWP';

        return view('library.books.edit', compact('book', 'grades', 'authors', 'publishers', 'authorNames', 'locations', 'itemTypeNames', 'categories', 'readingLevels', 'defaultCurrency'));
    }

    /**
     * Update an existing book.
     *
     * Updates publisher, book record, and author associations
     * within a single database transaction.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $book) {
            // Update or create Publisher
            $publisherId = $book->publisher_id;
            if (!empty($validated['publisher_name'])) {
                $publisher = Publisher::firstOrCreate(
                    ['name' => trim($validated['publisher_name'])]
                );
                $publisherId = $publisher->id;
            } elseif (array_key_exists('publisher_name', $validated)) {
                $publisherId = null;
            }

            // Update the Book
            $book->update([
                'isbn'            => preg_replace('/[\s\-]/', '', $validated['isbn']),
                'title'           => $validated['title'],
                'publisher_id'    => $publisherId,
                'grade_id'        => $validated['grade_id'] ?? null,
                'publication_year' => $validated['publication_year'] ?? null,
                'edition'         => $validated['edition'] ?? null,
                'genre'           => $validated['genre'] ?? null,
                'language'        => $validated['language'] ?? null,
                'format'          => $validated['format'] ?? null,
                'pages'           => $validated['pages'] ?? null,
                'description'     => $validated['description'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'dewey_decimal'   => $validated['dewey_decimal'] ?? null,
                'reading_level'   => $validated['reading_level'] ?? null,
                'condition'       => $validated['condition'] ?? null,
                'keywords'        => $validated['keywords'] ?? null,
                'price'           => $validated['price'] ?? null,
                'currency'        => $validated['currency'] ?? null,
                'location'        => $validated['location'] ?? null,
            ]);

            // Sync authors
            $authorIds = $this->parseAndCreateAuthors($validated['author_names'] ?? null);
            $book->authors()->sync($authorIds);

            // Update author_id for backward compatibility
            $book->update(['author_id' => $authorIds[0] ?? null]);
        });

        return redirect()
            ->route('library.catalog.show', $book)
            ->with('message', 'Book updated successfully.');
    }

    /**
     * Parse comma-separated author names and create Author records.
     *
     * Each name is split on the last space into first_name/last_name.
     * Single-word names are stored as last_name only.
     *
     * @param string|null $authorNamesString Comma-separated author names
     * @return array<int> Author IDs
     */
    private function parseAndCreateAuthors(?string $authorNamesString): array {
        if (empty($authorNamesString)) {
            return [];
        }

        $authorIds = [];
        $names = array_map('trim', explode(',', $authorNamesString));

        foreach ($names as $name) {
            if (empty($name)) {
                continue;
            }

            // Split on last space: "Theodore H. Brown" -> first="Theodore H.", last="Brown"
            $lastSpace = strrpos($name, ' ');
            if ($lastSpace !== false) {
                $firstName = trim(substr($name, 0, $lastSpace));
                $lastName = trim(substr($name, $lastSpace + 1));
            } else {
                // Single word name: use as last name
                $firstName = '';
                $lastName = $name;
            }

            $author = Author::firstOrCreate([
                'first_name' => $firstName,
                'last_name'  => $lastName,
            ]);

            $authorIds[] = $author->id;
        }

        return $authorIds;
    }
}
