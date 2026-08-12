<?php

namespace App\Services\ClientSetup;

use App\Jobs\ImportClientSetupMigrationJob;
use App\Models\CrmClientSetupMigrationUpload;
use App\Models\User;
use App\Services\ClientSetup\Contracts\ClientSetupMigrationImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ClientSetupMigrationImportService
{
    public function __construct(
        private readonly ClientSetupAuditService $auditService
    ) {
    }

    public function isConfigured(): bool
    {
        $adapter = config('client_setup.release.importer_adapter');

        return is_string($adapter)
            && $adapter !== ''
            && class_exists($adapter)
            && is_a($adapter, ClientSetupMigrationImporter::class, true);
    }

    public function queue(
        CrmClientSetupMigrationUpload $upload,
        User $actor
    ): CrmClientSetupMigrationUpload {
        if (! $this->isConfigured()) {
            throw new RuntimeException('No approved production importer adapter is configured.');
        }

        $queued = DB::transaction(function () use ($upload, $actor): CrmClientSetupMigrationUpload {
            $locked = CrmClientSetupMigrationUpload::query()
                ->with(['submission', 'attachment'])
                ->lockForUpdate()
                ->findOrFail($upload->id);

            $this->assertImportGates($locked);

            if ($locked->import_status === 'completed') {
                return $locked->fresh(['attachment', 'importRequestedBy']);
            }

            if (in_array($locked->import_status, ['queued', 'running'], true)) {
                throw new RuntimeException('This migration upload is already queued or importing.');
            }

            $locked->forceFill([
                'import_status' => 'queued',
                'import_requested_by_id' => $actor->id,
                'import_started_at' => null,
                'import_completed_at' => null,
                'import_reference' => null,
                'import_summary' => null,
                'import_error' => null,
            ])->save();

            $this->auditService->record($locked->submission, 'migration_import_queued', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'metadata' => [
                    'migration_upload_uuid' => $locked->uuid,
                    'kind' => $locked->kind,
                    'template_version' => $locked->template_version,
                ],
            ]);

            return $locked->fresh(['attachment', 'importRequestedBy']);
        });

        if ($queued->import_status === 'queued') {
            ImportClientSetupMigrationJob::dispatch($queued->id, $actor->id);
        }

        return $queued;
    }

    public function execute(int $uploadId, int $actorId): CrmClientSetupMigrationUpload
    {
        $state = DB::transaction(function () use ($uploadId): array {
            $locked = CrmClientSetupMigrationUpload::query()
                ->with(['submission', 'attachment'])
                ->lockForUpdate()
                ->find($uploadId);

            if (! $locked || in_array($locked->import_status, ['completed', 'running'], true)) {
                return [
                    'upload' => $locked?->fresh(['attachment', 'importRequestedBy']),
                    'should_execute' => false,
                ];
            }

            if ($locked->import_status !== 'queued') {
                throw new RuntimeException('Only a queued migration upload can be executed.');
            }

            $this->assertImportGates($locked);
            $locked->forceFill([
                'import_status' => 'running',
                'import_started_at' => now(),
                'import_error' => null,
            ])->save();

            return [
                'upload' => $locked->fresh(['submission', 'attachment', 'importRequestedBy']),
                'should_execute' => true,
            ];
        });

        /** @var CrmClientSetupMigrationUpload|null $upload */
        $upload = $state['upload'];
        if (! $upload) {
            throw new RuntimeException('The migration upload could not be found.');
        }

        if (! $state['should_execute']) {
            return $upload;
        }

        try {
            $adapterClass = config('client_setup.release.importer_adapter');
            /** @var ClientSetupMigrationImporter $importer */
            $importer = app($adapterClass);
            $summary = $importer->import($upload);

            return $this->markCompleted($upload, $actorId, $summary);
        } catch (Throwable $exception) {
            Log::error('Client setup migration import failed.', [
                'upload_id' => $upload->id,
                'upload_uuid' => $upload->uuid,
                'exception' => $exception->getMessage(),
            ]);

            return $this->markFailed($upload, $actorId, $exception->getMessage());
        }
    }

    private function markCompleted(
        CrmClientSetupMigrationUpload $upload,
        int $actorId,
        array $summary
    ): CrmClientSetupMigrationUpload {
        return DB::transaction(function () use ($upload, $actorId, $summary): CrmClientSetupMigrationUpload {
            $locked = CrmClientSetupMigrationUpload::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($upload->id);

            if ($locked->import_status === 'completed') {
                return $locked->fresh(['attachment', 'importRequestedBy']);
            }

            $cleanSummary = $this->cleanSummary($summary);
            $locked->forceFill([
                'import_status' => 'completed',
                'import_completed_at' => now(),
                'import_reference' => $this->safeText($cleanSummary['reference'] ?? null, 255),
                'import_summary' => $cleanSummary,
                'import_error' => null,
            ])->save();

            $this->auditService->record($locked->submission, 'migration_import_completed', [
                'user' => User::query()->find($actorId),
                'actor_type' => 'crm_user',
                'metadata' => [
                    'migration_upload_uuid' => $locked->uuid,
                    'kind' => $locked->kind,
                    'summary' => $cleanSummary,
                ],
            ]);

            return $locked->fresh(['attachment', 'importRequestedBy']);
        });
    }

    private function markFailed(
        CrmClientSetupMigrationUpload $upload,
        int $actorId,
        string $message
    ): CrmClientSetupMigrationUpload {
        return DB::transaction(function () use ($upload, $actorId, $message): CrmClientSetupMigrationUpload {
            $locked = CrmClientSetupMigrationUpload::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($upload->id);
            $safeMessage = $this->safeText($message, 2000) ?: 'The production importer failed.';

            $locked->forceFill([
                'import_status' => 'failed',
                'import_error' => $safeMessage,
                'import_completed_at' => null,
            ])->save();

            $this->auditService->record($locked->submission, 'migration_import_failed', [
                'user' => User::query()->find($actorId),
                'actor_type' => 'crm_user',
                'metadata' => [
                    'migration_upload_uuid' => $locked->uuid,
                    'kind' => $locked->kind,
                    'error' => $safeMessage,
                ],
            ]);

            return $locked->fresh(['attachment', 'importRequestedBy']);
        });
    }

    private function assertImportGates(CrmClientSetupMigrationUpload $upload): void
    {
        if ($upload->crm_approval_status !== 'approved') {
            throw new RuntimeException('The migration upload must be approved by CRM before import.');
        }
        if ($upload->validation_status !== 'validated') {
            throw new RuntimeException('The migration upload must have no validation errors before import.');
        }
        if ($upload->template_compatibility_status !== 'compatible') {
            throw new RuntimeException('The migration upload must match the current approved template version before import.');
        }
        if (! $upload->attachment || $upload->attachment->scan_status !== 'approved') {
            throw new RuntimeException('The migration upload must pass security scanning before import.');
        }
    }

    private function cleanSummary(array $summary): array
    {
        $clean = [];
        foreach ($summary as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $clean[(string) $key] = $value;
            } elseif (is_array($value)) {
                $clean[(string) $key] = $this->cleanSummary($value);
            }
        }

        return $clean;
    }

    private function safeText(mixed $value, int $length): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return mb_substr(trim((string) $value), 0, $length);
    }
}
