<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Term; 

class UpdateTermDates extends Command{
    protected $signature = 'update:term-dates';
    protected $description = 'Updates the term dates for each year';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $years = Term::distinct()->pluck('year');
        foreach ($years as $year) {
            Term::where('year', $year)
                ->where('term', 1)
                ->update([
                    'start_date' => "{$year}-01-08",
                    'end_date' => "{$year}-04-10"
                ]);

            Term::where('year', $year)
                ->where('term', 2)
                ->update([
                    'start_date' => "{$year}-05-09",
                    'end_date' => "{$year}-08-08"
                ]);

            Term::where('year', $year)
                ->where('term', 3)
                ->update([
                    'start_date' => "{$year}-09-05",
                    'end_date' => "{$year}-12-12"
                ]);

            $this->info("Updated term dates for year: $year");
        }
        $this->info('All term dates updated successfully.');
    }
}
