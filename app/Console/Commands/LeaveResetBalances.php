<?php

namespace App\Console\Commands;

use App\Services\Crm\LeaveBalanceService;
use Illuminate\Console\Command;

class LeaveResetBalances extends Command
{
    protected $signature = 'leave:reset-balances {year?}';

    protected $description = 'Reset leave balances for a new year with carry-over calculation';

    public function handle(LeaveBalanceService $balanceService): int
    {
        $year = (int) ($this->argument('year') ?? ($balanceService->currentLeaveYear() + 1));

        $this->info("Resetting leave balances for year {$year}...");

        $count = $balanceService->resetBalancesForYear($year);

        $this->info("Done. {$count} balance records processed.");

        return self::SUCCESS;
    }
}
