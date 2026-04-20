<?php

namespace App\Services\Library;

use App\Services\Library\Providers\IsbnDbProvider;
use App\Services\Library\Providers\OpenLibraryProvider;
use Illuminate\Support\Facades\Log;

class BookLookupService {
    /**
     * Ordered provider chain (priority: ISBNdb first, Open Library fallback).
     *
     * @var array
     */
    protected array $providers;

    public function __construct() {
        $this->providers = [
            new IsbnDbProvider(),
            new OpenLibraryProvider(),
        ];
    }

    /**
     * Look up book metadata by ISBN.
     *
     * Sanitizes the ISBN, validates format, then tries each provider
     * in order until one returns a result.
     *
     * @param string $isbn Raw ISBN input (may contain hyphens/spaces)
     * @return array|null Normalized book data or null if all providers fail
     */
    public function lookup(string $isbn): ?array {
        // Sanitize: remove hyphens and spaces, trim
        $isbn = preg_replace('/[\s\-]/', '', trim($isbn));

        // Validate: must be 10 or 13 digits
        if (!preg_match('/^\d{10}(\d{3})?$/', $isbn)) {
            Log::info("BookLookup: Invalid ISBN format: {$isbn}");
            return null;
        }

        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                Log::info("BookLookup: {$provider->name()} is not available, skipping");
                continue;
            }

            try {
                $result = $provider->lookup($isbn);
                if ($result) {
                    Log::info("BookLookup: {$provider->name()} returned data for ISBN {$isbn}");
                    return $result;
                }
            } catch (\Exception $e) {
                Log::warning("BookLookup: {$provider->name()} failed for ISBN {$isbn}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        Log::warning("BookLookup: All providers failed for ISBN {$isbn}");
        return null;
    }

    /**
     * Get names of currently available providers.
     *
     * @return array<string>
     */
    public function getAvailableProviders(): array {
        $available = [];
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable()) {
                $available[] = $provider->name();
            }
        }

        return $available;
    }
}
