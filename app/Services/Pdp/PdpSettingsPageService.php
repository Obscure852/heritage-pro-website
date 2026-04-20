<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PdpSettingsPageService
{
    public const TAB_TEMPLATES = 'templates';
    public const TAB_ROLLOUTS = 'rollouts';
    public const TAB_GENERAL = 'general';
    public const TAB_COMMENTS = 'comments-bank';
    public const TAB_PERIODS = 'review-periods';
    public const TAB_SCORING = 'scoring-ratings';
    public const TAB_APPROVALS = 'approvals-signatures';
    public const TAB_WORKFLOW = 'workflow';

    public function __construct(
        private readonly PdpSettingsService $settingsService,
        private readonly PdpTemplateService $templateService,
        private readonly PdpAccessService $accessService,
        private readonly PdpRolloutService $rolloutService
    ) {
    }

    public function build(?string $requestedTab = null): array
    {
        $activeTab = $this->normalizeTab($requestedTab);
        $activeTemplate = $this->templateService->getActiveTemplate();
        $templates = PdpTemplate::query()
            ->withCount(['plans', 'rollouts'])
            ->with('createdBy')
            ->orderByDesc('is_default')
            ->orderBy('template_family_key')
            ->orderByDesc('version')
            ->get();

        $generalSettings = $this->settingsService->generalSettings();
        $accessSettings = $this->settingsService->accessSettings();
        $commentBank = $this->settingsService->commentBank();
        $suggestedDates = $this->settingsService->suggestedPlanDatesForYear((int) now()->year);
        $activeRollout = $this->rolloutService->activeRollout();
        $rollouts = $this->rolloutService->allRollouts();

        return [
            'activeTab' => $activeTab,
            'tabs' => $this->tabs(),
            'helpText' => $this->helpText($activeTemplate),
            'templates' => $templates,
            'templateStats' => $this->templateStats($templates),
            'activeTemplate' => $activeTemplate,
            'activeRollout' => $activeRollout,
            'rollouts' => $rollouts,
            'generalSettings' => $generalSettings,
            'accessSettings' => $accessSettings,
            'commentBank' => $commentBank,
            'suggestedDates' => $suggestedDates,
            'fallbackSupervisors' => $this->fallbackSupervisors(),
            'availablePositions' => $this->availablePositions(),
            'availableRoles' => $this->availableRoles(),
            'periodRows' => $this->periodRows($activeTemplate),
            'ratingRows' => $this->ratingRows($activeTemplate),
            'approvalRows' => $this->approvalRows($activeTemplate),
            'workflowSteps' => $this->workflowSteps($activeTemplate, $generalSettings, $accessSettings, $suggestedDates),
        ];
    }

    public function tabs(): array
    {
        return [
            self::TAB_TEMPLATES => ['label' => 'Templates', 'icon' => 'bx bx-layer'],
            self::TAB_ROLLOUTS => ['label' => 'Rollouts', 'icon' => 'bx bx-rocket'],
            self::TAB_GENERAL => ['label' => 'General', 'icon' => 'bx bx-cog'],
            self::TAB_COMMENTS => ['label' => 'Canned Comments', 'icon' => 'bx bx-comment-detail'],
            self::TAB_PERIODS => ['label' => 'Review Periods', 'icon' => 'bx bx-calendar-event'],
            self::TAB_SCORING => ['label' => 'Scoring & Ratings', 'icon' => 'bx bx-line-chart'],
            self::TAB_APPROVALS => ['label' => 'Approvals & Signatures', 'icon' => 'bx bx-check-shield'],
            self::TAB_WORKFLOW => ['label' => 'PDP Workflow', 'icon' => 'bx bx-git-branch'],
        ];
    }

    public function normalizeTab(?string $requestedTab): string
    {
        $requested = trim((string) $requestedTab);

        return array_key_exists($requested, $this->tabs()) ? $requested : self::TAB_TEMPLATES;
    }

    private function templateStats(Collection $templates): array
    {
        return [
            'total' => $templates->count(),
            'published' => $templates->where('status', PdpTemplate::STATUS_PUBLISHED)->count(),
            'drafts' => $templates->where('status', PdpTemplate::STATUS_DRAFT)->count(),
        ];
    }

    private function helpText(?PdpTemplate $activeTemplate): array
    {
        $activeTemplateCopy = $activeTemplate
            ? "{$activeTemplate->name} (v{$activeTemplate->version}) is currently active."
            : 'No active PDP template is configured yet.';

        return [
            self::TAB_TEMPLATES => [
                'title' => 'Template Version Control',
                'content' => 'Manage seeded and custom PDP template versions here. Drafts can be fully authored inside the bounded builder, and used templates can only be deleted after reviewing their destructive cleanup impact.',
            ],
            self::TAB_ROLLOUTS => [
                'title' => 'School-Wide PDP Rollouts',
                'content' => 'Launch one rollout from the active template to provision staff plans in bulk, keep one active cycle for future staff, and audit any skipped exceptions.',
            ],
            self::TAB_GENERAL => [
                'title' => 'Module-Level PDP Defaults',
                'content' => $activeTemplateCopy . ' Use this tab for module defaults such as support copy, Part A guidance, ministry labels, and suggested plan dates.',
            ],
            self::TAB_COMMENTS => [
                'title' => 'Canned Comment Bank',
                'content' => 'Maintain ready-to-use supervisee and supervisor comments here. These suggestions appear directly on PDP comment fields to speed up consistent review writing.',
            ],
            self::TAB_PERIODS => [
                'title' => 'Active Review Cadence',
                'content' => 'These review windows come from the active template. Change period structure through a template draft, not by editing published plans.',
            ],
            self::TAB_SCORING => [
                'title' => 'Active Scoring Configuration',
                'content' => 'These weights, scales, and formulas are owned by the active template version and are shown here for audit and administrator review.',
            ],
            self::TAB_APPROVALS => [
                'title' => 'Approvals and Access Resolution',
                'content' => 'The approval chain comes from the active template. The editable settings below control which roles and positions are treated as elevated PDP administrators.',
            ],
            self::TAB_WORKFLOW => [
                'title' => 'End-to-End PDP Workflow',
                'content' => 'This tab explains how plan creation, mapped values, review windows, scoring, signatures, PDFs, and historical template binding work in the current PDP module.',
            ],
        ];
    }

    private function availablePositions(): array
    {
        if (!Schema::hasTable('user_positions')) {
            return [];
        }

        return DB::table('user_positions')
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    private function availableRoles(): array
    {
        if (!Schema::hasTable('roles')) {
            return [];
        }

        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    private function fallbackSupervisors(): Collection
    {
        return User::query()
            ->where('status', 'Current')
            ->where('active', true)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->filter(fn (User $user): bool => $this->accessService->hasElevatedAccess($user))
            ->values();
    }

    private function periodRows(?PdpTemplate $template): Collection
    {
        if (!$template) {
            return collect();
        }

        return $template->periods
            ->sortBy('sequence')
            ->values()
            ->map(function ($period): array {
                return [
                    'key' => $period->key,
                    'label' => $period->label,
                    'sequence' => $period->sequence,
                    'window_type' => $period->window_type,
                    'summary_label' => $period->summary_label,
                    'open_rule' => $this->formatRuleSummary($period->open_rule_json),
                    'close_rule' => $this->formatRuleSummary($period->close_rule_json),
                ];
            });
    }

    private function ratingRows(?PdpTemplate $template): Collection
    {
        if (!$template) {
            return collect();
        }

        return $template->ratingSchemes
            ->values()
            ->map(function ($scheme): array {
                return [
                    'key' => $scheme->key,
                    'label' => $scheme->label,
                    'input_type' => $scheme->input_type,
                    'weight' => $scheme->weight,
                    'rounding_rule' => $scheme->rounding_rule,
                    'scale' => $this->formatConfigSummary($scheme->scale_config_json),
                    'formula' => $this->formatConfigSummary($scheme->formula_config_json),
                    'bands' => $this->formatConfigSummary($scheme->band_config_json),
                ];
            });
    }

    private function approvalRows(?PdpTemplate $template): Collection
    {
        if (!$template) {
            return collect();
        }

        return $template->approvalSteps
            ->sortBy('sequence')
            ->values()
            ->map(function ($step): array {
                return [
                    'key' => $step->key,
                    'label' => $step->label,
                    'sequence' => $step->sequence,
                    'role_type' => $step->role_type,
                    'required' => $step->required,
                    'comment_required' => $step->comment_required,
                    'period_scope' => $step->period_scope ?: 'Plan-level',
                ];
            });
    }

    private function workflowSteps(?PdpTemplate $template, array $generalSettings, array $accessSettings, array $suggestedDates): array
    {
        $periodLabels = $template
            ? $template->periods->sortBy('sequence')->pluck('label')->implode(', ')
            : 'No active template configured';
        $ratingLabels = $template
            ? $template->ratingSchemes->pluck('label')->implode(', ')
            : 'No scoring configuration available';
        $approvalLabels = $template
            ? $template->approvalSteps->sortBy('sequence')->pluck('label')->implode(' -> ')
            : 'No approval chain configured';

        return [
            [
                'title' => '1. Template Activation and Rollout Binding',
                'body' => $template
                    ? "Template activation marks {$template->name} v{$template->version} as current and immediately launches the new staff cycle from that version. Every provisioned plan keeps that template version forever."
                    : 'A published PDP template must be activated before a school-wide cycle can be created.',
                'meta' => [
                    'Active template' => $template ? "{$template->code} ({$template->template_family_key})" : 'None',
                    'Active rollout' => $this->rolloutService->activeRollout()?->label ?? 'None',
                ],
            ],
            [
                'title' => '2. Bulk Provisioning and Staff Eligibility',
                'body' => 'Launching a rollout provisions plans for current active staff and can keep auto-provisioning future eligible staff. Reporting lines are snapshotted onto the plan when it is created.',
                'meta' => [
                    'Eligibility' => 'Current staff with active = 1',
                    'Future staff' => $this->rolloutService->activeRollout()?->auto_provision_new_staff ? 'Auto-provision enabled' : 'Auto-provision disabled',
                ],
            ],
            [
                'title' => '3. Employee Information Resolution',
                'body' => 'Profile summary fields are resolved from user columns, school settings, PDP settings, plan values, and legacy profile metadata based on each template field mapping.',
                'meta' => [
                    'Primary user fields' => 'Full name, position, personal payroll number, date of appointment, earning band',
                    'Fallback support' => 'Historical templates can still resolve legacy profile metadata mappings',
                ],
            ],
            [
                'title' => '4. Shared Objectives and Plan Snapshots',
                'body' => 'Template-managed performance objectives are copied into each plan at creation time. The template owns objective, output, measure, and target; plans only store actual result, score out of 10, supervisee comment, and supervisor comment.',
                'meta' => [
                    'Row ownership' => 'Template owns objective rows; plans own review data',
                    'Custom extras' => 'Performance objective rows are locked to the template for new template versions',
                ],
            ],
            [
                'title' => '5. Plan Date Defaults',
                'body' => 'The PDP module suggests plan dates from General settings, but rollout dates and manual admin-only plans still save explicit start and end dates on each plan.',
                'meta' => [
                    'Suggested start' => $suggestedDates['start']->format('Y-m-d'),
                    'Suggested end' => $suggestedDates['end']->format('Y-m-d'),
                    'Support note' => $generalSettings['active_template_support_note'] ?: 'Not set',
                ],
            ],
            [
                'title' => '6. Review Progression and Locking',
                'body' => 'Only one configured period can be open at a time. Period-scoped fields lock when that review closes, while plan-level fields lock once plan-level sign-off starts.',
                'meta' => [
                    'Configured periods' => $periodLabels,
                    'Window rules' => 'Derived from each template period open/close rule',
                ],
            ],
            [
                'title' => '7. Scoring and Summary Calculation',
                'body' => 'Review summaries and final plan summaries are calculated from the active template rating schemes, their scale definitions, weights, and rounding rules.',
                'meta' => [
                    'Rating schemes' => $ratingLabels,
                ],
            ],
            [
                'title' => '8. Signatures and Elevated Access',
                'body' => 'Approval steps are template-defined. Elevated PDP administrators can view templates, reports, and broader plan sets using the configured role and position allow-lists.',
                'meta' => [
                    'Approval chain' => $approvalLabels,
                    'Elevated positions' => implode(', ', $accessSettings['elevated_positions']),
                    'Elevated roles' => implode(', ', $accessSettings['elevated_roles']),
                ],
            ],
            [
                'title' => '9. PDF Output and Historical Audit',
                'body' => 'PDF rendering resolves the bound template definition, so output structure changes only when a plan is created from a different template version. Older plans keep their historical layout and workflow.',
                'meta' => [
                    'PDF source' => 'Bound template sections, periods, ratings, and approval steps',
                ],
            ],
        ];
    }

    private function formatRuleSummary(mixed $rule): string
    {
        if (!is_array($rule) || $rule === []) {
            return 'No explicit rule';
        }

        $parts = [];

        if (isset($rule['starts_at'])) {
            $parts[] = 'Starts at ' . Carbon::parse($rule['starts_at'])->format('Y-m-d');
        }

        if (isset($rule['ends_at'])) {
            $parts[] = 'Ends at ' . Carbon::parse($rule['ends_at'])->format('Y-m-d');
        }

        if (isset($rule['start_offset_days'])) {
            $parts[] = 'Start offset ' . (int) $rule['start_offset_days'] . ' day(s)';
        }

        if (isset($rule['end_offset_days'])) {
            $parts[] = 'End offset ' . (int) $rule['end_offset_days'] . ' day(s)';
        }

        if (isset($rule['must_close_previous'])) {
            $parts[] = ((bool) $rule['must_close_previous']) ? 'Requires prior review closure' : 'Prior review closure not required';
        }

        return $parts !== [] ? implode(' | ', $parts) : $this->formatConfigSummary($rule);
    }

    private function formatConfigSummary(mixed $config): string
    {
        if (!is_array($config) || $config === []) {
            return 'Not configured';
        }

        return collect($config)
            ->map(function ($value, $key): string {
                if (is_array($value)) {
                    return Str::headline((string) $key) . ': ' . implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value));
                }

                if (is_bool($value)) {
                    return Str::headline((string) $key) . ': ' . ($value ? 'Yes' : 'No');
                }

                return Str::headline((string) $key) . ': ' . (string) $value;
            })
            ->implode(' | ');
    }
}
