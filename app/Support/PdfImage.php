<?php

namespace App\Support;

class PdfImage
{
    public static function toDataUri(?string $path): ?string
    {
        $filePath = self::resolveLocalPath($path);

        if (!$filePath || !is_readable($filePath)) {
            return null;
        }

        $contents = @file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($filePath) ?: 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    public static function resolveLocalPath(?string $path): ?string
    {
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('/^https?:\/\//i', $path)) {
            $urlPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($urlPath) && $urlPath !== '' ? $urlPath : $path;
        }

        $relativePath = ltrim($path, '/');
        $relativeWithoutPublic = self::stripLeadingSegment($relativePath, 'public/');
        $relativeWithoutStorage = self::stripLeadingSegment($relativeWithoutPublic, 'storage/');

        $candidates = array_unique(array_filter([
            $path,
            public_path($relativePath),
            public_path($relativeWithoutPublic),
            public_path('storage/' . $relativeWithoutStorage),
            storage_path('app/' . $relativeWithoutPublic),
            storage_path('app/public/' . $relativeWithoutStorage),
        ]));

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function stripLeadingSegment(string $path, string $segment): string
    {
        return str_starts_with($path, $segment)
            ? substr($path, strlen($segment))
            : $path;
    }
}
