<?php

namespace App\Console\Commands;

use App\Services\ClientSetup\ClientSetupReleaseCheckService;
use Illuminate\Console\Command;

class CheckClientSetupRelease extends Command
{
    protected $signature = 'client-setup:release-check {--strict : Treat warnings as release failures} {--json : Emit machine-readable JSON}';

    protected $description = 'Check client-setup production release prerequisites';

    public function handle(ClientSetupReleaseCheckService $releaseCheckService): int
    {
        $result = $releaseCheckService->check(
            (bool) $this->option('strict') || app()->environment('production')
        );

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($result['checks'] as $check) {
                $this->line(sprintf(
                    '[%s] %s: %s',
                    strtoupper($check['status']),
                    $check['key'],
                    $check['message']
                ));
            }
            $this->line(sprintf(
                'Result: %s (%d failure(s), %d warning(s)).',
                $result['passed'] ? 'READY' : 'BLOCKED',
                $result['failures'],
                $result['warnings']
            ));
        }

        return $result['passed'] ? self::SUCCESS : self::FAILURE;
    }
}
