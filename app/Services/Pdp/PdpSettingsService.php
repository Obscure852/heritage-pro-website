<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PdpSettingsService
{
    public const DEFAULT_GENERAL_SETTINGS = [
        'active_template_support_label' => 'Template-driven PDP workflow',
        'active_template_support_contact' => null,
        'active_template_support_note' => 'Review periods, scoring, and approvals are controlled by the active template version.',
        'part_a_ministry_department' => 'Secondary',
        'general_guidance' => "1. Use the PDP to assess performance against the agreed objectives for the reporting period.\n2. The supervisor should understand the employee's role, expected outputs, and evidence before scoring.\n3. Review the full reporting period and note both strengths and the factors that affected delivery.\n4. The immediate supervisor should normally complete the assessment and discuss it with the appraisee.\n5. Use the review discussion to agree practical support, improvement, and development actions for the next cycle.",
        'default_plan_start_month' => 1,
        'default_plan_start_day' => 1,
        'default_plan_end_month' => 12,
        'default_plan_end_day' => 31,
    ];

    public const DEFAULT_ACCESS_SETTINGS = [
        'elevated_positions' => ['School Head'],
        'elevated_roles' => [
            'Administrator',
            'HR Admin',
            'HR Edit',
            'HR View',
            'HOD',
            'Academic Admin',
        ],
    ];

    public const DEFAULT_COMMENT_BANK = [
        'supervisee_comments' => [
            'I met the agreed targets for this period and can provide evidence of the progress made.',
            'I improved my lesson preparation and delivered more structured learning activities.',
            'I maintained consistent classroom routines and learner engagement throughout the period.',
            'I completed the required records, planning documents, and assessments on time.',
            'I made steady progress toward my objectives and can identify areas for further growth.',
            'I responded positively to feedback and adjusted my practice where needed.',
            'I collaborated effectively with colleagues to support planning and moderation.',
            'I used learner performance data to guide intervention and improve outcomes.',
            'I strengthened my classroom management and reduced time lost during lessons.',
            'I need additional support in balancing administrative tasks with instructional demands.',
            'I attended meetings, workshops, and professional development sessions consistently.',
            'I demonstrated commitment to punctuality, preparation, and professional conduct.',
            'I achieved partial progress on this target and need more time to fully embed the practice.',
            'I can now apply the agreed strategy more confidently and consistently in class.',
            'I need to improve the consistency of evidence collection for this objective.',
            'I worked closely with my supervisor and acted on the guidance provided.',
            'I improved learner support by giving clearer feedback and follow-up tasks.',
            'I experienced challenges in resource availability, which affected full completion of the target.',
            'I remain committed to strengthening my performance in the next review cycle.',
            'I believe this objective has contributed positively to learner achievement and my professional growth.',
        ],
        'supervisor_comments' => [
            'The teacher has shown clear commitment to the agreed performance objectives.',
            'Performance during this period reflects steady progress against the planned targets.',
            'The teacher demonstrates sound professional conduct and reliability in assigned duties.',
            'Lesson preparation and record keeping have improved and are meeting expectations.',
            'Classroom management is generally effective and supports a stable learning environment.',
            'The teacher responds well to guidance and implements feedback in practice.',
            'There is visible improvement in learner support and instructional delivery.',
            'The evidence provided supports the progress reported for this objective.',
            'More consistency is needed in meeting timelines and completing follow-up actions.',
            'The teacher should continue strengthening assessment analysis and use of learner data.',
            'Performance on this objective is satisfactory and shows potential for stronger impact.',
            'Additional coaching is recommended to strengthen planning and execution in this area.',
            'The teacher works collaboratively and contributes positively to team responsibilities.',
            'Greater consistency is needed in documentation and evidence submission.',
            'The teacher has met most expectations for this review period.',
            'This objective has been partially achieved and requires continued attention.',
            'The teacher demonstrates a positive attitude toward professional growth.',
            'Improvement is needed in translating plans into measurable classroom outcomes.',
            'The teacher has made commendable progress and should maintain the current momentum.',
            'Overall performance in this area is acceptable, with clear opportunities for further growth.',
        ],
    ];

    public function get(string $key, $default = null)
    {
        $setting = PdpSetting::query()->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public function set(string $key, $value, ?int $userId = null, ?string $description = null): PdpSetting
    {
        return PdpSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'updated_by' => $userId,
            ]
        );
    }

    public function forget(string $key): void
    {
        PdpSetting::query()->where('key', $key)->delete();
    }

    public function getMany(array $defaultsByKey): array
    {
        $keys = array_keys($defaultsByKey);
        $storedValues = PdpSetting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->all();

        return collect($defaultsByKey)
            ->mapWithKeys(fn ($default, string $key): array => [$key => $storedValues[$key] ?? $default])
            ->all();
    }

    public function getGroup(string $prefix, array $defaults = []): array
    {
        $normalizedPrefix = trim($prefix, '.') . '.';
        $settings = PdpSetting::query()
            ->where('key', 'like', $normalizedPrefix . '%')
            ->get()
            ->mapWithKeys(fn (PdpSetting $setting): array => [substr($setting->key, strlen($normalizedPrefix)) => $setting->value])
            ->all();

        return array_replace($defaults, $settings);
    }

    public function saveGroup(string $prefix, array $values, ?int $userId = null, array $descriptions = []): array
    {
        $normalizedPrefix = trim($prefix, '.') . '.';
        $saved = [];

        foreach ($values as $key => $value) {
            $fullKey = $normalizedPrefix . $key;
            $saved[$key] = $this->set($fullKey, $value, $userId, $descriptions[$key] ?? null)->value;
        }

        return $saved;
    }

    public function generalSettings(): array
    {
        return $this->normalizeGeneralSettings($this->getGroup('general', self::DEFAULT_GENERAL_SETTINGS));
    }

    public function saveGeneralSettings(array $values, ?int $userId = null): array
    {
        $normalized = $this->normalizeGeneralSettings(array_replace(self::DEFAULT_GENERAL_SETTINGS, Arr::only($values, array_keys(self::DEFAULT_GENERAL_SETTINGS))));

        return $this->saveGroup('general', $normalized, $userId, [
            'active_template_support_label' => 'Short label shown in the PDP settings summary for the active template.',
            'active_template_support_contact' => 'Contact person or mailbox for PDP template support.',
            'active_template_support_note' => 'Support note shown on the PDP settings General tab.',
            'part_a_ministry_department' => 'Default Part A Ministry / Department label shown on PDP employee information sections.',
            'general_guidance' => 'Guidance text shown above Part A on PDP plans and PDFs.',
            'default_plan_start_month' => 'Suggested start month for new PDP plans.',
            'default_plan_start_day' => 'Suggested start day for new PDP plans.',
            'default_plan_end_month' => 'Suggested end month for new PDP plans.',
            'default_plan_end_day' => 'Suggested end day for new PDP plans.',
        ]);
    }

    public function accessSettings(): array
    {
        return $this->normalizeAccessSettings($this->getGroup('access', self::DEFAULT_ACCESS_SETTINGS));
    }

    public function saveAccessSettings(array $values, ?int $userId = null): array
    {
        $normalized = $this->normalizeAccessSettings(array_replace(self::DEFAULT_ACCESS_SETTINGS, Arr::only($values, array_keys(self::DEFAULT_ACCESS_SETTINGS))));

        return $this->saveGroup('access', $normalized, $userId, [
            'elevated_positions' => 'Positions treated as elevated PDP administrators.',
            'elevated_roles' => 'Roles treated as elevated PDP administrators.',
        ]);
    }

    public function commentBank(): array
    {
        return $this->normalizeCommentBank($this->getGroup('comment_bank', self::DEFAULT_COMMENT_BANK));
    }

    public function saveCommentBank(array $values, ?int $userId = null): array
    {
        $normalized = $this->normalizeCommentBank(array_replace(self::DEFAULT_COMMENT_BANK, Arr::only($values, array_keys(self::DEFAULT_COMMENT_BANK))));

        return $this->saveGroup('comment_bank', $normalized, $userId, [
            'supervisee_comments' => 'Saved comment suggestions shown on supervisee PDP comment fields.',
            'supervisor_comments' => 'Saved comment suggestions shown on supervisor PDP comment fields.',
        ]);
    }

    public function commentSuggestionsForField(?string $fieldKey): array
    {
        $bank = $this->commentBank();

        return match ($fieldKey) {
            'supervisee_comment' => Arr::wrap($bank['supervisee_comments'] ?? []),
            'supervisor_comment' => Arr::wrap($bank['supervisor_comments'] ?? []),
            default => [],
        };
    }

    public function suggestedPlanDatesForYear(?int $year = null): array
    {
        $year = $year ?: (int) now()->year;
        $settings = $this->generalSettings();

        return [
            'start' => $this->buildClampedDate(
                $year,
                (int) $settings['default_plan_start_month'],
                (int) $settings['default_plan_start_day']
            ),
            'end' => $this->buildClampedDate(
                $year,
                (int) $settings['default_plan_end_month'],
                (int) $settings['default_plan_end_day']
            ),
        ];
    }

    private function normalizeGeneralSettings(array $values): array
    {
        return [
            'active_template_support_label' => $this->normalizeNullableString($values['active_template_support_label'] ?? self::DEFAULT_GENERAL_SETTINGS['active_template_support_label']),
            'active_template_support_contact' => $this->normalizeNullableString($values['active_template_support_contact'] ?? self::DEFAULT_GENERAL_SETTINGS['active_template_support_contact']),
            'active_template_support_note' => $this->normalizeNullableString($values['active_template_support_note'] ?? self::DEFAULT_GENERAL_SETTINGS['active_template_support_note']),
            'part_a_ministry_department' => $this->normalizeNullableString($values['part_a_ministry_department'] ?? self::DEFAULT_GENERAL_SETTINGS['part_a_ministry_department']),
            'general_guidance' => $this->normalizeTextBlock($values['general_guidance'] ?? self::DEFAULT_GENERAL_SETTINGS['general_guidance']),
            'default_plan_start_month' => $this->normalizeMonth($values['default_plan_start_month'] ?? self::DEFAULT_GENERAL_SETTINGS['default_plan_start_month']),
            'default_plan_start_day' => $this->normalizeDay($values['default_plan_start_day'] ?? self::DEFAULT_GENERAL_SETTINGS['default_plan_start_day']),
            'default_plan_end_month' => $this->normalizeMonth($values['default_plan_end_month'] ?? self::DEFAULT_GENERAL_SETTINGS['default_plan_end_month']),
            'default_plan_end_day' => $this->normalizeDay($values['default_plan_end_day'] ?? self::DEFAULT_GENERAL_SETTINGS['default_plan_end_day']),
        ];
    }

    private function normalizeAccessSettings(array $values): array
    {
        return [
            'elevated_positions' => $this->normalizeStringList($values['elevated_positions'] ?? self::DEFAULT_ACCESS_SETTINGS['elevated_positions']),
            'elevated_roles' => $this->normalizeStringList($values['elevated_roles'] ?? self::DEFAULT_ACCESS_SETTINGS['elevated_roles']),
        ];
    }

    private function normalizeCommentBank(array $values): array
    {
        return [
            'supervisee_comments' => $this->normalizeCommentList($values['supervisee_comments'] ?? self::DEFAULT_COMMENT_BANK['supervisee_comments']),
            'supervisor_comments' => $this->normalizeCommentList($values['supervisor_comments'] ?? self::DEFAULT_COMMENT_BANK['supervisor_comments']),
        ];
    }

    private function normalizeStringList(mixed $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n|,/', (string) $value);

        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeCommentList(mixed $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n/', (string) $value);

        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->map(fn (string $item) => mb_substr($item, 0, 500))
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeMonth(mixed $value): int
    {
        $month = (int) $value;

        return max(1, min(12, $month));
    }

    private function normalizeDay(mixed $value): int
    {
        $day = (int) $value;

        return max(1, min(31, $day));
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeTextBlock(mixed $value): ?string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim((string) ($value ?? '')));

        return $normalized === '' ? null : $normalized;
    }

    private function buildClampedDate(int $year, int $month, int $day): Carbon
    {
        $date = Carbon::create($year, $month, 1)->startOfDay();

        return $date->copy()->day(min($day, $date->daysInMonth));
    }
}
