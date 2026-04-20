<?php

namespace App\Console\Commands\Timetable;

use App\Services\Timetable\TimetableIntegrityService;
use Illuminate\Console\Command;

class RepairTimetableCommand extends Command {
    protected $signature = 'timetable:repair {timetable_id : Timetable ID to analyze and repair}';
    protected $description = 'Analyze and repair non-locked timetable integrity issues';

    public function handle(TimetableIntegrityService $integrityService): int {
        $timetableId = (int) $this->argument('timetable_id');
        if ($timetableId <= 0) {
            $this->error('timetable_id must be a positive integer.');
            return Command::FAILURE;
        }

        $this->info("Analyzing timetable {$timetableId}...");
        $result = $integrityService->repairNonLocked($timetableId);

        $beforeCounts = $result['before']['counts'] ?? [];
        $afterCounts = $result['after']['counts'] ?? [];

        $this->line('Before repair:');
        foreach ($beforeCounts as $type => $count) {
            $this->line(" - {$type}: {$count}");
        }

        $this->info("Deleted non-locked slots: {$result['deleted_count']}");
        if (!empty($result['deleted_slot_ids'])) {
            $this->line('Deleted slot IDs: ' . implode(', ', $result['deleted_slot_ids']));
        }

        $this->line('After repair:');
        foreach ($afterCounts as $type => $count) {
            $this->line(" - {$type}: {$count}");
        }

        $lockedBlockers = $result['unresolved_locked'] ?? [];
        if (!empty($lockedBlockers)) {
            $this->warn('Unresolved locked blockers:');
            foreach ($lockedBlockers as $issue) {
                $type = (string) ($issue['type'] ?? 'unknown');
                $message = (string) ($issue['message'] ?? 'Locked issue');
                $slotIds = implode(', ', array_map('intval', (array) ($issue['locked_slot_ids'] ?? [])));
                $this->line(" - [{$type}] {$message} (locked slots: {$slotIds})");
            }
            $this->line('Next action: unlock and manually fix these slots, then run generation again.');
            return Command::FAILURE;
        }

        $this->info('Repair completed with no unresolved locked blockers.');
        return Command::SUCCESS;
    }
}

