<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Message;
use App\Models\User;
use App\Helpers\TermHelper;
use Illuminate\Support\Facades\DB;

class NotificationAnalyticsService
{
    /**
     * Get email and SMS success rates
     *
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getSuccessRate(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Email success rate
        $totalEmails = Email::where('created_at', '>=', $startDate)->count();
        $successfulEmails = Email::where('created_at', '>=', $startDate)
            ->whereIn('status', ['sent', 'Sent'])->count();
        $emailSuccessRate = $totalEmails > 0 ? ($successfulEmails / $totalEmails) * 100 : 0;

        // SMS success rate (assuming 'Delivered' status means success)
        $totalSms = Message::where('created_at', '>=', $startDate)->count();
        $successfulSms = Message::where('created_at', '>=', $startDate)
            ->where('status', 'Delivered')->count();
        $smsSuccessRate = $totalSms > 0 ? ($successfulSms / $totalSms) * 100 : 0;

        return [
            'email' => [
                'total' => $totalEmails,
                'successful' => $successfulEmails,
                'failed' => $totalEmails - $successfulEmails,
                'success_rate' => round($emailSuccessRate, 2),
            ],
            'sms' => [
                'total' => $totalSms,
                'successful' => $successfulSms,
                'failed' => $totalSms - $successfulSms,
                'success_rate' => round($smsSuccessRate, 2),
            ],
            'period_days' => $days,
        ];
    }

    /**
     * Get cost analytics for current term or specified term
     *
     * @param int|null $termId
     * @return array
     */
    public function getCostAnalytics(?int $termId = null): array
    {
        $termId = $termId ?? session('selected_term_id', TermHelper::getCurrentTerm()?->id);

        $smsData = Message::where('term_id', $termId)
            ->select(
                DB::raw('SUM(price_bwp) as total_cost'),
                DB::raw('SUM(num_recipients) as total_sent'),
                DB::raw('SUM(sms_count) as total_units'),
                DB::raw('COUNT(*) as total_messages')
            )
            ->first();

        $emailData = Email::where('term_id', $termId)
            ->select(
                DB::raw('COUNT(*) as total_emails'),
                DB::raw('SUM(num_of_recipients) as total_recipients')
            )
            ->first();

        return [
            'term_id' => $termId,
            'sms' => [
                'total_cost' => $smsData->total_cost ?? 0,
                'total_sent' => $smsData->total_sent ?? 0,
                'total_units' => $smsData->total_units ?? 0,
                'total_messages' => $smsData->total_messages ?? 0,
                'avg_cost_per_message' => $smsData->total_messages > 0
                    ? ($smsData->total_cost / $smsData->total_messages)
                    : 0,
            ],
            'email' => [
                'total_cost' => 0, // Emails are typically free
                'total_sent' => $emailData->total_emails ?? 0,
                'total_recipients' => $emailData->total_recipients ?? 0,
            ],
            'total_cost' => $smsData->total_cost ?? 0,
        ];
    }

