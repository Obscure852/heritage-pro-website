<?php

namespace App\Jobs;

use App\Services\ClientSetup\ClientSetupMigrationImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportClientSetupMigrationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly int $uploadId,
        public readonly int $actorId
    ) {
    }

    public function handle(ClientSetupMigrationImportService $importService): void
    {
        $importService->execute($this->uploadId, $this->actorId);
    }
}
