<?php

namespace App\Console\Commands;

use App\Models\SchoolSetup;
use App\Services\SchoolModeProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class JuniorSeniorMigrationRunCommand extends Command
{
    protected $signature = 'junior-senior:migration:run';

    protected $description = 'Initialize JUNIOR_SENIOR migration bookkeeping and canonical target structure.';

    public function handle(SchoolModeProvisioner $provisioner): int
    {
        try {
            $batchId = DB::table('migration_batches')->insertGetId([
                'mode' => SchoolSetup::TYPE_JUNIOR_SENIOR,
                'status' => 'initialized',
                'summary' => json_encode(['message' => 'JUNIOR_SENIOR target initialized by migration run command.'], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $result = $provisioner->provisionMode(SchoolSetup::TYPE_JUNIOR_SENIOR);

            DB::table('migration_batches')->where('id', $batchId)->update([
                'status' => 'prepared',
                'summary' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);

            $this->info("JUNIOR_SENIOR migration target prepared. Batch {$batchId} created.");

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}
