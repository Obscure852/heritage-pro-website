<?php

namespace App\Console\Commands;

use App\Services\Documents\RetentionService;
use Illuminate\Console\Command;

class CheckDocumentExpirations extends Command {
    protected $signature = 'documents:check-expirations';

    protected $description = 'Check for expiring documents, send warnings, and auto-archive expired documents';

    public function handle(RetentionService $retentionService): int {
        $this->info('Checking document expirations...');

        $results = $retentionService->processExpirations();
        $this->info("{$results['warnings']} pre-expiry warnings sent");
        $this->info("{$results['grace_notices']} grace period notices sent");
        $this->info("{$results['archived']} documents archived (grace period expired)");
        $this->info("{$results['skipped_legal_hold']} skipped (legal hold)");

        $policyResults = $retentionService->processRetentionPolicies();
        $this->info("{$policyResults['policies_run']} retention policies executed");
        $this->info("{$policyResults['documents_affected']} documents affected by policies");

        return Command::SUCCESS;
    }
}
