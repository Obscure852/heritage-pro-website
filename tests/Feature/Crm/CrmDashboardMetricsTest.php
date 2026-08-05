<?php

namespace Tests\Feature\Crm;

use App\Models\CrmCalendarEvent;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmInvoice;
use App\Models\CrmLeaveBalance;
use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveType;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use App\Services\Crm\AttendanceReportService;
use App\Services\Crm\CrmCalendarService;
use App\Services\Crm\CrmModulePermissionService;
use App\Services\Crm\DashboardMetricsService;
use App\Services\Crm\DashboardWidgetRegistry;
use App\Services\Crm\LeaveBalanceService;
use App\Support\Crm\DashboardPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_headline_metrics_count_the_current_book_of_work(): void
    {
        $admin = $this->createUser();
        $this->buildRequestFixtures($admin);

        $metrics = collect($this->service()->headlineMetrics($admin))
            ->pluck('value', 'key');

        $this->assertSame(2, $metrics['active_leads']);
        $this->assertSame(1, $metrics['active_customers']);
        $this->assertSame(4, $metrics['open_sales_requests']);
        $this->assertSame(1, $metrics['open_support_requests']);
    }

    public function test_pressure_metrics_count_late_and_queued_work(): void
    {
        $admin = $this->createUser();
        $this->buildRequestFixtures($admin);

        $metrics = collect($this->service()->pressureMetrics($admin))
            ->pluck('value', 'key');

        $this->assertSame(1, $metrics['overdue_follow_ups']);
        $this->assertSame(1, $metrics['today_follow_ups']);
        $this->assertSame(1, $metrics['open_development_items']);
    }

    public function test_headline_metrics_are_scoped_to_the_owning_rep(): void
    {
        $rep = $this->createUser(['role' => 'rep']);
        $otherRep = $this->createUser(['role' => 'rep']);

        Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Owned Lead',
            'status' => 'active',
        ]);

        Lead::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Someone Else Lead',
            'status' => 'active',
        ]);

        $metrics = collect($this->service()->headlineMetrics($rep))->pluck('value', 'key');

        $this->assertSame(1, $metrics['active_leads']);
    }

    public function test_cached_metrics_are_reused_and_isolated_per_user(): void
    {
        config(['heritage_crm.dashboard.cache_seconds' => 60]);

        $admin = $this->createUser();
        $otherAdmin = $this->createUser();
        $service = $this->service();

        $this->assertSame(0, $this->activeLeadCount($service, $admin));

        Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Late Arrival',
            'status' => 'active',
        ]);

        // Still served from the cache for this user.
        $this->assertSame(0, $this->activeLeadCount($service, $admin));

        // A different user has their own cache entry and sees the new row.
        $this->assertSame(1, $this->activeLeadCount($service, $otherAdmin));
    }

    public function test_flow_metrics_compare_the_period_against_the_preceding_window(): void
    {
        $admin = $this->createUser();
        $period = DashboardPeriod::fromKey('30d');

        // Three leads inside the selected window, one inside the comparison window.
        $this->createLeadCreatedAt($admin, now()->subDays(5));
        $this->createLeadCreatedAt($admin, now()->subDays(5));
        $this->createLeadCreatedAt($admin, now()->subDays(5));
        $this->createLeadCreatedAt($admin, now()->subDays(40));

        $newLeads = collect($this->service()->flowMetrics($admin, $period))
            ->firstWhere('key', 'new_leads');

        $this->assertSame(3, $newLeads['value']);
        $this->assertSame(1, $newLeads['previous']);
        $this->assertSame(200, $newLeads['delta']);
        $this->assertSame('up', $newLeads['direction']);
    }

    public function test_flow_metric_delta_is_null_without_a_baseline(): void
    {
        $admin = $this->createUser();
        $this->createLeadCreatedAt($admin, now()->subDays(2));

        $newLeads = collect($this->service()->flowMetrics($admin, DashboardPeriod::fromKey('30d')))
            ->firstWhere('key', 'new_leads');

        $this->assertSame(1, $newLeads['value']);
        $this->assertSame(0, $newLeads['previous']);
        $this->assertNull($newLeads['delta'], 'A zero baseline cannot produce a percentage change.');
    }

    public function test_records_written_in_the_current_instant_still_count(): void
    {
        $admin = $this->createUser();

        // Created "now" — the same second the period's end boundary is taken.
        $this->createRequest($admin);

        $opened = collect($this->service()->flowMetrics($admin, DashboardPeriod::fromKey('30d')))
            ->firstWhere('key', 'requests_opened');

        $this->assertSame(1, $opened['value'], 'The current window must include records written this instant.');
    }

    public function test_a_record_on_the_window_boundary_is_not_counted_twice(): void
    {
        $admin = $this->createUser();
        $period = DashboardPeriod::fromKey('30d');

        // Sits exactly on the seam between the comparison window and the current one.
        $this->createLeadCreatedAt($admin, $period->start);

        $newLeads = collect($this->service()->flowMetrics($admin, $period))
            ->firstWhere('key', 'new_leads');

        $this->assertSame(1, $newLeads['value']);
        $this->assertSame(0, $newLeads['previous'], 'The boundary record belongs to the current window only.');
    }

    public function test_period_selector_switches_the_window_and_rejects_unknown_values(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.dashboard', ['period' => '7d']))
            ->assertOk()
            ->assertSee('Last 7 days');

        // An unrecognised period falls back to the default rather than erroring.
        $this->actingAs($admin)
            ->get(route('crm.dashboard', ['period' => 'all-time-ever']))
            ->assertOk()
            ->assertSee('Last 30 days');
    }

    public function test_commercial_metrics_sum_quote_and_invoice_value(): void
    {
        $admin = $this->createUser();
        $period = DashboardPeriod::fromKey('30d');

        $this->createQuote($admin, ['status' => 'draft', 'total_amount' => 1000]);
        $this->createQuote($admin, ['status' => 'sent', 'total_amount' => 2500]);
        $this->createQuote($admin, [
            'status' => 'accepted',
            'total_amount' => 4000,
            'accepted_at' => now()->subDays(3),
        ]);
        // Accepted before the window opened — must not count toward this period.
        $this->createQuote($admin, [
            'status' => 'accepted',
            'total_amount' => 9999,
            'accepted_at' => now()->subDays(45),
        ]);

        $this->createInvoice($admin, ['status' => 'issued', 'total_amount' => 3000]);
        $this->createInvoice($admin, ['status' => 'draft', 'total_amount' => 500]);

        $metrics = collect($this->service()->commercialMetrics($admin, $period))->keyBy('key');

        // Only draft + sent quotes are open pipeline.
        $this->assertSame(3500.0, $metrics['pipeline_value']['value']);
        $this->assertSame(1.0, $metrics['quotes_awaiting']['value']);
        $this->assertSame(4000.0, $metrics['accepted_value']['value']);
        // Drafts are not revenue.
        $this->assertSame(3000.0, $metrics['invoiced_value']['value']);
        $this->assertStringContainsString('3,000.00', $metrics['invoiced_value']['display']);
    }

    public function test_revenue_trend_buckets_invoices_by_month(): void
    {
        $admin = $this->createUser();

        $this->createInvoice($admin, [
            'status' => 'issued',
            'total_amount' => 1200,
            'invoice_date' => now()->startOfMonth()->toDateString(),
        ]);
        $this->createInvoice($admin, [
            'status' => 'draft',
            'total_amount' => 800,
            'invoice_date' => now()->startOfMonth()->toDateString(),
        ]);

        $trend = $this->service()->revenueTrend($admin);

        $this->assertCount(12, $trend['labels']);
        $this->assertSame(now()->format('M y'), end($trend['labels']));

        $issued = $trend['series'][0];
        $draft = $trend['series'][1];

        $this->assertSame('Issued', $issued['name']);
        $this->assertSame(1200.0, end($issued['data']));
        $this->assertSame(800.0, end($draft['data']));
    }

    public function test_quote_conversion_groups_this_period_by_status(): void
    {
        $admin = $this->createUser();

        $this->createQuote($admin, ['status' => 'accepted', 'total_amount' => 100]);
        $this->createQuote($admin, ['status' => 'accepted', 'total_amount' => 100]);
        $this->createQuote($admin, ['status' => 'rejected', 'total_amount' => 100]);
        $this->createQuote($admin, [
            'status' => 'sent',
            'total_amount' => 100,
            'quote_date' => now()->subDays(200)->toDateString(),
        ]);

        $conversion = $this->service()->quoteConversion($admin, DashboardPeriod::fromKey('30d'));

        $this->assertSame(3, $conversion['total']);
        $this->assertSame(['Accepted', 'Rejected'], $conversion['labels']);
        $this->assertSame([2, 1], $conversion['values']);
    }

    public function test_commercial_widgets_are_hidden_without_products_access(): void
    {
        config(['heritage_crm.dashboard.widgets.commercial.enabled' => true]);

        $admin = $this->createUser();
        $this->createQuote($admin, ['status' => 'sent', 'total_amount' => 7654]);

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Pipeline and revenue');

        // Revoking products access must remove the money widgets entirely.
        app(CrmModulePermissionService::class)->syncPermissions($admin, ['products' => null]);
        config(['heritage_crm.modules.products.default_permissions' => []]);

        $this->actingAs($admin->fresh())
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertDontSee('Pipeline and revenue')
            ->assertDontSee('7,654.00');
    }

    public function test_my_day_merges_todays_events_with_due_and_late_follow_ups(): void
    {
        $admin = $this->createUser();

        $this->createRequest($admin, [
            'title' => 'Late renewal',
            'next_action' => 'Call the bursar',
            'next_action_at' => now()->startOfDay()->subDays(2),
        ]);
        $this->createRequest($admin, [
            'title' => 'Due today',
            'next_action' => 'Send revised quote',
            'next_action_at' => now()->startOfDay()->addHours(15),
        ]);
        // Tomorrow is not today's problem.
        $this->createRequest($admin, [
            'title' => 'Later this week',
            'next_action' => 'Not yet',
            'next_action_at' => now()->addDays(3),
        ]);

        $items = $this->service()->myDay($admin);
        $labels = array_column($items, 'label');

        $this->assertContains('Call the bursar', $labels);
        $this->assertContains('Send revised quote', $labels);
        $this->assertNotContains('Not yet', $labels);

        $late = collect($items)->firstWhere('label', 'Call the bursar');
        $this->assertTrue($late['overdue']);
        $this->assertSame('follow_up', $late['kind']);

        // Chronological, so the late item leads.
        $this->assertSame('Call the bursar', $labels[0]);
    }

    public function test_my_day_only_shows_calendar_events_the_user_owns_or_attends(): void
    {
        $admin = $this->createUser();
        $colleague = $this->createUser();

        $this->createEvent($admin, 'My planning session');
        $this->createEvent($colleague, "Someone else's meeting");

        $labels = array_column($this->service()->myDay($admin), 'label');

        $this->assertContains('My planning session', $labels);
        $this->assertNotContains("Someone else's meeting", $labels);
    }

    public function test_going_cold_excludes_recent_contact_and_brand_new_requests(): void
    {
        $admin = $this->createUser();

        $cold = $this->createRequest($admin, ['title' => 'Silent since May']);
        DB::table('requests')->where('id', $cold->id)->update([
            'last_contact_at' => now()->subDays(40),
        ]);

        $warm = $this->createRequest($admin, ['title' => 'Spoke yesterday']);
        DB::table('requests')->where('id', $warm->id)->update([
            'last_contact_at' => now()->subDay(),
        ]);

        // Never contacted, but only created today — not cold yet.
        $this->createRequest($admin, ['title' => 'Brand new']);

        // Never contacted and old enough to count.
        $neglected = $this->createRequest($admin, ['title' => 'Never called back']);
        DB::table('requests')->where('id', $neglected->id)->update([
            'created_at' => now()->subDays(30),
        ]);

        $titles = $this->service()->goingCold($admin)->pluck('title')->all();

        $this->assertContains('Silent since May', $titles);
        $this->assertContains('Never called back', $titles);
        $this->assertNotContains('Spoke yesterday', $titles);
        $this->assertNotContains('Brand new', $titles);
    }

    public function test_needs_attention_withholds_commercial_rows_when_not_permitted(): void
    {
        $admin = $this->createUser();

        $this->createQuote($admin, [
            'status' => 'sent',
            'subject' => 'Lapsing licence',
            'valid_until' => now()->subDays(2)->toDateString(),
        ]);

        $aged = $this->createRequest($admin, ['type' => 'support', 'title' => 'Stuck ticket', 'support_status' => 'open']);
        DB::table('requests')->where('id', $aged->id)->update(['created_at' => now()->subDays(20)]);

        $withCommercial = collect($this->service()->needsAttention($admin, true));
        $this->assertTrue($withCommercial->contains(fn ($row) => str_contains($row['label'], 'Lapsing licence')));
        $this->assertTrue($withCommercial->contains('label', 'Stuck ticket'));

        $withoutCommercial = collect($this->service()->needsAttention($admin, false));
        $this->assertFalse($withoutCommercial->contains(fn ($row) => str_contains($row['label'], 'Lapsing licence')));
        $this->assertTrue($withoutCommercial->contains('label', 'Stuck ticket'));
    }

    public function test_needs_attention_ranks_urgency_above_age(): void
    {
        $admin = $this->createUser();

        // Older, but merely sitting in draft.
        $this->createInvoice($admin, [
            'status' => 'draft',
            'subject' => 'Old draft',
            'invoice_date' => now()->subDays(60)->toDateString(),
        ]);

        // Newer, but the deadline has already passed.
        $this->createQuote($admin, [
            'status' => 'sent',
            'subject' => 'Lapsed quote',
            'valid_until' => now()->subDay()->toDateString(),
        ]);

        $rows = $this->service()->needsAttention($admin, true);

        $this->assertStringContainsString('Lapsed quote', $rows[0]['label'], 'A lapsed deadline must outrank an older backlog item.');
        $this->assertSame('danger', $rows[0]['severity']);
    }

    public function test_leave_approvals_only_show_requests_parked_on_this_user(): void
    {
        $approver = $this->createUser();
        $otherApprover = $this->createUser();
        $applicant = $this->createUser(['role' => 'rep']);

        $mine = $this->createLeaveRequest($applicant, $approver, 'pending');
        $this->createLeaveRequest($applicant, $otherApprover, 'pending');
        // Already decided — no longer awaiting anyone.
        $this->createLeaveRequest($applicant, $approver, 'approved');

        $awaiting = $this->service()->leaveAwaitingApproval($approver);

        $this->assertCount(1, $awaiting);
        $this->assertSame($mine->id, $awaiting->first()->id);
    }

    public function test_team_today_reports_attendance_and_who_is_on_leave(): void
    {
        $admin = $this->createUser();
        $applicant = $this->createUser(['role' => 'rep']);

        // Approved and spanning today.
        $this->createLeaveRequest($applicant, $admin, 'approved', now()->subDay(), now()->addDay());
        // Approved but already finished.
        $this->createLeaveRequest($applicant, $admin, 'approved', now()->subDays(9), now()->subDays(5));

        $teamToday = $this->service()->teamToday($admin, app(AttendanceReportService::class));

        $this->assertArrayHasKey('present', $teamToday['stats']);
        $this->assertCount(1, $teamToday['on_leave']);
        $this->assertSame($applicant->id, $teamToday['on_leave']->first()->user_id);
    }

    public function test_leave_balances_are_read_without_creating_rows(): void
    {
        $admin = $this->createUser();
        $year = app(LeaveBalanceService::class)->currentLeaveYear();

        $before = CrmLeaveBalance::query()->count();
        $balances = $this->service()->myLeaveBalances($admin, $year);

        $this->assertCount(0, $balances);
        $this->assertSame($before, CrmLeaveBalance::query()->count(), 'Rendering the dashboard must not write leave balance rows.');
    }

    public function test_people_widgets_are_hidden_without_module_access(): void
    {
        config([
            'heritage_crm.dashboard.widgets.team_today.enabled' => true,
            'heritage_crm.dashboard.widgets.leave.enabled' => true,
        ]);

        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Who is in')
            ->assertSee('Awaiting your decision');

        config([
            'heritage_crm.modules.attendance.default_permissions' => [],
            'heritage_crm.modules.leave.default_permissions' => [],
        ]);
        app(CrmModulePermissionService::class)->syncPermissions($admin, ['attendance' => null, 'leave' => null]);

        $this->actingAs($admin->fresh())
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertDontSee('Who is in')
            ->assertDontSee('Awaiting your decision');
    }

    public function test_widget_registry_filters_by_module_role_and_setting(): void
    {
        config([
            'heritage_crm.dashboard.widgets.commercial.enabled' => true,
            'heritage_crm.dashboard.widgets.going_cold.enabled' => true,
            'heritage_crm.dashboard.widgets.needs_attention.enabled' => true,
        ]);

        $admin = $this->createUser();
        $registry = app(DashboardWidgetRegistry::class);

        $withClock = $registry->visibleFor($admin, ['show_dashboard_clock' => true])->pluck('key');
        $this->assertTrue($withClock->contains('clock'));

        // The clock is driven by an attendance setting, not by permissions.
        $withoutClock = $registry->visibleFor($admin, ['show_dashboard_clock' => false])->pluck('key');
        $this->assertFalse($withoutClock->contains('clock'));

        // Finance is excluded from the sales-only widgets by role.
        $finance = $this->createUser(['role' => 'finance']);
        $financeKeys = $registry->visibleFor($finance)->pluck('key');

        $this->assertFalse($financeKeys->contains('going_cold'));
        $this->assertTrue($financeKeys->contains('commercial'), 'Finance keeps the money widgets.');
        $this->assertTrue($financeKeys->contains('needs_attention'));
    }

    public function test_disabled_widgets_are_switched_off_without_being_deleted(): void
    {
        $admin = $this->createUser();
        $registry = app(DashboardWidgetRegistry::class);

        // Still declared, so it can be switched back on from config alone.
        $this->assertTrue($registry->all()->has('commercial'));
        $this->assertFalse($registry->visibleFor($admin)->pluck('key')->contains('commercial'));

        config(['heritage_crm.dashboard.widgets.commercial.enabled' => true]);

        $this->assertTrue($registry->visibleFor($admin)->pluck('key')->contains('commercial'));
    }

    public function test_widget_rows_pair_halves_and_close_gaps_left_by_hidden_widgets(): void
    {
        $admin = $this->createUser();
        $registry = app(DashboardWidgetRegistry::class);

        config([
            'heritage_crm.dashboard.widgets' => [
                'a' => ['partial' => 'x', 'size' => 'full'],
                'b' => ['partial' => 'x', 'size' => 'half'],
                'c' => ['partial' => 'x', 'size' => 'half', 'roles' => ['nobody']],
                'd' => ['partial' => 'x', 'size' => 'half'],
                'e' => ['partial' => 'x', 'size' => 'half'],
            ],
        ]);

        $rows = $registry->rowsFor($admin);
        $shape = array_map(fn (array $row) => implode('+', array_column($row, 'key')), $rows);

        // 'c' is hidden, so 'b' pairs with 'd' rather than leaving a hole.
        $this->assertSame(['a', 'b+d', 'e'], $shape);
    }

    public function test_hidden_widgets_are_not_queried(): void
    {
        $admin = $this->createUser();

        config(['heritage_crm.dashboard.widgets' => [
            'pressure' => ['partial' => 'crm.dashboard.partials.pressure', 'size' => 'full'],
        ]]);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $this->actingAs($admin)->get(route('crm.dashboard'))->assertOk();

        $touched = fn (string $table) => collect($queries)->contains(fn ($sql) => str_contains($sql, $table));

        $this->assertFalse($touched('crm_quotes'), 'The commercial widget is not registered, so quotes must not be queried.');
        $this->assertFalse($touched('crm_leave_requests'), 'The leave widget is not registered, so leave must not be queried.');
        $this->assertFalse($touched('crm_calendar_events'), 'My day is not registered, so calendar events must not be queried.');
    }

    public function test_dashboard_page_renders_the_metric_labels(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Active leads')
            ->assertSee('Overdue follow-ups')
            ->assertSee('My day')
            // Switched off in config — present in the registry, absent from the page.
            ->assertDontSee('Pipeline and revenue');
    }

    private function createLeaveRequest(
        User $applicant,
        User $approver,
        string $status,
        $startDate = null,
        $endDate = null
    ): CrmLeaveRequest {
        $startDate = $startDate ?: now()->addDays(5);
        $endDate = $endDate ?: now()->addDays(7);

        return CrmLeaveRequest::query()->create([
            'user_id' => $applicant->id,
            'leave_type_id' => CrmLeaveType::query()->firstOrFail()->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => 3,
            'reason' => 'Dashboard fixture.',
            'status' => $status,
            'submitted_at' => now(),
            'current_approver_id' => $status === 'pending' ? $approver->id : null,
        ]);
    }

    private function createEvent(User $owner, string $title): CrmCalendarEvent
    {
        $calendar = app(CrmCalendarService::class)->ensurePersonalCalendar($owner);

        return CrmCalendarEvent::query()->create([
            'calendar_id' => $calendar->id,
            'owner_id' => $owner->id,
            'created_by_id' => $owner->id,
            'updated_by_id' => $owner->id,
            'title' => $title,
            'starts_at' => now()->startOfDay()->addHours(10),
            'ends_at' => now()->startOfDay()->addHours(11),
            'all_day' => false,
            'status' => 'scheduled',
            'visibility' => 'standard',
            'timezone' => config('app.timezone'),
        ]);
    }

    private function createQuote(User $owner, array $attributes = []): CrmQuote
    {
        $currency = CrmCommercialCurrency::query()->firstOrFail();

        return CrmQuote::query()->create(array_merge([
            'owner_id' => $owner->id,
            'quote_number' => 'QT-' . uniqid(),
            'status' => 'draft',
            'subject' => 'Dashboard fixture',
            'quote_date' => now()->toDateString(),
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            'currency_position' => $currency->symbol_position,
            'currency_precision' => $currency->precision,
            'total_amount' => 0,
        ], $attributes));
    }

    private function createInvoice(User $owner, array $attributes = []): CrmInvoice
    {
        $currency = CrmCommercialCurrency::query()->firstOrFail();

        return CrmInvoice::query()->create(array_merge([
            'owner_id' => $owner->id,
            'invoice_number' => 'INV-' . uniqid(),
            'status' => 'draft',
            'subject' => 'Dashboard fixture',
            'invoice_date' => now()->toDateString(),
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            'currency_position' => $currency->symbol_position,
            'currency_precision' => $currency->precision,
            'total_amount' => 0,
        ], $attributes));
    }

    private function createLeadCreatedAt(User $owner, $createdAt): void
    {
        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Lead ' . uniqid(),
            'status' => 'active',
        ]);

        // created_at is guarded, so set it straight on the table to place the row in time.
        DB::table('leads')->where('id', $lead->id)->update(['created_at' => $createdAt]);
    }

    private function activeLeadCount(DashboardMetricsService $service, User $user): int
    {
        return collect($service->headlineMetrics($user))
            ->firstWhere('key', 'active_leads')['value'];
    }

    private function buildRequestFixtures(User $owner): void
    {
        $cold = SalesStage::query()->where('slug', 'cold')->firstOrFail();
        $qualified = SalesStage::query()->where('slug', 'qualified')->firstOrFail();

        Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Active Lead',
            'status' => 'active',
        ]);
        Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Qualified Lead',
            'status' => 'qualified',
        ]);
        Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Lost Lead',
            'status' => 'lost',
        ]);

        Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Live Customer',
            'status' => 'active',
        ]);
        Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Dormant Customer',
            'status' => 'inactive',
        ]);

        $this->createRequest($owner, ['sales_stage_id' => $cold->id]);
        $this->createRequest($owner, ['sales_stage_id' => $qualified->id]);
        $this->createRequest($owner, [
            'sales_stage_id' => $cold->id,
            'closed_at' => now(),
        ]);
        $this->createRequest($owner, [
            'type' => 'support',
            'support_status' => 'open',
        ]);
        $this->createRequest($owner, [
            'type' => 'support',
            'support_status' => 'resolved',
        ]);
        $this->createRequest($owner, [
            'next_action_at' => now()->startOfDay()->subDay(),
        ]);
        $this->createRequest($owner, [
            'next_action_at' => now()->startOfDay()->addHours(12),
        ]);

        DevelopmentRequest::query()->create([
            'owner_id' => $owner->id,
            'title' => 'Open dev item',
            'description' => 'Still in the backlog.',
            'status' => 'backlog',
        ]);
        DevelopmentRequest::query()->create([
            'owner_id' => $owner->id,
            'title' => 'Delivered dev item',
            'description' => 'Already shipped.',
            'status' => 'shipped',
        ]);
    }

    private function createRequest(User $owner, array $attributes = []): CrmRequest
    {
        return CrmRequest::query()->create(array_merge([
            'owner_id' => $owner->id,
            'type' => 'sales',
            'title' => 'Request ' . uniqid(),
            'description' => 'Dashboard fixture.',
        ], $attributes));
    }

    private function service(): DashboardMetricsService
    {
        return app(DashboardMetricsService::class);
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
