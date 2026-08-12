<?php

namespace Tests\Feature;

use App\Services\ClientSetup\ClientSetupReleaseCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSetupReleaseCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_release_check_passes_core_gates_and_reports_environment_warnings(): void
    {
        $result = app(ClientSetupReleaseCheckService::class)->check(false, false);

        $this->assertTrue($result['passed']);
        $this->assertSame(0, $result['failures']);
        $this->assertContains('database_schema', collect($result['checks'])->pluck('key')->all());
        $this->assertContains('templates', collect($result['checks'])->pluck('key')->all());
        $this->assertContains('warn', collect($result['checks'])->pluck('status')->all());
    }

    public function test_strict_production_check_blocks_without_environment_owned_release_controls(): void
    {
        $result = app(ClientSetupReleaseCheckService::class)->check(true, true);
        $failedKeys = collect($result['checks'])
            ->where('status', 'fail')
            ->pluck('key')
            ->all();

        $this->assertFalse($result['passed']);
        $this->assertContains('scanner', $failedKeys);
        $this->assertContains('importer', $failedKeys);
        $this->assertContains('backup', $failedKeys);
        $this->assertContains('pilot', $failedKeys);
    }

    public function test_release_check_command_returns_json_and_success_for_non_production_environment(): void
    {
        $this->artisan('client-setup:release-check', ['--json' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('"passed": true');
    }
}
