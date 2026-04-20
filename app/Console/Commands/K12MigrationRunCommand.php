<?php

namespace App\Console\Commands;

use App\Models\SchoolSetup;
use App\Services\SchoolModeProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class K12MigrationRunCommand extends Command
{
    protected $signature = 'k12:migration:run';

    protected $description = 'Initialize K12 migration bookkeeping and canonical target structure.';

    public function handle(SchoolModeProvisioner $provisioner): int
    {
        try {
            $batchId = DB::table('migration_batches')->insertGetId([
                'mode' => SchoolSetup::TYPE_K12,
                'status' => 'initialized',
                'summary' => json_encode(['message' => 'K12 target initialized by migration run command.'], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $result = $provisioner->provisionMode(SchoolSetup::TYPE_K12);

            DB::table('migration_batches')->where('id', $batchId)->update([
                'status' => 'prepared',
                'summary' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);

            $this->info("K12 migration target prepared. Batch {$batchId} created.");

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}
