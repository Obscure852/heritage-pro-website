<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Role;
use App\Models\Term;
use App\Models\User;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivitySettingsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(BlockNonAfricanCountries::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->ensureActivitiesPhaseOneSchema();

        DB::table('school_setup')->updateOrInsert(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => 'Junior',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function test_only_administrator_and_activities_admin_can_open_settings_page(): void
    {
        $administrator = $this->createUserWithRoles('activities-settings-admin@example.com', ['Administrator']);
        $activitiesAdmin = $this->createUserWithRoles('activities-settings-module-admin@example.com', ['Activities Admin']);
        $editor = $this->createUserWithRoles('activities-settings-edit@example.com', ['Activities Edit']);
        $viewer = $this->createUserWithRoles('activities-settings-view@example.com', ['Activities View']);
        $staff = $this->createUserWithRoles('activities-settings-staff@example.com', ['Activities Staff']);

        $this->actingAs($administrator)
            ->get(route('activities.settings.index'))
            ->assertOk()
            ->assertSee('Activities Settings');

        $this->actingAs($activitiesAdmin)
            ->get(route('activities.settings.index'))
            ->assertOk()
            ->assertSee('Activities Settings');

        $this->actingAs($editor)
            ->get(route('activities.settings.index'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('activities.settings.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('activities.settings.index'))
            ->assertForbidden();
    }

    public function test_settings_link_is_visible_only_to_settings_managers(): void
    {
        $term = $this->createTerm(2026, 1);
        $settingsManager = $this->createUserWithRoles('activities-settings-link-admin@example.com', ['Activities Admin']);
        $editor = $this->createUserWithRoles('activities-settings-link-edit@example.com', ['Activities Edit']);

        $managerResponse = $this->actingAs($settingsManager)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.index'));

        $managerResponse->assertOk()
            ->assertSee(route('activities.settings.index'), false);

        $editorResponse = $this->actingAs($editor)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.index'));

        $editorResponse->assertOk()
            ->assertDontSee(route('activities.settings.index'), false);
    }

    public function test_each_settings_tab_persists_its_changes_to_system_settings(): void
    {
        $user = $this->createUserWithRoles('activities-settings-save@example.com', ['Activities Admin']);

        $activityFieldsPayload = $this->activityFieldsPayload();
        $activityFieldsPayload['categories'][0]['label'] = 'Club Program';
        $activityFieldsPayload['gender_policies'][] = [
            'key' => 'open',
            'label' => 'Open',
            'active' => 1,
            'system' => 0,
        ];

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_ACTIVITY_FIELDS]), $activityFieldsPayload)
            ->assertRedirect(route('activities.settings.index', ['tab' => ActivitySettingsService::TAB_ACTIVITY_FIELDS]));

        $categoryRows = app(ActivitySettingsService::class)->categoryRows();
        $genderPolicyRows = app(ActivitySettingsService::class)->genderPolicyRows();

        $this->assertSame('Club Program', collect($categoryRows)->firstWhere('key', Activity::CATEGORY_CLUB)['label'] ?? null);
        $this->assertSame('Open', collect($genderPolicyRows)->firstWhere('key', 'open')['label'] ?? null);

        $eventFieldsPayload = $this->eventFieldsPayload();
        $eventFieldsPayload['event_types'][] = [
            'key' => 'festival',
            'label' => 'Festival',
            'active' => 1,
            'system' => 0,
        ];

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_EVENT_FIELDS]), $eventFieldsPayload)
            ->assertRedirect(route('activities.settings.index', ['tab' => ActivitySettingsService::TAB_EVENT_FIELDS]));

        $eventTypeRows = app(ActivitySettingsService::class)->eventTypeRows();

        $this->assertSame('Festival', collect($eventTypeRows)->firstWhere('key', 'festival')['label'] ?? null);

        $defaultsPayload = $this->defaultsPayload([
            'default_category' => Activity::CATEGORY_SPORT,
            'default_delivery_mode' => Activity::DELIVERY_ONE_OFF,
            'default_participation_mode' => Activity::PARTICIPATION_INDIVIDUAL,
            'default_result_mode' => Activity::RESULT_POINTS,
            'default_gender_policy' => 'open',
            'default_capacity' => 24,
            'default_attendance_required' => 0,
            'default_allow_house_linkage' => 1,
            'default_event_type' => 'festival',
            'default_publish_to_calendar' => 1,
            'default_house_linked' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_DEFAULTS]), $defaultsPayload)
            ->assertRedirect(route('activities.settings.index', ['tab' => ActivitySettingsService::TAB_DEFAULTS]));

        $activityDefaults = app(ActivitySettingsService::class)->activityDefaults();
        $eventDefaults = app(ActivitySettingsService::class)->eventDefaults();

        $this->assertSame(Activity::CATEGORY_SPORT, $activityDefaults['category']);
        $this->assertSame(Activity::DELIVERY_ONE_OFF, $activityDefaults['delivery_mode']);
        $this->assertSame(Activity::PARTICIPATION_INDIVIDUAL, $activityDefaults['participation_mode']);
        $this->assertSame(Activity::RESULT_POINTS, $activityDefaults['result_mode']);
        $this->assertSame('open', $activityDefaults['gender_policy']);
        $this->assertSame(24, $activityDefaults['capacity']);
        $this->assertFalse($activityDefaults['attendance_required']);
        $this->assertTrue($activityDefaults['allow_house_linkage']);
        $this->assertSame('festival', $eventDefaults['event_type']);
        $this->assertTrue($eventDefaults['publish_to_calendar']);
        $this->assertTrue($eventDefaults['house_linked']);

        $this->assertDatabaseHas('s_m_s_api_settings', ['key' => ActivitySettingsService::KEY_CATEGORIES]);
        $this->assertDatabaseHas('s_m_s_api_settings', ['key' => ActivitySettingsService::KEY_EVENT_TYPES]);
        $this->assertDatabaseHas('s_m_s_api_settings', ['key' => ActivitySettingsService::KEY_ACTIVITY_DEFAULTS]);
        $this->assertDatabaseHas('s_m_s_api_settings', ['key' => ActivitySettingsService::KEY_EVENT_DEFAULTS]);
    }

    public function test_updated_settings_are_used_on_create_report_filters_and_event_forms(): void
    {
        $user = $this->createUserWithRoles('activities-settings-integration@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 2);

        $activityFieldsPayload = $this->activityFieldsPayload();
        $activityFieldsPayload['categories'][0]['label'] = 'Club Program';
        $activityFieldsPayload['categories'][] = [
            'key' => 'leadership',
            'label' => 'Leadership',
            'active' => 1,
            'system' => 0,
        ];

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_ACTIVITY_FIELDS]), $activityFieldsPayload)
            ->assertRedirect();

        $eventFieldsPayload = $this->eventFieldsPayload();
        $eventFieldsPayload['event_types'][] = [
            'key' => 'festival',
            'label' => 'Festival',
            'active' => 1,
            'system' => 0,
        ];

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_EVENT_FIELDS]), $eventFieldsPayload)
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_DEFAULTS]), $this->defaultsPayload([
                'default_category' => 'leadership',
                'default_delivery_mode' => Activity::DELIVERY_ONE_OFF,
                'default_participation_mode' => Activity::PARTICIPATION_INDIVIDUAL,
                'default_result_mode' => Activity::RESULT_POINTS,
                'default_gender_policy' => 'boys',
                'default_capacity' => 24,
                'default_attendance_required' => 0,
                'default_allow_house_linkage' => 1,
                'default_event_type' => 'festival',
                'default_publish_to_calendar' => 1,
                'default_house_linked' => 1,
            ]))
            ->assertRedirect();

        $createResponse = $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.create'));

        $createResponse->assertOk();
        $createHtml = $createResponse->getContent();

        $this->assertSame('leadership', $this->selectedOptionValue($createHtml, 'category'));
        $this->assertSame('one_off', $this->selectedOptionValue($createHtml, 'delivery_mode'));
        $this->assertSame('individual', $this->selectedOptionValue($createHtml, 'participation_mode'));
        $this->assertSame('points', $this->selectedOptionValue($createHtml, 'result_mode'));
        $this->assertSame('boys', $this->selectedOptionValue($createHtml, 'gender_policy'));
        $this->assertSame('24', $this->inputValue($createHtml, 'capacity'));
        $this->assertFalse($this->isChecked($createHtml, 'attendance_required'));
        $this->assertTrue($this->isChecked($createHtml, 'allow_house_linkage'));

        $reportResponse = $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.reports.index'));

        $reportResponse->assertOk();
        $reportCategories = $this->selectOptions($reportResponse->getContent(), 'report-category');

        $this->assertContains('Club Program', array_values($reportCategories));
        $this->assertSame('Leadership', $reportCategories['leadership'] ?? null);

        $activity = $this->createActivity($term, $user, [
            'category' => 'leadership',
            'allow_house_linkage' => true,
        ]);

        $eventsResponse = $this->actingAs($user)
            ->get(route('activities.events.index', $activity));

        $eventsResponse->assertOk();
        $eventsHtml = $eventsResponse->getContent();
        $eventTypeOptions = $this->selectOptions($eventsHtml, 'event-type');

        $this->assertSame('Festival', $eventTypeOptions['festival'] ?? null);
        $this->assertSame('festival', $this->selectedOptionValue($eventsHtml, 'event-type'));
        $this->assertTrue($this->isChecked($eventsHtml, 'event-house-linked'));
        $this->assertTrue($this->isChecked($eventsHtml, 'event-publish-calendar'));
    }

    public function test_inactive_options_are_hidden_from_new_forms_but_preserved_for_existing_records_events_and_reports(): void
    {
        $user = $this->createUserWithRoles('activities-settings-inactive@example.com', ['Activities Admin']);
        $term = $this->createTerm(2026, 3);
        $activity = $this->createActivity($term, $user, [
            'category' => Activity::CATEGORY_CLUB,
            'allow_house_linkage' => true,
        ]);
        $event = $this->createEvent($activity, $user, [
            'event_type' => ActivityEvent::TYPE_FIXTURE,
        ]);

        $activityFieldsPayload = $this->activityFieldsPayload();

        foreach ($activityFieldsPayload['categories'] as &$categoryRow) {
            if (($categoryRow['key'] ?? null) === Activity::CATEGORY_CLUB) {
                $categoryRow['label'] = 'Legacy Club';
                $categoryRow['active'] = 0;
            }
        }
        unset($categoryRow);

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_ACTIVITY_FIELDS]), $activityFieldsPayload)
            ->assertRedirect();

        $eventFieldsPayload = $this->eventFieldsPayload();

        foreach ($eventFieldsPayload['event_types'] as &$eventTypeRow) {
            if (($eventTypeRow['key'] ?? null) === ActivityEvent::TYPE_FIXTURE) {
                $eventTypeRow['label'] = 'Legacy Fixture';
                $eventTypeRow['active'] = 0;
            }
        }
        unset($eventTypeRow);

        $this->actingAs($user)
            ->post(route('activities.settings.update', ['tab' => ActivitySettingsService::TAB_EVENT_FIELDS]), $eventFieldsPayload)
            ->assertRedirect();

        $createResponse = $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.create'));

        $createResponse->assertOk();
        $createCategories = $this->selectOptions($createResponse->getContent(), 'category');

        $this->assertArrayNotHasKey(Activity::CATEGORY_CLUB, $createCategories);
        $this->assertNotContains('Legacy Club', array_values($createCategories));

        $editResponse = $this->actingAs($user)
            ->get(route('activities.edit', $activity));

        $editResponse->assertOk();
        $editHtml = $editResponse->getContent();
        $editCategories = $this->selectOptions($editHtml, 'category');

        $this->assertSame('Legacy Club', $editCategories[Activity::CATEGORY_CLUB] ?? null);
        $this->assertSame(Activity::CATEGORY_CLUB, $this->selectedOptionValue($editHtml, 'category'));

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('Legacy Club');

        $eventsResponse = $this->actingAs($user)
            ->get(route('activities.events.index', $activity));

        $eventsResponse->assertOk()
            ->assertSee('Legacy Fixture');

        $eventsHtml = $eventsResponse->getContent();
        $createEventTypes = $this->selectOptions($eventsHtml, 'event-type');
        $editEventTypes = $this->selectOptions($eventsHtml, 'event-type-' . $event->id);

        $this->assertArrayNotHasKey(ActivityEvent::TYPE_FIXTURE, $createEventTypes);
        $this->assertSame('Legacy Fixture', $editEventTypes[ActivityEvent::TYPE_FIXTURE] ?? null);
        $this->assertSame(ActivityEvent::TYPE_FIXTURE, $this->selectedOptionValue($eventsHtml, 'event-type-' . $event->id));

        $reportResponse = $this->actingAs($user)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('activities.reports.index'));

        $reportResponse->assertOk()
            ->assertSee('Legacy Club');

        $reportCategories = $this->selectOptions($reportResponse->getContent(), 'report-category');

        $this->assertArrayNotHasKey(Activity::CATEGORY_CLUB, $reportCategories);
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $resolvedEmail = $email;

        if (User::withTrashed()->where('email', $resolvedEmail)->exists()) {
            $resolvedEmail = uniqid('activities-settings-user-', true) . '@example.com';
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Settings Tester',
            'email' => $resolvedEmail,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
    }

    private function createTerm(int $year, int $termNumber): Term
    {
        $attributes = [
            'term' => $termNumber,
            'year' => $year,
            'start_date' => sprintf('%d-0%d-01', $year, max(1, min(9, $termNumber))),
            'end_date' => sprintf('%d-0%d-28', $year, max(1, min(9, $termNumber))),
            'closed' => false,
        ];

        if (Schema::hasColumn('terms', 'term_type')) {
            $attributes['term_type'] = 'Academic';
        }

        return Term::query()->firstOrCreate(
            [
                'term' => $termNumber,
                'year' => $year,
            ],
            $attributes
        );
    }

    private function createActivity(Term $term, User $user, array $overrides = []): Activity
    {
        $code = 'ACT-' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 6));

        return Activity::query()->create(array_merge([
            'name' => 'Debate Club',
            'code' => $code,
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'description' => 'Weekly debate preparation.',
            'default_location' => 'Hall 1',
            'capacity' => 40,
            'gender_policy' => 'mixed',
            'attendance_required' => true,
            'allow_house_linkage' => false,
            'status' => Activity::STATUS_ACTIVE,
            'term_id' => $term->id,
            'year' => $term->year,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $overrides));
    }

    private function createEvent(Activity $activity, User $user, array $overrides = []): ActivityEvent
    {
        return ActivityEvent::query()->create(array_merge([
            'activity_id' => $activity->id,
            'title' => 'Regional Fixture',
            'event_type' => ActivityEvent::TYPE_FIXTURE,
            'description' => 'Quarter-final fixture.',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHours(2),
            'location' => 'Main Field',
            'opponent_or_partner_name' => 'North School',
            'house_linked' => false,
            'publish_to_calendar' => false,
            'calendar_sync_status' => ActivityEvent::CALENDAR_NOT_PUBLISHED,
            'status' => ActivityEvent::STATUS_SCHEDULED,
            'created_by' => $user->id,
        ], $overrides));
    }

    private function activityFieldsPayload(): array
    {
        $payload = ['tab' => ActivitySettingsService::TAB_ACTIVITY_FIELDS];

        foreach (app(ActivitySettingsService::class)->activityFieldGroups() as $groupKey => $group) {
            $payload[$groupKey] = array_values($group['rows']);
        }

        return $payload;
    }

    private function eventFieldsPayload(): array
    {
        $payload = ['tab' => ActivitySettingsService::TAB_EVENT_FIELDS];

        foreach (app(ActivitySettingsService::class)->eventFieldGroups() as $groupKey => $group) {
            $payload[$groupKey] = array_values($group['rows']);
        }

        return $payload;
    }

    private function defaultsPayload(array $overrides = []): array
    {
        $settingsService = app(ActivitySettingsService::class);
        $activityDefaults = $settingsService->activityDefaults();
        $eventDefaults = $settingsService->eventDefaults();

        return array_merge([
            'tab' => ActivitySettingsService::TAB_DEFAULTS,
            'default_category' => $activityDefaults['category'],
            'default_delivery_mode' => $activityDefaults['delivery_mode'],
            'default_participation_mode' => $activityDefaults['participation_mode'],
            'default_result_mode' => $activityDefaults['result_mode'],
            'default_gender_policy' => $activityDefaults['gender_policy'],
            'default_capacity' => $activityDefaults['capacity'],
            'default_attendance_required' => $activityDefaults['attendance_required'] ? 1 : 0,
            'default_allow_house_linkage' => $activityDefaults['allow_house_linkage'] ? 1 : 0,
            'default_event_type' => $eventDefaults['event_type'],
            'default_publish_to_calendar' => $eventDefaults['publish_to_calendar'] ? 1 : 0,
            'default_house_linked' => $eventDefaults['house_linked'] ? 1 : 0,
        ], $overrides);
    }

    private function selectOptions(string $html, string $id): array
    {
        $xpath = $this->xpath($html);
        $options = [];

        foreach ($xpath->query("//select[@id='{$id}']/option") as $option) {
            $options[$option->getAttribute('value')] = trim($option->textContent);
        }

        return $options;
    }

    private function selectedOptionValue(string $html, string $id): ?string
    {
        $xpath = $this->xpath($html);
        $selected = $xpath->query("//select[@id='{$id}']/option[@selected]")->item(0);

        return $selected?->getAttribute('value');
    }

    private function inputValue(string $html, string $id): ?string
    {
        $xpath = $this->xpath($html);
        $input = $xpath->query("//*[@id='{$id}']")->item(0);

        return $input?->getAttribute('value');
    }

    private function isChecked(string $html, string $id): bool
    {
        $xpath = $this->xpath($html);
        $input = $xpath->query("//*[@id='{$id}']")->item(0);

        return $input?->attributes?->getNamedItem('checked') !== null;
    }

    private function xpath(string $html): \DOMXPath
    {
        $previous = libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($document);
    }
}
