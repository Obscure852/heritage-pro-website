<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Logging;
use App\Models\LoggingArchive;

class ArchiveLoggingRecords extends Command{
    protected $signature = 'logging:archive';
    protected $description = 'Archive older logging records';

    public function handle(){
        $threshold = 1000;
        $loggingCount = Logging::count();

        if ($loggingCount > $threshold) {
            $cutoffDate = now()->subYear();

            $loggingRecords = Logging::where('created_at', '<', $cutoffDate)->get();

            foreach ($loggingRecords as $loggingRecord) {
                LoggingArchive::create($loggingRecord->toArray());
                $loggingRecord->delete();
            }
            $this->info('Logging records archived successfully.');
        } else {
            $this->info('Number of logging records is below the threshold. Archiving is not needed.');
        }
    }
}