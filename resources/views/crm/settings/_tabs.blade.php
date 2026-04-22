@php($crmUser = auth()->user())
<div class="crm-tabs crm-tabs-top">
    @if ($crmUser->canManageCommercialSettings())
        <a href="{{ route('crm.settings.company-information') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'company-information'])>
            <i class="bx bx-buildings"></i>
            <span>Company information</span>
        </a>
        <a href="{{ route('crm.settings.branding') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'branding'])>
            <i class="bx bx-palette"></i>
            <span>Branding</span>
        </a>
    @endif
    @if ($crmUser->canManageCrmSettings())
        <a href="{{ route('crm.settings.index') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'overview'])>
            <i class="bx bx-slider-alt"></i>
            <span>Overview</span>
        </a>
        <a href="{{ route('crm.settings.sales-stages') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'sales-stages'])>
            <i class="bx bx-flag"></i>
            <span>Sales stages</span>
        </a>
        <a href="{{ route('crm.settings.imports') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'imports'])>
            <i class="bx bx-import"></i>
            <span>Imports</span>
        </a>
    @endif
    @if ($crmUser->canManageCommercialSettings())
        <a href="{{ route('crm.settings.commercial') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'commercial'])>
            <i class="bx bx-credit-card-front"></i>
            <span>Commercial</span>
        </a>
    @endif
</div>
