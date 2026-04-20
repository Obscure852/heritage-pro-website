<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\QuotaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeDeletedDocuments extends Command {
    protected $signature = 'documents:purge-trash';

    protected $description = 'Permanently delete soft-deleted documents older than 30 days';

    public function handle(DocumentStorageService $storageService, QuotaService $quotaService): int {
        $trashDays = config('documents.retention.trash_retention_days', 30);

        $documents = Document::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($trashDays))
            ->with('owner', 'versions')
            ->get();

        $purged = 0;
        $skipped = 0;

        foreach ($documents as $document) {
            if ($document->legal_hold) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($document, $storageService, $quotaService) {
                    // Audit before deletion
                    DocumentAudit::create([
                        'document_id' => $document->id,
                        'action' => DocumentAudit::ACTION_DELETED,
                        'metadata' => [
                            'reason' => 'trash_purge',
                            'original_title' => $document->title,
                            'deleted_at' => $document->deleted_at->toDateTimeString(),
                        ],
                    ]);

                    // Delete physical files for all versions
                    foreach ($document->versions as $version) {
                        try {
                            $storageService->delete($version->storage_path);
                        } catch (\Throwable $e) {
                            Log::warning('Failed to delete version file during purge', [
                                'document_id' => $document->id,
                                'version_id' => $version->id,
                                'path' => $version->storage_path,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Delete main document file
                    try {
                        $storageService->delete($document->storage_path);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to delete document file during purge', [
                            'document_id' => $document->id,
                            'path' => $document->storage_path,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Decrement owner quota
                    if ($document->owner) {
                        $quotaService->decrementUsage($document->owner, $document->size_bytes);
                    }

                    // Force delete the record
                    $document->forceDelete();
                });

                $purged++;
            } catch (\Throwable $e) {
                Log::error('Failed to purge document', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Permanently deleted {$purged} documents");
        if ($skipped > 0) {
            $this->info("Skipped {$skipped} documents (legal hold)");
        }

        return Command::SUCCESS;
    }
}
