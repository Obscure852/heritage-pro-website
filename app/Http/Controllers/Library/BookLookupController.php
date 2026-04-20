<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\Library\BookLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookLookupController extends Controller {
    /**
     * Look up book metadata by ISBN via API providers.
     *
     * First checks if a book with this ISBN already exists in the database.
     * If not, queries ISBNdb (primary) and Open Library (fallback) for metadata.
     */
    public function lookup(Request $request): JsonResponse {
        $request->validate([
            'isbn' => ['required', 'string', 'min:10', 'max:17'],
        ]);

        $isbn = $request->input('isbn');

        // Sanitize for database lookup
        $cleanIsbn = preg_replace('/[\s\-]/', '', trim($isbn));

        // Check if book already exists
        $existingBook = Book::where('isbn', $cleanIsbn)->first();
        if ($existingBook) {
            return response()->json([
                'exists'  => true,
                'book_id' => $existingBook->id,
                'message' => 'A book with this ISBN already exists in the catalog.',
            ]);
        }

        // Query API providers
        $service = new BookLookupService();
        $result = $service->lookup($isbn);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for this ISBN. You can enter book details manually.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
