<div class="crm-tabs crm-tabs-top">
    <a href="{{ route('crm.products.catalog.index') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'catalog'])>
        <i class="bx bx-package"></i>
        <span>Catalog</span>
    </a>
    <a href="{{ route('crm.products.quotes.index') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'quotes'])>
        <i class="bx bx-receipt"></i>
        <span>Quotes</span>
    </a>
    <a href="{{ route('crm.products.invoices.index') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'invoices'])>
        <i class="bx bx-file"></i>
        <span>Invoices</span>
    </a>
</div>
