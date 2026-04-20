<?php

namespace App\Console\Commands;

use App\Services\SchoolModeProvisioner;
use Illuminate\Console\Command;
use Throwable;

class ProvisionSchoolModeCommand extends Command
{
    protected $signature = 'school:provision-mode
        {--mode= : School mode to provision (Primary, Junior, Senior, PRE_F3, JUNIOR_SENIOR, K12)}';

    protected $description = 'Provision canonical academic structure for a school mode.';

    public function handle(SchoolModeProvisioner $provisioner): int
    {
        $mode = (string) $this->option('mode');

        if ($mode === '') {
            $this->error('The --mode option is required.');

            return self::FAILURE;
        }

        try {
            $result = $provisioner->provisionMode($mode);

            $this->info('School mode provisioned successfully.');
            $this->table(
                ['Key', 'Value'],
                collect($result)->map(fn ($value, $key) => [$key, (string) $value])->all()
            );

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}
