<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreVersionRequest;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentVersion;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\QuotaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentVersionController extends Controller {
    protected DocumentStorageService $storageService;
    protected QuotaService $quotaService;

    public function __construct(DocumentStorageService $storageService, QuotaService $quotaService) {
        $this->storageService = $storageService;
        $this->quotaService = $quotaService;
    }

    /**
     * Show the version upload form.
     *
     * Provides version preview info (next minor/major numbers) for the UI.
     */
    public function create(Document $document): View {
        $this->authorize('uploadVersion', $document);
        abort_unless($document->supportsVersioning(), 404);

        $nextMinor = DocumentVersion::calculateNextVersion($document->current_version, DocumentVersion::TYPE_MINOR);
        $nextMajor = DocumentVersion::calculateNextVersion($document->current_version, DocumentVersion::TYPE_MAJOR);

        return view('documents.versions.create', compact('document', 'nextMinor', 'nextMajor'));
    }

    /**
     * Upload a new version of a document.
     *
     * Stores the file first, then wraps all DB work in a transaction with lockForUpdate
     * to prevent concurrent version number conflicts. Cleans up orphan files on failure.
     */
    public function store(StoreVersionRequest $request, Document $document): RedirectResponse {
        abort_unless($document->supportsVersioning(), 404);

        $validated = $request->validated();
        $uploadedFile = $request->file('file');

        $projectedIncrease = max(0, (int) $uploadedFile->getSize() - (int) $document->size_bytes);
        $quotaCheck = ['allowed' => true, 'reason' => null];
        if ($projectedIncrease > 0) {
            $quotaCheck = $this->quotaService->canUpload($request->user(), $projectedIncrease);
        }
        if (!$quotaCheck['allowed']) {
            return back()
                ->withErrors(['file' => $quotaCheck['reason']])
                ->withInput();
        }

        // Store file OUTSIDE transaction (filesystem operation)
        $fileData = $this->storageService->store($uploadedFile, auth()->id());

        try {
            DB::transaction(function () use ($document, $validated, $fileData) {
                // Lock document row to prevent concurrent version uploads producing same version number
                $lockedDocument = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();

                // Recalculate version number inside lock for accuracy
                $newVersionNumber = DocumentVersion::calculateNextVersion(
                    $lockedDocument->current_version,
                    $validated['version_type']
                );

                // Clear all existing is_current flags
                $lockedDocument->versions()->update(['is_current' => false]);

                // Create new version record
                $version = DocumentVersion::create([
                    'document_id' => $lockedDocument->id,
                    'version_number' => $newVersionNumber,
                    'version_type' => $validated['version_type'],
                    'storage_disk' => $this->storageService->disk(),
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'version_notes' => $validated['version_notes'] ?? null,
                    'uploaded_by_user_id' => auth()->id(),
                    'is_current' => true,
                ]);

                // Update document metadata to reflect new current version
                $lockedDocument->update([
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'extension' => $fileData['extension'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'current_version' => $newVersionNumber,
                    'version_count' => $lockedDocument->version_count + 1,
                ]);

                // Create audit trail entry
                DocumentAudit::create([
                    'document_id' => $lockedDocument->id,
                    'version_id' => $version->id,
                    'user_id' => auth()->id(),
                    'action' => DocumentAudit::ACTION_VERSIONED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'version_number' => $newVersionNumber,
                        'version_type' => $validated['version_type'],
                        'original_name' => $fileData['original_name'],
                        'size_bytes' => $fileData['size_bytes'],
                    ],
                ]);
            });

            $owner = $document->owner;
            if ($owner) {
                $this->quotaService->recalculate($owner);
            }

            $redirect = redirect()->route('documents.show', $document)
                ->with('success', 'New version uploaded successfully.');

            if ($quotaCheck['reason'] === 'over_quota_warning') {
                $redirect->with('warning', 'You are over your storage quota. Please free up space to avoid upload blocks.');
            }

            return $redirect;
        } catch (\Exception $e) {
            // Clean up orphan file on failure
            $this->storageService->delete($fileData['storage_path']);
            throw $e;
        }
    }

    /**
     * Download a specific version of a document.
     *
     * Uses the version's own storage_path, not the document's current path.
     */
    public function download(Document $document, DocumentVersion $version): StreamedResponse {
        $this->authorize('view', $document);
        abort_unless($document->supportsVersioning(), 404);

        // Prevent accessing versions from other documents
        if ($version->document_id !== $document->id) {
            abort(404);
        }

        $stream = $this->storageService->download($version->storage_path);
        if (!$stream) {
            abort(404, 'Version file not found.');
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $version->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $version->original_name . '"',
        ]);
    }

    /**
     * Restore a previous version as the new current version.
     *
     * Creates a new minor version with a copy of the selected version's file.
     * The original version's file remains untouched.
     */
    public function restore(Request $request, Document $document, DocumentVersion $version): RedirectResponse {
        $this->authorize('uploadVersion', $document);
        abort_unless($document->supportsVersioning(), 404);

        // Prevent accessing versions from other documents
        if ($version->document_id !== $document->id) {
            abort(404);
        }

        // Cannot restore the already-current version
        if ($version->is_current) {
            abort(400, 'This version is already the current version.');
        }

        $projectedIncrease = max(0, (int) $version->size_bytes - (int) $document->size_bytes);
        $quotaCheck = ['allowed' => true, 'reason' => null];
        if ($projectedIncrease > 0) {
            $quotaCheck = $this->quotaService->canUpload($request->user(), $projectedIncrease);
        }
        if (!$quotaCheck['allowed']) {
            return back()->withErrors(['file' => $quotaCheck['reason']]);
        }

        // Copy file OUTSIDE transaction (filesystem operation)
        $fileData = $this->storageService->copyFile($version->storage_path, auth()->id());

        try {
            DB::transaction(function () use ($document, $version, $fileData) {
                // Lock document row to prevent concurrent modifications
                $lockedDocument = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();

                // Always creates a new minor version for restores
                $newVersionNumber = DocumentVersion::calculateNextVersion(
                    $lockedDocument->current_version,
                    DocumentVersion::TYPE_MINOR
                );

                // Clear all existing is_current flags
                $lockedDocument->versions()->update(['is_current' => false]);

                // Create new version record from copied file
                $newVersion = DocumentVersion::create([
                    'document_id' => $lockedDocument->id,
                    'version_number' => $newVersionNumber,
                    'version_type' => DocumentVersion::TYPE_MINOR,
                    'storage_disk' => $this->storageService->disk(),
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'version_notes' => 'Restored from version ' . $version->version_number,
                    'uploaded_by_user_id' => auth()->id(),
                    'is_current' => true,
                ]);

                // Update document metadata to reflect restored version
                $lockedDocument->update([
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'extension' => $fileData['extension'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'current_version' => $newVersionNumber,
                    'version_count' => $lockedDocument->version_count + 1,
                ]);

                // Create audit trail entry
                DocumentAudit::create([
                    'document_id' => $lockedDocument->id,
                    'version_id' => $newVersion->id,
                    'user_id' => auth()->id(),
                    'action' => DocumentAudit::ACTION_VERSION_RESTORED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'source_version' => $version->version_number,
                        'new_version' => $newVersionNumber,
                        'original_name' => $fileData['original_name'],
                    ],
                ]);
            });

            $owner = $document->owner;
            if ($owner) {
                $this->quotaService->recalculate($owner);
            }

            $redirect = redirect()->route('documents.show', $document)
                ->with('success', 'Version ' . $version->version_number . ' has been restored.');

            if ($quotaCheck['reason'] === 'over_quota_warning') {
                $redirect->with('warning', 'You are over your storage quota. Please free up space to avoid upload blocks.');
            }

            return $redirect;
        } catch (\Exception $e) {
            // Clean up orphan file on failure
            $this->storageService->delete($fileData['storage_path']);
            throw $e;
        }
    }
}
