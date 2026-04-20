<?php

namespace App\Http\Controllers\Invigilation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invigilation\UpdateInvigilationSettingsRequest;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\SMSApiSetting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;

class InvigilationSettingsController extends Controller
{
    private const SETTINGS = [
        'default_type' => [
            'key' => 'invigilation.defaults.type',
            'type' => 'string',
            'display_name' => 'Invigilation Default Series Type',
            'default' => InvigilationSeries::TYPE_MOCK,
        ],
        'default_required_invigilators' => [
            'key' => 'invigilation.defaults.required_invigilators',
            'type' => 'integer',
            'display_name' => 'Invigilation Default Required Invigilators',
            'default' => 1,
        ],
        'default_eligibility_policy' => [
            'key' => 'invigilation.defaults.eligibility_policy',
            'type' => 'string',
            'display_name' => 'Invigilation Default Eligibility Policy',
            'default' => InvigilationSeries::POLICY_ANY_TEACHER,
        ],
        'default_timetable_conflict_policy' => [
            'key' => 'invigilation.defaults.timetable_conflict_policy',
            'type' => 'string',
            'display_name' => 'Invigilation Default Timetable Conflict Policy',
            'default' => InvigilationSeries::TIMETABLE_IGNORE,
        ],
    ];

    public function __construct(protected SettingsService $settingsService)
    {
        $this->middleware(['auth', 'can:manage-invigilation']);
    }

    public function index()
    {
        return view('invigilation.settings', [
            'defaults' => self::defaults($this->settingsService),
            'seriesTypes' => InvigilationSeries::types(),
            'eligibilityPolicies' => InvigilationSeries::eligibilityPolicies(),
            'timetablePolicies' => InvigilationSeries::timetableConflictPolicies(),
        ]);
    }

    public function update(UpdateInvigilationSettingsRequest $request): RedirectResponse
    {
        foreach (self::SETTINGS as $field => $definition) {
            SMSApiSetting::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'value' => (string) $request->validated()[$field],
                    'category' => 'invigilation',
                    'type' => $definition['type'],
                    'display_name' => $definition['display_name'],
                    'is_editable' => true,
                    'display_order' => 0,
                ]
            );
        }

        $this->settingsService->refresh();

        return redirect()
            ->route('invigilation.settings.index')
            ->with('message', 'Invigilation settings updated successfully.');
    }

    public static function defaults(SettingsService $settingsService): array
    {
        return collect(self::SETTINGS)->mapWithKeys(
            fn (array $definition, string $field) => [$field => $settingsService->get($definition['key'], $definition['default'])]
        )->all();
    }
}
