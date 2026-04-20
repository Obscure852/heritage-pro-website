<?php

namespace App\Console\Commands;

use App\Services\SchoolModeProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class PreF3MigrationRunCommand extends Command
{
    protected $signature = 'pref3:migration:run';

    protected $description = 'Initialize PRE_F3 migration bookkeeping and canonical target structure.';

    public function handle(SchoolModeProvisioner $provisioner): int
    {
        try {
            $batchId = DB::table('migration_batches')->insertGetId([
                'mode' => 'PRE_F3',
                'status' => 'initialized',
                'summary' => json_encode(['message' => 'PRE_F3 target initialized by migration run command.'], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $result = $provisioner->provisionMode('PRE_F3');

            DB::table('migration_batches')->where('id', $batchId)->update([
                'status' => 'prepared',
                'summary' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);

            $this->info("PRE_F3 migration target prepared. Batch {$batchId} created.");

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}
