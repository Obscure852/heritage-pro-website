<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDateFixer{

    public static function fixMisalignedTestDates(){
        try {
            Log::info('Starting test date alignment fix for Form 3, Term 1');
            $updatedCount = 0;
            $testFixes = [
                // January CA tests - should be 2025-01-01 to 2025-01-31
                [
                    'conditions' => ['term_id' => 1, 'grade_id' => 3, 'type' => 'CA', 'sequence' => 1],
                    'name_like' => 'Jan%',
                    'new_start_date' => '2025-01-01',
                    'new_end_date' => '2025-01-31'
                ],
                
                // February CA tests - should be 2025-02-01 to 2025-02-28
                [
                    'conditions' => ['term_id' => 1, 'grade_id' => 3, 'type' => 'CA', 'sequence' => 2],
                    'name_like' => 'Feb%',
                    'new_start_date' => '2025-02-01',
                    'new_end_date' => '2025-02-28'
                ],
                
                // March CA tests - should be 2025-03-01 to 2025-03-31
                [
                    'conditions' => ['term_id' => 1, 'grade_id' => 3, 'type' => 'CA', 'sequence' => 3],
                    'name_like' => 'March%',
                    'new_start_date' => '2025-03-01',
                    'new_end_date' => '2025-03-31'
                ],
                
                // Exam tests - should be 2025-05-01 to 2025-05-31
                [
                    'conditions' => ['term_id' => 1, 'grade_id' => 3, 'type' => 'Exam', 'sequence' => 1],
                    'name_like' => 'Exam%',
                    'new_start_date' => '2025-05-01',
                    'new_end_date' => '2025-05-31'
                ]
            ];
            
            DB::beginTransaction();
            
            foreach ($testFixes as $fix) {
                $query = DB::table('tests')
                    ->where($fix['conditions'])
                    ->where('name', 'like', $fix['name_like'])
                    ->where(function($q) use ($fix) {
                        $q->where('start_date', '!=', $fix['new_start_date'])
                          ->orWhere('end_date', '!=', $fix['new_end_date']);
                    })->whereNull('deleted_at');
                
                $testsToUpdate = $query->get();
                
                if ($testsToUpdate->count() > 0) {
                    Log::info("Found {$testsToUpdate->count()} tests to fix for pattern: {$fix['name_like']}");
                    
                    foreach ($testsToUpdate as $test) {
                        Log::info("Fixing test ID {$test->id}: '{$test->name}' from {$test->start_date}-{$test->end_date} to {$fix['new_start_date']}-{$fix['new_end_date']}");
                    }
                    
                    $updated = DB::table('tests')
                        ->where($fix['conditions'])
                        ->where('name', 'like', $fix['name_like'])
                        ->where(function($q) use ($fix) {
                            $q->where('start_date', '!=', $fix['new_start_date'])
                              ->orWhere('end_date', '!=', $fix['new_end_date']);
                        })
                        ->whereNull('deleted_at')
                        ->update([
                            'start_date' => $fix['new_start_date'],
                            'end_date' => $fix['new_end_date'],
                            'updated_at' => now()
                        ]);
                    
                    $updatedCount += $updated;
                    Log::info("Updated {$updated} tests for pattern: {$fix['name_like']}");
                }
            }
            
            $problematicTests = DB::table('tests')
                ->where('term_id', 1)
                ->where('grade_id', 3)
                ->whereNull('deleted_at')
                ->whereRaw('DATEDIFF(end_date, start_date) > 35')
                ->orWhere(function($q) {
                    $q->where('term_id', 1)
                      ->where('grade_id', 3)
                      ->whereNull('deleted_at')
                      ->whereRaw('start_date > end_date');
                })
                ->get();
            
            foreach ($problematicTests as $test) {
                $newDates = self::getStandardDatesFromTestName($test->name, $test->type);
                if ($newDates) {
                    Log::info("Fixing problematic test ID {$test->id}: '{$test->name}' from {$test->start_date}-{$test->end_date} to {$newDates['start']}-{$newDates['end']}");
                    
                    DB::table('tests')
                        ->where('id', $test->id)
                        ->update([
                            'start_date' => $newDates['start'],
                            'end_date' => $newDates['end'],
                            'updated_at' => now()
                        ]);
                    
                    $updatedCount++;
                }
            }
            
            DB::commit();
            
            Log::info("Test date alignment completed. Updated {$updatedCount} tests.");
            
            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "Successfully aligned {$updatedCount} test dates"
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Test date alignment failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'updated_count' => 0,
                'message' => 'Test date alignment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get standard date ranges based on test name and type
     */
    private static function getStandardDatesFromTestName($testName, $testType)
    {
        $testName = strtolower($testName);
        
        // January tests
        if (strpos($testName, 'jan') !== false) {
            return ['start' => '2025-01-01', 'end' => '2025-01-31'];
        }
        
        // February tests
        if (strpos($testName, 'feb') !== false) {
            return ['start' => '2025-02-01', 'end' => '2025-02-28'];
        }
        
        // March tests
        if (strpos($testName, 'mar') !== false) {
            return ['start' => '2025-03-01', 'end' => '2025-03-31'];
        }
        
        // April tests
        if (strpos($testName, 'apr') !== false) {
            return ['start' => '2025-04-01', 'end' => '2025-04-30'];
        }
        
        // May tests or Exams
        if (strpos($testName, 'may') !== false || 
           (strpos($testName, 'exam') !== false && $testType === 'Exam')) {
            return ['start' => '2025-05-01', 'end' => '2025-05-31'];
        }
        
        return null;
    }
    
    /**
     * Show what would be fixed without actually fixing (dry run)
     */
    public static function previewTestDateFixes()
    {
        $preview = [];
        
        // Get all potentially problematic tests
        $tests = DB::table('tests')
            ->where('term_id', 1)
            ->where('grade_id', 3)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->orderBy('sequence')
            ->get();
        
        foreach ($tests as $test) {
            $newDates = self::getStandardDatesFromTestName($test->name, $test->type);
            
            if ($newDates && 
               ($test->start_date !== $newDates['start'] || $test->end_date !== $newDates['end'])) {
                
                $preview[] = [
                    'test_id' => $test->id,
                    'test_name' => $test->name,
                    'test_type' => $test->type,
                    'sequence' => $test->sequence,
                    'current_start' => $test->start_date,
                    'current_end' => $test->end_date,
                    'proposed_start' => $newDates['start'],
                    'proposed_end' => $newDates['end'],
                    'days_current' => (strtotime($test->end_date) - strtotime($test->start_date)) / 86400 + 1,
                    'days_proposed' => (strtotime($newDates['end']) - strtotime($newDates['start'])) / 86400 + 1
                ];
            }
        }
        
        return $preview;
    }
}
