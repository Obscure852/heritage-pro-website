<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\SalesStageUpsertRequest;
use App\Models\SalesStage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SettingController extends CrmController
{
    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->crmUser()->canManageCrmSettings()) {
            $this->authorizeCommercialSettings();

            return redirect()->route('crm.settings.commercial');
        }

        return $this->renderIndex($request, 'overview');
    }

    public function salesStages(Request $request): View
    {
        return $this->renderIndex($request, 'sales-stages');
    }

    public function createSalesStage(): View
    {
        $this->authorizeAdminSettings();

        return view('crm.settings.sales-stages.create', [
            'activeSection' => 'sales-stages',
            'stage' => null,
            'defaultPosition' => SalesStage::query()->count() + 1,
        ]);
    }

    public function editSalesStage(SalesStage $salesStage): View
    {
        $this->authorizeAdminSettings();

        return view('crm.settings.sales-stages.edit', [
            'activeSection' => 'sales-stages',
            'stage' => $salesStage,
        ]);
    }

    public function storeSalesStage(SalesStageUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        SalesStage::query()->create([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueStageSlug($request->validated('name')),
            'position' => $request->validated('position'),
            'is_active' => $request->boolean('is_active'),
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
        ]);

        return redirect()
            ->route('crm.settings.sales-stages')
            ->with('crm_success', 'Sales stage created successfully.');
    }

    public function updateSalesStage(SalesStageUpsertRequest $request, SalesStage $salesStage): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $salesStage->update([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueStageSlug($request->validated('name'), $salesStage->id),
            'position' => $request->validated('position'),
            'is_active' => $request->boolean('is_active'),
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
        ]);

        return redirect()
            ->route('crm.settings.sales-stages.edit', $salesStage)
            ->with('crm_success', 'Sales stage updated successfully.');
    }

    public function destroySalesStage(SalesStage $salesStage): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $salesStage->delete();

        return redirect()
            ->route('crm.settings.sales-stages')
            ->with('crm_success', 'Sales stage deleted permanently.');
    }

    private function renderIndex(Request $request, string $activeSection): View
    {
        $this->authorizeAdminSettings();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'active' => (string) $request->query('active', ''),
            'terminal' => (string) $request->query('terminal', ''),
        ];

        $stages = SalesStage::query()
            ->withCount('requests')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['q'] . '%');
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['active'] === '1');
            })
            ->when($filters['terminal'] === 'won', function ($query) {
                $query->where('is_won', true);
            })
            ->when($filters['terminal'] === 'lost', function ($query) {
                $query->where('is_lost', true);
            })
            ->orderBy('position')
            ->get();

        return view('crm.settings.index', [
            'activeSection' => $activeSection,
            'stages' => $stages,
            'filters' => $filters,
        ]);
    }

    private function uniqueStageSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'stage';
        $slug = $base;
        $counter = 2;

        while (
            SalesStage::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, function ($query) use ($ignoreId) {
                    $query->whereKeyNot($ignoreId);
                })
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
