<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Services\SettingsService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivitySettingsService
{
    public const TAB_ACTIVITY_FIELDS = 'activity-fields';
    public const TAB_EVENT_FIELDS = 'event-fields';
    public const TAB_DEFAULTS = 'defaults';
    public const TAB_ALL = 'all';

    public const KEY_CATEGORIES = 'activities.options.categories';
    public const KEY_DELIVERY_MODES = 'activities.options.delivery_modes';
    public const KEY_PARTICIPATION_MODES = 'activities.options.participation_modes';
    public const KEY_RESULT_MODES = 'activities.options.result_modes';
    public const KEY_GENDER_POLICIES = 'activities.options.gender_policies';
    public const KEY_EVENT_TYPES = 'activities.options.event_types';
    public const KEY_ACTIVITY_DEFAULTS = 'activities.defaults.activity';
    public const KEY_EVENT_DEFAULTS = 'activities.defaults.events';

    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function tabs(): array
    {
        return [
            self::TAB_ACTIVITY_FIELDS => ['label' => 'Activity Fields', 'icon' => 'bx bx-category-alt'],
            self::TAB_EVENT_FIELDS => ['label' => 'Event Fields', 'icon' => 'bx bx-calendar-event'],
            self::TAB_DEFAULTS => ['label' => 'Defaults', 'icon' => 'bx bx-slider-alt'],
        ];
    }

    public function normalizeTab(?string $requestedTab): string
    {
        $requestedTab = trim((string) $requestedTab);
        $allowed = array_merge(array_keys($this->tabs()), [self::TAB_ALL]);

        return in_array($requestedTab, $allowed, true)
            ? $requestedTab
            : self::TAB_ALL;
    }

    public function categoryRows(): array
    {
        return $this->optionRows('categories');
    }

    public function deliveryModeRows(): array
    {
        return $this->optionRows('delivery_modes');
    }

    public function participationModeRows(): array
    {
        return $this->optionRows('participation_modes');
    }

    public function resultModeRows(): array
    {
        return $this->optionRows('result_modes');
    }

    public function genderPolicyRows(): array
    {
        return $this->optionRows('gender_policies');
    }

    public function eventTypeRows(): array
    {
        return $this->optionRows('event_types');
    }

    public function categoryLabels(): array
    {
        return $this->optionLabels('categories');
    }

    public function deliveryModeLabels(): array
    {
        return $this->optionLabels('delivery_modes');
    }

    public function participationModeLabels(): array
    {
        return $this->optionLabels('participation_modes');
    }

    public function resultModeLabels(): array
    {
        return $this->optionLabels('result_modes');
    }

    public function genderPolicyLabels(): array
    {
        return $this->optionLabels('gender_policies');
    }

    public function eventTypeLabels(): array
    {
        return $this->optionLabels('event_types');
    }

    public function activeCategoryOptions(): array
    {
        return $this->optionMapForSelect('categories');
    }

    public function activeDeliveryModeOptions(): array
    {
        return $this->optionMapForSelect('delivery_modes');
    }

    public function activeParticipationModeOptions(): array
    {
        return $this->optionMapForSelect('participation_modes');
    }

    public function activeResultModeOptions(): array
    {
        return $this->optionMapForSelect('result_modes');
    }

    public function activeGenderPolicyOptions(): array
    {
        return $this->optionMapForSelect('gender_policies');
    }

    public function activeEventTypeOptions(): array
    {
        return $this->optionMapForSelect('event_types');
    }

    public function categoryOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('categories', $currentKey);
    }

    public function deliveryModeOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('delivery_modes', $currentKey);
    }

    public function participationModeOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('participation_modes', $currentKey);
    }

    public function resultModeOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('result_modes', $currentKey);
    }

    public function genderPolicyOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('gender_policies', $currentKey);
    }

    public function eventTypeOptionsForValue(?string $currentKey): array
    {
        return $this->optionMapForSelect('event_types', $currentKey);
    }

    public function activityDefaults(): array
    {
        $stored = $this->settingsService->get(self::KEY_ACTIVITY_DEFAULTS, $this->defaultActivityDefaults());
        $defaults = is_array($stored) ? $stored : $this->defaultActivityDefaults();

        return [
            'category' => $this->resolveDefaultOption(
                $defaults['category'] ?? null,
                $this->activeCategoryOptions(),
                Activity::CATEGORY_CLUB
            ),
            'delivery_mode' => $this->resolveDefaultOption(
                $defaults['delivery_mode'] ?? null,
                $this->activeDeliveryModeOptions(),
                Activity::DELIVERY_RECURRING
            ),
            'participation_mode' => $this->resolveDefaultOption(
                $defaults['participation_mode'] ?? null,
                $this->activeParticipationModeOptions(),
                Activity::PARTICIPATION_TEAM
            ),
            'result_mode' => $this->resolveDefaultOption(
                $defaults['result_mode'] ?? null,
                $this->activeResultModeOptions(),
                Activity::RESULT_MIXED
            ),
            'gender_policy' => $this->resolveDefaultOption(
                $defaults['gender_policy'] ?? null,
                $this->activeGenderPolicyOptions(),
                'mixed'
            ),
            'capacity' => filled($defaults['capacity'] ?? null) ? (int) $defaults['capacity'] : null,
            'attendance_required' => (bool) ($defaults['attendance_required'] ?? true),
            'allow_house_linkage' => (bool) ($defaults['allow_house_linkage'] ?? false),
        ];
    }

    public function eventDefaults(?Activity $activity = null): array
    {
        $stored = $this->settingsService->get(self::KEY_EVENT_DEFAULTS, $this->defaultEventDefaults());
        $defaults = is_array($stored) ? $stored : $this->defaultEventDefaults();
        $allowHouseLinkage = $activity?->allow_house_linkage ?? true;

        return [
            'event_type' => $this->resolveDefaultOption(
                $defaults['event_type'] ?? null,
                $this->activeEventTypeOptions(),
                ActivityEvent::TYPE_FIXTURE
            ),
            'publish_to_calendar' => (bool) ($defaults['publish_to_calendar'] ?? false),
            'house_linked' => $allowHouseLinkage && (bool) ($defaults['house_linked'] ?? false),
        ];
    }

    public function resultModeAllowsResults(?string $key): bool
    {
        $row = collect($this->resultModeRows())->firstWhere('key', (string) $key);

        if (!$row) {
            return true;
        }

        return (bool) ($row['allows_results'] ?? true);
    }

    public function saveActivityFieldOptions(array $payload, ?int $userId = null): void
    {
        foreach (['categories', 'delivery_modes', 'participation_modes', 'result_modes', 'gender_policies'] as $name) {
            $this->saveOptionGroup($name, $payload[$name] ?? [], $userId);
        }
    }

    public function saveEventFieldOptions(array $payload, ?int $userId = null): void
    {
        $this->saveOptionGroup('event_types', $payload['event_types'] ?? [], $userId);
    }

    public function saveActivityDefaults(array $payload, ?int $userId = null): void
    {
        $defaults = [
            'category' => $this->resolveDefaultOption(
                $payload['default_category'] ?? null,
                $this->activeCategoryOptions(),
                Activity::CATEGORY_CLUB
            ),
            'delivery_mode' => $this->resolveDefaultOption(
                $payload['default_delivery_mode'] ?? null,
                $this->activeDeliveryModeOptions(),
                Activity::DELIVERY_RECURRING
            ),
            'participation_mode' => $this->resolveDefaultOption(
                $payload['default_participation_mode'] ?? null,
                $this->activeParticipationModeOptions(),
                Activity::PARTICIPATION_TEAM
            ),
            'result_mode' => $this->resolveDefaultOption(
                $payload['default_result_mode'] ?? null,
                $this->activeResultModeOptions(),
                Activity::RESULT_MIXED
            ),
            'gender_policy' => $this->resolveDefaultOption(
                $payload['default_gender_policy'] ?? null,
                $this->activeGenderPolicyOptions(),
                'mixed'
            ),
            'capacity' => filled($payload['default_capacity'] ?? null) ? (int) $payload['default_capacity'] : null,
            'attendance_required' => (bool) ($payload['default_attendance_required'] ?? false),
            'allow_house_linkage' => (bool) ($payload['default_allow_house_linkage'] ?? false),
        ];

        $this->settingsService->set(self::KEY_ACTIVITY_DEFAULTS, $defaults, $userId);
    }

    public function saveEventDefaults(array $payload, ?int $userId = null): void
    {
        $defaults = [
            'event_type' => $this->resolveDefaultOption(
                $payload['default_event_type'] ?? null,
                $this->activeEventTypeOptions(),
                ActivityEvent::TYPE_FIXTURE
            ),
            'publish_to_calendar' => (bool) ($payload['default_publish_to_calendar'] ?? false),
            'house_linked' => (bool) ($payload['default_house_linked'] ?? false),
        ];

        $this->settingsService->set(self::KEY_EVENT_DEFAULTS, $defaults, $userId);
    }

    public function activityFieldGroups(): array
    {
        return [
            'categories' => ['label' => 'Categories', 'rows' => $this->categoryRows()],
            'delivery_modes' => ['label' => 'Delivery Modes', 'rows' => $this->deliveryModeRows()],
            'participation_modes' => ['label' => 'Participation Modes', 'rows' => $this->participationModeRows()],
            'result_modes' => ['label' => 'Result Modes', 'rows' => $this->resultModeRows()],
            'gender_policies' => ['label' => 'Gender Policies', 'rows' => $this->genderPolicyRows()],
        ];
    }

    public function eventFieldGroups(): array
    {
        return [
            'event_types' => ['label' => 'Event Types', 'rows' => $this->eventTypeRows()],
        ];
    }

    private function saveOptionGroup(string $name, array $submittedRows, ?int $userId = null): void
    {
        $definition = $this->optionDefinition($name);
        $currentRows = $this->optionRows($name);
        $currentByKey = [];

        foreach ($currentRows as $row) {
            $currentByKey[$row['key']] = $row;
        }

        $normalizedRows = [];
        $seenKeys = [];

        foreach ($submittedRows as $row) {
            $key = Str::snake(trim((string) ($row['key'] ?? '')));
            $label = trim((string) ($row['label'] ?? ''));

            if ($key === '' || $label === '') {
                continue;
            }

            if (isset($seenKeys[$key])) {
                throw ValidationException::withMessages([
                    $name => ["Duplicate option key '{$key}' is not allowed."],
                ]);
            }

            $baseRow = $currentByKey[$key]
                ?? $definition['defaults_by_key'][$key]
                ?? [
                    'key' => $key,
                    'label' => Str::headline(str_replace('_', ' ', $key)),
                    'active' => true,
                    'system' => false,
                ];

            $normalizedRows[] = $this->normalizeRow(
                $baseRow,
                [
                    'key' => $key,
                    'label' => $label,
                    'active' => $this->toBool($row['active'] ?? false),
                    'system' => $this->toBool($row['system'] ?? false),
                ]
            );

            $seenKeys[$key] = true;
        }

        foreach ($currentRows as $row) {
            if (!isset($seenKeys[$row['key']])) {
                $normalizedRows[] = $row;
            }
        }

        $normalizedRows = $this->normalizeRows($normalizedRows, $definition['defaults']);

        if (!collect($normalizedRows)->contains(fn (array $row) => $row['active'])) {
            throw ValidationException::withMessages([
                $name => ['Keep at least one active option in this list.'],
            ]);
        }

        $this->settingsService->set($definition['setting_key'], array_values($normalizedRows), $userId);
    }

    private function optionRows(string $name): array
    {
        $definition = $this->optionDefinition($name);
        $stored = $this->settingsService->get($definition['setting_key'], $definition['defaults']);
        $rows = is_array($stored) ? $stored : $definition['defaults'];

        return $this->normalizeRows($rows, $definition['defaults']);
    }

    private function optionLabels(string $name): array
    {
        $labels = [];

        foreach ($this->optionRows($name) as $row) {
            $labels[$row['key']] = $row['label'];
        }

        return $labels;
    }

    private function optionMapForSelect(string $name, ?string $currentKey = null): array
    {
        $labels = [];

        foreach ($this->optionRows($name) as $row) {
            if ($row['active'] || $row['key'] === $currentKey) {
                $labels[$row['key']] = $row['label'];
            }
        }

        return $labels;
    }

    private function normalizeRows(array $rows, array $seedRows): array
    {
        $seedByKey = [];
        $normalized = [];
        $seenKeys = [];

        foreach ($seedRows as $seedRow) {
            $seedByKey[$seedRow['key']] = $seedRow;
        }

        foreach ($rows as $row) {
            $key = Str::snake(trim((string) ($row['key'] ?? '')));

            if ($key === '' || isset($seenKeys[$key])) {
                continue;
            }

            $baseRow = $seedByKey[$key] ?? [
                'key' => $key,
                'label' => Str::headline(str_replace('_', ' ', $key)),
                'active' => true,
                'system' => false,
            ];

            $normalized[] = $this->normalizeRow($baseRow, $row);
            $seenKeys[$key] = true;
        }

        foreach ($seedRows as $seedRow) {
            if (!isset($seenKeys[$seedRow['key']])) {
                $normalized[] = $seedRow;
            }
        }

        return array_values($normalized);
    }

    private function normalizeRow(array $baseRow, array $row): array
    {
        $key = Str::snake(trim((string) ($row['key'] ?? $baseRow['key'] ?? '')));
        $label = trim((string) ($row['label'] ?? $baseRow['label'] ?? ''));
        $active = array_key_exists('active', $row)
            ? $this->toBool($row['active'])
            : (bool) ($baseRow['active'] ?? true);
        $system = (bool) ($baseRow['system'] ?? false) || $this->toBool($row['system'] ?? false);

        $normalized = $system
            ? array_merge($row, $baseRow)
            : array_merge($baseRow, $row);

        $normalized['key'] = $key;
        $normalized['label'] = $label !== '' ? $label : (string) ($baseRow['label'] ?? Str::headline($key));
        $normalized['active'] = $active;
        $normalized['system'] = $system;

        return $normalized;
    }

    private function resolveDefaultOption(?string $selectedKey, array $activeOptions, ?string $fallbackKey = null): ?string
    {
        $selectedKey = filled($selectedKey) ? (string) $selectedKey : null;

        if ($selectedKey !== null && array_key_exists($selectedKey, $activeOptions)) {
            return $selectedKey;
        }

        if ($fallbackKey !== null && array_key_exists($fallbackKey, $activeOptions)) {
            return $fallbackKey;
        }

        $keys = array_keys($activeOptions);

        return $keys[0] ?? null;
    }

    private function optionDefinition(string $name): array
    {
        $definitions = [
            'categories' => [
                'setting_key' => self::KEY_CATEGORIES,
                'defaults' => $this->seedRows(Activity::defaultCategories()),
            ],
            'delivery_modes' => [
                'setting_key' => self::KEY_DELIVERY_MODES,
                'defaults' => $this->seedRows(Activity::defaultDeliveryModes()),
            ],
            'participation_modes' => [
                'setting_key' => self::KEY_PARTICIPATION_MODES,
                'defaults' => $this->seedRows(Activity::defaultParticipationModes()),
            ],
            'result_modes' => [
                'setting_key' => self::KEY_RESULT_MODES,
                'defaults' => $this->seedRows(
                    Activity::defaultResultModes(),
                    [
                        Activity::RESULT_ATTENDANCE_ONLY => ['allows_results' => false],
                        Activity::RESULT_PLACEMENTS => ['allows_results' => true],
                        Activity::RESULT_POINTS => ['allows_results' => true],
                        Activity::RESULT_AWARDS => ['allows_results' => true],
                        Activity::RESULT_MIXED => ['allows_results' => true],
                    ]
                ),
            ],
            'gender_policies' => [
                'setting_key' => self::KEY_GENDER_POLICIES,
                'defaults' => $this->seedRows(Activity::defaultGenderPolicies()),
            ],
            'event_types' => [
                'setting_key' => self::KEY_EVENT_TYPES,
                'defaults' => $this->seedRows(ActivityEvent::defaultEventTypes()),
            ],
        ];

        $definition = $definitions[$name] ?? null;

        if (!$definition) {
            throw ValidationException::withMessages([
                'settings' => ["Unknown activities option group '{$name}'."],
            ]);
        }

        $definition['defaults_by_key'] = collect($definition['defaults'])->keyBy('key')->all();

        return $definition;
    }

    private function seedRows(array $labels, array $extraByKey = []): array
    {
        $rows = [];

        foreach ($labels as $key => $label) {
            $rows[] = array_merge([
                'key' => $key,
                'label' => $label,
                'active' => true,
                'system' => true,
            ], $extraByKey[$key] ?? []);
        }

        return $rows;
    }

    private function defaultActivityDefaults(): array
    {
        return [
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'gender_policy' => 'mixed',
            'capacity' => null,
            'attendance_required' => true,
            'allow_house_linkage' => false,
        ];
    }

    private function defaultEventDefaults(): array
    {
        return [
            'event_type' => ActivityEvent::TYPE_FIXTURE,
            'publish_to_calendar' => false,
            'house_linked' => false,
        ];
    }

    private function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
