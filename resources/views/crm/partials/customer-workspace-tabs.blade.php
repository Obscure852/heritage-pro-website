<div class="crm-tabs">
    <a href="{{ route('crm.leads.index') }}" @class(['crm-tab', 'is-active' => request()->routeIs('crm.leads.*')])>Leads</a>
    <a href="{{ route('crm.customers.index') }}" @class(['crm-tab', 'is-active' => request()->routeIs('crm.customers.*')])>Customers</a>
</div>
