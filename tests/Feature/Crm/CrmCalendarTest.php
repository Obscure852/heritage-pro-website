<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCalendar;
use App\Models\CrmCalendarEvent;
use App\Models\CrmCalendarMembership;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\CrmCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_workspace_renders_for_crm_roles_and_bootstraps_personal_calendars(): void
    {
        $admin = $this->createUser([
            'email' => 'calendar-admin@example.com',
            'role' => 'admin',
        ]);

        $finance = $this->createUser([
            'email' => 'calendar-finance@example.com',
            'role' => 'finance',
        ]);

        $manager = $this->createUser([
            'email' => 'calendar-manager@example.com',
            'role' => 'manager',
        ]);

        $rep = $this->createUser([
            'email' => 'calendar-rep@example.com',
            'role' => 'rep',
        ]);

        foreach ([$admin, $finance, $manager, $rep] as $user) {
            $this->actingAs($user)
                ->get(route('crm.calendar.index'))
                ->assertOk()
                ->assertSee('CRM Calendar');

            $this->assertDatabaseHas('crm_calendars', [
                'owner_id' => $user->id,
                'type' => 'personal',
            ]);
        }
    }

    public function test_rep_can_create_and_update_calendar_event_on_personal_calendar(): void
    {
        $rep = $this->createUser([
            'email' => 'event-owner@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'North Campus',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Decision Maker',
            'email' => 'dm@example.com',
            'is_primary' => true,
        ]);

        $this->actingAs($rep)->get(route('crm.calendar.index'))->assertOk();

        $calendar = CrmCalendar::query()
            ->where('owner_id', $rep->id)
            ->where('type', 'personal')
            ->firstOrFail();

        $createPayload = [
            'calendar_id' => $calendar->id,
            'owner_id' => $rep->id,
            'title' => 'Campus Demo',
            'starts_at' => now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->setTime(11, 0)->format('Y-m-d\TH:i'),
            'all_day' => false,
            'status' => 'scheduled',
            'visibility' => 'standard',
            'location' => 'Virtual',
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'attendee_user_ids' => [$rep->id],
            'reminder_minutes' => [15],
            'description' => 'Walk through the onboarding sequence.',
            'timezone' => config('app.timezone'),
        ];

        $response = $this->actingAs($rep)
            ->postJson(route('crm.calendar.events.store'), $createPayload)
            ->assertCreated()
            ->assertJsonPath('event.title', 'Campus Demo');

        $eventId = (int) $response->json('event.id');

        $this->assertDatabaseHas('crm_calendar_events', [
            'id' => $eventId,
            'calendar_id' => $calendar->id,
            'title' => 'Campus Demo',
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('crm_calendar_event_attendees', [
            'event_id' => $eventId,
            'user_id' => $rep->id,
        ]);

        $updatePayload = $createPayload;
        $updatePayload['title'] = 'Campus Demo Rescheduled';
        $updatePayload['status'] = 'completed';
        $updatePayload['starts_at'] = now()->addDays(2)->setTime(9, 30)->format('Y-m-d\TH:i');
        $updatePayload['ends_at'] = now()->addDays(2)->setTime(10, 30)->format('Y-m-d\TH:i');

        $this->actingAs($rep)
            ->patchJson(route('crm.calendar.events.update', $eventId), $updatePayload)
            ->assertOk()
            ->assertJsonPath('event.title', 'Campus Demo Rescheduled')
            ->assertJsonPath('event.extendedProps.status', 'completed');

        $this->assertDatabaseHas('crm_calendar_events', [
            'id' => $eventId,
            'title' => 'Campus Demo Rescheduled',
            'status' => 'completed',
        ]);
    }

    public function test_rep_cannot_modify_or_view_other_reps_personal_event(): void
    {
        $owner = $this->createUser([
            'email' => 'owner-rep@example.com',
            'role' => 'rep',
        ]);

        $intruder = $this->createUser([
            'email' => 'intruder-rep@example.com',
            'role' => 'rep',
        ]);

        /** @var CrmCalendarService $calendarService */
        $calendarService = app(CrmCalendarService::class);
        $calendar = $calendarService->ensurePersonalCalendar($owner);

        $event = CrmCalendarEvent::query()->create([
            'calendar_id' => $calendar->id,
            'owner_id' => $owner->id,
            'created_by_id' => $owner->id,
            'updated_by_id' => $owner->id,
            'title' => 'Owner Follow-up',
            'starts_at' => now()->addDay()->setTime(14, 0),
            'ends_at' => now()->addDay()->setTime(15, 0),
            'all_day' => false,
            'status' => 'scheduled',
            'visibility' => 'standard',
            'timezone' => config('app.timezone'),
        ]);

        $payload = [
            'calendar_id' => $calendar->id,
            'owner_id' => $owner->id,
            'title' => 'Intrusion Attempt',
            'starts_at' => now()->addDay()->setTime(14, 0)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->setTime(15, 0)->format('Y-m-d\TH:i'),
            'all_day' => false,
            'status' => 'scheduled',
            'visibility' => 'standard',
            'timezone' => config('app.timezone'),
        ];

        $this->actingAs($intruder)
            ->patchJson(route('crm.calendar.events.update', $event), $payload)
            ->assertForbidden();

        $this->actingAs($intruder)
            ->deleteJson(route('crm.calendar.events.destroy', $event))
            ->assertForbidden();

        $feedResponse = $this->actingAs($intruder)
            ->getJson(route('crm.calendar.feed', [
                'start' => now()->startOfDay()->toIso8601String(),
                'end' => now()->addDays(7)->endOfDay()->toIso8601String(),
                'calendar_ids' => [$calendar->id],
            ]))
            ->assertOk();

        $this->assertCount(0, $feedResponse->json());
    }

    public function test_busy_only_events_are_redacted_for_view_only_members(): void
    {
        $admin = $this->createUser([
            'email' => 'calendar-admin-owner@example.com',
            'role' => 'admin',
        ]);

        $financeViewer = $this->createUser([
            'email' => 'calendar-viewer@example.com',
            'role' => 'finance',
        ]);

        $sharedCalendar = CrmCalendar::query()->create([
            'owner_id' => $admin->id,
            'created_by_id' => $admin->id,
            'updated_by_id' => $admin->id,
            'name' => 'Leadership',
            'slug' => 'leadership',
            'type' => 'shared',
            'color' => '#5156be',
            'is_active' => true,
            'is_default' => false,
        ]);

        CrmCalendarMembership::query()->create([
            'calendar_id' => $sharedCalendar->id,
            'user_id' => $financeViewer->id,
            'permission' => 'view',
            'is_visible' => true,
        ]);

        CrmCalendarEvent::query()->create([
            'calendar_id' => $sharedCalendar->id,
            'owner_id' => $admin->id,
            'created_by_id' => $admin->id,
            'updated_by_id' => $admin->id,
            'title' => 'Salary Review',
            'description' => 'Sensitive internal compensation review.',
            'location' => 'Boardroom',
            'starts_at' => now()->addDay()->setTime(8, 0),
            'ends_at' => now()->addDay()->setTime(9, 0),
            'all_day' => false,
            'status' => 'scheduled',
            'visibility' => 'busy_only',
            'timezone' => config('app.timezone'),
        ]);

        $this->actingAs($financeViewer)
            ->getJson(route('crm.calendar.feed', [
                'start' => now()->startOfDay()->toIso8601String(),
                'end' => now()->addDays(7)->endOfDay()->toIso8601String(),
                'calendar_ids' => [$sharedCalendar->id],
            ]))
            ->assertOk()
            ->assertJsonPath('0.title', 'Busy')
            ->assertJsonPath('0.extendedProps.description', null)
            ->assertJsonPath('0.extendedProps.location', null)
            ->assertJsonPath('0.extendedProps.can_view_sensitive', false);
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
