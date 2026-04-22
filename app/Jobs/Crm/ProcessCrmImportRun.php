<?php

namespace App\Jobs\Crm;

use App\Models\CrmImportRun;
use App\Services\Crm\Imports\CrmImportRunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCrmImportRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $runId
    ) {
        $this->onConnection(config('heritage_crm.imports.queue.connection', config('queue.default')));
        $this->onQueue(config('heritage_crm.imports.queue.queue', 'crm-imports'));
    }

    public function handle(CrmImportRunService $runService): void
    {
        $run = CrmImportRun::query()->findOrFail($this->runId);
        $runService->process($run);
    }

    public function failed(\Throwable $exception): void
    {
        $run = CrmImportRun::query()->find($this->runId);

        if ($run) {
            app(CrmImportRunService::class)->fail($run, $exception);
        }
    }
}
