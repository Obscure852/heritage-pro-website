<?php

namespace App\Console\Commands\Library;

use App\Services\Library\FineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AssessFines extends Command {
    protected $signature = 'library:assess-fines
                            {--dry-run : Show what would be assessed without making changes}';

    protected $description = 'Assess fines on overdue and lost library items';

    public function __construct(protected FineService $fineService) {
        parent::__construct();
    }

    public function handle(): int {
        $isDryRun = $this->option('dry-run');

        $this->info('Library Fine Assessment');
        $this->info(str_repeat('-', 40));

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $result = $this->fineService->assessOverdueFines($isDryRun);

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['New fines assessed', $result['assessed']],
                ['Existing fines updated', $result['updated']],
                ['Skipped (lost book fine exists)', $result['skipped']],
            ]
        );

        $this->info('Fine assessment complete.');

        Log::info('Library fine assessment completed', [
            'assessed' => $result['assessed'],
            'updated' => $result['updated'],
            'skipped' => $result['skipped'],
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }
}
