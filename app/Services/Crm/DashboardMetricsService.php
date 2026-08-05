<?php

namespace App\Services\Crm;

use App\Models\CrmCalendarEvent;
use App\Models\CrmCommercialSetting;
use App\Models\CrmInvoice;
use App\Models\CrmLeaveBalance;
use App\Models\CrmLeaveRequest;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\DiscussionThread;
use App\Models\Lead;
use App\Models\User;
use App\Support\Crm\DashboardPeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class DashboardMetricsService
{
    /**
     * Point-in-time counters describing the current book of work. These have no
     * meaningful period comparison, so they carry no delta.
     *
     * @return array<int, array{key: string, label: string, value: int, url: string|null}>
     */
    public function headlineMetrics(User $user): array {
        return $this->stockMetrics($user, [
            'active_leads' => ['Active leads', 'crm.leads.index'],
            'active_customers' => ['Live customers', 'crm.customers.index'],
            'open_sales_requests' => ['Open sales requests', 'crm.requests.sales.index'],
            'open_support_requests' => ['Open support requests', 'crm.requests.support.index'],
        ]);
    }

    /**
     * Workload pressure counters — what is late, due, or queued right now.
     *
     * @return array<int, array{key: string, label: string, value: int, url: string|null}>
     */
    public function pressureMetrics(User $user): array {
        return $this->stockMetrics($user, [
            'overdue_follow_ups' => ['Overdue follow-ups', 'crm.requests.sales.index'],
            'today_follow_ups' => ['Due today', 'crm.requests.sales.index'],
            'open_development_items' => ['Open dev requests', 'crm.dev.index'],
            'discussion_threads' => ['Discussion threads', 'crm.discussions.index'],
        ]);
    }

    /**
     * Movement inside the selected period, each compared against the equally long
     * window immediately before it.
     *
     * @return array<int, array{key: string, label: string, value: int, previous: int, delta: int|null, direction: string, url: string|null}>
     */
    public function flowMetrics(User $user, DashboardPeriod $period): array {
        $counts = $this->flowCounts($user, $period);

        $definitions = [
            'new_leads' => ['New leads', 'crm.leads.index'],
            'converted_leads' => ['Leads converted', 'crm.customers.index'],
            'requests_opened' => ['Requests opened', 'crm.requests.sales.index'],
            'requests_closed' => ['Requests closed', 'crm.requests.sales.index'],
        ];

        $metrics = [];

        foreach ($definitions as $key => [$label, $routeName]) {
            $value = (int) ($counts['current'][$key] ?? 0);
            $previous = (int) ($counts['previous'][$key] ?? 0);

            $metrics[] = [
                'key' => $key,
                'label' => $label,
                'value' => $value,
                'previous' => $previous,
                'delta' => $this->percentageChange($value, $previous),
                'direction' => $this->direction($value, $previous),
                'url' => $this->routeUrl($routeName),
            ];
        }

        return $metrics;
    }

    /**
     * Commercial headline figures. Amounts are summed in the workspace's default
     * currency — see mixedCurrencies() for the guard when documents disagree.
     *
     * @return array<int, array{key: string, label: string, value: float, display: string, previous: float, delta: int|null, direction: string, url: string|null}>
     */
    public function commercialMetrics(User $user, DashboardPeriod $period): array {
        $figures = $this->commercialFigures($user, $period);
        $currency = $this->currencyCode();
        $quotesUrl = $this->routeUrl('crm.products.quotes.index');
        $invoicesUrl = $this->routeUrl('crm.products.invoices.index');

        return [
            $this->moneyMetric('pipeline_value', 'Open pipeline value', $figures['pipeline_value'], null, $currency, $quotesUrl),
            $this->moneyMetric('accepted_value', 'Accepted this period', $figures['accepted_value'], $figures['previous_accepted_value'], $currency, $quotesUrl),
            $this->moneyMetric('invoiced_value', 'Invoiced this period', $figures['invoiced_value'], $figures['previous_invoiced_value'], $currency, $invoicesUrl),
            [
                'key' => 'quotes_awaiting',
                'label' => 'Quotes awaiting reply',
                'value' => (float) $figures['quotes_awaiting'],
                'display' => number_format($figures['quotes_awaiting']),
                'previous' => 0.0,
                'delta' => null,
                'direction' => 'flat',
                'url' => $quotesUrl,
            ],
        ];
    }

    /**
     * Invoice value per month for the trailing window, split into issued and draft.
     *
     * @return array{labels: array<int, string>, series: array<int, array{name: string, data: array<int, float>}>}
     */
    public function revenueTrend(User $user, int $months = 12): array {
        return $this->remember("revenue.{$months}", $user, function () use ($user, $months) {
            $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

            $rows = $this->scopeOwned(
                CrmInvoice::query()
                    ->whereIn('status', ['draft', 'issued', 'sent'])
                    ->where('invoice_date', '>=', $start),
                $user
            )->get(['invoice_date', 'status', 'total_amount']);

            // Bucketed in PHP rather than SQL: production runs MySQL and the test
            // suite runs SQLite, which disagree on date formatting functions.
            $issued = [];
            $draft = [];
            $labels = [];

            for ($offset = 0; $offset < $months; $offset++) {
                $month = $start->addMonths($offset);
                $labels[$month->format('Y-m')] = $month->format('M y');
                $issued[$month->format('Y-m')] = 0.0;
                $draft[$month->format('Y-m')] = 0.0;
            }

            foreach ($rows as $row) {
                $bucket = $row->invoice_date?->format('Y-m');

                if ($bucket === null || ! array_key_exists($bucket, $labels)) {
                    continue;
                }

                if ($row->status === 'draft') {
                    $draft[$bucket] += (float) $row->total_amount;

                    continue;
                }

                $issued[$bucket] += (float) $row->total_amount;
            }

            return [
                'labels' => array_values($labels),
                'series' => [
                    ['name' => 'Issued', 'data' => array_map(fn ($value) => round($value, 2), array_values($issued))],
                    ['name' => 'Draft', 'data' => array_map(fn ($value) => round($value, 2), array_values($draft))],
                ],
            ];
        });
    }

    /**
     * How quotes raised inside the period are currently resolving.
     *
     * @return array{labels: array<int, string>, values: array<int, int>, total: int}
     */
    public function quoteConversion(User $user, DashboardPeriod $period): array {
        return $this->remember("quotes.{$period->key}", $user, function () use ($user, $period) {
            $counts = $this->windowed(
                $this->scopeOwned(CrmQuote::query(), $user),
                'quote_date',
                $period->start,
                $period->end,
                true
            )
                ->groupBy('status')
                ->selectRaw('status, count(*) as aggregate')
                ->pluck('aggregate', 'status');

            $labels = [];
            $values = [];

            foreach (config('heritage_crm.quote_statuses', []) as $status => $label) {
                $count = (int) ($counts[$status] ?? 0);

                if ($count === 0) {
                    continue;
                }

                $labels[] = $label;
                $values[] = $count;
            }

            return [
                'labels' => $labels,
                'values' => $values,
                'total' => array_sum($values),
            ];
        });
    }

    /**
     * True when quotes or invoices exist in more than one currency, which would make
     * the summed totals above meaningless.
     */
    public function mixedCurrencies(User $user): bool {
        return $this->remember('currencies', $user, function () use ($user) {
            $codes = $this->scopeOwned(CrmQuote::query(), $user)
                ->distinct()
                ->pluck('currency_code')
                ->merge(
                    $this->scopeOwned(CrmInvoice::query(), $user)
                        ->distinct()
                        ->pluck('currency_code')
                )
                ->filter()
                ->unique();

            return $codes->count() > 1;
        });
    }

    public function currencyCode(): string {
        return CrmCommercialSetting::query()
            ->with('defaultCurrency:id,code')
            ->first()?->defaultCurrency?->code ?? '';
    }

    /**
     * Today's commitments for this user: calendar events they own or attend, merged
     * with follow-ups that are due today or already late. Personal and live, so it is
     * never cached.
     *
     * @return array<int, array{kind: string, time: string, label: string, context: string, url: string|null, overdue: bool}>
     */
    public function myDay(User $user): array {
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();
        $limit = (int) config('heritage_crm.dashboard.list_limit', 6);

        $items = [];

        // Only the user's own commitments — deliberately not the full set of calendars
        // an admin can see, which would be everybody's diary.
        $events = CrmCalendarEvent::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('starts_at', [$startOfDay, $endOfDay])
            ->where(function (Builder $query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('attendees', function (Builder $attendeeQuery) use ($user) {
                        $attendeeQuery->where('user_id', $user->id);
                    });
            })
            ->with(['lead:id,company_name', 'customer:id,company_name'])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get(['id', 'title', 'starts_at', 'all_day', 'lead_id', 'customer_id', 'owner_id']);

        foreach ($events as $event) {
            $items[] = [
                'kind' => 'event',
                'sort' => $event->starts_at?->getTimestamp() ?? 0,
                'time' => $event->all_day ? 'All day' : ($event->starts_at?->format('H:i') ?? ''),
                'label' => $event->title,
                'context' => $this->accountLabel($event->customer?->company_name, $event->lead?->company_name),
                'url' => $this->routeUrl('crm.calendar.index'),
                'overdue' => false,
            ];
        }

        $followUps = $this->scopeOwned(
            CrmRequest::query()
                ->whereNull('closed_at')
                ->whereNotNull('next_action_at')
                ->where('next_action_at', '<=', $endOfDay),
            $user
        )
            ->with(['lead:id,company_name', 'customer:id,company_name'])
            ->orderBy('next_action_at')
            ->limit($limit)
            ->get(['id', 'title', 'next_action', 'next_action_at', 'owner_id', 'lead_id', 'customer_id']);

        foreach ($followUps as $request) {
            $isOverdue = $request->next_action_at->lt($startOfDay);

            $items[] = [
                'kind' => 'follow_up',
                'sort' => $request->next_action_at->getTimestamp(),
                'time' => $isOverdue ? $request->next_action_at->diffForHumans(null, true) . ' late' : $request->next_action_at->format('H:i'),
                'label' => $request->next_action ?: $request->title,
                'context' => $this->accountLabel($request->customer?->company_name, $request->lead?->company_name),
                'url' => $this->routeUrl('crm.requests.show', $request->id),
                'overdue' => $isOverdue,
            ];
        }

        usort($items, fn (array $a, array $b) => $a['sort'] <=> $b['sort']);

        return array_slice($items, 0, $limit * 2);
    }

    /**
     * Open sales work nobody has touched recently. A request only counts once it has
     * had a chance to go quiet, so brand new records with no contact yet are excluded.
     *
     * @return EloquentCollection<int, CrmRequest>
     */
    public function goingCold(User $user, ?int $days = null): EloquentCollection {
        $days = $days ?: (int) config('heritage_crm.dashboard.going_cold_days', 14);
        $threshold = now()->subDays($days);

        return $this->scopeOwned(
            CrmRequest::query()
                ->where('type', 'sales')
                ->whereNull('closed_at')
                ->where(function (Builder $query) use ($threshold) {
                    $query->where('last_contact_at', '<', $threshold)
                        ->orWhere(function (Builder $neverQuery) use ($threshold) {
                            $neverQuery->whereNull('last_contact_at')
                                ->where('created_at', '<', $threshold);
                        });
                }),
            $user
        )
            ->with(['owner', 'lead:id,company_name', 'customer:id,company_name'])
            // Nulls sort first ascending on both MySQL and SQLite, which is what we want:
            // never contacted is colder than contacted a while ago.
            ->orderBy('last_contact_at')
            ->limit((int) config('heritage_crm.dashboard.list_limit', 6))
            ->get();
    }

    /**
     * A prioritised mix of records that are ageing past their useful life.
     *
     * @return array<int, array{type: string, label: string, context: string, age: string, url: string|null, severity: string}>
     */
    public function needsAttention(User $user, bool $includeCommercial): array {
        $days = (int) config('heritage_crm.dashboard.aging_days', 7);
        $limit = (int) config('heritage_crm.dashboard.list_limit', 6);
        $stale = now()->subDays($days);
        $rows = [];

        $ageingSupport = $this->scopeOwned(
            CrmRequest::query()
                ->where('type', 'support')
                ->whereNotIn('support_status', ['resolved', 'closed'])
                ->where('created_at', '<', $stale),
            $user
        )
            ->with(['lead:id,company_name', 'customer:id,company_name'])
            ->orderBy('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'created_at', 'owner_id', 'lead_id', 'customer_id']);

        foreach ($ageingSupport as $request) {
            $rows[] = [
                'type' => 'Support',
                'sort' => $request->created_at->getTimestamp(),
                'label' => $request->title,
                'context' => $this->accountLabel($request->customer?->company_name, $request->lead?->company_name),
                'age' => 'Open ' . $request->created_at->diffForHumans(null, true),
                'url' => $this->routeUrl('crm.requests.show', $request->id),
                'severity' => 'warning',
            ];
        }

        if ($includeCommercial) {
            $expiring = $this->scopeOwned(
                CrmQuote::query()
                    ->where('status', 'sent')
                    ->whereNotNull('valid_until')
                    ->where('valid_until', '<=', now()->addDays($days)),
                $user
            )
                ->with(['lead:id,company_name', 'customer:id,company_name'])
                ->orderBy('valid_until')
                ->limit($limit)
                ->get(['id', 'quote_number', 'subject', 'valid_until', 'owner_id', 'lead_id', 'customer_id']);

            foreach ($expiring as $quote) {
                $hasLapsed = $quote->valid_until->isPast();

                $rows[] = [
                    'type' => 'Quote',
                    'sort' => $quote->valid_until->getTimestamp(),
                    'label' => $quote->quote_number . ($quote->subject ? ' — ' . $quote->subject : ''),
                    'context' => $this->accountLabel($quote->customer?->company_name, $quote->lead?->company_name),
                    'age' => $hasLapsed
                        ? 'Lapsed ' . $quote->valid_until->diffForHumans(null, true) . ' ago'
                        : 'Expires in ' . $quote->valid_until->diffForHumans(null, true),
                    'url' => $this->routeUrl('crm.products.quotes.show', $quote->id),
                    'severity' => $hasLapsed ? 'danger' : 'warning',
                ];
            }

            $stuckDrafts = $this->scopeOwned(
                CrmInvoice::query()
                    ->where('status', 'draft')
                    ->where('invoice_date', '<', $stale),
                $user
            )
                ->with(['lead:id,company_name', 'customer:id,company_name'])
                ->orderBy('invoice_date')
                ->limit($limit)
                ->get(['id', 'invoice_number', 'subject', 'invoice_date', 'owner_id', 'lead_id', 'customer_id']);

            foreach ($stuckDrafts as $invoice) {
                $rows[] = [
                    'type' => 'Invoice',
                    'sort' => $invoice->invoice_date->getTimestamp(),
                    'label' => $invoice->invoice_number . ($invoice->subject ? ' — ' . $invoice->subject : ''),
                    'context' => $this->accountLabel($invoice->customer?->company_name, $invoice->lead?->company_name),
                    'age' => 'Unissued ' . $invoice->invoice_date->diffForHumans(null, true),
                    'url' => $this->routeUrl('crm.products.invoices.show', $invoice->id),
                    'severity' => 'muted',
                ];
            }
        }

        // Urgency first, then age. A lapsed quote outranks a draft invoice that has
        // merely been sitting a while, even though the invoice is the older record.
        $rank = ['danger' => 0, 'warning' => 1, 'muted' => 2];

        usort(
            $rows,
            fn (array $a, array $b) => [$rank[$a['severity']] ?? 9, $a['sort']]
                <=> [$rank[$b['severity']] ?? 9, $b['sort']]
        );

        return array_slice($rows, 0, $limit + 2);
    }

    /**
     * Today's attendance picture for the team, plus who is away on approved leave.
     *
     * @return array{stats: array<string, int>, on_leave: EloquentCollection<int, CrmLeaveRequest>, url: string|null}
     */
    public function teamToday(User $user, AttendanceReportService $attendance): array {
        $today = now()->toDateString();

        return [
            'stats' => $attendance->todayStats(),
            'on_leave' => CrmLeaveRequest::query()
                ->approved()
                ->overlapping($today, $today)
                ->with(['user:id,name', 'leaveType:id,name'])
                ->orderBy('end_date')
                ->limit((int) config('heritage_crm.dashboard.list_limit', 6))
                ->get(),
            'url' => $this->routeUrl('crm.attendance.grid'),
        ];
    }

    /**
     * Leave requests parked on this user for a decision.
     *
     * @return EloquentCollection<int, CrmLeaveRequest>
     */
    public function leaveAwaitingApproval(User $user): EloquentCollection {
        return CrmLeaveRequest::query()
            ->forApprover($user->id)
            ->with(['user:id,name', 'leaveType:id,name'])
            ->orderBy('start_date')
            ->limit((int) config('heritage_crm.dashboard.list_limit', 6))
            ->get();
    }

    /**
     * This user's own leave balances for the current leave year.
     *
     * Reads what already exists rather than going through LeaveBalanceService, whose
     * getOrCreateBalance() writes rows — not something a dashboard render should do.
     *
     * @return EloquentCollection<int, CrmLeaveBalance>
     */
    public function myLeaveBalances(User $user, int $year): EloquentCollection {
        return CrmLeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->whereHas('leaveType', fn (Builder $query) => $query->where('is_active', true))
            ->with('leaveType:id,name')
            ->get();
    }

    private function accountLabel(?string $customerName, ?string $leadName): string {
        return $customerName ?: ($leadName ?: 'Unassigned account');
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $definitions
     * @return array<int, array{key: string, label: string, value: int, url: string|null}>
     */
    private function stockMetrics(User $user, array $definitions): array {
        $counts = $this->headlineCounts($user);
        $metrics = [];

        foreach ($definitions as $key => [$label, $routeName]) {
            $metrics[] = [
                'key' => $key,
                'label' => $label,
                'value' => (int) ($counts[$key] ?? 0),
                'url' => $this->routeUrl($routeName),
            ];
        }

        return $metrics;
    }

    /**
     * @return array{current: array<string, int>, previous: array<string, int>}
     */
    private function flowCounts(User $user, DashboardPeriod $period): array {
        return $this->remember("flow.{$period->key}", $user, fn () => [
            'current' => $this->countsBetween($user, $period->start, $period->end, true),
            'previous' => $this->countsBetween($user, $period->previousStart, $period->previousEnd),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function countsBetween(User $user, CarbonInterface $from, CarbonInterface $to, bool $includeEnd = false): array {
        return [
            'new_leads' => $this->windowed(
                $this->scopeOwned(Lead::query(), $user),
                'created_at',
                $from,
                $to,
                $includeEnd
            )->count(),
            'converted_leads' => $this->windowed(
                $this->scopeOwned(Lead::query()->whereNotNull('converted_at'), $user),
                'converted_at',
                $from,
                $to,
                $includeEnd
            )->count(),
            'requests_opened' => $this->windowed(
                $this->scopeOwned(CrmRequest::query(), $user),
                'created_at',
                $from,
                $to,
                $includeEnd
            )->count(),
            'requests_closed' => $this->windowed(
                $this->scopeOwned(CrmRequest::query()->whereNotNull('closed_at'), $user),
                'closed_at',
                $from,
                $to,
                $includeEnd
            )->count(),
        ];
    }

    /**
     * @return array{key: string, label: string, value: float, display: string, previous: float, delta: int|null, direction: string, url: string|null}
     */
    private function moneyMetric(
        string $key,
        string $label,
        float $value,
        ?float $previous,
        string $currency,
        ?string $url
    ): array {
        $prefix = $currency === '' ? '' : $currency . ' ';

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'display' => $prefix . number_format($value, 2),
            'previous' => (float) $previous,
            'delta' => $previous === null ? null : $this->percentageChange($value, $previous),
            'direction' => $previous === null ? 'flat' : $this->direction($value, $previous),
            'url' => $url,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function commercialFigures(User $user, DashboardPeriod $period): array {
        return $this->remember("commercial.{$period->key}", $user, fn () => [
            'pipeline_value' => (float) $this->scopeOwned(
                CrmQuote::query()->whereIn('status', ['draft', 'sent']),
                $user
            )->sum('total_amount'),
            'quotes_awaiting' => $this->scopeOwned(
                CrmQuote::query()->where('status', 'sent'),
                $user
            )->count(),
            'accepted_value' => $this->acceptedValueBetween($user, $period->start, $period->end, true),
            'previous_accepted_value' => $this->acceptedValueBetween($user, $period->previousStart, $period->previousEnd),
            'invoiced_value' => $this->invoicedValueBetween($user, $period->start, $period->end, true),
            'previous_invoiced_value' => $this->invoicedValueBetween($user, $period->previousStart, $period->previousEnd),
        ]);
    }

    private function acceptedValueBetween(User $user, CarbonInterface $from, CarbonInterface $to, bool $includeEnd = false): float {
        return (float) $this->windowed(
            $this->scopeOwned(CrmQuote::query()->whereNotNull('accepted_at'), $user),
            'accepted_at',
            $from,
            $to,
            $includeEnd
        )->sum('total_amount');
    }

    private function invoicedValueBetween(User $user, CarbonInterface $from, CarbonInterface $to, bool $includeEnd = false): float {
        return (float) $this->windowed(
            $this->scopeOwned(CrmInvoice::query()->whereIn('status', ['issued', 'sent']), $user),
            'invoice_date',
            $from,
            $to,
            $includeEnd
        )->sum('total_amount');
    }

    /**
     * Constrain a query to a reporting window.
     *
     * Bounds are passed as datetimes rather than date strings on purpose. `invoice_date`
     * and `quote_date` are DATE columns in MySQL but SQLite stores them as
     * "Y-m-d 00:00:00", so a bare date string compares short and drops the last day.
     *
     * The start is always inclusive, because a DATE column lands exactly on midnight and
     * would otherwise lose the window's first day. The end is inclusive only for the
     * current window, whose end is "now" — a record written in the same second as the
     * page load still belongs to it. The comparison window keeps an exclusive end so
     * that a record sitting exactly on the boundary is not counted in both.
     */
    private function windowed(
        Builder $query,
        string $column,
        CarbonInterface $from,
        CarbonInterface $to,
        bool $includeEnd = false
    ): Builder {
        return $query
            ->where($column, '>=', $from)
            ->where($column, $includeEnd ? '<=' : '<', $to);
    }

    /**
     * Percentage change against the comparison window. Null when there is no
     * baseline to compare against, so the view can say "no prior activity"
     * instead of claiming a meaningless 100% rise.
     */
    private function percentageChange(float $value, float $previous): ?int {
        if ($previous == 0.0) {
            return null;
        }

        return (int) round((($value - $previous) / $previous) * 100);
    }

    private function direction(float $value, float $previous): string {
        if ($value == $previous) {
            return 'flat';
        }

        return $value > $previous ? 'up' : 'down';
    }

    private function routeUrl(?string $routeName, mixed $parameters = []): ?string {
        if (! is_string($routeName) || ! Route::has($routeName)) {
            return null;
        }

        return route($routeName, $parameters);
    }

    /**
     * @return array<string, int>
     */
    private function headlineCounts(User $user): array {
        return $this->remember('headline', $user, function () use ($user) {
            $today = now()->startOfDay();
            $tomorrow = now()->copy()->addDay()->startOfDay();

            return [
                'active_leads' => $this->scopeOwned(
                    Lead::query()->whereIn('status', ['active', 'qualified']),
                    $user
                )->count(),
                'active_customers' => $this->scopeOwned(
                    Customer::query()->where('status', '!=', 'inactive'),
                    $user
                )->count(),
                'open_sales_requests' => $this->scopeOwned(
                    CrmRequest::query()->where('type', 'sales')->whereNull('closed_at'),
                    $user
                )->count(),
                'open_support_requests' => $this->scopeOwned(
                    CrmRequest::query()
                        ->where('type', 'support')
                        ->whereNotIn('support_status', ['resolved', 'closed']),
                    $user
                )->count(),
                'overdue_follow_ups' => $this->scopeOwned(
                    CrmRequest::query()
                        ->whereNull('closed_at')
                        ->whereNotNull('next_action_at')
                        ->where('next_action_at', '<', $today),
                    $user
                )->count(),
                'today_follow_ups' => $this->scopeOwned(
                    CrmRequest::query()
                        ->whereNull('closed_at')
                        ->whereNotNull('next_action_at')
                        ->where('next_action_at', '>=', $today)
                        ->where('next_action_at', '<', $tomorrow),
                    $user
                )->count(),
                'open_development_items' => $this->scopeOwned(
                    DevelopmentRequest::query()->whereNotIn('status', ['shipped', 'declined']),
                    $user
                )->count(),
                'discussion_threads' => DiscussionThread::query()
                    ->when(! $user->canManageOperationalRecords(), function (Builder $query) use ($user) {
                        $query->where(function (Builder $threadQuery) use ($user) {
                            $threadQuery->where('initiated_by_id', $user->id)
                                ->orWhere('recipient_user_id', $user->id)
                                ->orWhere('owner_id', $user->id);
                        });
                    })
                    ->count(),
            ];
        });
    }

    private function scopeOwned(Builder $query, User $user, string $ownerColumn = 'owner_id'): Builder {
        if ($user->isRep()) {
            $query->where($ownerColumn, $user->id);
        }

        return $query;
    }

    /**
     * Cache per user — visibility is owner-scoped, so results are not shareable between users.
     * A non-positive TTL bypasses the cache entirely, which keeps tests and local debugging honest.
     */
    private function remember(string $bucket, User $user, callable $callback): mixed {
        $seconds = (int) config('heritage_crm.dashboard.cache_seconds', 60);

        if ($seconds <= 0) {
            return $callback();
        }

        return Cache::remember("crm.dashboard.{$bucket}.{$user->id}", $seconds, $callback);
    }
}
