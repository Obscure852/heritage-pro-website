<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

return new class extends Migration {
    
    public function up(){
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('term'); 
            $table->year('year'); 
            $table->boolean('closed')->default(false);
            $table->integer('extension_days')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['term', 'year'], 'unique_term_year');

            $table->index('start_date');
            $table->index('end_date');
            $table->index('year');
        });
    
        try{
            $years = 10;
            $currentYear = date('Y');
            for ($i = 0; $i < $years; $i++) {
                $year = $currentYear + $i;
                DB::table('terms')->insert([
                    [
                        'start_date' => Carbon::create($year, 1, 10),
                        'end_date' => Carbon::create($year, 5, 10),
                        'term' => 1,
                        'year' => $year,
                        'closed' => Carbon::create($year, 5, 10)->lt(now()),
                        'extension_days' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'start_date' => Carbon::create($year, 5, 14),
                        'end_date' => Carbon::create($year, 8, 21),
                        'term' => 2,
                        'year' => $year,
                        'closed' => Carbon::create($year, 8, 21)->lt(now()),
                        'extension_days' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'start_date' => Carbon::create($year, 8, 27),
                        'end_date' => Carbon::create($year, 12, 10),
                        'term' => 3,
                        'year' => $year,
                        'closed' => Carbon::create($year, 12, 10)->lt(now()),
                        'extension_days' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                ]);
            }
        }catch(\Exception $e){
            Log::error('Failed to insert terms: ' . $e->getMessage());
        }
    }
    
    public function down(){
        Schema::dropIfExists('terms');
    }

};
