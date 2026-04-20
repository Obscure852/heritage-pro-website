<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Services\Pdp\PdpSectionEntryService;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Validation\ValidationException;

class PdpSectionEntryController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpSectionEntryService $sectionEntryService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function store(Request $request, PdpPlan $plan, string $sectionKey): RedirectResponse
    {
        $this->authorizePlanManage($plan, $request->user());

        try {
            $this->sectionEntryService->createEntry(
                $plan,
                $sectionKey,
                $request->user(),
                $this->resolvedValues($request)
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
                ->withErrors(['pdp' => $exception->getMessage()]);
        }

        return redirect()
            ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
            ->with('message', 'Section entry added successfully.');
    }

    public function update(Request $request, PdpPlan $plan, string $sectionKey, PdpPlanSectionEntry $entry): RedirectResponse
    {
        $this->authorizePlanManage($plan, $request->user());

        abort_unless($entry->pdp_plan_id === $plan->id, 404);

        try {
            $this->sectionEntryService->updateEntry(
                $plan,
                $sectionKey,
                $entry,
                $request->user(),
                $this->resolvedValues($request)
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
                ->withErrors(['pdp' => $exception->getMessage()]);
        }

        return redirect()
            ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
            ->with('message', 'Section entry updated successfully.');
    }

    public function destroy(Request $request, PdpPlan $plan, string $sectionKey, PdpPlanSectionEntry $entry): RedirectResponse
    {
        $this->authorizePlanManage($plan, $request->user());

        abort_unless($entry->pdp_plan_id === $plan->id, 404);

        try {
            $this->sectionEntryService->deleteEntry($plan, $sectionKey, $entry, $request->user());
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
                ->withErrors(['pdp' => $exception->getMessage()]);
        }

        return redirect()
            ->to(route('staff.pdp.plans.show', $plan) . '#section-' . $sectionKey)
            ->with('message', 'Section entry deleted successfully.');
    }

    private function resolvedValues(Request $request): array
    {
        $values = Arr::wrap($request->input('values', []));
        $commentBankSelections = Arr::wrap($request->input('comment_bank', []));

        foreach ($commentBankSelections as $fieldKey => $selectedComment) {
            if (!is_string($fieldKey)) {
                continue;
            }

            $selectedComment = is_scalar($selectedComment) ? trim((string) $selectedComment) : '';

            if ($selectedComment === '') {
                continue;
            }

            $currentValue = $values[$fieldKey] ?? null;
            $normalizedCurrentValue = is_scalar($currentValue) ? trim((string) $currentValue) : null;

            if ($normalizedCurrentValue === null || $normalizedCurrentValue === '' || $normalizedCurrentValue === $selectedComment) {
                $values[$fieldKey] = $selectedComment;
            }
        }

        return $values;
    }
}
