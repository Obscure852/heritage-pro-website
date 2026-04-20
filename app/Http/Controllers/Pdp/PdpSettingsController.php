<?php

namespace App\Http\Controllers\Pdp;

use App\Services\Pdp\PdpSettingsPageService;
use App\Services\Pdp\PdpSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdpSettingsController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpSettingsPageService $pageService,
        private readonly PdpSettingsService $settingsService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->authorizeTemplateManage($request->user());

        return view('pdp.settings.index', $this->pageService->build($request->query('tab')));
    }

    public function update(Request $request, string $scope): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        if ($scope === PdpSettingsPageService::TAB_GENERAL) {
            $validated = $request->validate([
                'active_template_support_label' => ['nullable', 'string', 'max:120'],
                'active_template_support_contact' => ['nullable', 'string', 'max:255'],
                'active_template_support_note' => ['nullable', 'string', 'max:500'],
                'part_a_ministry_department' => ['nullable', 'string', 'max:120'],
                'general_guidance' => ['nullable', 'string', 'max:5000'],
                'default_plan_start_month' => ['required', 'integer', 'between:1,12'],
                'default_plan_start_day' => ['required', 'integer', 'between:1,31'],
                'default_plan_end_month' => ['required', 'integer', 'between:1,12'],
                'default_plan_end_day' => ['required', 'integer', 'between:1,31'],
            ]);

            $this->settingsService->saveGeneralSettings($validated, $request->user()->id);

            return redirect()
                ->route('staff.pdp.settings.index', ['tab' => PdpSettingsPageService::TAB_GENERAL])
                ->with('message', 'PDP general settings updated successfully.');
        }

        if ($scope === PdpSettingsPageService::TAB_COMMENTS) {
            $validated = $request->validate([
                'supervisee_comments' => ['nullable', 'string', 'max:12000'],
                'supervisor_comments' => ['nullable', 'string', 'max:12000'],
            ]);

            $this->settingsService->saveCommentBank($validated, $request->user()->id);

            return redirect()
                ->route('staff.pdp.settings.index', ['tab' => PdpSettingsPageService::TAB_COMMENTS])
                ->with('message', 'PDP canned comments updated successfully.');
        }

        if ($scope === PdpSettingsPageService::TAB_APPROVALS) {
            $validated = $request->validate([
                'elevated_positions' => ['nullable', 'string'],
                'elevated_roles' => ['nullable', 'string'],
            ]);

            $this->settingsService->saveAccessSettings($validated, $request->user()->id);

            return redirect()
                ->route('staff.pdp.settings.index', ['tab' => PdpSettingsPageService::TAB_APPROVALS])
                ->with('message', 'PDP approvals and access settings updated successfully.');
        }

        abort(404);
    }
}
