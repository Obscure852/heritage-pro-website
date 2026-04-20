<?php

namespace App\Services\Library\Providers;

use App\Models\Library\LibrarySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IsbnDbProvider {
    /**
     * Provider name.
     */
    public function name(): string {
        return 'ISBNdb';
    }

    /**
     * Check if ISBNdb API key is configured and non-empty.
     */
    public function isAvailable(): bool {
        $key = LibrarySetting::get('isbndb_api_key')['key'] ?? null;

        return !empty($key);
    }

    /**
     * Look up a book by ISBN via ISBNdb API.
     *
     * @param string $isbn Sanitized ISBN (10 or 13 digits)
     * @return array|null Normalized book data or null on failure
     */
    public function lookup(string $isbn): ?array {
        $apiKey = LibrarySetting::get('isbndb_api_key')['key'] ?? null;
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(5)->get("https://api2.isbndb.com/book/{$isbn}");

            if (!$response->successful()) {
                Log::warning("BookLookup: ISBNdb returned HTTP {$response->status()} for ISBN {$isbn}");
                return null;
            }

            $book = $response->json('book');
            if (!$book) {
                return null;
            }

            return [
                'title'            => $book['title'] ?? null,
                'title_long'       => $book['title_long'] ?? null,
                'authors'          => $book['authors'] ?? [],
                'publisher'        => $book['publisher'] ?? null,
                'isbn13'           => $book['isbn13'] ?? $isbn,
                'isbn10'           => $book['isbn'] ?? null,
                'pages'            => $book['pages'] ?? null,
                'publication_year' => $this->extractYear($book['date_published'] ?? null),
                'genre'            => $book['subjects'][0] ?? null,
                'subjects'         => $book['subjects'] ?? [],
                'dewey_decimal'    => $book['dewey_decimal'] ?? null,
                'cover_image_url'  => $book['image'] ?? null,
                'language'         => $book['language'] ?? null,
                'description'      => $book['synopsis'] ?? $book['overview'] ?? null,
                'binding'          => $book['binding'] ?? null,
                'source'           => 'isbndb',
            ];
        } catch (\Exception $e) {
            Log::warning("BookLookup: ISBNdb exception for ISBN {$isbn}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract a 4-digit year from a date string.
     */
    private function extractYear(?string $datePublished): ?int {
        if (!$datePublished) {
            return null;
        }

        if (preg_match('/(\d{4})/', $datePublished, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
