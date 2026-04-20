<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class ExternalDocumentMetadata
{
    /**
     * @return array{original_name:?string,extension:?string,mime_type:?string}
     */
    public static function fromUrl(string $url, ?string $fallbackTitle = null): array
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $basename = trim(basename($path));

        if ($basename === '' || $basename === '.' || $basename === '/') {
            $basename = null;
        } else {
            $basename = urldecode($basename);
        }

        $extension = $basename ? strtolower((string) pathinfo($basename, PATHINFO_EXTENSION)) : null;
        if ($extension === '') {
            $extension = null;
        }

        if ($basename === null && filled($fallbackTitle)) {
            $slug = Str::slug((string) $fallbackTitle, '-');
            $basename = $slug !== '' ? $slug : null;
        }

        if ($basename !== null && $extension === null) {
            $detectedExtension = strtolower((string) pathinfo($basename, PATHINFO_EXTENSION));
            $extension = $detectedExtension !== '' ? $detectedExtension : null;
        }

        $mimeType = null;
        if ($extension !== null) {
            $mimeTypes = MimeTypes::getDefault()->getMimeTypes($extension);
            $mimeType = $mimeTypes[0] ?? null;
        }

        return [
            'original_name' => $basename,
            'extension' => $extension,
            'mime_type' => $mimeType,
        ];
    }
}