    /**
     * Get usage statistics over time
     *
     * @param int $days Number of days to analyze
     * @param string $groupBy 'day', 'week', or 'month'
     * @return array
     */
    public function getUsageStats(int $days = 30, string $groupBy = 'day'): array
    {
        $startDate = now()->subDays($days);

        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Email stats
        $emailStats = Email::where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '$dateFormat') as period"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // SMS stats
        $smsStats = Message::where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '$dateFormat') as period"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(price_bwp) as cost')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'period_days' => $days,
            'group_by' => $groupBy,
            'email' => $emailStats->map(function($stat) {
                return [
                    'period' => $stat->period,
                    'count' => $stat->count,
                ];
            }),
            'sms' => $smsStats->map(function($stat) {
                return [
                    'period' => $stat->period,
                    'count' => $stat->count,
                    'cost' => $stat->cost,
                ];
            }),
        ];
    }

    /**
     * Get top senders by volume
     *
     * @param int $limit Number of top senders to return
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getTopSenders(int $limit = 10, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Top email senders
        $topEmailSenders = Email::where('created_at', '>=', $startDate)
            ->select('sender_id', DB::raw('COUNT(*) as email_count'))
            ->groupBy('sender_id')
            ->orderByDesc('email_count')
            ->limit($limit)
            ->with('sender:id,first_name,last_name,email')
            ->get();

        // Top SMS senders
        $topSmsSenders = Message::where('created_at', '>=', $startDate)
            ->select('author', DB::raw('COUNT(*) as sms_count'), DB::raw('SUM(price_bwp) as total_cost'))
            ->groupBy('author')
            ->orderByDesc('sms_count')
            ->limit($limit)
            ->with(['authorUser:id,first_name,last_name,email'])
            ->get();

        return [
            'period_days' => $days,
            'email_senders' => $topEmailSenders->map(function($sender) {
                return [
                    'user_id' => $sender->sender_id,
                    'name' => $sender->sender ? $sender->sender->first_name . ' ' . $sender->sender->last_name : 'Unknown',
                    'email' => $sender->sender->email ?? 'N/A',
                    'count' => $sender->email_count,
                ];
            }),
            'sms_senders' => $topSmsSenders->map(function($sender) {
                return [
                    'user_id' => $sender->author,
                    'name' => $sender->authorUser ? $sender->authorUser->first_name . ' ' . $sender->authorUser->last_name : 'Unknown',
                    'email' => $sender->authorUser->email ?? 'N/A',
                    'count' => $sender->sms_count,
                    'total_cost' => $sender->total_cost,
                ];
            }),
        ];
    }

    /**
     * Get failure reasons and counts
     *
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getFailureReasons(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Get failed emails with error messages
        $failedEmails = Email::where('created_at', '>=', $startDate)
            ->where('status', 'failed')
            ->whereNotNull('error_message')
            ->select('error_message', DB::raw('COUNT(*) as count'))
            ->groupBy('error_message')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $totalFailedEmails = Email::where('created_at', '>=', $startDate)
            ->where('status', 'failed')
            ->count();

        $totalFailedSms = Message::where('created_at', '>=', $startDate)
            ->where('status', '!=', 'Delivered')
            ->count();

        return [
            'period_days' => $days,
            'email' => [
                'total_failed' => $totalFailedEmails,
                'top_reasons' => $failedEmails->map(function($failure) {
                    return [
                        'reason' => substr($failure->error_message, 0, 100),
                        'count' => $failure->count,
                    ];
                }),
            ],
            'sms' => [
                'total_failed' => $totalFailedSms,
            ],
        ];
    }

    /**
     * Get monthly trend comparison
     *
     * @return array
     */
    public function getMonthlyTrends(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentMonthEmails = Email::whereBetween('created_at', [$currentMonth, now()])->count();
        $lastMonthEmails = Email::whereBetween('created_at', [$lastMonth, $currentMonth])->count();

        $currentMonthSms = Message::whereBetween('created_at', [$currentMonth, now()])->count();
        $lastMonthSms = Message::whereBetween('created_at', [$lastMonth, $currentMonth])->count();

        $currentMonthCost = Message::whereBetween('created_at', [$currentMonth, now()])->sum('price_bwp');
        $lastMonthCost = Message::whereBetween('created_at', [$lastMonth, $currentMonth])->sum('price_bwp');

        return [
            'current_month' => [
                'emails' => $currentMonthEmails,
                'sms' => $currentMonthSms,
                'cost' => $currentMonthCost,
            ],
            'last_month' => [
                'emails' => $lastMonthEmails,
                'sms' => $lastMonthSms,
                'cost' => $lastMonthCost,
            ],
            'change' => [
                'emails' => $lastMonthEmails > 0 ? (($currentMonthEmails - $lastMonthEmails) / $lastMonthEmails) * 100 : 0,
                'sms' => $lastMonthSms > 0 ? (($currentMonthSms - $lastMonthSms) / $lastMonthSms) * 100 : 0,
                'cost' => $lastMonthCost > 0 ? (($currentMonthCost - $lastMonthCost) / $lastMonthCost) * 100 : 0,
            ],
        ];
    }
}
