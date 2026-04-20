<?php

namespace App\Services\Documents;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for managing physical file operations in the Document Management System.
 *
 * Handles file upload, download, deletion, checksum calculation, and validation.
 * Does NOT handle Eloquent model creation — controllers handle that.
 * Files are organized by user ID: user-{id}/{ulid}_{sanitized_filename}
 *
 * Uses config('documents.storage.disk') for disk name (infrastructure, not admin-configurable).
 * Reads allowed_extensions and max_file_size_mb from DocumentSettingService (DB overrides config).
 */
class DocumentStorageService {
    protected DocumentSettingService $settingService;

    public function __construct(DocumentSettingService $settingService) {
        $this->settingService = $settingService;
    }
    /**
     * Get the configured storage disk name.
     *
     * @return string
     */
    public function disk(): string {
        return config('documents.storage.disk', 'documents');
    }

    /**
     * Get the Storage disk instance.
     *
     * @return Filesystem
     */
    protected function storage(): Filesystem {
        return Storage::disk($this->disk());
    }

    /**
     * Store an uploaded file and return storage metadata.
     *
     * Organizes files by user ID: user-{id}/{sanitized_filename}
     * Generates a unique filename using ULID prefix to prevent collisions.
     *
     * @param UploadedFile $file The uploaded file
     * @param int $userId The owner's user ID
     * @param string|null $folderPath Optional subfolder path within user directory
     * @return array{storage_path: string, original_name: string, mime_type: string, extension: string, size_bytes: int, checksum_sha256: string}
     *
     * @throws \RuntimeException If file storage fails
     */
    public function store(UploadedFile $file, int $userId, ?string $folderPath = null): array {
        try {
            $originalName = $file->getClientOriginalName();
            $sanitizedName = $this->sanitizeFilename($originalName);
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType() ?: $file->getClientMimeType();
            $sizeBytes = $file->getSize();

            // Build directory path
            $directory = 'user-' . $userId;
            if ($folderPath) {
                $directory .= '/' . trim($folderPath, '/');
            }

            // Generate unique filename with ULID prefix
            $uniqueFilename = (string) Str::ulid() . '_' . $sanitizedName;

            // Calculate SHA-256 checksum before storing (DOC-05)
            $checksum = hash_file('sha256', $file->getRealPath());

            // Store the file
            $storagePath = $directory . '/' . $uniqueFilename;
            $this->storage()->putFileAs($directory, $file, $uniqueFilename);

            return [
                'storage_path' => $storagePath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size_bytes' => (int) $sizeBytes,
                'checksum_sha256' => $checksum,
            ];
        } catch (\Exception $e) {
            Log::error('DocumentStorageService: Failed to store file', [
                'original_name' => $file->getClientOriginalName(),
                'user_id' => $userId,
                'folder_path' => $folderPath,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to store document file: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Download/get file contents as a stream.
     *
     * @param string $storagePath The path within the documents disk
     * @return resource|null File stream or null if not found
     */
    public function download(string $storagePath) {
        try {
            if (!$this->exists($storagePath)) {
                return null;
            }

            return $this->storage()->readStream($storagePath);
        } catch (\Exception $e) {
            Log::error('DocumentStorageService: Failed to download file', [
                'storage_path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get the full filesystem path for a stored file.
     *
     * @param string $storagePath The path within the documents disk
     * @return string Absolute filesystem path
     */
    public function fullPath(string $storagePath): string {
        return $this->storage()->path($storagePath);
    }

    /**
     * Delete a file from storage.
     *
     * @param string $storagePath The path within the documents disk
     * @return bool True if deleted successfully
     */
    public function delete(string $storagePath): bool {
        try {
            return $this->storage()->delete($storagePath);
        } catch (\Exception $e) {
            Log::error('DocumentStorageService: Failed to delete file', [
                'storage_path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if a file exists in storage.
     *
     * @param string $storagePath The path within the documents disk
     * @return bool
     */
    public function exists(string $storagePath): bool {
        return $this->storage()->exists($storagePath);
    }

    /**
     * Get file size in bytes.
     *
     * @param string $storagePath The path within the documents disk
     * @return int File size in bytes
     */
    public function size(string $storagePath): int {
        return $this->storage()->size($storagePath);
    }

    /**
     * Calculate SHA-256 checksum for an existing stored file.
     *
     * @param string $storagePath The path within the documents disk
     * @return string 64-character hex SHA-256 hash
     *
     * @throws \RuntimeException If file does not exist or checksum fails
     */
    public function checksum(string $storagePath): string {
        $fullPath = $this->fullPath($storagePath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("File not found at path: {$storagePath}");
        }

        $hash = hash_file('sha256', $fullPath);

        if ($hash === false) {
            throw new \RuntimeException("Failed to calculate checksum for: {$storagePath}");
        }

        return $hash;
    }

    /**
     * Copy an existing stored file to a new storage path.
     *
     * Used for version restore operations — creates an independent copy
     * with a new ULID-prefixed filename in the user's directory.
     *
     * @param string $sourcePath The source file's storage path within the documents disk
     * @param int $userId The user ID for the destination directory
     * @return array{storage_path: string, original_name: string, mime_type: string, extension: string, size_bytes: int, checksum_sha256: string}
     *
     * @throws \RuntimeException If source file does not exist or copy fails
     */
    public function copyFile(string $sourcePath, int $userId): array {
        try {
            $fullSourcePath = $this->fullPath($sourcePath);

            if (!file_exists($fullSourcePath)) {
                throw new \RuntimeException("Source file not found at path: {$sourcePath}");
            }

            // Extract clean filename by stripping any existing ULID prefix
            $basename = basename($sourcePath);
            $cleanName = preg_replace('/^[A-Z0-9]{26}_/', '', $basename);

            // Generate new unique filename with ULID prefix
            $uniqueFilename = (string) Str::ulid() . '_' . $cleanName;
            $directory = 'user-' . $userId;
            $newStoragePath = $directory . '/' . $uniqueFilename;

            // Copy the file on disk
            $this->storage()->copy($sourcePath, $newStoragePath);

            // Calculate metadata for the new copy
            $checksum = hash_file('sha256', $this->fullPath($newStoragePath));
            $size = $this->size($newStoragePath);
            $extension = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));
            $mimeType = mime_content_type($this->fullPath($newStoragePath));

            return [
                'storage_path' => $newStoragePath,
                'original_name' => $cleanName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size_bytes' => $size,
                'checksum_sha256' => $checksum,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('DocumentStorageService: Failed to copy file', [
                'source_path' => $sourcePath,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to copy document file: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate that the uploaded file meets DMS requirements.
     *
     * Checks extension against DocumentSettingService allowed_extensions
     * and size against DocumentSettingService storage.max_file_size_mb.
     *
     * @param UploadedFile $file The uploaded file to validate
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateFile(UploadedFile $file): array {
        $errors = [];

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $this->settingService->get('allowed_extensions', []);

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "File extension '{$extension}' is not allowed. Allowed extensions: " . implode(', ', $allowedExtensions);
        }

        // Check file size
        $maxSizeMb = $this->settingService->get('storage.max_file_size_mb', 50);
        $maxSizeBytes = $maxSizeMb * 1024 * 1024;
        $fileSize = $file->getSize();

        if ($fileSize > $maxSizeBytes) {
            $fileSizeMb = round($fileSize / (1024 * 1024), 2);
            $errors[] = "File size ({$fileSizeMb}MB) exceeds the maximum allowed size ({$maxSizeMb}MB).";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Sanitize a filename for safe storage.
     *
     * Removes special characters, preserves extension, limits length to 200 characters.
     *
     * @param string $filename The original filename
     * @return string Sanitized filename
     */
    protected function sanitizeFilename(string $filename): string {
        // Get extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Remove special characters, keep alphanumeric, hyphens, underscores, and dots
        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);

        // Remove consecutive underscores
        $name = preg_replace('/_+/', '_', $name);

        // Trim underscores from start and end
        $name = trim($name, '_');

        // Limit length (200 chars for name, leaving room for ULID prefix and extension)
        if (strlen($name) > 200) {
            $name = substr($name, 0, 200);
        }

        // Ensure name is not empty
        if (empty($name)) {
            $name = 'unnamed';
        }

        // Reattach extension
        if ($extension) {
            return $name . '.' . strtolower($extension);
        }

        return $name;
    }
}
