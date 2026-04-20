<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpTemplate;
use App\Models\Pdp\PdpTemplateSection;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class PdpTemplateController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpTemplateService $templateService,
        private readonly \App\Services\Pdp\PdpRolloutService $rolloutService,
        private readonly \App\Services\Pdp\PdpSettingsService $settingsService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function index(Request $request): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        return redirect()->route('staff.pdp.settings.index', ['tab' => 'templates']);
    }

    public function create(Request $request): View
    {
        $this->authorizeTemplateManage($request->user());

        return view('pdp.templates.create', [
            'blueprints' => $this->templateService->blueprintCatalog(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $baselineKeys = array_merge(array_keys($this->templateService->blueprintCatalog()), ['blank_bounded']);

        $validated = $request->validate([
            'baseline_key' => ['required', 'string', 'in:' . implode(',', $baselineKeys)],
            'template_family_key' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:150', 'unique:pdp_templates,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_reference' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $template = $this->templateService->createDraftFromBlueprint(
                $validated['baseline_key'],
                [
                    'template_family_key' => $validated['template_family_key'],
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'source_reference' => $validated['source_reference'] ?? null,
                ],
                $request->user()->id
            );
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['template' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'PDP template draft created successfully.');
    }

    public function show(Request $request, PdpTemplate $template): View
    {
        $this->authorizeTemplateManage($request->user());

        if ($template->status === PdpTemplate::STATUS_DRAFT) {
            $template = $this->templateService->upgradeDraftTemplateToFullBuilder($template);
        }

        $template = $this->loadTemplateDefinition($template)->loadCount(['plans', 'rollouts']);
        $relatedTemplates = PdpTemplate::query()
            ->where('template_family_key', $template->template_family_key)
            ->orderByDesc('version')
            ->get();

        return view('pdp.templates.show', [
            'template' => $template,
            'relatedTemplates' => $relatedTemplates,
            'showDeleteWarning' => $request->boolean('confirm_delete'),
            'deletionImpact' => $this->templateService->templateDeletionImpact($template),
            'suggestedDates' => $this->settingsService->suggestedPlanDatesForYear((int) now()->year),
        ]);
    }

    public function clone(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $clone = $this->templateService->cloneAsNextDraft($template, $request->user()->id);

        return redirect()
            ->route('staff.pdp.templates.show', $clone)
            ->with('message', 'PDP template version cloned into a new draft.');
    }

    public function publish(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        try {
            $template = $this->templateService->publish($template);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()->withErrors(['template' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'PDP template published successfully.');
    }

    public function activate(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'cycle_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'plan_period_start' => ['nullable', 'date'],
            'plan_period_end' => ['nullable', 'date'],
            'auto_provision_new_staff' => ['nullable', 'boolean'],
            'fallback_supervisor_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        try {
            $template = $this->templateService->activate($template);
            $cycleYear = (int) ($validated['cycle_year'] ?? now()->year);
            $suggestedDates = $this->settingsService->suggestedPlanDatesForYear($cycleYear);

            $this->rolloutService->launch([
                'pdp_template_id' => $template->id,
                'label' => trim((string) ($validated['label'] ?? '')) ?: $template->name . ' Cycle ' . $cycleYear,
                'cycle_year' => $cycleYear,
                'plan_period_start' => $validated['plan_period_start'] ?? $suggestedDates['start']->format('Y-m-d'),
                'plan_period_end' => $validated['plan_period_end'] ?? $suggestedDates['end']->format('Y-m-d'),
                'auto_provision_new_staff' => array_key_exists('auto_provision_new_staff', $validated)
                    ? (bool) $validated['auto_provision_new_staff']
                    : true,
                'fallback_supervisor_user_id' => $validated['fallback_supervisor_user_id'] ?? $request->user()->id,
            ], $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'PDP template activated and applied to the current cycle successfully.');
    }

    public function archive(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        try {
            $template = $this->templateService->archive($template);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'PDP template archived successfully.');
    }

    public function update(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'template_family_key' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:150', 'unique:pdp_templates,code,' . $template->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_reference' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $template = $this->templateService->updateDraftTemplateMetadata($template, $validated);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Template metadata updated successfully.');
    }

    public function updateSections(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.label' => ['required', 'string', 'max:255'],
            'sections.*.sequence' => ['required', 'integer', 'min:1', 'max:50'],
            'sections.*.min_items' => ['nullable', 'integer', 'min:0'],
            'sections.*.max_items' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $template = $this->templateService->updateDraftSections($template, $validated['sections']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template_sections' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Template sections updated successfully.');
    }

    public function updateEmployeeInformation(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'fields' => ['required', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.mapping_source' => ['nullable', 'string', 'in:user,settings,profile_metadata,plan,computed'],
            'fields.*.mapping_key' => ['nullable', 'string', 'max:255'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.sort_order' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $section = $this->resolveEmployeeInformationSection($template);
            $this->templateService->updateDraftSectionFieldMappings($section, $validated['fields']);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()->withErrors(['template_fields' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Employee information mapping updated successfully.');
    }

    public function updateSectionBuilder(Request $request, PdpTemplate $template, PdpTemplateSection $section): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());
        abort_unless($section->pdp_template_id === $template->id, 404);

        if ($section->key !== 'performance_objectives') {
            return back()->withErrors(['template_builder' => 'This template section does not expose additional builder settings.']);
        }

        $validated = $request->validate([
            'category_options' => ['nullable', 'string'],
        ]);

        $categories = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['category_options'] ?? '')) ?: [])
            ->map(fn ($category) => trim((string) $category))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($categories === []) {
            return back()->withErrors(['template_builder' => 'Add at least one Part B category before saving.']);
        }

        try {
            $this->templateService->updateDraftPerformanceObjectiveCategories($section, $categories);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()->withErrors(['template_builder' => $exception->getMessage()]);
        }

        return redirect()
            ->to(route('staff.pdp.templates.show', $template) . '#section-' . $section->key)
            ->with('message', 'Part B categories updated successfully.');
    }

    public function updatePeriods(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'periods' => ['required', 'array'],
            'periods.*.key' => ['required', 'string', 'max:100'],
            'periods.*.label' => ['required', 'string', 'max:255'],
            'periods.*.sequence' => ['required', 'integer', 'min:1', 'max:50'],
            'periods.*.window_type' => ['nullable', 'string', 'max:50'],
            'periods.*.summary_label' => ['nullable', 'string', 'max:255'],
            'periods.*.include_in_final_score' => ['nullable', 'boolean'],
            'periods.*.due_rule_json' => ['nullable', 'string'],
            'periods.*.open_rule_json' => ['nullable', 'string'],
            'periods.*.close_rule_json' => ['nullable', 'string'],
        ]);

        try {
            $periods = collect($validated['periods'])->mapWithKeys(function (array $period, string $id): array {
                return [
                    $id => array_merge($period, [
                        'include_in_final_score' => (bool) ($period['include_in_final_score'] ?? false),
                        'due_rule_json' => $this->decodeJsonInput($period['due_rule_json'] ?? null),
                        'open_rule_json' => $this->decodeJsonInput($period['open_rule_json'] ?? null),
                        'close_rule_json' => $this->decodeJsonInput($period['close_rule_json'] ?? null),
                    ]),
                ];
            })->all();

            $template = $this->templateService->updateDraftPeriods($template, $periods);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template_periods' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Template review periods updated successfully.');
    }

    public function updateRatings(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'schemes' => ['required', 'array'],
            'schemes.*.label' => ['required', 'string', 'max:255'],
            'schemes.*.input_type' => ['required', 'string', 'max:50'],
            'schemes.*.weight' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'schemes.*.rounding_rule' => ['nullable', 'string', 'max:50'],
            'schemes.*.scale_config_json' => ['nullable', 'string'],
            'schemes.*.conversion_config_json' => ['nullable', 'string'],
            'schemes.*.formula_config_json' => ['nullable', 'string'],
            'schemes.*.band_config_json' => ['nullable', 'string'],
        ]);

        try {
            $schemes = collect($validated['schemes'])->mapWithKeys(function (array $scheme, string $id): array {
                return [
                    $id => array_merge($scheme, [
                        'scale_config_json' => $this->decodeJsonInput($scheme['scale_config_json'] ?? null),
                        'conversion_config_json' => $this->decodeJsonInput($scheme['conversion_config_json'] ?? null),
                        'formula_config_json' => $this->decodeJsonInput($scheme['formula_config_json'] ?? null),
                        'band_config_json' => $this->decodeJsonInput($scheme['band_config_json'] ?? null),
                    ]),
                ];
            })->all();

            $template = $this->templateService->updateDraftRatingSchemes($template, $schemes);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template_ratings' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Template scoring configuration updated successfully.');
    }

    public function updateApprovals(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        $validated = $request->validate([
            'steps' => ['required', 'array'],
            'steps.*.key' => ['required', 'string', 'max:100'],
            'steps.*.label' => ['required', 'string', 'max:255'],
            'steps.*.sequence' => ['required', 'integer', 'min:1', 'max:50'],
            'steps.*.role_type' => ['required', 'string', 'max:100'],
            'steps.*.required' => ['nullable', 'boolean'],
            'steps.*.comment_required' => ['nullable', 'boolean'],
            'steps.*.period_scope' => ['nullable', 'string', 'max:100'],
        ]);

        $steps = collect($validated['steps'])->mapWithKeys(function (array $step, string $id): array {
            return [
                $id => array_merge($step, [
                    'required' => (bool) ($step['required'] ?? false),
                    'comment_required' => (bool) ($step['comment_required'] ?? false),
                    'period_scope' => $step['period_scope'] ?: null,
                ]),
            ];
        })->all();

        try {
            $template = $this->templateService->updateDraftApprovalSteps($template, $steps);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['template_approvals' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.templates.show', $template)
            ->with('message', 'Template approval steps updated successfully.');
    }

    public function destroy(Request $request, PdpTemplate $template): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());

        try {
            $this->templateService->deleteTemplate($template, $request->boolean('confirm_delete'));
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('staff.pdp.templates.show', ['template' => $template, 'confirm_delete' => 1])
                ->withErrors(['template_delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.settings.index', ['tab' => 'templates'])
            ->with('message', 'PDP template deleted successfully.');
    }

    private function resolveEmployeeInformationSection(PdpTemplate $template): PdpTemplateSection
    {
        $template = $this->loadTemplateDefinition($template);
        $section = $template->sections->firstWhere('key', 'employee_information');

        if (!$section) {
            throw new InvalidArgumentException('The employee information section could not be found for this template.');
        }

        return $section;
    }

    private function decodeJsonInput(?string $input): ?array
    {
        $value = trim((string) $input);

        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('JSON configuration values must decode to an object or array.');
        }

        return $decoded;
    }
}
