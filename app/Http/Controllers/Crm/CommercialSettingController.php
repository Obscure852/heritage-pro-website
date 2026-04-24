<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CommercialCurrencyUpsertRequest;
use App\Http\Requests\Crm\CommercialSettingUpdateRequest;
use App\Http\Requests\Crm\CrmUserBrandingUpdateRequest;
use App\Http\Requests\Crm\CrmUserCompanyInformationUpdateRequest;
use App\Http\Requests\Crm\ProductUnitUpsertRequest;
use App\Http\Requests\Crm\SectorUpsertRequest;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmCommercialSetting;
use App\Models\CrmProductUnit;
use App\Models\CrmSector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Crm\CrmUserMediaService;

class CommercialSettingController extends CrmController
{
    public function __construct(
        private readonly CrmUserMediaService $mediaService
    ) {
    }

    public function index(): View
    {
        $this->authorizeCommercialSettings();

        $activeSettingsTab = in_array(request()->query('tab'), ['currencies', 'units', 'sectors'], true)
            ? request()->query('tab')
            : 'defaults';

        return $this->renderIndex(activeSettingsTab: $activeSettingsTab);
    }

    public function legacyIndex(): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        return redirect()->route('crm.products.settings');
    }

    public function editCurrency(CrmCommercialCurrency $currency): View
    {
        $this->authorizeCommercialSettings();

        return $this->renderIndex(editCurrency: $currency, activeSettingsTab: 'currencies');
    }

    public function legacyEditCurrency(CrmCommercialCurrency $currency): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        return redirect()->route('crm.products.settings.edit-currency', $currency);
    }

    public function update(CommercialSettingUpdateRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $settings = $this->commercialSettings();
        $payload = $request->validated();

        $settings->update([
            'default_currency_id' => $payload['default_currency_id'],
            'quote_prefix' => $payload['quote_prefix'],
            'quote_next_sequence' => $payload['quote_next_sequence'],
            'invoice_prefix' => $payload['invoice_prefix'],
            'invoice_next_sequence' => $payload['invoice_next_sequence'],
            'default_tax_rate' => $payload['default_tax_rate'] ?? 0,
            'allow_line_discounts' => $request->boolean('allow_line_discounts'),
            'allow_document_discounts' => $request->boolean('allow_document_discounts'),
        ]);

        return redirect()
            ->route('crm.products.settings')
            ->with('crm_success', 'Commercial settings updated successfully.');
    }

    public function companyInformation(): View
    {
        $this->authorizeCommercialSettings();

        return view('crm.settings.company-information', [
            'activeSection' => 'company-information',
            'settings' => $this->commercialSettings(),
        ]);
    }

    public function updateCompanyInformation(CrmUserCompanyInformationUpdateRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $this->commercialSettings()->update($request->validated());

        return redirect()
            ->route('crm.settings.company-information')
            ->with('crm_success', 'Company information updated successfully.');
    }

    public function branding(): View
    {
        $this->authorizeCommercialSettings();

        return view('crm.settings.branding', [
            'activeSection' => 'branding',
            'settings' => $this->commercialSettings(),
        ]);
    }

    public function updateBranding(CrmUserBrandingUpdateRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $settings = $this->commercialSettings();
        $validated = $request->validated();

        DB::transaction(function () use ($settings, $validated) {
            $settings->update([
                'company_logo_path' => $this->mediaService->storeBrandingImageFromCroppedImage(
                    $validated['company_logo_cropped_image'] ?? null,
                    'branding/logo',
                    $settings->company_logo_path,
                    'company_logo_cropped_image'
                ),
                'login_image_path' => $this->mediaService->storeBrandingImageFromCroppedImage(
                    $validated['login_image_cropped_image'] ?? null,
                    'branding/login',
                    $settings->login_image_path,
                    'login_image_cropped_image'
                ),
            ]);
        });

        return redirect()
            ->route('crm.settings.branding')
            ->with('crm_success', 'Branding assets updated successfully.');
    }

    public function storeCurrency(CommercialCurrencyUpsertRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        CrmCommercialCurrency::query()->create($this->validatedCurrencyPayload($request));

        return redirect()
            ->route('crm.products.settings', ['tab' => 'currencies'])
            ->with('crm_success', 'Currency added successfully.');
    }

    public function updateCurrency(CommercialCurrencyUpsertRequest $request, CrmCommercialCurrency $currency): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $payload = $this->validatedCurrencyPayload($request);
        $settings = $this->commercialSettings();

        if ($settings->default_currency_id === $currency->id && ! $payload['is_active']) {
            return redirect()
                ->route('crm.products.settings.edit-currency', $currency)
                ->withErrors(['currency' => 'Select a different default currency before deactivating this one.'])
                ->withInput();
        }

        $currency->update($payload);

        return redirect()
            ->route('crm.products.settings.edit-currency', $currency)
            ->with('crm_success', 'Currency updated successfully.');
    }

    public function destroyCurrency(CrmCommercialCurrency $currency): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        if ($this->commercialSettings()->default_currency_id === $currency->id) {
            return redirect()
                ->route('crm.products.settings', ['tab' => 'currencies'])
                ->withErrors(['currency' => 'Select a different default currency before deleting this one.']);
        }

        $currency->update(['is_active' => false]);

        return redirect()
            ->route('crm.products.settings', ['tab' => 'currencies'])
            ->with('crm_success', 'Currency deactivated successfully.');
    }

    public function storeUnit(ProductUnitUpsertRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        CrmProductUnit::query()->create($this->validatedUnitPayload($request));

        return redirect()
            ->route('crm.products.settings', ['tab' => 'units'])
            ->with('crm_success', 'Product unit added successfully.');
    }

    public function editUnit(CrmProductUnit $unit): View
    {
        $this->authorizeCommercialSettings();

        return $this->renderIndex(editUnit: $unit, activeSettingsTab: 'units');
    }

    public function updateUnit(ProductUnitUpsertRequest $request, CrmProductUnit $unit): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $unit->update($this->validatedUnitPayload($request));

        return redirect()
            ->route('crm.products.settings.edit-unit', $unit)
            ->with('crm_success', 'Product unit updated successfully.');
    }

    public function destroyUnit(CrmProductUnit $unit): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $unit->update(['is_active' => false]);

        return redirect()
            ->route('crm.products.settings', ['tab' => 'units'])
            ->with('crm_success', 'Product unit deactivated successfully.');
    }

    public function storeSector(SectorUpsertRequest $request): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        CrmSector::query()->create($this->validatedSectorPayload($request));

        return redirect()
            ->route('crm.products.settings', ['tab' => 'sectors'])
            ->with('crm_success', 'Sector added successfully.');
    }

    public function editSector(CrmSector $sector): View
    {
        $this->authorizeCommercialSettings();

        return $this->renderIndex(editSector: $sector, activeSettingsTab: 'sectors');
    }

    public function updateSector(SectorUpsertRequest $request, CrmSector $sector): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $sector->update($this->validatedSectorPayload($request));

        return redirect()
            ->route('crm.products.settings.edit-sector', $sector)
            ->with('crm_success', 'Sector updated successfully.');
    }

    public function destroySector(CrmSector $sector): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $sector->update(['is_active' => false]);

        return redirect()
            ->route('crm.products.settings', ['tab' => 'sectors'])
            ->with('crm_success', 'Sector deactivated successfully.');
    }

    private function renderIndex(
        ?CrmCommercialCurrency $editCurrency = null,
        ?CrmProductUnit $editUnit = null,
        ?CrmSector $editSector = null,
        string $activeSettingsTab = 'defaults'
    ): View
    {
        $settings = $this->commercialSettings()->load('defaultCurrency');
        $currencies = CrmCommercialCurrency::query()
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();
        $units = CrmProductUnit::query()
            ->ordered()
            ->get();
        $sectors = CrmSector::query()
            ->ordered()
            ->get();

        return view('crm.products.settings', [
            'activeSection' => 'settings',
            'activeSettingsTab' => $activeSettingsTab,
            'settings' => $settings,
            'currencies' => $currencies,
            'editCurrency' => $editCurrency,
            'units' => $units,
            'editUnit' => $editUnit,
            'sectors' => $sectors,
            'editSector' => $editSector,
        ]);
    }

    private function commercialSettings(): CrmCommercialSetting
    {
        $settings = CrmCommercialSetting::query()->first();

        if ($settings !== null) {
            return $settings;
        }

        $defaultCurrencyId = CrmCommercialCurrency::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->value('id');

        return CrmCommercialSetting::query()->create([
            'default_currency_id' => $defaultCurrencyId,
            'company_name' => 'Heritage Pro',
            'quote_prefix' => 'QT',
            'quote_next_sequence' => 1,
            'invoice_prefix' => 'INV',
            'invoice_next_sequence' => 1,
            'default_tax_rate' => 0,
            'allow_line_discounts' => true,
            'allow_document_discounts' => true,
        ]);
    }

    private function validatedCurrencyPayload(CommercialCurrencyUpsertRequest $request): array
    {
        $payload = $request->validated();
        $payload['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        return $payload;
    }

    private function validatedUnitPayload(ProductUnitUpsertRequest $request): array
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        return $payload;
    }

    private function validatedSectorPayload(SectorUpsertRequest $request): array
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        return $payload;
    }
}
