<?php

namespace App\Services\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExternalDocumentCacheService
{
    private const CACHE_DISK = 'local';
    private const CACHE_ROOT = 'cached-syllabus-documents';

    /**
     * @return array{stream:resource,mime_type:string,filename:string,cache_path:string}|null
     */
    public function openStream(Document $document): ?array
    {
        if (!$document->isExternalUrl() || blank($document->external_url)) {
            return null;
        }

        $cachePath = $this->cachePath($document);
        $disk = Storage::disk(self::CACHE_DISK);

        if (!$disk->exists($cachePath)) {
            $this->cacheRemoteDocument($document, $cachePath);
        }

        if (!$disk->exists($cachePath)) {
            return null;
        }

        $stream = $disk->readStream($cachePath);
        if (!is_resource($stream)) {
            return null;
        }

        return [
            'stream' => $stream,
            'mime_type' => $document->mime_type ?: ($disk->mimeType($cachePath) ?: 'application/octet-stream'),
            'filename' => $this->resolveFilename($document),
            'cache_path' => $cachePath,
        ];
    }

    public function cachePath(Document $document): string
    {
        $extension = $document->extension ?: pathinfo($this->resolveFilename($document), PATHINFO_EXTENSION) ?: 'pdf';

        return self::CACHE_ROOT
            . '/'
            . $document->id
            . '-'
            . sha1((string) $document->external_url)
            . '.'
            . strtolower((string) $extension);
    }

    private function cacheRemoteDocument(Document $document, string $cachePath): void
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Accept' => $document->mime_type ?: 'application/pdf,application/octet-stream,*/*',
                ])
                ->get((string) $document->external_url);

            if (!$response->successful() || $response->body() === '') {
                throw new \RuntimeException('Remote document request was unsuccessful.');
            }

            Storage::disk(self::CACHE_DISK)->put($cachePath, $response->body());
        } catch (\Throwable $e) {
            Log::warning('Failed to cache external syllabus document.', [
                'document_id' => $document->id,
                'external_url' => $document->external_url,
                'cache_path' => $cachePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveFilename(Document $document): string
    {
        $filename = trim((string) ($document->original_name ?: ''));
        if ($filename !== '') {
            return $filename;
        }

        $title = trim((string) ($document->title ?: 'syllabus-document'));
        $extension = $document->extension ?: 'pdf';

        return Str::slug($title, '-') . '.' . strtolower((string) $extension);
    }
}
