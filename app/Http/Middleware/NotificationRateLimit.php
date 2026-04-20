<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class NotificationRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $type  'sms' or 'email'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $type = 'email')
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Get rate limits from database settings
        $hourlyLimit = settings("rate_limits.{$type}_hourly");
        $dailyLimit = settings("rate_limits.{$type}_daily");

        // Check hourly limit
        $hourlyKey = $this->getRateLimitKey($user->id, $type, 'hourly');
        $hourlySent = Cache::get($hourlyKey, 0);

        if ($hourlySent >= $hourlyLimit) {
            return response()->json([
                'success' => false,
                'message' => "Hourly {$type} limit exceeded. You can send {$hourlyLimit} {$type}s per hour. Please try again later.",
                'limit' => $hourlyLimit,
                'sent' => $hourlySent,
                'reset_in' => $this->getResetTime('hourly'),
            ], 429);
        }

        // Check daily limit
        $dailyKey = $this->getRateLimitKey($user->id, $type, 'daily');
        $dailySent = Cache::get($dailyKey, 0);

        if ($dailySent >= $dailyLimit) {
            return response()->json([
                'success' => false,
                'message' => "Daily {$type} limit exceeded. You can send {$dailyLimit} {$type}s per day. Please try again tomorrow.",
                'limit' => $dailyLimit,
                'sent' => $dailySent,
                'reset_in' => $this->getResetTime('daily'),
            ], 429);
        }

        // Process request
        $response = $next($request);

        // Increment counters only if request was successful
        if ($response->isSuccessful() || ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            $this->incrementCounter($user->id, $type);
        }

        return $response;
    }

    /**
     * Get rate limit cache key
     *
     * @param int $userId
     * @param string $type
     * @param string $period
     * @return string
     */
    protected function getRateLimitKey(int $userId, string $type, string $period): string
    {
        $prefix = 'notification_rate_limit:';
        $date = now()->format($period === 'hourly' ? 'Y-m-d-H' : 'Y-m-d');

        return "{$prefix}{$type}:{$userId}:{$period}:{$date}";
    }

    /**
     * Increment rate limit counter
     *
     * @param int $userId
     * @param string $type
     * @return void
     */
    protected function incrementCounter(int $userId, string $type): void
    {
        // Increment hourly counter
        $hourlyKey = $this->getRateLimitKey($userId, $type, 'hourly');
        $hourlyTTL = now()->addHour()->diffInSeconds();
        Cache::put($hourlyKey, Cache::get($hourlyKey, 0) + 1, $hourlyTTL);

        // Increment daily counter
        $dailyKey = $this->getRateLimitKey($userId, $type, 'daily');
        $dailyTTL = now()->addDay()->diffInSeconds();
        Cache::put($dailyKey, Cache::get($dailyKey, 0) + 1, $dailyTTL);
    }

    /**
     * Get time until rate limit resets
     *
     * @param string $period
     * @return string
     */
    protected function getResetTime(string $period): string
    {
        if ($period === 'hourly') {
            $resetAt = now()->addHour()->startOfHour();
        } else {
            $resetAt = now()->addDay()->startOfDay();
        }

        $minutes = now()->diffInMinutes($resetAt);
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '') . " and {$remainingMinutes} minute" . ($remainingMinutes != 1 ? 's' : '');
        }

        return "{$remainingMinutes} minute" . ($remainingMinutes != 1 ? 's' : '');
    }

    /**
     * Get current usage for a user
     *
     * @param int $userId
     * @param string $type
     * @return array
     */
    public static function getUsage(int $userId, string $type): array
    {
        $instance = new static();

        $hourlyKey = $instance->getRateLimitKey($userId, $type, 'hourly');
        $dailyKey = $instance->getRateLimitKey($userId, $type, 'daily');

        $hourlyLimit = settings("rate_limits.{$type}_hourly");
        $dailyLimit = settings("rate_limits.{$type}_daily");

        return [
            'hourly' => [
                'sent' => Cache::get($hourlyKey, 0),
                'limit' => $hourlyLimit,
                'remaining' => max(0, $hourlyLimit - Cache::get($hourlyKey, 0)),
            ],
            'daily' => [
                'sent' => Cache::get($dailyKey, 0),
                'limit' => $dailyLimit,
                'remaining' => max(0, $dailyLimit - Cache::get($dailyKey, 0)),
            ],
        ];
    }
}
