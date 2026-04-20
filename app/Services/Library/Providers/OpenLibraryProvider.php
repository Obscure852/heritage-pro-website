<?php

namespace App\Services\Library\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenLibraryProvider {
    /**
     * Provider name.
     */
    public function name(): string {
        return 'Open Library';
    }

    /**
     * Open Library is always available (no auth required).
     */
    public function isAvailable(): bool {
        return true;
    }

    /**
     * Look up a book by ISBN via Open Library API.
     *
     * Makes 2-3 API calls:
     * 1. Edition data via /isbn/{isbn}.json
     * 2. Author name resolution via /authors/{key}.json (per author)
     * 3. Work data for subjects via /works/{key}.json
     *
     * @param string $isbn Sanitized ISBN (10 or 13 digits)
     * @return array|null Normalized book data or null on failure
     */
    public function lookup(string $isbn): ?array {
        try {
            // Step 1: Get edition data
            $response = Http::timeout(5)
                ->get("https://openlibrary.org/isbn/{$isbn}.json");

            if (!$response->successful()) {
                Log::warning("BookLookup: Open Library returned HTTP {$response->status()} for ISBN {$isbn}");
                return null;
            }

            $edition = $response->json();
            if (!$edition) {
                return null;
            }

            // Step 2: Resolve author names from author keys
            $authors = [];
            foreach ($edition['authors'] ?? [] as $authorRef) {
                $authorKey = $authorRef['key'] ?? null;
                if ($authorKey) {
                    usleep(200000); // 200ms delay between API calls
                    $authorResp = Http::timeout(3)
                        ->get("https://openlibrary.org{$authorKey}.json");
                    if ($authorResp->successful()) {
                        $authors[] = $authorResp->json('name') ?? 'Unknown';
                    }
                }
            }

            // Step 3: Get work data for subjects (optional)
            $subjects = [];
            $workKey = $edition['works'][0]['key'] ?? null;
            if ($workKey) {
                usleep(200000); // 200ms delay
                $workResp = Http::timeout(3)
                    ->get("https://openlibrary.org{$workKey}.json");
                if ($workResp->successful()) {
                    $subjects = $workResp->json('subjects') ?? [];
                }
            }

            // Cover URL construction
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";

            return [
                'title'            => $edition['title'] ?? null,
                'title_long'       => $edition['full_title'] ?? $edition['title'] ?? null,
                'authors'          => $authors,
                'publisher'        => ($edition['publishers'] ?? [null])[0],
                'isbn13'           => $this->findIsbn13($edition, $isbn),
                'isbn10'           => $this->findIsbn10($edition),
                'pages'            => $edition['number_of_pages'] ?? null,
                'publication_year' => $this->extractYear($edition['publish_date'] ?? null),
                'genre'            => $subjects[0] ?? null,
                'subjects'         => array_slice($subjects, 0, 10),
                'dewey_decimal'    => ($edition['dewey_decimal_class'] ?? [null])[0],
                'cover_image_url'  => $coverUrl,
                'language'         => $this->resolveLanguage($edition['languages'] ?? []),
                'description'      => is_string($edition['description'] ?? null)
                    ? $edition['description']
                    : ($edition['description']['value'] ?? null),
                'binding'          => $edition['physical_format'] ?? null,
                'source'           => 'openlibrary',
            ];
        } catch (\Exception $e) {
            Log::warning("BookLookup: Open Library exception for ISBN {$isbn}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Find ISBN-13 from edition data.
     */
    private function findIsbn13(array $edition, string $fallback): string {
        foreach ($edition['isbn_13'] ?? [] as $isbn) {
            return $isbn;
        }

        return $fallback;
    }

    /**
     * Find ISBN-10 from edition data.
     */
    private function findIsbn10(array $edition): ?string {
        foreach ($edition['isbn_10'] ?? [] as $isbn) {
            return $isbn;
        }

        return null;
    }

    /**
     * Extract a 4-digit year from a date string.
     */
    private function extractYear(?string $publishDate): ?int {
        if (!$publishDate) {
            return null;
        }

        if (preg_match('/(\d{4})/', $publishDate, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Resolve language code to human-readable name.
     * Open Library stores languages as keys like "/languages/eng".
     */
    private function resolveLanguage(array $languages): ?string {
        $key = $languages[0]['key'] ?? null;
        if (!$key) {
            return null;
        }

        $code = basename($key);
        $map = [
            'eng' => 'English',
            'fre' => 'French',
            'spa' => 'Spanish',
            'ger' => 'German',
            'por' => 'Portuguese',
            'ita' => 'Italian',
            'dut' => 'Dutch',
            'rus' => 'Russian',
            'chi' => 'Chinese',
            'jpn' => 'Japanese',
            'ara' => 'Arabic',
            'hin' => 'Hindi',
            'sot' => 'Sotho',
            'tsn' => 'Tswana',
        ];

        return $map[$code] ?? $code;
    }
}
