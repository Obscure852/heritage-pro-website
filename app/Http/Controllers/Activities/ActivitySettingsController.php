<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\UpdateActivitySettingsRequest;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivitySettingsController extends Controller
{
    public function __construct(private readonly ActivitySettingsService $settingsService)
    {
    }

    public function index(Request $request): View
    {
        $activeTab = $this->settingsService->normalizeTab($request->query('tab'));

        return view('activities.settings', [
            'activeTab' => $activeTab,
            'tabs' => $this->settingsService->tabs(),
            'activityFieldGroups' => $this->settingsService->activityFieldGroups(),
            'eventFieldGroups' => $this->settingsService->eventFieldGroups(),
            'activityDefaults' => $this->settingsService->activityDefaults(),
            'eventDefaults' => $this->settingsService->eventDefaults(),
            'activeCategoryOptions' => $this->settingsService->activeCategoryOptions(),
            'activeDeliveryModeOptions' => $this->settingsService->activeDeliveryModeOptions(),
            'activeParticipationModeOptions' => $this->settingsService->activeParticipationModeOptions(),
            'activeResultModeOptions' => $this->settingsService->activeResultModeOptions(),
            'activeGenderPolicyOptions' => $this->settingsService->activeGenderPolicyOptions(),
            'activeEventTypeOptions' => $this->settingsService->activeEventTypeOptions(),
        ]);
    }

    public function update(UpdateActivitySettingsRequest $request): RedirectResponse
    {
        $tab = $this->settingsService->normalizeTab($request->validated('tab'));
        $validated = $request->validated();
        $userId = $request->user()?->id;
        $group = $request->input('group');

        $redirectParameters = array_filter([
            'tab' => $tab,
            'group' => filled($group) ? $group : null,
        ]);

        if ($tab === ActivitySettingsService::TAB_ACTIVITY_FIELDS) {
            $this->settingsService->saveActivityFieldOptions($validated, $userId);

            return redirect()
                ->route('activities.settings.index', $redirectParameters)
                ->with('message', 'Activity field settings updated successfully.');
        }

        if ($tab === ActivitySettingsService::TAB_EVENT_FIELDS) {
            $this->settingsService->saveEventFieldOptions($validated, $userId);

            return redirect()
                ->route('activities.settings.index', $redirectParameters)
                ->with('message', 'Event field settings updated successfully.');
        }

        if ($tab === ActivitySettingsService::TAB_ALL) {
            $this->settingsService->saveActivityFieldOptions($validated, $userId);
            $this->settingsService->saveEventFieldOptions($validated, $userId);
            $this->settingsService->saveActivityDefaults($validated, $userId);
            $this->settingsService->saveEventDefaults($validated, $userId);

            return redirect()
                ->route('activities.settings.index', $redirectParameters)
                ->with('message', 'Activities settings updated successfully.');
        }

        $this->settingsService->saveActivityDefaults($validated, $userId);
        $this->settingsService->saveEventDefaults($validated, $userId);

        return redirect()
            ->route('activities.settings.index', $redirectParameters)
            ->with('message', 'Activities defaults updated successfully.');
    }
}
