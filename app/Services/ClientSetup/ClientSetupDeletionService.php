<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ClientSetupDeletionService
{
    public function forceDelete(CrmClientSetupSubmission $submission): void
    {
        $attachments = $submission->attachments()
            ->get(['disk', 'path'])
            ->map(fn ($attachment): array => [
                'disk' => $attachment->disk ?: 'documents',
                'path' => $attachment->path,
            ])
            ->all();

        DB::transaction(function () use ($submission): void {
            $submission->forceDelete();
        });

        foreach ($attachments as $attachment) {
            try {
                Storage::disk($attachment['disk'])->delete($attachment['path']);
            } catch (Throwable $exception) {
                Log::warning('Client setup attachment cleanup failed after force deletion.', [
                    'submission_uuid' => $submission->uuid,
                    'attachment_path' => $attachment['path'],
                    'reason' => $exception->getMessage(),
                ]);
            }
        }

        try {
            Storage::disk('documents')->deleteDirectory('client-setup/' . $submission->uuid);
        } catch (Throwable $exception) {
            Log::warning('Client setup attachment directory cleanup failed after force deletion.', [
                'submission_uuid' => $submission->uuid,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
