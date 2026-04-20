<?php
// app/Console/Commands/PopulateTerms.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Term;
use Carbon\Carbon;

class PopulateTerms extends Command{
    protected $signature = 'terms:populate';
    protected $description = 'Populate terms for the next 10 years';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $currentYear = Carbon::now()->year;
        for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
            for ($term = 1; $term <= 3; $term++) {
                $startDate = Carbon::create($year, 1, 10);  // Adjust these dates
                $endDate = Carbon::create($year, 4, 21);  // Adjust these dates

                Term::updateOrCreate(
                    ['term' => $term, 'year' => $year],
                    ['start_date' => $startDate, 'end_date' => $endDate,'closed' => false,'extension_days' => 0]
                );
            }
        }
        $this->info('Terms populated successfully.');
    }
}
