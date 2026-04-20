<?php

namespace App\Console\Commands\Library;

use App\Models\Library\LibraryReservation;
use App\Services\Library\ReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireHolds extends Command {
    protected $signature = 'library:expire-holds
                            {--dry-run : Show what would expire without making changes}';

    protected $description = 'Expire uncollected holds past their pickup window';

    public function __construct(protected ReservationService $reservationService) {
        parent::__construct();
    }

    public function handle(): int {
        $isDryRun = $this->option('dry-run');

        $this->info('Library Hold Expiry');
        $this->info('===================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $expired = LibraryReservation::where('status', 'ready')
            ->where('expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No holds to expire.');
            Log::info('Library hold expiry completed', ['expired' => 0, 'dry_run' => $isDryRun]);
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $reservation) {
            $reservation->load('book', 'borrower');
            $bookTitle = $reservation->book->title ?? 'Unknown';
            $borrowerName = optional($reservation->borrower)->full_name
                ?? optional($reservation->borrower)->name
                ?? 'Unknown';

            if ($isDryRun) {
                $this->line("[DRY RUN] Would expire: Reservation #{$reservation->id} - \"{$bookTitle}\" held for {$borrowerName}");
            } else {
                try {
                    $this->reservationService->expireHold($reservation);
                    $this->line("Expired: Reservation #{$reservation->id} - \"{$bookTitle}\" held for {$borrowerName}");
                } catch (\Exception $e) {
                    $this->error("Failed to expire #{$reservation->id}: {$e->getMessage()}");
                }
            }
            $count++;
        }

        $prefix = $isDryRun ? '[DRY RUN] Would expire' : 'Expired';
        $this->info("{$prefix} {$count} hold(s).");

        Log::info('Library hold expiry completed', [
            'expired' => $count,
            'dry_run' => $isDryRun,
        ]);

        return Command::SUCCESS;
    }
}
