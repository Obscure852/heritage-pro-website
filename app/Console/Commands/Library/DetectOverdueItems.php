<?php

namespace App\Console\Commands\Library;

use App\Services\Library\OverdueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectOverdueItems extends Command {
    protected $signature = 'library:detect-overdue
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Detect and mark overdue library items';

    public function __construct(protected OverdueService $overdueService) {
        parent::__construct();
    }

    public function handle(): int {
        $isDryRun = $this->option('dry-run');

        $this->info('Library Overdue Detection');
        $this->info('========================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $results = $this->overdueService->detectAndMarkOverdue($isDryRun);

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Newly marked overdue', $isDryRun ? $results['would_mark'] : $results['marked_overdue']],
                ['Total overdue items', $results['already_overdue']],
            ]
        );

        Log::info('Library overdue detection completed', [
            'marked_overdue' => $results['marked_overdue'],
            'would_mark' => $results['would_mark'],
            'already_overdue' => $results['already_overdue'],
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }
}
