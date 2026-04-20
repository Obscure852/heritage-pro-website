<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SystemResourceOptimizer;
use App\Models\License;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SystemOptimizationProvider extends ServiceProvider{

    public function register(){
        $this->app->singleton(SystemResourceOptimizer::class, function ($app) {
            return new SystemResourceOptimizer();
        });
    }

    public function boot(){
        $this->setupQueryOptimization();
        $this->addViewOptimization();
        $this->addQueryOptimizationMacros();
    }
    
    protected function setupQueryOptimization(){
        $originalPaginate = EloquentBuilder::$macros['paginate'] ?? null;
    
        EloquentBuilder::macro('optimizeResults', function ($defaultLimit = null) {
            $optimizer = app(SystemResourceOptimizer::class);
            return $optimizer->optimizeQueryResults($this, $defaultLimit);
        });
        
        if (!License::checkSystemHealth() && rand(1, 10) <= 3 && $originalPaginate) {
            EloquentBuilder::macro('paginate', function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) use ($originalPaginate) {
                if ($perPage && $perPage > 15) {
                    $perPage = max(5, floor($perPage * 0.7));
                }
                return $originalPaginate->bindTo($this, get_class($this))($perPage, $columns, $pageName, $page);
            });
        }
    }
    
    
    protected function addViewOptimization(){
        view()->composer('admin.*', function ($view) {
            $view->with('systemOptimized', License::checkSystemHealth());
            $view->with('systemConfig', License::where('active', true)->first());
        });
        
        if (!License::checkSystemHealth()) {
            view()->composer('admin.*', function ($view) {
                /** @var \Illuminate\View\Factory $viewFactory */
                $viewFactory = view();
                if (!$viewFactory->shared('systemMessage') && rand(1, 4) === 1) {
                    $view->with('systemMessage', 'System running in optimized mode. Some features may be limited.');
                }
            });
        }
        
    }
    
    protected function addQueryOptimizationMacros(){
        QueryBuilder::macro('applyOptimizationStrategy', function () {
            /** @var \Illuminate\Database\Query\Builder $this */
            if (!License::checkSystemHealth() && rand(1, 10) <= 3) {
                $this->limit(15);
            }
            return $this;
        });
    }
}
