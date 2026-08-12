<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupAttachment;
use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use App\Jobs\ScanClientSetupAttachmentJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ClientSetupAttachmentService
{
    public function __construct(
        private readonly ClientSetupAuditService $auditService
    ) {
    }

    public function store(
        CrmClientSetupSubmission $submission,
        CrmClientSetupInvitation $invitation,
        UploadedFile $file,
        string $category,
        string $requirement = 'optional'
    ): CrmClientSetupAttachment {
        $disk = 'documents';
        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = $uuid . '.' . $extension;
        $path = null;

        try {
            $path = $file->storeAs('client-setup/' . $submission->uuid, $filename, $disk);

            if (! $path) {
                throw new RuntimeException('The attachment could not be stored.');
            }

            $attachment = DB::transaction(function () use ($submission, $invitation, $file, $category, $requirement, $disk, $path): CrmClientSetupAttachment {
                $attachment = $submission->attachments()->create([
                    'invitation_id' => $invitation->id,
                    'category' => trim($category),
                    'requirement' => $requirement,
                    'original_name' => $file->getClientOriginalName(),
                    'disk' => $disk,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize() ?: 0,
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'scan_status' => 'pending',
                    'uploaded_at' => now(),
                ]);

                $this->auditService->record($submission, 'attachment_uploaded', [
                    'invitation' => $invitation,
                    'metadata' => [
                        'attachment_uuid' => $attachment->uuid,
                        'category' => $attachment->category,
                        'original_name' => $attachment->original_name,
                        'scan_status' => $attachment->scan_status,
                    ],
                ]);

                return $attachment;
            });

            if (app(ClientSetupAttachmentScanService::class)->isConfigured()) {
                ScanClientSetupAttachmentJob::dispatch($attachment->id);
            }

            return $attachment;
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk($disk)->delete($path);
            }

            throw $exception;
        }
    }
}
