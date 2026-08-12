<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupAttachment;
use App\Services\ClientSetup\Contracts\ClientSetupScanner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ClientSetupAttachmentScanService
{
    private const TERMINAL_STATUSES = ['approved', 'rejected', 'failed'];

    public function __construct(
        private readonly ClientSetupAuditService $auditService
    ) {
    }

    public function isConfigured(): bool
    {
        $adapter = config('client_setup.release.scanner_adapter');

        return is_string($adapter)
            && $adapter !== ''
            && class_exists($adapter)
            && is_a($adapter, ClientSetupScanner::class, true);
    }

    public function scan(CrmClientSetupAttachment $attachment): CrmClientSetupAttachment
    {
        $adapterClass = config('client_setup.release.scanner_adapter');

        if (! $this->isConfigured()) {
            return $this->recordResult($attachment, [
                'status' => 'failed',
                'provider' => is_string($adapterClass) ? $adapterClass : null,
                'message' => 'No compatible attachment scanner is configured.',
            ]);
        }

        try {
            /** @var ClientSetupScanner $scanner */
            $scanner = app($adapterClass);
            $result = $scanner->scan($attachment);

            return $this->recordResult($attachment, $result);
        } catch (Throwable $exception) {
            Log::error('Client setup attachment scan failed.', [
                'attachment_id' => $attachment->id,
                'attachment_uuid' => $attachment->uuid,
                'exception' => $exception->getMessage(),
            ]);

            return $this->recordResult($attachment, [
                'status' => 'failed',
                'provider' => is_string($adapterClass) ? $adapterClass : null,
                'message' => 'The attachment scanner failed; the file remains blocked.',
            ]);
        }
    }

    public function recordResult(
        CrmClientSetupAttachment $attachment,
        array $result
    ): CrmClientSetupAttachment {
        $status = strtolower(trim((string) ($result['status'] ?? '')));

        if (! in_array($status, self::TERMINAL_STATUSES, true)) {
            throw new RuntimeException('The attachment scanner returned an invalid status.');
        }

        $updated = DB::transaction(function () use ($attachment, $result, $status): CrmClientSetupAttachment {
            $locked = CrmClientSetupAttachment::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($attachment->id);

            if (in_array($locked->scan_status, ['approved', 'rejected'], true)
                && $locked->scan_status !== $status) {
                throw new RuntimeException('A terminal attachment scan result cannot be overwritten.');
            }

            if ($locked->scan_status === $status
                && $locked->scan_completed_at !== null) {
                return $locked->fresh(['submission']);
            }

            $locked->forceFill([
                'scan_status' => $status,
                'scan_provider' => $this->safeText($result['provider'] ?? null, 150),
                'scan_reference' => $this->safeText($result['reference'] ?? null, 255),
                'scan_message' => $this->safeText($result['message'] ?? null, 1000),
                'scan_completed_at' => now(),
            ])->save();

            if ($status !== 'approved') {
                $locked->migrationUploads()
                    ->where('crm_approval_status', 'approved')
                    ->update([
                        'crm_approval_status' => 'pending',
                        'crm_approved_by_id' => null,
                        'crm_approved_at' => null,
                        'crm_approval_note' => null,
                    ]);
            }

            $this->auditService->record($locked->submission, 'attachment_scan_completed', [
                'actor_type' => 'system',
                'metadata' => [
                    'attachment_uuid' => $locked->uuid,
                    'scan_status' => $status,
                    'scan_provider' => $locked->scan_provider,
                    'scan_reference' => $locked->scan_reference,
                ],
            ]);

            return $locked->fresh(['submission']);
        });

        return $updated;
    }

    private function safeText(mixed $value, int $length): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return mb_substr(trim((string) $value), 0, $length);
    }
}
