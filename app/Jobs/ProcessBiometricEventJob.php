<?php

namespace App\Jobs;

use App\Models\CrmAttendanceDevice;
use App\Services\Crm\BiometricEventProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBiometricEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly CrmAttendanceDevice $device,
        public readonly array $payload
    ) {
        $this->onQueue(config('heritage_crm.attendance.queue.queue', 'crm-attendance'));
    }

    public function handle(BiometricEventProcessor $processor): void
    {
        $processor->process($this->device, $this->payload);
    }
}
