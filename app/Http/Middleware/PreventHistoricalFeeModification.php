<?php

namespace App\Http\Middleware;

use App\Models\SMSApiSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to prevent modifications to historical (locked) fee years.
 *
 * Checks the request for a 'year' parameter and blocks modifications
 * if the year is locked or in the past.
 */
class PreventHistoricalFeeModification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to modification requests (POST, PUT, PATCH, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        // Get year from request (check various common parameter names)
        $year = $request->input('year') ??
                $request->route('year') ??
                $request->input('invoice_year') ??
                null;

        // If no year in request, try to get it from the model being modified
        if (!$year) {
            // Check route model binding for common fee models
            $invoice = $request->route('invoice');
            $structure = $request->route('structure');
            $payment = $request->route('payment');
            $refund = $request->route('refund');

            if ($invoice && method_exists($invoice, 'getAttribute')) {
                $year = $invoice->year;
            } elseif ($structure && method_exists($structure, 'getAttribute')) {
                $year = $structure->year;
            } elseif ($payment && method_exists($payment, 'getAttribute')) {
                // Get year from payment's invoice
                $year = $payment->invoice?->year;
            } elseif ($refund && method_exists($refund, 'getAttribute')) {
                // Get year from refund's invoice
                $year = $refund->invoice?->year;
            }
        }

        // If still no year, allow the request (some operations don't have year context)
        if (!$year) {
            return $next($request);
        }

        $year = (int) $year;

        // Check if year is locked
        if ($this->isYearLocked($year)) {
            // Allow administrators to override if they have the permission
            if (Gate::allows('override-historical-year-lock')) {
                return $next($request);
            }

            return response()->json([
                'message' => "Fee data for year {$year} is locked and cannot be modified.",
                'error' => 'year_locked',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if a year is locked.
     */
    private function isYearLocked(int $year): bool
    {
        // Get the earliest unlocked year from settings
        $lockedUntilYear = (int) SMSApiSetting::where('key', 'fee.locked_until_year')->value('value');

        // If locked_until_year is set, check if the requested year is at or before it
        if ($lockedUntilYear > 0 && $year <= $lockedUntilYear) {
            return true;
        }

        // Also check if year is in the past and auto-lock is enabled
        $autoLockPastYears = SMSApiSetting::where('key', 'fee.auto_lock_past_years')->value('value');

        if ($autoLockPastYears === 'true' || $autoLockPastYears === '1') {
            $currentYear = (int) date('Y');
            if ($year < $currentYear) {
                return true;
            }
        }

        return false;
    }
}
