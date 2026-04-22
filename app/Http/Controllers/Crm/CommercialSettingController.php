<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CommercialCurrencyUpsertRequest;
use App\Http\Requests\Crm\CommercialSettingUpdateRequest;
use App\Http\Requests\Crm\CrmUserBrandingUpdateRequest;
use App\Http\Requests\Crm\CrmUserCompanyInformationUpdateRequest;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmCommercialSetting;
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

        return $this->renderIndex();
    }

    public function editCurrency(CrmCommercialCurrency $currency): View
    {
        $this->authorizeCommercialSettings();

        return $this->renderIndex($currency);
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
            ->route('crm.settings.commercial')
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
            ->route('crm.settings.commercial')
            ->with('crm_success', 'Currency added successfully.');
    }

    public function updateCurrency(CommercialCurrencyUpsertRequest $request, CrmCommercialCurrency $currency): RedirectResponse
    {
        $this->authorizeCommercialSettings();

        $payload = $this->validatedCurrencyPayload($request);
        $settings = $this->commercialSettings();

        if ($settings->default_currency_id === $currency->id && ! $payload['is_active']) {
            return redirect()
                ->route('crm.settings.commercial.edit-currency', $currency)
                ->withErrors(['currency' => 'Select a different default currency before deactivating this one.'])
                ->withInput();
        }

        $currency->update($payload);

        return redirect()
            ->route('crm.settings.commercial.edit-currency', $currency)
            ->with('crm_success', 'Currency updated successfully.');
    }

    private function renderIndex(?CrmCommercialCurrency $editCurrency = null): View
    {
        $settings = $this->commercialSettings()->load('defaultCurrency');
        $currencies = CrmCommercialCurrency::query()
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        return view('crm.settings.commercial', [
            'activeSection' => 'commercial',
            'settings' => $settings,
            'currencies' => $currencies,
            'editCurrency' => $editCurrency,
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
}
