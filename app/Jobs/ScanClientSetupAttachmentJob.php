<?php

namespace App\Jobs;

use App\Models\CrmClientSetupAttachment;
use App\Services\ClientSetup\ClientSetupAttachmentScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanClientSetupAttachmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $attachmentId
    ) {
    }

    public function handle(ClientSetupAttachmentScanService $scanService): void
    {
        $attachment = CrmClientSetupAttachment::query()->find($this->attachmentId);

        if (! $attachment) {
            return;
        }

        $scanService->scan($attachment);
    }
}
