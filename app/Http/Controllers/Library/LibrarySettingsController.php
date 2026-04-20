<?php

namespace App\Http\Controllers\Library;

use App\Helpers\CacheHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\UpdateLibrarySettingsRequest;
use App\Models\Author;
use App\Models\Library\LibrarySetting;
use App\Models\Publisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibrarySettingsController extends Controller {
    /**
     * Display library settings page.
     */
    public function index(): View {
        $settings = LibrarySetting::all()->pluck('value', 'key')->toArray();
        $authors = Author::withCount(['books', 'booksPivot'])->orderBy('last_name')->orderBy('first_name')->get();
        $publishers = Publisher::withCount('books')->orderBy('name')->get();

        return view('library.settings.index', [
            'settings' => $settings,
            'authors' => $authors,
            'publishers' => $publishers,
        ]);
    }

    /**
     * Update library settings.
     */
    public function update(UpdateLibrarySettingsRequest $request): JsonResponse {
        $validated = $request->validated();
        $userId = auth()->id();

        try {
            // Borrowing rules - loan period
            if (isset($validated['loan_period_student']) || isset($validated['loan_period_staff'])) {
                $current = LibrarySetting::get('loan_period', ['student' => 14, 'staff' => 30]);
                LibrarySetting::set('loan_period', [
                    'student' => (int) ($validated['loan_period_student'] ?? $current['student']),
                    'staff' => (int) ($validated['loan_period_staff'] ?? $current['staff']),
                ], $userId);
            }

            // Borrowing rules - max books
            if (isset($validated['max_books_student']) || isset($validated['max_books_staff'])) {
                $current = LibrarySetting::get('max_books', ['student' => 3, 'staff' => 5]);
                LibrarySetting::set('max_books', [
                    'student' => (int) ($validated['max_books_student'] ?? $current['student']),
                    'staff' => (int) ($validated['max_books_staff'] ?? $current['staff']),
                ], $userId);
            }

            // Borrowing rules - max renewals
            if (isset($validated['max_renewals_student']) || isset($validated['max_renewals_staff'])) {
                $current = LibrarySetting::get('max_renewals', ['student' => 1, 'staff' => 2]);
                LibrarySetting::set('max_renewals', [
                    'student' => (int) ($validated['max_renewals_student'] ?? $current['student']),
                    'staff' => (int) ($validated['max_renewals_staff'] ?? $current['staff']),
                ], $userId);
            }

            // Currency
            if (isset($validated['library_currency'])) {
                LibrarySetting::set('library_currency', [
                    'code' => strtoupper(trim($validated['library_currency'])),
                ], $userId);
            }

            // Fine settings - rate per day
            if (isset($validated['fine_rate_student']) || isset($validated['fine_rate_staff'])) {
                $current = LibrarySetting::get('fine_rate_per_day', ['student' => 1.00, 'staff' => 2.00]);
                LibrarySetting::set('fine_rate_per_day', [
                    'student' => (float) ($validated['fine_rate_student'] ?? $current['student']),
                    'staff' => (float) ($validated['fine_rate_staff'] ?? $current['staff']),
                ], $userId);
            }

            // Fine settings - threshold
            if (isset($validated['fine_threshold'])) {
                LibrarySetting::set('fine_threshold', [
                    'amount' => (float) $validated['fine_threshold'],
                ], $userId);
            }

            // Fine settings - lost book fine
            if (isset($validated['lost_book_fine_amount'])) {
                LibrarySetting::set('lost_book_fine', [
                    'amount' => (float) $validated['lost_book_fine_amount'],
                ], $userId);
            }

            // Fine settings - lost book period
            if (isset($validated['lost_book_period_student']) || isset($validated['lost_book_period_staff'])) {
                $current = LibrarySetting::get('lost_book_period', ['student' => 60, 'staff' => 60]);
                LibrarySetting::set('lost_book_period', [
                    'student' => (int) ($validated['lost_book_period_student'] ?? $current['student']),
                    'staff' => (int) ($validated['lost_book_period_staff'] ?? $current['staff']),
                ], $userId);
            }

            // API Keys - ISBNdb
            if (array_key_exists('isbndb_api_key', $validated)) {
                LibrarySetting::set('isbndb_api_key', [
                    'key' => $validated['isbndb_api_key'] ?: null,
                ], $userId);
            }

            // Catalog - New Arrivals Period
            if (isset($validated['new_arrivals_period'])) {
                LibrarySetting::set('new_arrivals_period', [
                    'days' => (int) $validated['new_arrivals_period'],
                ], $userId);
            }

            // Catalog options - simple lists
            // Sentinel hidden inputs ensure these keys are always present; value is "" when empty or array when chips exist
            if (array_key_exists('catalog_locations', $validated)) {
                $val = $validated['catalog_locations'];
                LibrarySetting::set('catalog_locations', is_array($val) ? array_values(array_filter($val)) : [], $userId);
            }
            if (array_key_exists('catalog_categories', $validated)) {
                $val = $validated['catalog_categories'];
                LibrarySetting::set('catalog_categories', is_array($val) ? array_values(array_filter($val)) : [], $userId);
            }
            if (array_key_exists('catalog_reading_levels', $validated)) {
                $val = $validated['catalog_reading_levels'];
                LibrarySetting::set('catalog_reading_levels', is_array($val) ? array_values(array_filter($val)) : [], $userId);
            }

            // Catalog options - item types with rules
            if (array_key_exists('item_types', $validated)) {
                $itemTypes = collect($validated['item_types'] ?? [])
                    ->filter(fn($t) => !empty($t['name']))
                    ->map(fn($t) => [
                        'name'                 => $t['name'],
                        'loan_period_student'  => $t['loan_period_student'] ?? null,
                        'loan_period_staff'    => $t['loan_period_staff'] ?? null,
                        'fine_rate_student'    => $t['fine_rate_student'] ?? null,
                        'fine_rate_staff'      => $t['fine_rate_staff'] ?? null,
                        'max_renewals_student' => $t['max_renewals_student'] ?? null,
                        'max_renewals_staff'   => $t['max_renewals_staff'] ?? null,
                    ])->values()->toArray();
                LibrarySetting::set('catalog_item_types', $itemTypes, $userId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Library settings saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new author.
     */
    public function storeAuthor(Request $request): JsonResponse {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);

        $author = DB::transaction(function () use ($validated) {
            $author = Author::create($validated);
            CacheHelper::forgetAuthors();
            return $author;
        });

        return response()->json([
            'success' => true,
            'message' => 'Author added successfully.',
            'author' => [
                'id' => $author->id,
                'first_name' => $author->first_name,
                'last_name' => $author->last_name,
                'books_count' => 0,
            ],
        ]);
    }

    /**
     * Update an existing author.
     */
    public function updateAuthor(Request $request, Author $author): JsonResponse {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($author, $validated) {
            $author->update($validated);
            CacheHelper::forgetAuthors();
        });

        return response()->json([
            'success' => true,
            'message' => 'Author updated successfully.',
            'author' => [
                'id' => $author->id,
                'first_name' => $author->first_name,
                'last_name' => $author->last_name,
            ],
        ]);
    }

    /**
     * Delete an author (only if no books are associated).
     */
    public function destroyAuthor(Author $author): JsonResponse {
        $bookCount = $author->books()->count() + $author->booksPivot()->count();

        if ($bookCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this author — {$bookCount} book(s) are still associated. Remove or reassign them first.",
            ], 422);
        }

        DB::transaction(function () use ($author) {
            $author->delete();
            CacheHelper::forgetAuthors();
        });

        return response()->json([
            'success' => true,
            'message' => 'Author deleted successfully.',
        ]);
    }

    /**
     * Store a new publisher.
     */
    public function storePublisher(Request $request): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:publishers,name',
        ]);

        $publisher = DB::transaction(function () use ($validated) {
            $publisher = Publisher::firstOrCreate(['name' => $validated['name']]);
            CacheHelper::forgetPublishers();
            return $publisher;
        });

        return response()->json([
            'success' => true,
            'message' => 'Publisher added successfully.',
            'publisher' => [
                'id' => $publisher->id,
                'name' => $publisher->name,
                'books_count' => 0,
            ],
        ]);
    }

    /**
     * Update an existing publisher.
     */
    public function updatePublisher(Request $request, Publisher $publisher): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:publishers,name,' . $publisher->id,
        ]);

        DB::transaction(function () use ($publisher, $validated) {
            $publisher->update($validated);
            CacheHelper::forgetPublishers();
        });

        return response()->json([
            'success' => true,
            'message' => 'Publisher updated successfully.',
            'publisher' => [
                'id' => $publisher->id,
                'name' => $publisher->name,
            ],
        ]);
    }

    /**
     * Delete a publisher (only if no books are associated).
     */
    public function destroyPublisher(Publisher $publisher): JsonResponse {
        $bookCount = $publisher->books()->count();

        if ($bookCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this publisher — {$bookCount} book(s) are still associated. Remove or reassign them first.",
            ], 422);
        }

        DB::transaction(function () use ($publisher) {
            $publisher->delete();
            CacheHelper::forgetPublishers();
        });

        return response()->json([
            'success' => true,
            'message' => 'Publisher deleted successfully.',
        ]);
    }
}
